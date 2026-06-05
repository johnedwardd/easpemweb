<?php
//  StepX Store — hapus.php (Hapus Produk — POST only)
require_once __DIR__ . '/includes/config.php';
requireLogin();

$user = currentUser();
if ($user['role'] === 'pembeli') {
  header('Location: catalog.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: catalog.php');
  exit;
}

$id = intval($_POST['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare('DELETE FROM produk WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

$_SESSION['flash'] = ['msg' => 'Produk berhasil dihapus', 'ok' => true];
header('Location: catalog.php');
exit;