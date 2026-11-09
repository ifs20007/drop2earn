<?php
/**
 * Nasabah Dashboard & Digital Passbook (Buku Tabungan)
 * Drop2Earn - Smart Waste Management
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth_check.php';

$user = check_nasabah($conn);
$id_user = $user['id_user'];

$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Calculate Total Weight of Completed Deposits
$stmt_kg = mysqli_prepare($conn, "SELECT COALESCE(SUM(berat_aktual), 0) as total_kg, COUNT(id_setoran) as total_transaksi FROM setoran_sampah WHERE id_user = ? AND status = 'Selesai'");
mysqli_stmt_bind_param($stmt_kg, "i", $id_user);
mysqli_stmt_execute($stmt_kg);
$res_kg = mysqli_stmt_get_result($stmt_kg);
$stat_selesai = mysqli_fetch_assoc($res_kg);
$total_kg = $stat_selesai['total_kg'] ?? 0;
$total_selesai = $stat_selesai['total_transaksi'] ?? 0;
mysqli_stmt_close($stmt_kg);

// Calculate Pending Deposits Count
$stmt_pending = mysqli_prepare($conn, "SELECT COUNT(id_setoran) as total_pending FROM setoran_sampah WHERE id_user = ? AND status IN ('Menunggu', 'Dijemput/Diproses')");
mysqli_stmt_bind_param($stmt_pending, "i", $id_user);
mysqli_stmt_execute($stmt_pending);
$res_pending = mysqli_stmt_get_result($stmt_pending);
$total_pending = mysqli_fetch_assoc($res_pending)['total_pending'] ?? 0;
mysqli_stmt_close($stmt_pending);

// Fetch Deposit History with Category Info
$stmt_setoran = mysqli_prepare($conn, "
    SELECT s.*, k.nama_kategori, k.harga_per_kg, k.jenis_induk
    FROM setoran_sampah s
    JOIN kategori_sampah k ON s.id_kategori = k.id_kategori
    WHERE s.id_user = ?
    ORDER BY s.created_at DESC
");
mysqli_stmt_bind_param($stmt_setoran, "i", $id_user);
mysqli_stmt_execute($stmt_setoran);
$res_setoran = mysqli_stmt_get_result($stmt_setoran);
$history_setoran = [];
while ($row = mysqli_fetch_assoc($res_setoran)) {
    $history_setoran[] = $row;
}
mysqli_stmt_close($stmt_setoran);

// Fetch Withdrawal History
$stmt_tarik = mysqli_prepare($conn, "
    SELECT * FROM penarikan_saldo
    WHERE id_user = ?
    ORDER BY tanggal_pengajuan DESC
");
mysqli_stmt_bind_param($stmt_tarik, "i", $id_user);
mysqli_stmt_execute($stmt_tarik);
$res_tarik = mysqli_stmt_get_result($stmt_tarik);
$history_tarik = [];
while ($row = mysqli_fetch_assoc($res_tarik)) {
    $history_tarik[] = $row;
}
mysqli_stmt_close($stmt_tarik);

$page_title = 'Buku Tabungan Nasabah';
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
                <!-- Welcome Banner & Quick Actions -->
                <div class="d2e-card mb-4 bg-white">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-md-7">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fs-4" style="width: 52px; height: 52px;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div>
                                    <span class="badge bg-light text-success border border-success-subtle mb-1">Buku Tabungan Nasabah</span>
                                    <h1 class="h4 fw-bold text-dark mb-0"><?= sanitize($user['nama_lengkap']) ?></h1>
                                    <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= sanitize($user['alamat'] ?: 'Alamat belum diatur') ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-5 d-flex justify-content-md-end gap-2">
                            <a href="<?= base_url('nasabah/setor.php') ?>" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> Setor Sampah
                            </a>
                            <a href="<?= base_url('nasabah/tarik_saldo.php') ?>" class="btn btn-outline-success">
                                <i class="bi bi-cash-stack me-1"></i> Tarik Saldo
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (!empty($flash_success)): ?>
                    <div class="alert alert-success alert-dismissible fade show small d-flex align-items-center" role="alert">
                        <i class="bi bi-check-circle-fill me-2 fs-6"></i>
                        <div><?= sanitize($flash_success) ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup notifikasi"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($flash_error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show small d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                        <div><?= sanitize($flash_error) ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup notifikasi"></button>
                    </div>
                <?php endif; ?>

                <!-- Stat Widgets -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card border-success shadow-sm">
                            <div class="stat-label text-success">Total Saldo Tabungan</div>
                            <div class="stat-value text-success"><?= rupiah($user['saldo']) ?></div>
                            <small class="text-muted" style="font-size: 0.78rem;">Tersedia untuk ditarik tunai/e-wallet</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-label">Total Sampah Terkumpul</div>
                            <div class="stat-value"><?= format_kg($total_kg) ?></div>
                            <small class="text-muted" style="font-size: 0.78rem;">Dari total <?= $total_selesai ?> setoran selesai</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-label">Permintaan Dalam Proses</div>
                            <div class="stat-value"><?= $total_pending ?> <span class="fs-6 fw-normal text-muted">antrean</span></div>
                            <small class="text-muted" style="font-size: 0.78rem;">Menunggu verifikasi / jadwal jemput</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-label">Nomor WhatsApp Nasabah</div>
                            <div class="stat-value fs-5 mt-1"><?= sanitize($user['no_hp']) ?></div>
                            <small class="text-muted" style="font-size: 0.78rem;">Digunakan untuk koordinasi jemput</small>
                        </div>
                    </div>
                </div>

                <!-- Tabel Riwayat Penyetoran Sampah (E-Passbook) -->
                <div class="card border mb-4">
                    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-journal-text text-success fs-5"></i>
                            <h2 class="h6 fw-bold text-dark mb-0">Riwayat Penyetoran Sampah (Buku Tabungan Digital)</h2>
                        </div>
                        <a href="<?= base_url('nasabah/setor.php') ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Ajukan Setoran Baru
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($history_setoran)): ?>
                            <div class="empty-state">
                                <i class="bi bi-inbox empty-state-icon"></i>
                                <h3 class="h6 text-dark fw-bold mb-1">Belum Ada Transaksi Setor Sampah</h3>
                                <p class="small text-muted mb-3">Mulai pilah sampah di rumah Anda dan ajukan penjemputan untuk menghasilkan saldo tabungan.</p>
                                <a href="<?= base_url('nasabah/setor.php') ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-box-arrow-in-down me-1"></i> Buat Setoran Pertama
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive border-0">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tgl &amp; Jadwal</th>
                                            <th>Kategori Sampah</th>
                                            <th>Metode</th>
                                            <th>Estimasi</th>
                                            <th>Hasil Timbangan</th>
                                            <th>Total Rupiah</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($history_setoran as $idx => $row): ?>
                                            <?php
                                            $status_cls = 'status-menunggu';
                                            if ($row['status'] === 'Dijemput/Diproses') $status_cls = 'status-dijemput';
                                            elseif ($row['status'] === 'Selesai') $status_cls = 'status-selesai';
                                            elseif ($row['status'] === 'Dibatalkan') $status_cls = 'status-dibatalkan';
                                            ?>
                                            <tr>
                                                <td><?= $idx + 1 ?></td>
                                                <td>
                                                    <strong class="d-block text-dark small"><?= format_tanggal($row['tanggal_jadwal']) ?></strong>
                                                    <span class="text-muted" style="font-size: 0.75rem;">Dibuat: <?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-dark"><?= sanitize($row['nama_kategori']) ?></span>
                                                    <div class="text-muted small" style="font-size: 0.75rem;"><?= rupiah($row['harga_per_kg']) ?> / kg</div>
                                                </td>
                                                <td>
                                                    <?php if ($row['metode_setor'] === 'Jemput ke Rumah'): ?>
                                                        <span class="badge bg-light text-primary border"><i class="bi bi-truck me-1"></i>Jemput ke Rumah</span>
                                                        <div class="text-muted text-truncate" style="font-size: 0.72rem; max-width: 160px;" title="<?= sanitize($row['alamat_jemput']) ?>">
                                                            <?= sanitize($row['alamat_jemput']) ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-secondary border"><i class="bi bi-building me-1"></i>Antar ke Drop Point</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= format_kg($row['berat_estimasi']) ?></td>
                                                <td>
                                                    <?php if ($row['status'] === 'Selesai'): ?>
                                                        <strong class="text-dark"><?= format_kg($row['berat_aktual']) ?></strong>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-warning border">Menunggu timbangan</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($row['status'] === 'Selesai'): ?>
                                                        <strong class="text-success"><?= rupiah($row['total_pendapatan']) ?></strong>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Rp 0</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?= $status_cls ?>">
                                                        <i class="bi bi-circle-fill" style="font-size: 0.45rem;"></i>
                                                        <?= sanitize($row['status']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tabel Riwayat Penarikan Saldo -->
                <div class="card border">
                    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-cash-coin text-success fs-5"></i>
                            <h2 class="h6 fw-bold text-dark mb-0">Riwayat Pengajuan Penarikan Saldo (Pencairan)</h2>
                        </div>
                        <a href="<?= base_url('nasabah/tarik_saldo.php') ?>" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-cash me-1"></i> Tarik Dana
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($history_tarik)): ?>
                            <div class="empty-state py-4">
                                <i class="bi bi-credit-card-2-front empty-state-icon"></i>
                                <p class="small text-muted mb-0">Belum ada riwayat penarikan saldo. Saldo Anda saat ini: <strong><?= rupiah($user['saldo']) ?></strong></p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive border-0">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Nominal Penarikan</th>
                                            <th>Metode Pencairan</th>
                                            <th>Nomor Rekening / E-Wallet</th>
                                            <th>Status Persetujuan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($history_tarik as $idx => $t): ?>
                                            <?php
                                            $t_status_cls = 'status-pending';
                                            if ($t['status_tarik'] === 'Disetujui') $t_status_cls = 'status-disetujui';
                                            elseif ($t['status_tarik'] === 'Ditolak') $t_status_cls = 'status-ditolak';
                                            ?>
                                            <tr>
                                                <td><?= $idx + 1 ?></td>
                                                <td><?= format_tanggal($t['tanggal_pengajuan']) ?></td>
                                                <td><strong class="text-dark"><?= rupiah($t['jumlah_tarik']) ?></strong></td>
                                                <td><span class="badge bg-light text-dark border"><?= sanitize($t['metode_pencairan']) ?></span></td>
                                                <td><?= sanitize($t['nomor_rekening'] ?: 'Ambil Tunai di Posko') ?></td>
                                                <td>
                                                    <span class="status-badge <?= $t_status_cls ?>">
                                                        <i class="bi bi-circle-fill" style="font-size: 0.45rem;"></i>
                                                        <?= sanitize($t['status_tarik']) ?>
                                                    </span>
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
        </main>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
