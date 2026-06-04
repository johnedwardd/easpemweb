<?php
//  StepX Store — logout.php
require_once __DIR__ . '/includes/config.php';

// Cart tidak perlu dihapus dari DB saat logout — tersimpan permanen
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

header('Location: index.php');
exit;
