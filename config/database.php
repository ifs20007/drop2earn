<?php
/**
 * Database Connection & Global Configuration
 * Drop2Earn - Smart Waste Management
 */

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = '';
$db_name = 'db_drop2earn';
$db_port = 3306;

// Create MySQLi connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set character set to utf8mb4
mysqli_set_charset($conn, 'utf8mb4');

/**
 * Sanitize output string for HTML rendering (XSS Prevention)
 */
function sanitize($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/**
 * Format number to Indonesian Rupiah currency format
 */
function rupiah($nominal) {
    return 'Rp ' . number_format((float)$nominal, 0, ',', '.');
}

/**
 * Format decimal weight with Indonesian comma
 */
function format_kg($weight) {
    return number_format((float)$weight, 2, ',', '.') . ' kg';
}

/**
 * Format date to Indonesian human-readable format
 */
function format_tanggal($date_str) {
    if (empty($date_str)) return '-';
    $timestamp = strtotime($date_str);
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $d = date('d', $timestamp);
    $m = (int)date('m', $timestamp);
    $y = date('Y', $timestamp);
    $t = date('H:i', $timestamp);

    // If datetime contains time component
    if (strlen($date_str) > 10 && $t !== '00:00') {
        return "$d " . $bulan[$m] . " $y, $t WIB";
    }
    return "$d " . $bulan[$m] . " $y";
}

/**
 * Base URL helper to resolve root and relative paths correctly
 */
function base_url($path = '') {
    // Drop2Earn project is located at root web folder /Drop2Earn
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Check if script is running inside /Drop2Earn subfolder or virtual host root
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $base = '/Drop2Earn';
    if (strpos($script_dir, '/Drop2Earn') === false) {
        $base = '';
    }
    
    $clean_path = ltrim($path, '/');
    return $base . ($clean_path ? '/' . $clean_path : '');
}

