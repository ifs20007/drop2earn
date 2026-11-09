<?php
/**
 * Monthly Financial & Waste Recap Report
 * Drop2Earn - Smart Waste Management (US-08)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth_check.php';

$admin = check_admin($conn);

// Filter by Month and Year (default to current month)
$bulan_filter = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun_filter = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

// Validate month and year
if ($bulan_filter < 1 || $bulan_filter > 12) {
    $bulan_filter = (int)date('m');
}
if ($tahun_filter < 2020 || $tahun_filter > 2035) {
    $tahun_filter = (int)date('Y');
}

$start_date = sprintf('%04d-%02d-01 00:00:00', $tahun_filter, $bulan_filter);
$end_date = date('Y-m-t 23:59:59', strtotime($start_date));

// 1. Completed Deposits for this month
$stmt_setoran = mysqli_prepare($conn, "
    SELECT s.*, u.nama_lengkap, u.no_hp, k.nama_kategori, k.jenis_induk, k.harga_per_kg
    FROM setoran_sampah s
    JOIN users u ON s.id_user = u.id_user
    JOIN kategori_sampah k ON s.id_kategori = k.id_kategori
    WHERE s.status = 'Selesai' 
      AND s.created_at BETWEEN ? AND ?
    ORDER BY s.created_at ASC
");
mysqli_stmt_bind_param($stmt_setoran, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_setoran);
$res_setoran = mysqli_stmt_get_result($stmt_setoran);
$laporan_setoran = [];
$total_berat_kg = 0.0;
$total_pembayaran_warga = 0;

while ($row = mysqli_fetch_assoc($res_setoran)) {
    $laporan_setoran[] = $row;
    $total_berat_kg += (float)$row['berat_aktual'];
    $total_pembayaran_warga += (int)$row['total_pendapatan'];
}
mysqli_stmt_close($stmt_setoran);

// Estimated Wholesale Resale Value to Recycling Factory (+15% gross spread fee as per business model)
$estimasi_jual_pabrik = (int)round($total_pembayaran_warga * 1.15);
$estimasi_margin_operasional = $estimasi_jual_pabrik - $total_pembayaran_warga;

// 2. Approved Withdrawals for this month
$stmt_tarik = mysqli_prepare($conn, "
    SELECT p.*, u.nama_lengkap, u.no_hp
    FROM penarikan_saldo p
    JOIN users u ON p.id_user = u.id_user
    WHERE p.status_tarik = 'Disetujui'
      AND p.tanggal_pengajuan BETWEEN ? AND ?
    ORDER BY p.tanggal_pengajuan ASC
");
mysqli_stmt_bind_param($stmt_tarik, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt_tarik);
$res_tarik = mysqli_stmt_get_result($stmt_tarik);
$laporan_tarik = [];
$total_dana_ditarik = 0;

while ($row = mysqli_fetch_assoc($res_tarik)) {
    $laporan_tarik[] = $row;
    $total_dana_ditarik += (int)$row['jumlah_tarik'];
}
mysqli_stmt_close($stmt_tarik);

$nama_bulan_id = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$page_title = 'Laporan Pembukuan Bulanan';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
@media print {
    .d2e-sidebar, .d2e-topbar, .d2e-dashboard-footer, .no-print, .breadcrumb, form {
        display: none !important;
    }
    .d2e-dashboard-main {
        margin-left: 0 !important;
    }
    body {
        background-color: #ffffff !important;
        font-size: 11pt;
    }
    .container-fluid {
        max-width: 100% !important;
        width: 100% !important;
        padding: 0 !important;
    }
    .card, .stat-card {
        border: 1px solid #ccc !important;
        box-shadow: none !important;
    }
    .print-header {
        display: block !important;
    }
}
.print-header {
    display: none;
}
</style>

<div class="d2e-dashboard-layout">
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="d2e-dashboard-main">
        <!-- Topbar with "Halaman Depan" & User Dropdown -->
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="d2e-dashboard-content">
            <div class="container-fluid p-0">
                <!-- Official Printable Header -->
                <div class="print-header mb-4 text-center border-bottom pb-3">
                    <h2 class="h4 fw-bold mb-1">BANK SAMPAH DIGITAL DROP2EARN</h2>
                    <p class="mb-1 text-muted small">Jl. Sutomo No. 18, Pematangsiantar &bull; Telp/WA: 0812-3456-7890</p>
                    <h3 class="h6 fw-bold text-dark mt-2">REKAPITULASI PEMBUKUAN TRANSAKSI BULAN: <?= strtoupper($nama_bulan_id[$bulan_filter]) ?> <?= $tahun_filter ?></h3>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 no-print">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-1">
                                <li class="breadcrumb-item"><a href="<?= base_url('admin/index.php') ?>" class="text-decoration-none">Dashboard Admin</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Laporan Bulanan</li>
                            </ol>
                        </nav>
                        <h1 class="h4 fw-bold text-dark mb-0">Laporan Pembukuan &amp; Volume Sampah</h1>
                        <small class="text-muted">Rekapitulasi transaksi penyetoran, penyaluran saldo, dan margin operasional bank sampah</small>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Cetak / Ekspor PDF
                        </button>
                    </div>
                </div>

                <!-- Filter Periode (No Print) -->
                <div class="card border mb-4 no-print shadow-sm">
                    <div class="card-body p-3">
                        <form action="<?= base_url('admin/laporan.php') ?>" method="GET" class="row g-2 align-items-center">
                            <div class="col-12 col-sm-auto">
                                <label for="bulan" class="small fw-semibold text-muted">Pilih Periode:</label>
                            </div>
                            <div class="col-6 col-sm-auto">
                                <select class="form-select form-select-sm" id="bulan" name="bulan">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?= $m ?>" <?= $m === $bulan_filter ? 'selected' : '' ?>>
                                            <?= $nama_bulan_id[$m] ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-6 col-sm-auto">
                                <select class="form-select form-select-sm" id="tahun" name="tahun">
                                    <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                                        <option value="<?= $y ?>" <?= $y === $tahun_filter ? 'selected' : '' ?>>
                                            <?= $y ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-12 col-sm-auto">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-filter me-1"></i> Tampilkan Rekap
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Ringkasan Finansial Periode Terpilih -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-label">Total Sampah Terkumpul</div>
                            <div class="stat-value text-success"><?= format_kg($total_berat_kg) ?></div>
                            <small class="text-muted" style="font-size: 0.78rem;"><?= count($laporan_setoran) ?> transaksi selesai</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-label">Total Dibayar ke Warga</div>
                            <div class="stat-value text-dark"><?= rupiah($total_pembayaran_warga) ?></div>
                            <small class="text-muted" style="font-size: 0.78rem;">Harga beli sampah nasabah</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card border-primary">
                            <div class="stat-label text-primary">Estimasi Nilai Jual Pabrik</div>
                            <div class="stat-value text-primary"><?= rupiah($estimasi_jual_pabrik) ?></div>
                            <small class="text-muted" style="font-size: 0.78rem;">Harga grosir ke pabrik (+15%)</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card border-success bg-light">
                            <div class="stat-label text-success">Estimasi Margin Operasional</div>
                            <div class="stat-value text-success"><?= rupiah($estimasi_margin_operasional) ?></div>
                            <small class="text-muted" style="font-size: 0.78rem;">Margin spread fee bank sampah</small>
                        </div>
                    </div>
                </div>

                <!-- Tabel 1: Rekap Penyetoran Sampah Selesai -->
                <div class="card border mb-4 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-box-seam text-success fs-5"></i>
                            <h2 class="h6 fw-bold text-dark mb-0">Rincian Penyetoran Sampah Selesai (Periode <?= $nama_bulan_id[$bulan_filter] ?> <?= $tahun_filter ?>)</h2>
                        </div>
                        <span class="badge bg-light text-dark border"><?= count($laporan_setoran) ?> Catatan</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($laporan_setoran)): ?>
                            <div class="empty-state py-4">
                                <i class="bi bi-inbox empty-state-icon"></i>
                                <p class="small text-muted mb-0">Tidak ada transaksi setoran selesai pada periode bulan ini.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive border-0">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tgl &amp; Waktu</th>
                                            <th>Nama Nasabah</th>
                                            <th>Jenis Sampah</th>
                                            <th>Metode</th>
                                            <th>Berat Riil (Kg)</th>
                                            <th>Harga Beli / Kg</th>
                                            <th>Total Bayar (Rp)</th>
                                            <th>Est. Jual Pabrik (+15%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($laporan_setoran as $i => $item): ?>
                                            <?php 
                                            $jual_pabrik_item = (int)round($item['total_pendapatan'] * 1.15);
                                            ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><small><?= date('d/m/Y H:i', strtotime($item['created_at'])) ?></small></td>
                                                <td>
                                                    <strong class="d-block small text-dark"><?= sanitize($item['nama_lengkap']) ?></strong>
                                                    <small class="text-muted"><?= sanitize($item['no_hp']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="small fw-semibold"><?= sanitize($item['nama_kategori']) ?></span>
                                                    <span class="badge bg-light text-muted border ms-1" style="font-size: 0.7rem;"><?= sanitize($item['jenis_induk']) ?></span>
                                                </td>
                                                <td><small class="badge bg-light text-dark border"><?= sanitize($item['metode_setor']) ?></small></td>
                                                <td><strong class="text-dark"><?= format_kg($item['berat_aktual']) ?></strong></td>
                                                <td><?= rupiah($item['harga_per_kg']) ?></td>
                                                <td><strong class="text-success"><?= rupiah($item['total_pendapatan']) ?></strong></td>
                                                <td><span class="text-muted small"><?= rupiah($jual_pabrik_item) ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td colspan="5" class="text-end">TOTAL KESELURUHAN:</td>
                                            <td><strong class="text-dark"><?= format_kg($total_berat_kg) ?></strong></td>
                                            <td>-</td>
                                            <td><strong class="text-success"><?= rupiah($total_pembayaran_warga) ?></strong></td>
                                            <td><strong class="text-primary"><?= rupiah($estimasi_jual_pabrik) ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tabel 2: Rekap Penarikan Saldo Disetujui -->
                <div class="card border shadow-sm">
                    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-wallet-fill text-primary fs-5"></i>
                            <h2 class="h6 fw-bold text-dark mb-0">Rincian Pencairan Saldo Disetujui (Periode <?= $nama_bulan_id[$bulan_filter] ?> <?= $tahun_filter ?>)</h2>
                        </div>
                        <span class="badge bg-light text-dark border">Total Keluar: <?= rupiah($total_dana_ditarik) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($laporan_tarik)): ?>
                            <div class="empty-state py-4">
                                <i class="bi bi-cash-stack empty-state-icon"></i>
                                <p class="small text-muted mb-0">Tidak ada transaksi pencairan dana disetujui pada periode bulan ini.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive border-0">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tgl Persetujuan</th>
                                            <th>Nama Nasabah</th>
                                            <th>Nominal Penarikan</th>
                                            <th>Metode Pencairan</th>
                                            <th>Nomor Rekening / E-Wallet</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($laporan_tarik as $j => $t): ?>
                                            <tr>
                                                <td><?= $j + 1 ?></td>
                                                <td><small><?= format_tanggal($t['tanggal_pengajuan']) ?></small></td>
                                                <td>
                                                    <strong class="d-block small text-dark"><?= sanitize($t['nama_lengkap']) ?></strong>
                                                    <small class="text-muted"><?= sanitize($t['no_hp']) ?></small>
                                                </td>
                                                <td><strong class="text-danger"><?= rupiah($t['jumlah_tarik']) ?></strong></td>
                                                <td><span class="badge bg-light text-primary border"><?= sanitize($t['metode_pencairan']) ?></span></td>
                                                <td><?= sanitize($t['nomor_rekening'] ?: 'Ambil Tunai di Posko') ?></td>
                                                <td><span class="badge bg-success-subtle text-success border border-success-subtle">Disetujui</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td colspan="3" class="text-end">TOTAL PENARIKAN DANA DICAIRKAN:</td>
                                            <td><strong class="text-danger"><?= rupiah($total_dana_ditarik) ?></strong></td>
                                            <td colspan="3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Print Footer Signatures (Print Only) -->
                <div class="print-header mt-5 pt-4">
                    <div class="row">
                        <div class="col-6 text-center">
                            <p class="mb-5">Mengetahui,<br><strong>Ketua Pengelola Bank Sampah</strong></p>
                            <p class="mt-5 text-decoration-underline fw-bold">( ............................................ )</p>
                        </div>
                        <div class="col-6 text-center">
                            <p class="mb-5">Pematangsiantar, <?= date('d') ?> <?= $nama_bulan_id[(int)date('m')] ?> <?= date('Y') ?><br><strong>Petugas Bendahara / Administrasi</strong></p>
                            <p class="mt-5 text-decoration-underline fw-bold">( <?= sanitize($admin['nama_lengkap']) ?> )</p>
                        </div>
                    </div>
                </div>

            </div>
        </main>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
