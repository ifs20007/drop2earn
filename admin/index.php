<?php
/**
 * Admin Analytical Dashboard
 * Drop2Earn - Smart Waste Management
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth_check.php';

$admin = check_admin($conn);

// Key Analytics Metrics
// 1. Total Weight Collected
$res_total_kg = mysqli_query($conn, "SELECT COALESCE(SUM(berat_aktual), 0) as total_kg FROM setoran_sampah WHERE status = 'Selesai'");
$total_kg = mysqli_fetch_assoc($res_total_kg)['total_kg'] ?? 0;

// 2. Total Payout Disbursed
$res_payout = mysqli_query($conn, "SELECT COALESCE(SUM(total_pendapatan), 0) as total_payout FROM setoran_sampah WHERE status = 'Selesai'");
$total_payout = mysqli_fetch_assoc($res_payout)['total_payout'] ?? 0;

// 3. Total Registered Customers
$res_nasabah = mysqli_query($conn, "SELECT COUNT(id_user) as total_nasabah FROM users WHERE role = 'nasabah'");
$total_nasabah = mysqli_fetch_assoc($res_nasabah)['total_nasabah'] ?? 0;

// 4. Pending Deposit Queue
$res_queue_setor = mysqli_query($conn, "SELECT COUNT(id_setoran) as queue_setor FROM setoran_sampah WHERE status IN ('Menunggu', 'Dijemput/Diproses')");
$queue_setor = mysqli_fetch_assoc($res_queue_setor)['queue_setor'] ?? 0;

// 5. Pending Withdrawal Queue
$res_queue_tarik = mysqli_query($conn, "SELECT COUNT(id_penarikan) as queue_tarik FROM penarikan_saldo WHERE status_tarik = 'Pending'");
$queue_tarik = mysqli_fetch_assoc($res_queue_tarik)['queue_tarik'] ?? 0;

// Fetch Recent Pending Deposits (Needs Action)
$query_recent_setor = "
    SELECT s.*, u.nama_lengkap, u.no_hp, k.nama_kategori, k.harga_per_kg
    FROM setoran_sampah s
    JOIN users u ON s.id_user = u.id_user
    JOIN kategori_sampah k ON s.id_kategori = k.id_kategori
    WHERE s.status IN ('Menunggu', 'Dijemput/Diproses')
    ORDER BY s.created_at ASC
    LIMIT 5
";
$recent_setor = mysqli_fetch_all(mysqli_query($conn, $query_recent_setor), MYSQLI_ASSOC);

// Fetch Recent Pending Withdrawals
$query_recent_tarik = "
    SELECT p.*, u.nama_lengkap, u.saldo, u.no_hp
    FROM penarikan_saldo p
    JOIN users u ON p.id_user = u.id_user
    WHERE p.status_tarik = 'Pending'
    ORDER BY p.tanggal_pengajuan ASC
    LIMIT 5
";
$recent_tarik = mysqli_fetch_all(mysqli_query($conn, $query_recent_tarik), MYSQLI_ASSOC);

$page_title = 'Dashboard Pengelola Admin';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d2e-dashboard-layout">
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="d2e-dashboard-main">
        <!-- Topbar with "Halaman Depan" & User Dropdown -->
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="d2e-dashboard-content">
            <div class="container-fluid p-0">
                <!-- Admin Header -->
                <div class="d2e-card bg-white mb-4">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-md-8">
                            <span class="badge bg-dark mb-1">Panel Operasional Pengelola</span>
                            <h1 class="h4 fw-bold text-dark mb-1">Dashboard Analitik Bank Sampah</h1>
                            <p class="text-muted small mb-0">Selamat datang, <strong><?= sanitize($admin['nama_lengkap']) ?></strong>. Pantau volume daur ulang dan proses permintaan nasabah.</p>
                        </div>
                        <div class="col-12 col-md-4 d-flex justify-content-md-end gap-2">
                            <a href="<?= base_url('admin/verifikasi_setoran.php') ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-check2-circle me-1"></i> Verifikasi Timbangan
                            </a>
                            <a href="<?= base_url('admin/pencairan.php') ?>" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-cash-stack me-1"></i> Pencairan Dana
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 5 Key Analytics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-label">Total Sampah Terkumpul</div>
                            <div class="stat-value text-success"><?= format_kg($total_kg) ?></div>
                            <small class="text-muted" style="font-size: 0.78rem;">Sampah terpilah siap kirim ke pabrik</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-label">Total Dana Disalurkan</div>
                            <div class="stat-value text-dark"><?= rupiah($total_payout) ?></div>
                            <small class="text-muted" style="font-size: 0.78rem;">Nilai ekonomi yang dinikmati warga</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-label">Nasabah Terdaftar</div>
                            <div class="stat-value"><?= $total_nasabah ?> <span class="fs-6 fw-normal text-muted">warga</span></div>
                            <small class="text-muted" style="font-size: 0.78rem;">Akun rumah tangga &amp; mahasiswa</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card border-warning">
                            <div class="stat-label text-warning">Antrean Menunggu Tindakan</div>
                            <div class="stat-value text-warning"><?= $queue_setor + $queue_tarik ?> <span class="fs-6 fw-normal text-muted">tugas</span></div>
                            <small class="text-muted" style="font-size: 0.78rem;"><?= $queue_setor ?> setoran &bull; <?= $queue_tarik ?> penarikan</small>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Antrean Setoran Perlu Verifikasi Timbangan -->
                    <div class="col-12 col-lg-7">
                        <div class="card border h-100 shadow-sm">
                            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-clock-history text-warning fs-5"></i>
                                    <h2 class="h6 fw-bold text-dark mb-0">Antrean Setoran Menunggu Verifikasi Timbangan</h2>
                                </div>
                                <a href="<?= base_url('admin/verifikasi_setoran.php') ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($recent_setor)): ?>
                                    <div class="empty-state py-4">
                                        <i class="bi bi-check-circle text-success empty-state-icon"></i>
                                        <p class="small text-muted mb-0">Tidak ada antrean setoran sampah baru. Semua telah diverifikasi.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive border-0">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Nasabah &amp; Kontak</th>
                                                    <th>Kategori</th>
                                                    <th>Metode</th>
                                                    <th>Est. Berat</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_setor as $rs): ?>
                                                    <tr>
                                                        <td>
                                                            <strong class="d-block text-dark small"><?= sanitize($rs['nama_lengkap']) ?></strong>
                                                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $rs['no_hp']) ?>" target="_blank" class="text-decoration-none text-success small">
                                                                <i class="bi bi-whatsapp"></i> <?= sanitize($rs['no_hp']) ?>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <span class="small fw-semibold"><?= sanitize($rs['nama_kategori']) ?></span>
                                                            <div class="text-muted" style="font-size: 0.72rem;"><?= rupiah($rs['harga_per_kg']) ?>/kg</div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark border"><?= sanitize($rs['metode_setor']) ?></span>
                                                        </td>
                                                        <td><strong class="text-dark"><?= format_kg($rs['berat_estimasi']) ?></strong></td>
                                                        <td>
                                                            <a href="<?= base_url('admin/verifikasi_setoran.php?verif_id=' . $rs['id_setoran']) ?>" class="btn btn-sm btn-primary py-1 px-2">
                                                                <i class="bi bi-check-lg"></i> Timbang
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Antrean Permintaan Penarikan Saldo -->
                    <div class="col-12 col-lg-5">
                        <div class="card border h-100 shadow-sm">
                            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-cash-stack text-success fs-5"></i>
                                    <h2 class="h6 fw-bold text-dark mb-0">Permintaan Tarik Saldo Pending</h2>
                                </div>
                                <a href="<?= base_url('admin/pencairan.php') ?>" class="btn btn-sm btn-outline-success">Kelola</a>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($recent_tarik)): ?>
                                    <div class="empty-state py-4">
                                        <i class="bi bi-check2-all text-success empty-state-icon"></i>
                                        <p class="small text-muted mb-0">Tidak ada pengajuan tarik saldo yang menunggu persetujuan.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive border-0">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Nasabah</th>
                                                    <th>Nominal</th>
                                                    <th>Metode</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_tarik as $rt): ?>
                                                    <tr>
                                                        <td>
                                                            <strong class="d-block text-dark small"><?= sanitize($rt['nama_lengkap']) ?></strong>
                                                            <span class="text-muted" style="font-size: 0.72rem;">Saldo: <?= rupiah($rt['saldo']) ?></span>
                                                        </td>
                                                        <td>
                                                            <strong class="text-success"><?= rupiah($rt['jumlah_tarik']) ?></strong>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-primary border"><?= sanitize($rt['metode_pencairan']) ?></span>
                                                            <div class="text-muted" style="font-size: 0.72rem;"><?= sanitize($rt['nomor_rekening'] ?: 'Tunai') ?></div>
                                                        </td>
                                                        <td>
                                                            <a href="<?= base_url('admin/pencairan.php') ?>" class="btn btn-sm btn-outline-success py-1 px-2">
                                                                Proses
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
