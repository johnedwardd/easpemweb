<?php
//  StepX Store — laba.php (Laporan Laba Penjual / Admin)
require_once __DIR__ . '/includes/config.php';
requireLogin();

$user = currentUser();
if ($user['role'] === 'pembeli') {
  header('Location: catalog.php');
  exit;
}

$db = getDB();

//  Handle POST: update status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
  $pesananId    = intval($_POST['pesanan_id'] ?? 0);
  $statusBaru   = $_POST['status_baru'] ?? '';
  $allowedStatus = ['proses', 'dikirim', 'selesai'];

  if (!in_array($statusBaru, $allowedStatus) || $pesananId <= 0) {
    $_SESSION['flash'] = ['msg' => 'Status tidak valid!', 'ok' => false];
    header('Location: laba.php');
    exit;
  }

  // Penjual hanya boleh update pesanan milik produknya sendiri
  if ($user['role'] === 'penjual') {
    $stmt = $db->prepare(
      'SELECT COUNT(*) AS cnt FROM detail_pesanan WHERE pesanan_id = ? AND penjual_id = ?'
    );
    $stmt->bind_param('ii', $pesananId, $user['id']);
    $stmt->execute();
    $cek = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ((int)$cek['cnt'] === 0) {
      $_SESSION['flash'] = ['msg' => 'Akses ditolak!', 'ok' => false];
      header('Location: laba.php');
      exit;
    }
  }
  $stmt = $db->prepare('UPDATE pesanan SET status_pesanan = ? WHERE id = ?');
  $stmt->bind_param('si', $statusBaru, $pesananId);
  $stmt->execute();
  $stmt->close();

  $_SESSION['flash'] = ['msg' => 'Status pesanan berhasil diperbarui!', 'ok' => true];
  header('Location: laba.php');
  exit;
}

//  Ambil data laporan
if ($user['role'] === 'admin') {
  $rows = $db->query(
    'SELECT
        dp.id,
        dp.pesanan_id,
        dp.produk_id,
        dp.nama_produk,
        dp.harga_satuan,
        dp.hpp_satuan,
        dp.qty,
        dp.subtotal,
        dp.ukuran,
        pr.brand,
        pr.emoji,
        u.nama  AS penjual_nama,
        p.kode_pesanan,
        p.status_pesanan
      FROM detail_pesanan dp
      JOIN produk  pr ON pr.id = dp.produk_id
      JOIN users   u  ON u.id  = dp.penjual_id
      JOIN pesanan p  ON p.id  = dp.pesanan_id
      ORDER BY dp.id DESC'
  )->fetch_all(MYSQLI_ASSOC);
} else {
  $stmt = $db->prepare(
    'SELECT
        dp.id,
        dp.pesanan_id,
        dp.produk_id,
        dp.nama_produk,
        dp.harga_satuan,
        dp.hpp_satuan,
        dp.qty,
        dp.subtotal,
        dp.ukuran,
        pr.brand,
        pr.emoji,
        p.kode_pesanan,
        p.status_pesanan
      FROM detail_pesanan dp
      JOIN produk  pr ON pr.id = dp.produk_id
      JOIN pesanan p  ON p.id  = dp.pesanan_id
      WHERE dp.penjual_id = ?
      ORDER BY dp.id DESC'
  );
  $stmt->bind_param('i', $user['id']);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}

$totalOmset = 0;
$totalLaba  = 0;
foreach ($rows as $r) {
  $totalOmset += (float)$r['subtotal'];
  $totalLaba  += ((float)$r['harga_satuan'] - (float)$r['hpp_satuan']) * (int)$r['qty'];
}

// Helper: status badge style
function statusStyle(string $s): array {
  return match($s) {
    'selesai'    => ['bg' => '#EDFFF2', 'color' => '#1A7F40', 'border' => '#B6F0C9'],
    'dikirim'    => ['bg' => '#EEF0FF', 'color' => '#4040CC', 'border' => '#C5C9FF'],
    'dikemas'    => ['bg' => '#FFFAEB', 'color' => '#A06300', 'border' => '#FFE5A0'],
    'proses'     => ['bg' => '#F5F4F0', 'color' => '#6B6B6B', 'border' => '#DCDCDC'],
    'dibatalkan' => ['bg' => '#FFF0EF', 'color' => '#FF3A2D', 'border' => '#FFCFCC'],
    default      => ['bg' => '#F5F4F0', 'color' => '#6B6B6B', 'border' => '#DCDCDC'],
  };
}

// Helper: opsi status berikutnya yang bisa dipilih
function nextStatusOptions(string $current): array {
  $all = ['proses', 'dikirim', 'selesai'];
  return array_filter($all, fn($s) => $s !== $current);
}

$colSpan = $user['role'] === 'admin' ? 12 : 11;

$pageTitle  = 'Laporan Laba';
$activePage = 'laba';
require_once __DIR__ . '/includes/header.php';
?>

<div class="laba-wrap">
  <a class="back-link" href="catalog.php" style="display:inline-flex;align-items:center;gap:6px;padding-top:8px">← Kembali</a>
  <h2>Laporan Laba</h2>

  <div class="laba-table-wrap">
    <table class="laba-table">
      <thead>
        <tr>
          <th>Kode Pesanan</th>
          <th>Nama Sepatu</th>
          <th>Brand</th>
          <?php if ($user['role'] === 'admin'): ?><th>Penjual</th><?php endif; ?>
          <th>Ukuran</th>
          <th>Harga Satuan</th>
          <th>HPP Satuan</th>
          <th>Qty</th>
          <th>Subtotal</th>
          <th>Laba Bersih</th>
          <th>Status</th>
          <th>Ubah Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="<?= $colSpan ?>"
                style="text-align:center;padding:32px;color:var(--muted)">
              Belum ada transaksi
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($rows as $r):
            $laba    = ((float)$r['harga_satuan'] - (float)$r['hpp_satuan']) * (int)$r['qty'];
            $st      = statusStyle($r['status_pesanan']);
            $options = nextStatusOptions($r['status_pesanan']);
            $dropId  = 'dd-' . $r['pesanan_id'] . '-' . $r['id'];
          ?>
          <tr id="row-<?= $r['pesanan_id'] ?>">
            <td style="font-weight:500"><?= htmlspecialchars($r['kode_pesanan']) ?></td>
            <td><?= htmlspecialchars($r['emoji'] ?? '') ?> <?= htmlspecialchars($r['nama_produk']) ?></td>
            <td><?= htmlspecialchars($r['brand']) ?></td>
            <?php if ($user['role'] === 'admin'): ?>
              <td><?= htmlspecialchars($r['penjual_nama']) ?></td>
            <?php endif; ?>
            <td><?= htmlspecialchars($r['ukuran'] ?? '-') ?></td>
            <td><?= rp($r['harga_satuan']) ?></td>
            <td><?= rp($r['hpp_satuan']) ?></td>
            <td><?= (int)$r['qty'] ?> pcs</td>
            <td><?= rp($r['subtotal']) ?></td>
            <td class="profit-val"><?= rp($laba) ?></td>

            <!-- Status badge -->
            <td>
              <span class="status-pill"
                style="background:<?= $st['bg'] ?>;color:<?= $st['color'] ?>;border-color:<?= $st['border'] ?>">
                <?= htmlspecialchars($r['status_pesanan']) ?>
              </span>
            </td>

            <!-- Ubah Status dropdown -->
            <td>
              <?php if ($r['status_pesanan'] === 'dibatalkan'): ?>
                <span style="font-size:12px;color:var(--muted)">—</span>
              <?php elseif ($r['status_pesanan'] === 'selesai'): ?>
                <span style="font-size:12px;color:#1A7F40;font-weight:500">✓ Selesai</span>
              <?php else: ?>
                <div class="status-dropdown" id="<?= $dropId ?>">
                  <button type="button" class="btn-ghost"
                    style="display:flex;align-items:center;gap:6px;font-size:12px"
                    onclick="toggleDropdown('<?= $dropId ?>')">
                    Ubah <span class="chevron">▼</span>
                  </button>
                  <div class="status-dropdown-menu" id="menu-<?= $dropId ?>">
                    <div class="menu-label">Ganti ke:</div>
                    <?php foreach ($options as $opt):
                      $os = statusStyle($opt);
                    ?>
                    <button type="button"
                      onclick="updateStatus(<?= $r['pesanan_id'] ?>, '<?= $opt ?>', this)"
                      style="color:<?= $os['color'] ?>">
                      <?= ucfirst($opt) ?>
                    </button>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="laba-summary">
    <div class="summary-card">
      <div class="label">Total Omset Kotor</div>
      <div class="value"><?= rp($totalOmset) ?></div>
    </div>
    <div class="summary-card">
      <div class="label">Total Laba Bersih</div>
      <div class="value green"><?= rp($totalLaba) ?></div>
    </div>
  </div>
</div>

<!-- Modal konfirmasi -->
<div class="modal-overlay" id="modal-status">
  <div class="modal-box">
    <h3>Konfirmasi Ubah Status</h3>
    <p id="modal-status-text"></p>
    <div class="modal-actions">
      <button class="btn-primary" id="modal-confirm-btn">Ya, Ubah</button>
      <button class="btn-ghost" onclick="closeStatusModal()">Batal</button>
    </div>
  </div>
</div>

<script>
// Dropdown toggle 
function toggleDropdown(id) {
  const dd   = document.getElementById(id);
  const menu = document.getElementById('menu-' + id);
  const isOpen = menu.classList.contains('open');
  // Tutup semua
  document.querySelectorAll('.status-dropdown-menu.open').forEach(m => m.classList.remove('open'));
  document.querySelectorAll('.status-dropdown.open').forEach(d => d.classList.remove('open'));
  if (!isOpen) {
    menu.classList.add('open');
    dd.classList.add('open');
  }
}
// Tutup dropdown kalau klik di luar
document.addEventListener('click', e => {
  if (!e.target.closest('.status-dropdown')) {
    document.querySelectorAll('.status-dropdown-menu.open').forEach(m => m.classList.remove('open'));
    document.querySelectorAll('.status-dropdown.open').forEach(d => d.classList.remove('open'));
  }
});

// Update status via AJAX
let _pendingPesananId  = null;
let _pendingStatusBaru = null;
let _pendingBtn        = null;

const labelMap = { proses: 'Proses', dikirim: 'Dikirim', selesai: 'Selesai' };
const styleMap = {
  selesai:    { bg:'#EDFFF2', color:'#1A7F40', border:'#B6F0C9' },
  dikirim:    { bg:'#EEF0FF', color:'#4040CC', border:'#C5C9FF' },
  proses:     { bg:'#F5F4F0', color:'#6B6B6B', border:'#DCDCDC' },
  dibatalkan: { bg:'#FFF0EF', color:'#FF3A2D', border:'#FFCFCC' },
};

function updateStatus(pesananId, statusBaru, btn) {
  _pendingPesananId  = pesananId;
  _pendingStatusBaru = statusBaru;
  _pendingBtn        = btn;

  document.getElementById('modal-status-text').textContent =
    'Ubah status pesanan ini menjadi "' + labelMap[statusBaru] + '"?';
  document.getElementById('modal-status').classList.add('open');

  // Tutup dropdown
  document.querySelectorAll('.status-dropdown-menu.open').forEach(m => m.classList.remove('open'));
  document.querySelectorAll('.status-dropdown.open').forEach(d => d.classList.remove('open'));
}

document.getElementById('modal-confirm-btn').addEventListener('click', function() {
  if (!_pendingPesananId) return;
  this.textContent = 'Menyimpan...';
  this.disabled    = true;

  const fd = new FormData();
  fd.append('action',      'update_status');
  fd.append('pesanan_id',  _pendingPesananId);
  fd.append('status_baru', _pendingStatusBaru);

  fetch('laba.php', { method: 'POST', body: fd })
    .then(r => r.text())
    .then(() => {
      // Update badge status di baris tabel tanpa reload
      const row = document.getElementById('row-' + _pendingPesananId);
      if (row) {
        const badge = row.querySelector('.status-pill');
        const s = styleMap[_pendingStatusBaru] || styleMap['proses'];
        badge.textContent = labelMap[_pendingStatusBaru];
        badge.style.background   = s.bg;
        badge.style.color        = s.color;
        badge.style.borderColor  = s.border;

        // Update kolom Ubah Status
        const ddCell = row.querySelector('td:last-child');
        if (_pendingStatusBaru === 'selesai') {
          ddCell.innerHTML = '<span style="font-size:12px;color:#1A7F40;font-weight:500">✓ Selesai</span>';
        } else {
          // Refresh dropdown options (hapus opsi yang sudah jadi status sekarang)
          const menus = row.querySelectorAll('.status-dropdown-menu button[type=button]');
          menus.forEach(b => {
            // sembunyikan opsi yang sama dengan status baru
            const txt = b.textContent.trim().toLowerCase();
            if (txt === _pendingStatusBaru) b.style.display = 'none';
            // tampilkan kembali opsi lainnya
            else b.style.display = '';
          });
        }
      }
      toast('Status berhasil diubah ke ' + labelMap[_pendingStatusBaru] + '!');
      closeStatusModal();
    })
    .catch(() => {
      toast('Gagal mengubah status!', false);
      closeStatusModal();
    });
});

function closeStatusModal() {
  document.getElementById('modal-status').classList.remove('open');
  const btn = document.getElementById('modal-confirm-btn');
  btn.textContent = 'Ya, Ubah';
  btn.disabled    = false;
  _pendingPesananId = _pendingStatusBaru = _pendingBtn = null;
}
document.getElementById('modal-status').addEventListener('click', function(e) {
  if (e.target === this) closeStatusModal();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>