<?php
//  StepX Store — cart.php (Keranjang Belanja + Checkout)
//  Cart disimpan di tabel `keranjang` (DB), bukan session
require_once __DIR__ . '/includes/config.php';
requireLogin();

$user = currentUser();
if ($user['role'] !== 'pembeli') {
    header('Location: catalog.php');
    exit;
}

$db     = getDB();
$userId = (int)$user['id'];
$error   = '';
$success = '';

// CHECKOUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'checkout') {
  // Ambil semua item keranjang dari DB
  $st = $db->prepare('
    SELECT k.produk_id, k.qty,
      p.nama, p.harga_jual, p.hpp, p.stok, p.penjual_id
    FROM keranjang k
    JOIN produk p ON p.id = k.produk_id
    WHERE k.user_id = ?
  ');
  $st->bind_param('i', $userId);
  $st->execute();
  $cartRows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
  $st->close();

  if (empty($cartRows)) {
    $error = 'Keranjang kosong, tidak ada yang di-checkout!';
  }else{
    // Validasi alamat & metode bayar
    $alamatId    = (int)($_POST['alamat_id'] ?? 0);
    $metodeBayar = $_POST['metode_bayar'] ?? '';
    $metodeBayarValid = ['transfer_bank', 'cod', 'ewallet', 'kartu_kredit'];

    if ($alamatId <= 0) {
      $error = 'Pilih alamat pengiriman terlebih dahulu!';
    }elseif(!in_array($metodeBayar, $metodeBayarValid)) {
      $error = 'Pilih metode pembayaran terlebih dahulu!';
    }else{
      // Verifikasi alamat milik user ini
      $st = $db->prepare('SELECT id FROM alamat_pembeli WHERE id = ? AND user_id = ? LIMIT 1');
      $st->bind_param('ii', $alamatId, $userId);
      $st->execute();
      $alamatCek = $st->get_result()->fetch_assoc();
      $st->close();

      if (!$alamatCek) {
        $error = 'Alamat tidak valid!';
      }else{
        // Validasi stok
        $ok = true;
        foreach ($cartRows as $row) {
          if ((int)$row['stok'] < (int)$row['qty']) {
            $error = 'Stok "' . htmlspecialchars($row['nama']) . '" tidak cukup! (tersisa: ' . $row['stok'] . ')';
            $ok = false;
            break;
          }
        }

          if ($ok) {
            // Hitung total
            $subtotal = 0;
            foreach ($cartRows as $row) {
                $subtotal += (float)$row['harga_jual'] * (int)$row['qty'];
            }
            $ongkir     = 15000;
            $diskon     = 0;
            $totalBayar = $subtotal + $ongkir - $diskon;

            // Kode pesanan unik
            $kodePesanan = 'ORD-' . strtoupper(substr(uniqid(), -8));

            // Insert pesanan
            $st = $db->prepare('
              INSERT INTO pesanan
                (kode_pesanan, pembeli_id, alamat_id, subtotal, ongkos_kirim, diskon,
                  total_bayar, metode_bayar, status_bayar, status_pesanan)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, "menunggu", "proses")
            ');
            $st->bind_param('siidddds',
              $kodePesanan, $userId, $alamatId,
              $subtotal, $ongkir, $diskon, $totalBayar, $metodeBayar
            );
            $st->execute();
            $pesananId = (int)$db->insert_id;
            $st->close();

            // Insert detail_pesanan + kurangi stok
            foreach ($cartRows as $row) {
              $dProdukId  = (int)$row['produk_id'];
              $dPenjualId = (int)$row['penjual_id'];
              $dNama      = (string)$row['nama'];
              $dHarga     = (float)$row['harga_jual'];
              $dHpp       = (float)$row['hpp'];
              $dQty       = (int)$row['qty'];
              $dSubtotal  = $dHarga * $dQty;

              $st = $db->prepare('
                INSERT INTO detail_pesanan
                  (pesanan_id, produk_id, penjual_id, nama_produk,
                    harga_satuan, hpp_satuan, ukuran, qty, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, NULL, ?, ?)
              ');
              $st->bind_param('iiisddid',
                $pesananId, $dProdukId, $dPenjualId, $dNama,
                $dHarga, $dHpp, $dQty, $dSubtotal
              );
              $st->execute();
              $st->close();

              // Kurangi stok produk
              $st = $db->prepare('UPDATE produk SET stok = stok - ? WHERE id = ?');
              $st->bind_param('ii', $dQty, $dProdukId);
              $st->execute();
              $st->close();
            }
          // Kosongkan keranjang di DB
          $st = $db->prepare('DELETE FROM keranjang WHERE user_id = ?');
          $st->bind_param('i', $userId);
          $st->execute();
          $st->close();

          $success = '✅ Checkout berhasil! Pesanan <strong>' . htmlspecialchars($kodePesanan) . '</strong> sedang diproses.';
        }
      }
    }
  }
}

// Ambil item keranjang dari DB untuk ditampilkan
$st = $db->prepare('
  SELECT k.produk_id AS id, k.qty,
    p.nama, p.brand, p.harga_jual, p.hpp, p.stok, p.emoji,
    ka.nama AS kategori_nama
  FROM keranjang k
  JOIN produk p  ON p.id  = k.produk_id
  LEFT JOIN kategori ka ON ka.id = p.kategori_id
  WHERE k.user_id = ?
  ORDER BY k.added_at ASC
');
$st->bind_param('i', $userId);
$st->execute();
$cartItems = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

// Ambil semua alamat user
$st = $db->prepare('SELECT * FROM alamat_pembeli WHERE user_id = ? ORDER BY is_utama DESC, id ASC');
$st->bind_param('i', $userId);
$st->execute();
$daftarAlamat = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

// Hitung total
$total = 0;
foreach ($cartItems as &$item) {
    $item['sub'] = (float)$item['harga_jual'] * (int)$item['qty'];
    $total += $item['sub'];
}
unset($item);

$ongkir     = 15000;
$totalBayar = $total + $ongkir;

$pageTitle  = 'Keranjang Belanja';
$activePage = 'cart';
require_once __DIR__ . '/includes/header.php';
?>

<div class="cart-wrap">
  <a class="back-link" href="catalog.php" style="display:inline-flex;align-items:center;gap:6px;padding-top:8px">← Lanjut Belanja</a>
  <h2>Keranjang Belanja</h2>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>

  <?php if (empty($cartItems)): ?>
    <div class="empty-state">
      <div class="icon">🛒</div>
      <p>Keranjang belanja kosong.<br>
        <a href="catalog.php" style="color:var(--text)">Lanjut belanja →</a>
      </p>
    </div>
  <?php else: ?>
    <div class="cart-layout">

      <!-- ITEM LIST -->
      <div class="cart-items">
        <?php foreach ($cartItems as $item): ?>
        <div class="cart-item" id="cart-row-<?= (int)$item['id'] ?>">
          <div class="cart-item-emoji"><?= htmlspecialchars($item['emoji'] ?: '👟') ?></div>
          <div>
            <div class="cart-item-name"><?= htmlspecialchars($item['nama']) ?></div>
            <div class="cart-item-brand"><?= htmlspecialchars($item['brand']) ?></div>
            <div class="cart-item-price"><?= rp($item['harga_jual']) ?> × <?= (int)$item['qty'] ?></div>
          </div>
          <div class="cart-item-sub"><?= rp($item['sub']) ?></div>
          <button class="btn-danger" style="padding:6px 10px"
            onclick="removeItem(<?= (int)$item['id'] ?>)">Batal</button>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- SUMMARY + CHECKOUT FORM -->
      <div class="cart-summary">
        <h3>Ringkasan</h3>
        <?php foreach ($cartItems as $item): ?>
          <div class="summary-row">
            <span><?= htmlspecialchars($item['nama']) ?> ×<?= (int)$item['qty'] ?></span>
            <span><?= rp($item['sub']) ?></span>
          </div>
        <?php endforeach; ?>
        <div class="summary-row" style="margin-top:8px">
          <span style="color:var(--muted);font-size:13px">Ongkos Kirim</span>
          <span style="font-size:13px"><?= rp($ongkir) ?></span>
        </div>
        <div style="margin-top:8px">
          <div class="summary-row" style="border:none;padding-top:0">
            <span style="color:var(--muted);font-size:13px">Total Pembayaran</span>
          </div>
          <div class="summary-total"><?= rp($totalBayar) ?></div>
        </div>

        <form method="POST" action="cart.php" id="form-checkout">
          <input type="hidden" name="action" value="checkout">
          <input type="hidden" name="alamat_id" id="selected-alamat-id" value="">
          <input type="hidden" name="metode_bayar" id="selected-metode-bayar" value="">

          <!-- PILIH ALAMAT -->
          <div style="margin-top:20px">
            <div style="font-weight:600;font-size:14px;margin-bottom:10px">📍 Alamat Pengiriman</div>
            <?php if (empty($daftarAlamat)): ?>
              <div style="font-size:13px;color:var(--muted);padding:10px;border:1px dashed var(--border);border-radius:8px;text-align:center">
                Belum ada alamat tersimpan.<br>
                <a href="profile.php" style="color:green">+ Tambah alamat di Profil</a>
              </div>
            <?php else: ?>
              <div id="alamat-list" style="display:flex;flex-direction:column;gap:8px">
                <?php foreach ($daftarAlamat as $al): ?>
                  <div class="alamat-card <?= $al['is_utama'] ? 'selected' : '' ?>"
                       data-id="<?= $al['id'] ?>"
                       onclick="pilihAlamat(this, <?= $al['id'] ?>)"
                       style="border:2px solid <?= $al['is_utama'] ? 'green' : 'var(--border)' ?>;border-radius:10px;padding:12px 14px;cursor:pointer;transition:border-color .2s">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                      <span style="font-size:12px;background:var(--surface2);padding:2px 8px;border-radius:20px;font-weight:500"><?= htmlspecialchars($al['label']) ?></span>
                      <?php if ($al['is_utama']): ?>
                        <span style="font-size:11px;color:green;font-weight:600">✓ Utama</span>
                      <?php endif; ?>
                    </div>
                    <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($al['nama_penerima']) ?></div>
                    <div style="font-size:13px;color:var(--muted)"><?= htmlspecialchars($al['no_hp']) ?></div>
                    <div style="font-size:13px;margin-top:2px"><?= htmlspecialchars($al['alamat_lengkap']) ?>, <?= htmlspecialchars($al['kota']) ?></div>
                    <?php if ($al['kode_pos']): ?>
                      <div style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($al['provinsi']) ?> <?= htmlspecialchars($al['kode_pos']) ?></div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- PILIH METODE BAYAR -->
          <div style="margin-top:20px">
            <div style="font-weight:600;font-size:14px;margin-bottom:10px">💳 Metode Pembayaran</div>
            <div id="metode-list" style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
              <?php
              $metodeBayarList = [
                  'transfer_bank' => ['label' => 'Transfer Bank', 'icon' => '🏦'],
                  'ewallet'       => ['label' => 'E-Wallet',      'icon' => '📱'],
                  'cod'           => ['label' => 'Bayar di Tempat (COD)', 'icon' => '🚪'],
                  'kartu_kredit'  => ['label' => 'Kartu Kredit',  'icon' => '💳'],
              ];
              foreach ($metodeBayarList as $kode => $info): ?>
                <div class="metode-card"
                     data-kode="<?= $kode ?>"
                     onclick="pilihMetode(this, '<?= $kode ?>')"
                     style="border:2px solid var(--border);border-radius:10px;padding:10px 12px;cursor:pointer;text-align:center;transition:border-color .2s">
                  <div style="font-size:20px"><?= $info['icon'] ?></div>
                  <div style="font-size:12px;font-weight:500;margin-top:4px"><?= $info['label'] ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <button type="submit" class="btn-primary full" id="btn-checkout"
            style="margin-top:20px;opacity:.5;cursor:not-allowed" disabled>
            Proses Checkout →
          </button>
        </form>
      </div>

    </div>
  <?php endif; ?>
</div>

<script>
// Set alamat default (utama) kalau ada
<?php if (!empty($daftarAlamat)): ?>
  const alamatUtama = document.querySelector('.alamat-card.selected');
  if (alamatUtama) {
    document.getElementById('selected-alamat-id').value = alamatUtama.dataset.id;
  }
<?php endif; ?>

function pilihAlamat(el, id) {
  document.querySelectorAll('.alamat-card').forEach(c => {
    c.style.borderColor = 'var(--border)';
  });
  el.style.borderColor = 'green';
  document.getElementById('selected-alamat-id').value = id;
  cekFormReady();
}

function pilihMetode(el, kode) {
  document.querySelectorAll('.metode-card').forEach(c => {
    c.style.borderColor = 'var(--border)';
    c.style.background  = '';
  });
  el.style.borderColor = 'green';
  el.style.background  = 'rgba(var(--accent-rgb, 10,10,10),.05)';
  document.getElementById('selected-metode-bayar').value = kode;
  cekFormReady();
}

function cekFormReady() {
  const alamat = document.getElementById('selected-alamat-id').value;
  const metode = document.getElementById('selected-metode-bayar').value;
  const btn    = document.getElementById('btn-checkout');
  if (alamat && metode) {
    btn.disabled = false;
    btn.style.opacity = '1';
    btn.style.cursor  = 'pointer';
  } else {
    btn.disabled = true;
    btn.style.opacity = '0.5';
    btn.style.cursor  = 'not-allowed';
  }
}

// Jalankan sekali waktu load (kalau alamat utama sudah ter-select)
cekFormReady();

function removeItem(produkId) {
  fetch('cart_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=remove&id=' + produkId
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) location.reload();
    else toast(d.msg, false);
  });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>