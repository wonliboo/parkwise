<?php
/**
 * ParkWise - Logout
 * Input  : Session aktif
 * Proses : Log aktivitas, destroy session
 * Output : Redirect ke landing page
 */
require_once __DIR__ . '/includes/config.php';

if (isLoggedIn()) {
    logAktivitas('Logout');
}

// Hapus semua session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

header('Location: ' . BASE_URL . '/landing.php');
exit;