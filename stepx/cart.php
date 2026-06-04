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
    } else {
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

            // Alamat utama pembeli
            $st = $db->prepare('SELECT id FROM alamat_pembeli WHERE user_id = ? AND is_utama = 1 LIMIT 1');
            $st->bind_param('i', $userId);
            $st->execute();
            $alamat   = $st->get_result()->fetch_assoc();
            $st->close();
            $alamatId = $alamat ? (int)$alamat['id'] : 1;

            // Insert pesanan
            $st = $db->prepare('
                INSERT INTO pesanan
                    (kode_pesanan, pembeli_id, alamat_id, subtotal, ongkos_kirim, diskon,
                     total_bayar, metode_bayar, status_bayar, status_pesanan)
                VALUES (?, ?, ?, ?, ?, ?, ?, "transfer_bank", "menunggu", "proses")
            ');
            $st->bind_param('siidddd',
                $kodePesanan, $userId, $alamatId,
                $subtotal, $ongkir, $diskon, $totalBayar
            );
            $st->execute();
            $pesananId = (int)$db->insert_id;
            $st->close();

            // Insert detail_pesanan + kurangi stok
            foreach ($cartRows as $row) {
                // Semua nilai ke variabel dulu — bind_param butuh referensi, bukan ekspresi cast
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
                    $pesananId,
                    $dProdukId,
                    $dPenjualId,
                    $dNama,
                    $dHarga,
                    $dHpp,
                    $dQty,
                    $dSubtotal
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

// Hitung total
$total = 0;
foreach ($cartItems as &$item) {
    $item['sub'] = (float)$item['harga_jual'] * (int)$item['qty'];
    $total += $item['sub'];
}
unset($item);

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

      <!-- SUMMARY -->
      <div class="cart-summary">
        <h3>Ringkasan</h3>
        <?php foreach ($cartItems as $item): ?>
          <div class="summary-row">
            <span><?= htmlspecialchars($item['nama']) ?> ×<?= (int)$item['qty'] ?></span>
            <span><?= rp($item['sub']) ?></span>
          </div>
        <?php endforeach; ?>
        <div style="margin-top:12px">
          <div class="summary-row" style="border:none;padding-top:0">
            <span style="color:var(--muted);font-size:13px">Total Pembayaran</span>
          </div>
          <div class="summary-total"><?= rp($total) ?></div>
        </div>
        <form method="POST" action="cart.php">
          <input type="hidden" name="action" value="checkout">
          <button type="submit" class="btn-primary full">Proses Checkout →</button>
        </form>
      </div>

    </div>
  <?php endif; ?>
</div>

<script>
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