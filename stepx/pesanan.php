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

// Ambil keyword search
$keyword = trim($_GET['q'] ?? '');

// Query riwayat pesanan + filter search
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
    $sql    .= ' AND (p.kode_pesanan LIKE ? OR dp.nama_produk LIKE ?)';
    $like    = '%' . $keyword . '%';
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

$sql .= ' GROUP BY p.id ORDER BY p.created_at DESC';

$st = $db->prepare($sql);
$st->bind_param($types, ...$params);
$st->execute();
$pesananList = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

// Label badge status
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
    return '<span style="background:' . $info['color'] . '1a;color:' . $info['color'] . ';border:1px solid ' . $info['color'] . '40;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600">'
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

$pageTitle  = 'Riwayat Pesanan';
$activePage = 'pesanan';
require_once __DIR__ . '/includes/header.php';
?>

<div class="cart-wrap">
  <a class="back-link" href="catalog.php" style="display:inline-flex;align-items:center;gap:6px;padding-top:8px">← Lanjut Belanja</a>
  <h2>Riwayat Pesanan</h2>

  <!-- SEARCH BAR -->
  <form method="GET" action="pesanan.php" style="margin:16px 0;display:flex;gap:8px">
    <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>"
           placeholder="Cari kode pesanan atau nama produk..."
           style="flex:1;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;background:var(--surface);color:var(--text)">
    <button type="submit" class="btn-primary" style="padding:10px 20px">Cari</button>
    <?php if ($keyword): ?>
      <a href="pesanan.php" class="btn-ghost" style="padding:10px 16px;text-decoration:none">Reset</a>
    <?php endif; ?>
  </form>

  <?php if ($keyword): ?>
    <p style="font-size:13px;color:var(--muted);margin-bottom:12px">
      Hasil pencarian untuk: <strong>"<?= htmlspecialchars($keyword) ?>"</strong>
      — <?= count($pesananList) ?> pesanan ditemukan
    </p>
  <?php endif; ?>

  <?php if (empty($pesananList)): ?>
    <div class="empty-state">
      <div class="icon"><?= $keyword ? '🔍' : '📦' ?></div>
      <p><?= $keyword ? 'Tidak ada pesanan yang cocok dengan pencarian.' : 'Belum ada pesanan.' ?></p>
    </div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px">
      <?php foreach ($pesananList as $p): ?>
      <div style="border:1.5px solid var(--border);border-radius:12px;padding:16px 18px;background:var(--surface)">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px">
          <div>
            <div style="font-weight:700;font-size:15px"><?= htmlspecialchars($p['kode_pesanan']) ?></div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px">
              <?= date('d M Y, H:i', strtotime($p['created_at'])) ?>
              &nbsp;·&nbsp; <?= (int)$p['jumlah_item'] ?> produk
              &nbsp;·&nbsp; <?= labelMetode($p['metode_bayar']) ?>
            </div>
          </div>
          <div style="font-weight:700;font-size:16px"><?= rp($p['total_bayar']) ?></div>
        </div>
        <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;align-items:center">
          <?= badgeStatus($p['status_pesanan'], 'status_pesanan') ?>
          <?= badgeStatus($p['status_bayar'], 'status_bayar') ?>
          <a href="pesanan.php?detail=<?= $p['id'] ?>" style="margin-left:auto;font-size:13px;color:var(--accent)">Lihat Detail →</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
