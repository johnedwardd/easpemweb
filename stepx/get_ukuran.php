<?php
// StepX Store — get_ukuran.php (AJAX endpoint: ambil ukuran per produk)
require_once __DIR__ . '/includes/config.php';
requireLogin();

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode([]);
    exit;
}

$db = getDB();
$st = $db->prepare(
    'SELECT ukuran, stok FROM produk_ukuran WHERE produk_id = ? ORDER BY CAST(ukuran AS UNSIGNED)'
);
$st->bind_param('i', $id);
$st->execute();
$rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

echo json_encode($rows);
