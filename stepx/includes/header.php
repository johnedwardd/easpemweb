<?php
// includes/header.php
if (!isset($pageTitle))  $pageTitle  = 'StepX Store';
if (!isset($activePage)) $activePage = '';
$user = currentUser();

$roleClass = [
  'admin'   => 'role-admin',
  'penjual' => 'role-penjual',
  'pembeli' => 'role-pembeli',
][$user['role']] ?? '';

$initials = strtoupper(implode('', array_map(fn($w) => $w[0],
  array_slice(explode(' ', $user['nama'] ?: 'SX'), 0, 2))));

// Cart count dari DB (bukan session)
$cartCount = 0;
if ($user['role'] === 'pembeli' && $user['id']) {
  $db = getDB();
  $st = $db->prepare('SELECT COALESCE(SUM(qty), 0) AS total FROM keranjang WHERE user_id = ?');
  $st->bind_param('i', $user['id']);
  $st->execute();
  $cartCount = (int)($st->get_result()->fetch_assoc()['total'] ?? 0);
  $st->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> — StepX Store</title>
<link rel="stylesheet" href="/stepx/assets/css/style.css">
</head>
<body>

<!-- TICKER -->
<div class="ticker-bar">
  <div class="ticker-inner">
    &nbsp;&nbsp;&nbsp;🔥 FREE ONGKIR se-Indonesia &nbsp;|&nbsp; STOK TERBATAS – Grab yours now! &nbsp;|&nbsp; 100% ORIGINAL &nbsp;|&nbsp; Easy Return 7 Hari &nbsp;|&nbsp;&nbsp;&nbsp;🔥 FREE ONGKIR se-Indonesia &nbsp;|&nbsp; STOK TERBATAS – Grab yours now! &nbsp;|&nbsp; 100% ORIGINAL &nbsp;|&nbsp; Easy Return 7 Hari &nbsp;|&nbsp;
  </div>
</div>

<!-- NAV -->
<nav id="nav">
  <a class="logo" href="/stepx/catalog.php">STEPX</a>
  <div id="nav-links">
    <a href="/stepx/catalog.php" class="<?= $activePage==='catalog' ? 'active' : '' ?>">Katalog</a>
    <?php if ($user['role'] === 'pembeli'): ?>
      <a href="/stepx/cart.php" class="<?= $activePage==='cart' ? 'active' : '' ?>">
        🛒 Keranjang
        <?php if ($cartCount > 0): ?>
          <span style="background:var(--accent2);color:#fff;padding:1px 6px;border-radius:10px;font-size:10px;margin-left:4px"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>
      <a href="/stepx/pesanan.php" class="<?= $activePage==='pesanan' ? 'active' : '' ?>">📦 Pesanan</a>
    <?php endif; ?>
    <?php if ($user['role'] !== 'pembeli'): ?>
      <a href="/stepx/tambah.php" class="<?= $activePage==='tambah' ? 'active' : '' ?>">+ Tambah</a>
    <?php endif; ?>
    <?php if ($user['role'] === 'penjual'): ?>
      <a href="/stepx/laba.php" class="<?= $activePage==='laba' ? 'active' : '' ?>">Laporan Laba</a>
    <?php endif; ?>
    <?php if ($user['role'] === 'admin'): ?>
      <a href="/stepx/laba.php" class="<?= $activePage==='laba' ? 'active' : '' ?>">Laporan</a>
    <?php endif; ?>
    <a href="/stepx/profile.php" class="<?= $activePage==='profile' ? 'active' : '' ?>">Profil</a>
  </div>
  <div id="user-pill">
    <span><?= htmlspecialchars($user['nama']) ?> (<?= htmlspecialchars($user['role']) ?>)</span>
    <a href="/stepx/logout.php" id="logout-btn">Logout</a>
  </div>
</nav>

<!-- TOAST (JS) -->
<div id="toast"></div>

<script>
function toast(msg, ok=true){
  const t=document.getElementById('toast');
  t.textContent=msg;
  t.style.background=ok?'#0A0A0A':'#E8302A';
  t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),2800);
}
<?php if (!empty($_SESSION['flash'])): ?>
const _flash = <?= json_encode($_SESSION['flash']) ?>;
document.addEventListener('DOMContentLoaded',()=>toast(_flash.msg, _flash.ok));
<?php unset($_SESSION['flash']); endif; ?>
</script>