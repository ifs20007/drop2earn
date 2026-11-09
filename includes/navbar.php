<?php
/**
 * Public Navbar Component (Landing Page & Auth Pages)
 * Drop2Earn - Smart Waste Management
 */
$logged_user = isset($conn) ? get_auth_user($conn) : null;
$role = $logged_user['role'] ?? null;
$dashboard_url = ($role === 'admin') ? base_url('admin/index.php') : base_url('nasabah/dashboard.php');
$profile_url = ($role === 'admin') ? base_url('admin/profil.php') : base_url('nasabah/profil.php');
?>
<nav class="navbar navbar-expand-lg d2e-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('index.php') ?>">
            <img src="<?= base_url('assets/img/logo.svg') ?>" alt="Logo Drop2Earn" width="32" height="32">
            <span>Drop2Earn</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Buka navigasi menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('index.php') ?>">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('index.php#katalog') ?>">Katalog Harga</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('index.php#panduan') ?>">Panduan Pilah</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('index.php#lokasi') ?>">Lokasi Drop Point</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('promosi.php') ?>">
                        <i class="bi bi-megaphone me-1 text-success"></i>Materi Promosi
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if ($logged_user): ?>
                    <a href="<?= $dashboard_url ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-speedometer2 me-1"></i> Buka Dashboard
                    </a>

                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle text-success"></i>
                            <span><?= sanitize($logged_user['nama_lengkap']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold small"><?= sanitize($logged_user['nama_lengkap']) ?></div>
                                <div class="text-muted small" style="font-size: 0.75rem;"><?= sanitize($logged_user['email']) ?></div>
                                <span class="badge bg-secondary mt-1" style="font-size: 0.65rem;"><?= $role === 'admin' ? 'Administrator' : 'Nasabah' ?></span>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="<?= $profile_url ?>">
                                    <i class="bi bi-person-gear me-2 text-secondary"></i>Profil Saya
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
                                    <i class="bi bi-box-arrow-right me-2"></i>Keluar (Logout)
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= base_url('login.php') ?>" class="btn btn-outline-primary btn-sm">Masuk</a>
                    <a href="<?= base_url('register.php') ?>" class="btn btn-primary btn-sm">Daftar Nasabah</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
