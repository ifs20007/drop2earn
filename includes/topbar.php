<?php
/**
 * CMS & Dashboard Topbar Component
 * Drop2Earn - Smart Waste Management
 */
$logged_user = isset($conn) ? get_auth_user($conn) : null;
$role = $logged_user['role'] ?? 'nasabah';
$profile_link = ($role === 'admin') ? base_url('admin/profil.php') : base_url('nasabah/profil.php');

// Generate initials for avatar badge
$initials = 'US';
if (!empty($logged_user['nama_lengkap'])) {
    $words = explode(' ', trim($logged_user['nama_lengkap']));
    $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
}
?>

<header class="d2e-topbar">
    <div class="d-flex align-items-center gap-3">
        <!-- Mobile Sidebar Toggle -->
        <button class="topbar-toggle" type="button" id="sidebarToggleBtn" aria-label="Buka navigasi menu">
            <i class="bi bi-list"></i>
        </button>

        <!-- Context Title -->
        <div class="d-none d-sm-block">
            <span class="text-muted small fw-medium">Panel <?= $role === 'admin' ? 'Pengelola Bank Sampah' : 'Nasabah Warga' ?></span>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 gap-sm-3">
        <!-- Nasabah Live Saldo Widget -->
        <?php if ($role === 'nasabah'): ?>
            <div class="d-flex align-items-center bg-light border rounded px-2 px-sm-3 py-1">
                <i class="bi bi-wallet2 text-success me-2"></i>
                <div>
                    <small class="text-muted d-none d-sm-block" style="font-size: 0.7rem; line-height: 1;">Saldo Tabungan</small>
                    <strong class="text-success" style="font-size: 0.88rem;"><?= rupiah($logged_user['saldo'] ?? 0) ?></strong>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tombol "Halaman Depan" di samping profil user -->
        <a href="<?= base_url('index.php') ?>" class="btn-halaman-depan" title="Lihat Tampilan Landing Page Publik">
            <i class="bi bi-house-door text-success"></i>
            <span>Halaman Depan</span>
        </a>

        <!-- Menu Dropdown Profil Minimalis -->
        <div class="dropdown">
            <button class="d2e-user-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="d2e-user-avatar"><?= $initials ?></div>
                <span class="d-none d-md-inline"><?= sanitize($logged_user['nama_lengkap'] ?? 'Pengguna') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 220px;">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-bold small text-dark"><?= sanitize($logged_user['nama_lengkap'] ?? '') ?></div>
                    <div class="text-muted small" style="font-size: 0.75rem;"><?= sanitize($logged_user['email'] ?? '') ?></div>
                    <span class="badge bg-secondary mt-1" style="font-size: 0.68rem;"><?= $role === 'admin' ? 'Administrator' : 'Nasabah' ?></span>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="<?= $profile_link ?>">
                        <i class="bi bi-person-gear me-2 text-primary"></i> Profil Saya
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item py-2 text-danger" 
                       href="<?= base_url('logout.php') ?>"
                       data-confirm="Apakah Anda yakin ingin keluar dari sistem Drop2Earn?" 
                       data-confirm-title="Konfirmasi Keluar" 
                       data-confirm-type="warning" 
                       data-confirm-btn="Ya, Keluar">
                        <i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

