<?php
/**
 * CMS & Dashboard Sidebar Component
 * Drop2Earn - Smart Waste Management
 */
$logged_user = isset($conn) ? get_auth_user($conn) : null;
$role = $logged_user['role'] ?? 'nasabah';
$current_script = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>

<aside class="d2e-sidebar" id="dashboardSidebar">
    <!-- Brand / CMS Title -->
    <a href="<?= $role === 'admin' ? base_url('admin/index.php') : base_url('nasabah/dashboard.php') ?>" class="d2e-sidebar-brand">
        <img src="<?= base_url('assets/img/logo.svg') ?>" alt="Logo Drop2Earn" width="30" height="30">
        <span>Drop2Earn</span>
    </a>

    <!-- Role Badge Indicator -->
    <div class="px-3 pt-3 pb-2">
        <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light border">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.75rem;">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div style="line-height: 1.2;">
                    <div class="fw-bold text-dark text-truncate" style="max-width: 140px; font-size: 0.82rem;">
                        <?= sanitize($logged_user['nama_lengkap'] ?? 'Pengguna') ?>
                    </div>
                    <span class="badge bg-secondary" style="font-size: 0.65rem;">
                        <?= $role === 'admin' ? 'Pengelola / Admin' : 'Nasabah Warga' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu (Strictly No Public Catalog/Guide/DropPoint Links) -->
    <nav class="d2e-sidebar-nav">
        <?php if ($role === 'nasabah'): ?>
            <div class="sidebar-heading">Menu Tabungan</div>
            <a href="<?= base_url('nasabah/dashboard.php') ?>" class="d2e-sidebar-link <?= ($current_script === 'dashboard.php') ? 'active' : '' ?>">
                <i class="bi bi-journal-check"></i>
                <span>Buku Tabungan</span>
            </a>
            <a href="<?= base_url('nasabah/setor.php') ?>" class="d2e-sidebar-link <?= ($current_script === 'setor.php') ? 'active' : '' ?>">
                <i class="bi bi-box-arrow-in-down"></i>
                <span>Setor Sampah</span>
            </a>
            <a href="<?= base_url('nasabah/tarik_saldo.php') ?>" class="d2e-sidebar-link <?= ($current_script === 'tarik_saldo.php') ? 'active' : '' ?>">
                <i class="bi bi-cash-stack"></i>
                <span>Tarik Saldo</span>
            </a>

            <div class="sidebar-heading mt-3">Akun &amp; Pengaturan</div>
            <a href="<?= base_url('nasabah/profil.php') ?>" class="d2e-sidebar-link <?= ($current_script === 'profil.php') ? 'active' : '' ?>">
                <i class="bi bi-person-gear"></i>
                <span>Profil Saya</span>
            </a>

        <?php elseif ($role === 'admin'): ?>
            <div class="sidebar-heading">Operasional Pengelola</div>
            <a href="<?= base_url('admin/index.php') ?>" class="d2e-sidebar-link <?= ($current_script === 'index.php') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard Analitik</span>
            </a>
            <a href="<?= base_url('admin/verifikasi_setoran.php') ?>" class="d2e-sidebar-link <?= ($current_script === 'verifikasi_setoran.php') ? 'active' : '' ?>">
                <i class="bi bi-check2-circle"></i>
                <span>Verifikasi Setoran</span>
            </a>
            <a href="<?= base_url('admin/pencairan.php') ?>" class="d2e-sidebar-link <?= ($current_script === 'pencairan.php') ? 'active' : '' ?>">
                <i class="bi bi-cash-coin"></i>
                <span>Pencairan Dana</span>
            </a>

            <div class="sidebar-heading mt-3">Master &amp; Laporan</div>
            <a href="<?= base_url('admin/kategori.php') ?>" class="d2e-sidebar-link <?= ($current_script === 'kategori.php') ? 'active' : '' ?>">
                <i class="bi bi-tags"></i>
                <span>Master Sampah</span>
            </a>
            <a href="<?= base_url('admin/laporan.php') ?>" class="d2e-sidebar-link <?= ($current_script === 'laporan.php') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Laporan Bulanan</span>
            </a>

            <div class="sidebar-heading mt-3">Sistem &amp; Pengguna</div>
            <a href="<?= base_url('admin/pengguna.php') ?>" class="d2e-sidebar-link <?= ($current_script === 'pengguna.php') ? 'active' : '' ?>">
                <i class="bi bi-people"></i>
                <span>Manajemen Pengguna</span>
            </a>
            <a href="<?= base_url('admin/profil.php') ?>" class="d2e-sidebar-link <?= ($current_script === 'profil.php') ? 'active' : '' ?>">
                <i class="bi bi-person-gear"></i>
                <span>Profil Saya</span>
            </a>
        <?php endif; ?>
    </nav>

    <!-- Sidebar Footer -->
    <div class="d2e-sidebar-footer">
        <a href="<?= base_url('logout.php') ?>" 
           class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2"
           data-confirm="Apakah Anda yakin ingin keluar dari sistem Drop2Earn?" 
           data-confirm-title="Konfirmasi Keluar" 
           data-confirm-type="warning" 
           data-confirm-btn="Ya, Keluar">
            <i class="bi bi-box-arrow-right"></i>
            <span>Keluar (Logout)</span>
        </a>
    </div>
</aside>

