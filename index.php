<?php
//  StepX Store — index.php (Halaman Login)
require_once __DIR__ . '/includes/config.php';

// Kalau sudah login, langsung ke catalog
if (isLoggedIn()) {
    header('Location: catalog.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');

    if (!$email || !$pass) {
        $error = 'Email dan password wajib diisi!';
    } else {
        $db   = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $error = 'Email tidak ditemukan!';
        } elseif (!password_verify($pass,$user['password'])) {
            $error = 'Password yang dimasukkan salah!';
        } else {
            // Simpan session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['no_hp']   = $user['no_hp'] ?? '';
            $_SESSION['cart']    = [];

            header('Location: catalog.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — StepX Store</title>
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
    <p class="tagline">Premium Footwear Store — Masuk ke akun Anda</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" placeholder="contoh@email.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
      </div>
      <div class="field">
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-primary full">Masuk</button>
    </form>
    <p style="text-align:right;margin-top:8px;font-size:13px">
      <a href="forgot_password.php" style="color:var(--muted)">Lupa password?</a>
    </p>

    <p style="text-align:center;margin-top:16px;font-size:14px;color:var(--muted)">
      Belum punya akun? <a href="register.php" style="color:var(--accent);font-weight:600">Daftar sekarang</a>
    </p>
  </div>
</div>
</body>
</html>