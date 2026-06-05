<?php
//  StepX Store — Database Configuration (Laragon v6.0)

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'stepx_db');
define('DB_PORT', 3306);

function getDB(): mysqli {
  static $conn = null;
  if ($conn === null) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
      die('<div style="font-family:sans-serif;padding:40px;color:#c00">
        <strong>Koneksi database gagal:</strong><br>' .
        htmlspecialchars($conn->connect_error) .
        '<br><br><small>Pastikan Laragon sudah running dan database <b>stepx_db</b> sudah diimport.</small>
      </div>');
    }$conn->set_charset('utf8mb4');
  }return $conn;
}

//  Session helper
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function isLoggedIn(): bool {
  return isset($_SESSION['user_id']);
}

function requireLogin(): void {
  if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
  }
}

function currentUser(): array {
  return [
    'id'    => $_SESSION['user_id']   ?? 0,
    'nama'  => $_SESSION['nama']      ?? '',
    'email' => $_SESSION['email']     ?? '',
    'role'  => $_SESSION['role']      ?? '',
    'no_hp' => $_SESSION['no_hp']     ?? '',
  ];
}

function rp(int|float $n): string {
  return 'Rp ' . number_format($n, 0, ',', '.');
}