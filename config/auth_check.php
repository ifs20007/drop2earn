<?php
/**
 * Authentication and Role-Based Access Control (RBAC)
 * Drop2Earn - Smart Waste Management
 */

require_once __DIR__ . '/database.php';

/**
 * Check if a session has an active logged-in user
 */
function is_logged_in() {
    return isset($_SESSION['id_user']) && !empty($_SESSION['id_user']);
}

/**
 * Get fresh user data directly from database
 */
function get_auth_user($conn) {
    if (!is_logged_in()) {
        return null;
    }
    $id_user = (int)$_SESSION['id_user'];
    $stmt = mysqli_prepare($conn, "SELECT id_user, nama_lengkap, email, no_hp, alamat, role, saldo, created_at FROM users WHERE id_user = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user) {
        // Keep session values updated with live database values
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['saldo'] = $user['saldo'];
        return $user;
    }
    return null;
}

/**
 * Enforce Nasabah access barrier
 * Redirects guests to login and admins to admin dashboard
 */
function check_nasabah($conn) {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = "Silakan login terlebih dahulu untuk mengakses halaman nasabah.";
        header("Location: " . base_url('login.php'));
        exit;
    }

    $user = get_auth_user($conn);
    if (!$user || $user['role'] !== 'nasabah') {
        $_SESSION['flash_error'] = "Akses khusus akun nasabah.";
        header("Location: " . base_url('admin/index.php'));
        exit;
    }
    return $user;
}

/**
 * Enforce Admin access barrier
 * Redirects guests to login and nasabah to nasabah dashboard
 */
function check_admin($conn) {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = "Silakan login menggunakan akun pengelola/admin.";
        header("Location: " . base_url('login.php'));
        exit;
    }

    $user = get_auth_user($conn);
    if (!$user || $user['role'] !== 'admin') {
        $_SESSION['flash_error'] = "Akses ditolak: Anda tidak memiliki hak akses pengelola.";
        header("Location: " . base_url('nasabah/dashboard.php'));
        exit;
    }
    return $user;
}

