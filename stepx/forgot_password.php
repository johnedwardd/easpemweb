<?php
//  StepX Store — forgot_password.php
require_once __DIR__ . '/includes/config.php';

if (isLoggedIn()) { header('Location: catalog.php'); exit; }

$db      = getDB();
$step    = $_GET['step'] ?? 'email'; // email → token → reset
$error   = '';
$success = '';

// Buat tabel password_resets kalau belum ada
$db->query('CREATE TABLE IF NOT EXISTS password_resets (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  token      VARCHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used       TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

// ── STEP 1: Input email ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['step'] ?? '') === 'email') {
  $email = trim($_POST['email'] ?? '');
  if (!$email) {
    $error = 'Email wajib diisi!';
  }else{
    $st = $db->prepare('SELECT id, nama FROM users WHERE email = ? AND status = "aktif" LIMIT 1');
    $st->bind_param('s', $email);
    $st->execute();
    $user = $st->get_result()->fetch_assoc();
    $st->close();

    if (!$user) {
      $error = 'Email tidak ditemukan atau akun tidak aktif!';
    }else{
      // Hapus token lama milik user ini
      $st = $db->prepare('DELETE FROM password_resets WHERE user_id = ?');
      $st->bind_param('i', $user['id']);
      $st->execute();
      $st->close();

      // Buat token baru (berlaku 30 menit)
      $token   = bin2hex(random_bytes(16)); // 32 karakter hex
      $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

      $st = $db->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
      $st->bind_param('iss', $user['id'], $token, $expires);
      $st->execute();
      $st->close();

      // Simpan email di session untuk step berikutnya
      $_SESSION['reset_email'] = $email;

      $success = 'Kode reset ditemukan! Gunakan kode berikut:';
      $step    = 'show_token';
      // Simpan token di session untuk ditampilkan (simulasi "email")
      $_SESSION['reset_token'] = $token;
    }
  }
}

// ── STEP 2: Verifikasi token ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['step'] ?? '') === 'token') {
  $token = trim($_POST['token'] ?? '');
  if (!$token) {
    $error = 'Masukkan kode reset!';
    $step  = 'token';
  }else{
    $now = date('Y-m-d H:i:s');
    $st  = $db->prepare('SELECT id, user_id FROM password_resets WHERE token = ? AND expires_at > ? AND used = 0 LIMIT 1');
    $st->bind_param('ss', $token, $now);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();

    if (!$row) {
      $error = 'Kode tidak valid atau sudah kedaluwarsa!';
      $step  = 'token';
    }else{
      $_SESSION['reset_token_valid'] = $token;
      $_SESSION['reset_user_id']     = $row['user_id'];
      $step = 'reset';
    }
  }
}

// ── STEP 3: Reset password ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['step'] ?? '') === 'reset') {
  $pass  = trim($_POST['password']  ?? '');
  $pass2 = trim($_POST['password2'] ?? '');
  $uid   = (int)($_SESSION['reset_user_id'] ?? 0);
  $token = $_SESSION['reset_token_valid'] ?? '';

  if (!$pass || !$pass2) {
    $error = 'Password wajib diisi!';
    $step  = 'reset';
  }elseif(strlen($pass) < 6) {
    $error = 'Password minimal 6 karakter!';
    $step  = 'reset';
  }elseif($pass !== $pass2) {
    $error = 'Konfirmasi password tidak cocok!';
    $step  = 'reset';
  }elseif(!$uid || !$token) {
    $error = 'Sesi tidak valid, mulai ulang dari awal!';
    $step  = 'email';
  } else {
    $hashed = password_hash($pass, PASSWORD_DEFAULT);

    // Update password user
    $st = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
    $st->bind_param('si', $hashed, $uid);
    $st->execute();
    $st->close();

    // Tandai token sudah dipakai
    $st = $db->prepare('UPDATE password_resets SET used = 1 WHERE token = ?');
    $st->bind_param('s', $token);
    $st->execute();
    $st->close();

    // Hapus session reset
    unset($_SESSION['reset_email'], $_SESSION['reset_token'],
    $_SESSION['reset_token_valid'], $_SESSION['reset_user_id']);
    $success = 'Password berhasil direset! Silakan login dengan password baru.';
    $step    = 'done';
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lupa Password — StepX Store</title>
<link rel="stylesheet" href="/stepx/assets/css/style.css">
</head>
<body>
<div class="ticker-bar"><div class="ticker-inner">
  &nbsp;&nbsp;&nbsp;🔥 FREE ONGKIR se-Indonesia &nbsp;|&nbsp; STOK TERBATAS &nbsp;|&nbsp; 100% ORIGINAL &nbsp;|&nbsp; Easy Return 7 Hari &nbsp;|&nbsp;&nbsp;&nbsp;🔥 FREE ONGKIR se-Indonesia &nbsp;|&nbsp; STOK TERBATAS &nbsp;|&nbsp; 100% ORIGINAL &nbsp;|&nbsp; Easy Return 7 Hari &nbsp;|&nbsp;
</div></div>

<div class="login-wrap">
  <div class="login-box">
    <h1>STEP<span style="color:var(--accent2)">X</span></h1>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($step === 'email'): ?>
      <!-- STEP 1: Input email -->
      <p class="tagline">Masukkan email akun kamu</p>
      <form method="POST" action="forgot_password.php">
        <input type="hidden" name="step" value="email">
        <div class="field">
          <label>Email</label>
          <input type="email" name="email" placeholder="contoh@email.com" required autofocus>
        </div>
        <button type="submit" class="btn-primary full">Kirim Kode Reset</button>
      </form>

    <?php elseif ($step === 'show_token'): ?>
      <!-- Tampilkan token (simulasi pengiriman email) -->
      <p class="tagline">Kode reset password kamu:</p>
      <div style="background:var(--surface2);border:2px dashed var(--accent);border-radius:10px;padding:16px;text-align:center;margin:12px 0">
        <div style="font-size:11px;color:var(--muted);margin-bottom:6px">TOKEN RESET (berlaku 30 menit)</div>
        <div style="font-size:18px;font-weight:700;letter-spacing:2px;word-break:break-all"><?= htmlspecialchars($_SESSION['reset_token'] ?? '') ?></div>
      </div>
      <p style="font-size:12px;color:var(--muted);text-align:center;margin-bottom:16px">Salin kode di atas, lalu klik lanjut</p>
      <a href="forgot_password.php?step=token" class="btn-primary full" style="display:block;text-align:center;text-decoration:none">Lanjut →</a>

    <?php elseif ($step === 'token'): ?>
      <!-- STEP 2: Input token -->
      <p class="tagline">Masukkan kode reset yang kamu dapat</p>
      <form method="POST" action="forgot_password.php">
        <input type="hidden" name="step" value="token">
        <div class="field">
          <label>Kode Reset</label>
          <input type="text" name="token" placeholder="Paste kode di sini..." required autofocus
                 value="<?= htmlspecialchars($_SESSION['reset_token'] ?? '') ?>">
        </div>
        <button type="submit" class="btn-primary full">Verifikasi Kode</button>
      </form>

    <?php elseif ($step === 'reset'): ?>
      <!-- STEP 3: Input password baru -->
      <p class="tagline">Buat password baru</p>
      <form method="POST" action="forgot_password.php">
        <input type="hidden" name="step" value="reset">
        <div class="field">
          <label>Password Baru <span style="color:var(--accent2)">*</span></label>
          <input type="password" name="password" placeholder="Min. 6 karakter" required autofocus>
        </div>
        <div class="field">
          <label>Konfirmasi Password <span style="color:var(--accent2)">*</span></label>
          <input type="password" name="password2" placeholder="Ulangi password baru" required>
        </div>
        <button type="submit" class="btn-primary full">Reset Password</button>
      </form>

    <?php elseif ($step === 'done'): ?>
      <!-- SELESAI -->
      <a href="index.php" class="btn-primary full" style="display:block;text-align:center;text-decoration:none;margin-top:8px">Login Sekarang →</a>

    <?php endif; ?>

    <?php if ($step === 'email' || $step === 'token'): ?>
    <p style="text-align:center;margin-top:16px;font-size:14px;color:var(--muted)">
      Ingat password? <a href="index.php" style="color:var(--accent);font-weight:600">Login di sini</a>
    </p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>