<?php
/**
 * Logout Page
 * Drop2Earn - Smart Waste Management
 */
require_once __DIR__ . '/config/database.php';

// Unset all session variables
$_SESSION = [];

// Destroy session cookie if present
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to login page
session_start();
$_SESSION['flash_success'] = "Anda telah berhasil keluar (logout) dari sistem.";
header("Location: " . base_url('login.php'));
exit;

