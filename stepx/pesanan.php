<?php
//  StepX Store — pesanan.php (Riwayat Pesanan Pembeli)
require_once __DIR__ . '/includes/config.php';
requireLogin();

$user = currentUser();
if ($user['role'] !== 'pembeli') {
  header('Location: catalog.php');
  exit;
}

$db     = getDB();
$userId = (int)$user['id'];

// Ambil keyword search dan filter status
$keyword = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

// Query riwayat pesanan + filter search & status
$sql = '
  SELECT p.id, p.kode_pesanan, p.total_bayar, p.metode_bayar,
    p.status_bayar, p.status_pesanan, p.created_at,
    COUNT(dp.id) AS jumlah_item
  FROM pesanan p
  LEFT JOIN detail_pesanan dp ON dp.pesanan_id = p.id
  WHERE p.pembeli_id = ?
';
$params = [$userId];
$types  = 'i';

if ($keyword !== '') {
  $sql     .= ' AND (p.kode_pesanan LIKE ? OR dp.nama_produk LIKE ?)';
  $like    = '%' . $keyword . '%';
  $params[] = $like;
  $params[] = $like;
  $types   .= 'ss';
}

if ($statusFilter !== '') {
  $sql     .= ' AND p.status_pesanan = ?';
  $params[] = $statusFilter;
  $types   .= 's';
}

$sql .= ' GROUP BY p.id ORDER BY p.created_at DESC';

$st = $db->prepare($sql);
$st->bind_param($types, ...$params);
$st->execute();
$pesananList = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

// Fungsi badge status tetap di sini
function badgeStatus(string $status, string $type): string {
  $map = [
    'status_pesanan' => [
      'proses'     => ['label' => 'Diproses',   'color' => '#f59e0b'],
      'dikemas'    => ['label' => 'Dikemas',     'color' => '#3b82f6'],
      'dikirim'    => ['label' => 'Dikirim',     'color' => '#8b5cf6'],
      'selesai'    => ['label' => 'Selesai',     'color' => '#10b981'],
      'dibatalkan' => ['label' => 'Dibatalkan',  'color' => '#ef4444'],
    ],
    'status_bayar' => [
      'menunggu' => ['label' => 'Belum Bayar', 'color' => '#f59e0b'],
      'lunas'    => ['label' => 'Lunas',        'color' => '#10b981'],
      'gagal'    => ['label' => 'Gagal',        'color' => '#ef4444'],
      'refund'   => ['label' => 'Refund',       'color' => '#6b7280'],
    ],
  ];
  $info  = $map[$type][$status] ?? ['label' => $status, 'color' => '#6b7280'];
  return '<span style="background:' . $info['color'] . '1a;color:' . $info['color'] . ';border:1px solid ' . $info['color'] . '40;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600">'
    . htmlspecialchars($info['label']) . '</span>';
}

function labelMetode(string $m): string {
  return match($m) {
    'transfer_bank' => '🏦 Transfer Bank',
    'ewallet'       => '📱 E-Wallet',
    'cod'           => '🚪 COD',
    'kartu_kredit'  => '💳 Kartu Kredit',
    default         => $m,
  };
}

// FUNGSI FILTER DROP DOWN KITA TARUH LANGSUNG DI SINI BIAR GAK USAH LOAD FILE LUAR
function renderStatusFilterDirect(string $currentStatus): void {
  $options = [
    ''           => '-- Semua Status Pesanan --',
    'proses'     => '⏳ Diproses',
    'dikemas'    => '📦 Dikemas',
    'dikirim'    => '🚚 Dikirim',
    'selesai'    => '✅ Selesai',
    'dibatalkan' => '❌ Dibatalkan'
  ];

  echo '<select name="status" style="flex:1; min-width:160px; padding:10px 14px; border:1.5px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface); color:var(--text); cursor:pointer;">';
  foreach ($options as $val => $label) {
    $selected = ($currentStatus === $val) ? 'selected' : '';
    echo '<option value="' . htmlspecialchars($val) . '" ' . $selected . '>' . htmlspecialchars($label) . '</option>';
  }
  echo '</select>';
}

$pageTitle  = 'Riwayat Pesanan';
$activePage = 'pesanan';
require_once __DIR__ . '/includes/header.php';
?>

<div class="cart-wrap" style="max-width: 800px; margin: 0 auto;">
  <a class="back-link" href="catalog.php" style="display:inline-flex;align-items:center;gap:6px;padding-top:8px; text-decoration: none;">← Lanjut Belanja</a>
  <h2>Riwayat Pesanan</h2>

  <form method="GET" action="pesanan.php" style="margin:16px 0; display:flex; gap:8px; flex-wrap:wrap;">
    <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>"
      placeholder="Cari kode pesanan atau nama produk..."
      style="flex:2; min-width:200px; padding:10px 14px; border:1.5px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface); color:var(--text)">
    <?php renderStatusFilterDirect($statusFilter); ?>

    <button type="submit" class="btn-primary" style="padding:10px 24px;">Filter</button>
    
    <?php if ($keyword || $statusFilter): ?>
      <a href="pesanan.php" class="btn-ghost" style="padding:10px 16px; text-decoration:none; display:inline-flex; align-items:center;">Reset</a>
    <?php endif; ?>
  </form>

  <?php if ($keyword || $statusFilter): ?>
    <p style="font-size:13px; color:var(--muted); margin-bottom:12px">
      Menerapkan filter 
      <?= $keyword ? 'kata kunci: <strong>"' . htmlspecialchars($keyword) . '"</strong>' : '' ?>
      <?= $keyword && $statusFilter ? ' dan ' : '' ?>
      <?= $statusFilter ? 'status: <strong>"' . ucfirst($statusFilter) . '"</strong>' : '' ?>
      — <?= count($pesananList) ?> pesanan ditemukan
    </p>
  <?php endif; ?>

  <?php if (empty($pesananList)): ?>
    <div class="empty-state">
      <div class="icon"><?= ($keyword || $statusFilter) ? '🔍' : '📦' ?></div>
      <p><?= ($keyword || $statusFilter) ? 'Tidak ada pesanan yang cocok dengan kriteria filter.' : 'Belum ada pesanan.' ?></p>
    </div>
  <?php else: ?>
    <div style="display:flex; flex-direction:column; gap:12px">
      <?php foreach ($pesananList as $p): ?>
      <div style="border:1.5px solid var(--border); border-radius:12px; padding:16px 18px; background:var(--surface)">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px">
          <div>
            <div style="font-weight:700; font-size:15px"><?= htmlspecialchars($p['kode_pesanan']) ?></div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px">
              <?= date('d M Y, H:i', strtotime($p['created_at'])) ?>
              &nbsp;·&nbsp; <?= (int)$p['jumlah_item'] ?> produk
              &nbsp;·&nbsp; <?= labelMetode($p['metode_bayar']) ?>
            </div>
          </div>
          <div style="font-weight:700; font-size:16px"><?= rp($p['total_bayar']) ?></div>
        </div>
        <div style="display:flex; gap:8px; margin-top:14px; flex-wrap:wrap; align-items:center">
          <?= badgeStatus($p['status_pesanan'], 'status_pesanan') ?>
          <?= badgeStatus($p['status_bayar'], 'status_bayar') ?>
          <a href="detail_pesanan.php?id=<?= $p['id'] ?>" style="margin-left:auto; font-size:13px; color:green; font-weight:600; text-decoration:none;">Lihat Detail →</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>