<?php
//  StepX Store — cart_action.php
require_once __DIR__ . '/includes/config.php';
requireLogin();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$id     = (int)($_POST['id'] ?? 0);
$db     = getDB();
$userId = (int)(currentUser()['id']);

function cartCount(mysqli $db, int $userId): int {
  $st = $db->prepare('SELECT COALESCE(SUM(qty), 0) AS total FROM keranjang WHERE user_id = ?');
  $st->bind_param('i', $userId);
  $st->execute();
  $r = $st->get_result()->fetch_assoc();
  $st->close();
  return (int)$r['total'];
}

if ($action === 'add') {
  if ($id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'ID produk tidak valid!', 'cartCount' => 0]);
    exit;
  }

  // Cek produk & stok
  $st = $db->prepare('SELECT stok, nama FROM produk WHERE id = ? AND status = "aktif" LIMIT 1');
  $st->bind_param('i', $id);
  $st->execute();
  $p = $st->get_result()->fetch_assoc();
  $st->close();

  if (!$p) {
    echo json_encode(['ok' => false, 'msg' => 'Produk tidak ditemukan!', 'cartCount' => cartCount($db, $userId)]);
    exit;
  }

  // Cek qty di keranjang saat ini (ukuran NULL)
  $st = $db->prepare('SELECT id, qty FROM keranjang WHERE user_id = ? AND produk_id = ? AND ukuran IS NULL LIMIT 1');
  $st->bind_param('ii', $userId, $id);
  $st->execute();
  $existing = $st->get_result()->fetch_assoc();
  $st->close();
  $curQty = (int)($existing['qty'] ?? 0);

  if ($curQty >= (int)$p['stok']) {
    echo json_encode([
      'ok'        => false,
      'msg'       => 'Stok tidak cukup! (stok tersisa: ' . $p['stok'] . ')',
      'cartCount' => cartCount($db, $userId),
    ]);
    exit;
  }

  if ($existing) {
    // Sudah ada, update qty
    $newQty = $curQty + 1;
    $st = $db->prepare('UPDATE keranjang SET qty = ? WHERE id = ?');
    $st->bind_param('ii', $newQty, (int)$existing['id']);
    $st->execute();
    $st->close();
  } else {
    // Belum ada, insert baru
    $st = $db->prepare('INSERT INTO keranjang (user_id, produk_id, ukuran, qty) VALUES (?, ?, NULL, 1)');
    $st->bind_param('ii', $userId, $id);
    $st->execute();
    $st->close();
  }

  echo json_encode([
    'ok'        => true,
    'msg'       => htmlspecialchars($p['nama']) . ' ditambah ke keranjang',
    'cartCount' => cartCount($db, $userId),
  ]);
  exit;
}

if ($action === 'remove') {
  $st = $db->prepare('DELETE FROM keranjang WHERE user_id = ? AND produk_id = ? AND ukuran IS NULL');
  $st->bind_param('ii', $userId, $id);
  $st->execute();
  $st->close();

  echo json_encode([
    'ok'        => true,
    'msg'       => 'Item dihapus dari keranjang',
    'cartCount' => cartCount($db, $userId),
  ]);
  exit;
}

echo json_encode(['ok' => false, 'msg' => 'Aksi tidak dikenal', 'cartCount' => 0]);