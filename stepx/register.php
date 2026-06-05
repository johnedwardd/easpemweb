<?php
//  StepX Store — register.php
require_once __DIR__ . '/includes/config.php';

if (isLoggedIn()) {
  header('Location: catalog.php');
  exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama  = trim($_POST['nama']     ?? '');
  $email = trim($_POST['email']    ?? '');
  $no_hp = trim($_POST['no_hp']    ?? '');
  $pass  = trim($_POST['password'] ?? '');
  $pass2 = trim($_POST['password2'] ?? '');

  if (!$nama || !$email || !$pass || !$pass2) {
    $error = 'Nama, email, dan password wajib diisi!';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Format email tidak valid!';
  } elseif (strlen($pass) < 6) {
    $error = 'Password minimal 6 karakter!';
  } elseif ($pass !== $pass2) {
    $error = 'Konfirmasi password tidak cocok!';
  } else {
    $db = getDB();

    // Cek email sudah terdaftar
    $st = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $st->bind_param('s', $email);
    $st->execute();
    $existing = $st->get_result()->fetch_assoc();
    $st->close();

    if ($existing) {
      $error = 'Email sudah terdaftar! Silakan login.';
    }else{
      $st = $db->prepare('INSERT INTO users (nama, email, password, no_hp, role, status) VALUES (?, ?, ?, ?, "pembeli", "aktif")');
      $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
      $st->bind_param('ssss', $nama, $email, $hashedPass, $no_hp);
      $st->execute();
      $st->close();

      $success = 'Registrasi berhasil! Silakan <a href="index.php" style="color:var(--accent);font-weight:600">login sekarang</a>.';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar — StepX Store</title>
<link rel="stylesheet" href="/stepx/assets/css/style.css">
</head>
<body>

<!-- TICKER -->
<div class="ticker-bar">
  <div class="ticker-inner">
    &nbsp;&nbsp;&nbsp;🔥 FREE ONGKIR se-Indonesia &nbsp;|&nbsp; STOK TERBATAS – Grab yours now! &nbsp;|&nbsp; 100% ORIGINAL &nbsp;|&nbsp; Easy Return 7 Hari &nbsp;|&nbsp;&nbsp;&nbsp;🔥 FREE ONGKIR se-Indonesia &nbsp;|&nbsp; STOK TERBATAS – Grab yours now! &nbsp;|&nbsp; 100% ORIGINAL &nbsp;|&nbsp; Easy Return 7 Hari &nbsp;|&nbsp;
  </div>
</div>

<div class="login-wrap">
  <div class="login-box">
    <h1>STEP<span style="color:var(--accent2)">X</span></h1>
    <p class="tagline">Buat akun baru — gratis!</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST" action="register.php">
      <div class="field">
        <label>Nama Lengkap <span style="color:var(--accent2)">*</span></label>
        <input type="text" name="nama" placeholder="John Doe"
               value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required autofocus>
      </div>
      <div class="field">
        <label>Email <span style="color:var(--accent2)">*</span></label>
        <input type="email" name="email" placeholder="contoh@email.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>
      <div class="field">
        <label>No. HP <span style="color:var(--muted);font-size:12px">(opsional)</span></label>
        <input type="tel" name="no_hp" placeholder="08123456789"
               value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
      </div>
      <div class="field">
        <label>Password <span style="color:var(--accent2)">*</span></label>
        <input type="password" name="password" placeholder="Min. 6 karakter" required>
      </div>
      <div class="field">
        <label>Konfirmasi Password <span style="color:var(--accent2)">*</span></label>
        <input type="password" name="password2" placeholder="Ulangi password" required>
      </div>
      <button type="submit" class="btn-primary full">Daftar Sekarang</button>
    </form>
    <?php endif; ?>

    <?php if (!$success): ?>
    <p style="text-align:center;margin-top:16px;font-size:14px;color:var(--muted)">
      Sudah punya akun? <a href="index.php" style="color:var(--accent);font-weight:600">Login di sini</a>
    </p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>