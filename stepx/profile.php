<?php
//  StepX Store — profile.php (Profil Pengguna)
require_once __DIR__ . '/includes/config.php';
requireLogin();

$user = currentUser();
$db   = getDB();

// Ambil data lengkap dari DB
$stmt = $db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Untuk penjual: ambil nama_toko dari tabel profil_penjual (bukan dari users)
$namaToko = null;
if ($u['role'] === 'penjual') {
    $stmt = $db->prepare('SELECT nama_toko FROM profil_penjual WHERE user_id = ? LIMIT 1');
    $stmt->bind_param('i', $u['id']);
    $stmt->execute();
    $pp = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $namaToko = $pp['nama_toko'] ?? null;
}

$roleClass = [
    'admin'   => 'role-admin',
    'penjual' => 'role-penjual',
    'pembeli' => 'role-pembeli',
][$u['role']] ?? '';

$initials = strtoupper(implode('', array_map(
    fn($w) => $w[0],
    array_slice(explode(' ', $u['nama'] ?: 'SX'), 0, 2)
)));

$pageTitle  = 'Profil Saya';
$activePage = 'profile';
require_once __DIR__ . '/includes/header.php';
?>

<div class="profile-wrap">
  <a class="back-link" href="catalog.php">← Kembali</a>
  <h2>Profil Saya</h2>

  <div class="profile-card">
    <div class="profile-avatar"><?= htmlspecialchars($initials) ?></div>

    <div class="profile-row">
      <span class="key">Nama Lengkap</span>
      <span><?= htmlspecialchars($u['nama']) ?></span>
    </div>
    <div class="profile-row">
      <span class="key">Email</span>
      <span><?= htmlspecialchars($u['email']) ?></span>
    </div>
    <div class="profile-row">
      <span class="key">Nomor HP</span>
      <span><?= htmlspecialchars($u['no_hp'] ?: '-') ?></span>
    </div>
    <div class="profile-row">
      <span class="key">Role</span>
      <span class="role-badge <?= $roleClass ?>"><?= htmlspecialchars($u['role']) ?></span>
    </div>
    <?php if ($u['role'] === 'penjual'): ?>
    <div class="profile-row">
      <span class="key">Nama Toko</span>
      <span><?= htmlspecialchars($namaToko ?: '-') ?></span>
    </div>
    <?php endif; ?>
    <div class="profile-row">
      <span class="key">Bergabung</span>
      <span><?= date('d M Y', strtotime($u['created_at'])) ?></span>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
