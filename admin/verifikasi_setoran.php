<?php
/**
 * Deposit Verification & Physical Weighing Panel
 * Drop2Earn - Smart Waste Management
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth_check.php';

$admin = check_admin($conn);

$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Handle Status Change to "Dijemput/Diproses"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_jemput'])) {
    $id_setoran = (int)($_POST['id_setoran'] ?? 0);
    $stmt = mysqli_prepare($conn, "UPDATE setoran_sampah SET status = 'Dijemput/Diproses' WHERE id_setoran = ? AND status = 'Menunggu'");
    mysqli_stmt_bind_param($stmt, "i", $id_setoran);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = "Status setoran #{$id_setoran} diperbarui menjadi 'Dijemput/Diproses'.";
    }
    mysqli_stmt_close($stmt);
    header("Location: " . base_url('admin/verifikasi_setoran.php'));
    exit;
}

// Handle Status Change to "Dibatalkan"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_cancel'])) {
    $id_setoran = (int)($_POST['id_setoran'] ?? 0);
    $stmt = mysqli_prepare($conn, "UPDATE setoran_sampah SET status = 'Dibatalkan' WHERE id_setoran = ? AND status IN ('Menunggu', 'Dijemput/Diproses')");
    mysqli_stmt_bind_param($stmt, "i", $id_setoran);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = "Setoran #{$id_setoran} telah dibatalkan.";
    }
    mysqli_stmt_close($stmt);
    header("Location: " . base_url('admin/verifikasi_setoran.php'));
    exit;
}

// Handle Verification & Atomic Balance Addition (TC-05)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_verify'])) {
    $id_setoran = (int)($_POST['id_setoran'] ?? 0);
    $berat_aktual = (float)($_POST['berat_aktual'] ?? 0);

    if ($id_setoran <= 0 || $berat_aktual <= 0) {
        $_SESSION['flash_error'] = "Berat aktual hasil penimbangan harus lebih besar dari 0 kg.";
    } else {
        // Fetch current deposit details to verify validity and fetch unit price & customer id
        $stmt_get = mysqli_prepare($conn, "
            SELECT s.id_setoran, s.id_user, s.status, k.harga_per_kg, u.nama_lengkap
            FROM setoran_sampah s
            JOIN kategori_sampah k ON s.id_kategori = k.id_kategori
            JOIN users u ON s.id_user = u.id_user
            WHERE s.id_setoran = ?
        ");
        mysqli_stmt_bind_param($stmt_get, "i", $id_setoran);
        mysqli_stmt_execute($stmt_get);
        $res = mysqli_stmt_get_result($stmt_get);
        $setoran_data = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt_get);

        if (!$setoran_data) {
            $_SESSION['flash_error'] = "Data setoran tidak ditemukan.";
        } elseif ($setoran_data['status'] === 'Selesai') {
            $_SESSION['flash_error'] = "Setoran ini sudah selesai diverifikasi sebelumnya.";
        } else {
            $harga_per_kg = (int)$setoran_data['harga_per_kg'];
            $id_user = (int)$setoran_data['id_user'];
            $total_pendapatan = (int)round($berat_aktual * $harga_per_kg);

            // Execute Atomic Transaction
            mysqli_begin_transaction($conn);
            try {
                // 1. Update setoran_sampah
                $status_selesai = 'Selesai';
                $stmt_upd = mysqli_prepare($conn, "
                    UPDATE setoran_sampah 
                    SET berat_aktual = ?, total_pendapatan = ?, status = ? 
                    WHERE id_setoran = ?
                ");
                mysqli_stmt_bind_param($stmt_upd, "disi", $berat_aktual, $total_pendapatan, $status_selesai, $id_setoran);
                if (!mysqli_stmt_execute($stmt_upd)) {
                    throw new Exception("Gagal memperbarui data timbangan setoran: " . mysqli_error($conn));
                }
                mysqli_stmt_close($stmt_upd);

                // 2. Add saldo to user
                $stmt_saldo = mysqli_prepare($conn, "
                    UPDATE users 
                    SET saldo = saldo + ? 
                    WHERE id_user = ?
                ");
                mysqli_stmt_bind_param($stmt_saldo, "ii", $total_pendapatan, $id_user);
                if (!mysqli_stmt_execute($stmt_saldo)) {
                    throw new Exception("Gagal menambahkan saldo ke rekening nasabah: " . mysqli_error($conn));
                }
                mysqli_stmt_close($stmt_saldo);

                // Commit the atomic transaction
                mysqli_commit($conn);

                $_SESSION['flash_success'] = "Verifikasi timbangan berhasil! Berat: " . format_kg($berat_aktual) . ", total " . rupiah($total_pendapatan) . " telah otomatis ditambahkan ke saldo nasabah " . sanitize($setoran_data['nama_lengkap']) . ".";
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $_SESSION['flash_error'] = "Terjadi kesalahan sistem saat memproses transaksi: " . $e->getMessage();
            }
        }
    }
    header("Location: " . base_url('admin/verifikasi_setoran.php'));
    exit;
}

// Filter handling
$filter_status = trim($_GET['status'] ?? 'Semua');
$where_clause = "";
$params = [];
$types = "";

if ($filter_status !== 'Semua' && in_array($filter_status, ['Menunggu', 'Dijemput/Diproses', 'Selesai', 'Dibatalkan'])) {
    $where_clause = "WHERE s.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$query_list = "
    SELECT s.*, u.nama_lengkap, u.email, u.no_hp, u.saldo as saldo_nasabah, k.nama_kategori, k.harga_per_kg, k.jenis_induk
    FROM setoran_sampah s
    JOIN users u ON s.id_user = u.id_user
    JOIN kategori_sampah k ON s.id_kategori = k.id_kategori
    $where_clause
    ORDER BY CASE 
        WHEN s.status = 'Menunggu' THEN 1
        WHEN s.status = 'Dijemput/Diproses' THEN 2
        WHEN s.status = 'Selesai' THEN 3
        ELSE 4
    END, s.created_at DESC
";

if (!empty($params)) {
    $stmt_list = mysqli_prepare($conn, $query_list);
    mysqli_stmt_bind_param($stmt_list, $types, ...$params);
    mysqli_stmt_execute($stmt_list);
    $res_list = mysqli_stmt_get_result($stmt_list);
} else {
    $res_list = mysqli_query($conn, $query_list);
}

$setoran_list = [];
while ($row = mysqli_fetch_assoc($res_list)) {
    $setoran_list[] = $row;
}

$page_title = 'Verifikasi Penimbangan Setoran';
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
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-1">
                                <li class="breadcrumb-item"><a href="<?= base_url('admin/index.php') ?>" class="text-decoration-none">Dashboard Admin</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Verifikasi Setoran</li>
                            </ol>
                        </nav>
                        <h1 class="h4 fw-bold text-dark mb-0">Verifikasi Penimbangan &amp; Perhitungan Saldo</h1>
                        <small class="text-muted">Input berat fisik hasil timbangan, sistem otomatis mengkalkulasi rupiah dan menambah saldo nasabah</small>
                    </div>
                    
                    <!-- Filter Status Tab -->
                    <div class="btn-group btn-group-sm" role="group" aria-label="Filter status transaksi">
                        <a href="<?= base_url('admin/verifikasi_setoran.php?status=Semua') ?>" class="btn <?= $filter_status === 'Semua' ? 'btn-primary' : 'btn-outline-secondary' ?>">Semua</a>
                        <a href="<?= base_url('admin/verifikasi_setoran.php?status=Menunggu') ?>" class="btn <?= $filter_status === 'Menunggu' ? 'btn-primary' : 'btn-outline-secondary' ?>">Menunggu</a>
                        <a href="<?= base_url('admin/verifikasi_setoran.php?status=Dijemput/Diproses') ?>" class="btn <?= $filter_status === 'Dijemput/Diproses' ? 'btn-primary' : 'btn-outline-secondary' ?>">Diproses</a>
                        <a href="<?= base_url('admin/verifikasi_setoran.php?status=Selesai') ?>" class="btn <?= $filter_status === 'Selesai' ? 'btn-primary' : 'btn-outline-secondary' ?>">Selesai</a>
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

                <div class="card border shadow-sm">
                    <div class="card-body p-0">
                        <?php if (empty($setoran_list)): ?>
                            <div class="empty-state py-5">
                                <i class="bi bi-folder2-open empty-state-icon"></i>
                                <h2 class="h6 fw-bold text-dark mb-1">Tidak Ada Data Setoran Sampah</h2>
                                <p class="small text-muted mb-0">Belum ada pengajuan setoran yang sesuai dengan filter ini.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive border-0">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nasabah &amp; Kontak</th>
                                            <th>Kategori Sampah</th>
                                            <th>Metode &amp; Jadwal</th>
                                            <th>Estimasi</th>
                                            <th>Hasil Timbangan</th>
                                            <th>Total Rupiah</th>
                                            <th>Status</th>
                                            <th class="text-end">Aksi Verifikasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($setoran_list as $row): ?>
                                            <?php
                                            $status_cls = 'status-menunggu';
                                            if ($row['status'] === 'Dijemput/Diproses') $status_cls = 'status-dijemput';
                                            elseif ($row['status'] === 'Selesai') $status_cls = 'status-selesai';
                                            elseif ($row['status'] === 'Dibatalkan') $status_cls = 'status-dibatalkan';
                                            ?>
                                            <tr>
                                                <td>#<?= $row['id_setoran'] ?></td>
                                                <td>
                                                    <strong class="d-block text-dark small"><?= sanitize($row['nama_lengkap']) ?></strong>
                                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $row['no_hp']) ?>" target="_blank" class="text-decoration-none text-success small">
                                                        <i class="bi bi-whatsapp"></i> <?= sanitize($row['no_hp']) ?>
                                                    </a>
                                                    <div class="text-muted" style="font-size: 0.72rem;">Saldo saat ini: <?= rupiah($row['saldo_nasabah']) ?></div>
                                                </td>
                                                <td>
                                                    <strong class="text-dark small d-block"><?= sanitize($row['nama_kategori']) ?></strong>
                                                    <span class="text-muted small"><?= rupiah($row['harga_per_kg']) ?> / kg</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border mb-1"><?= sanitize($row['metode_setor']) ?></span>
                                                    <div class="small text-muted"><i class="bi bi-calendar-event me-1"></i><?= format_tanggal($row['tanggal_jadwal']) ?></div>
                                                    <div class="text-muted text-truncate" style="max-width: 170px; font-size: 0.72rem;" title="<?= sanitize($row['alamat_jemput']) ?>">
                                                        <?= sanitize($row['alamat_jemput']) ?>
                                                    </div>
                                                </td>
                                                <td><?= format_kg($row['berat_estimasi']) ?></td>
                                                <td>
                                                    <?php if ($row['status'] === 'Selesai'): ?>
                                                        <strong class="text-dark"><?= format_kg($row['berat_aktual']) ?></strong>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-warning border">Belum Ditimbang</span>
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
                                                <td class="text-end">
                                                    <?php if ($row['status'] === 'Menunggu'): ?>
                                                        <form action="<?= base_url('admin/verifikasi_setoran.php') ?>" method="POST" class="d-inline">
                                                            <input type="hidden" name="action_jemput" value="1">
                                                            <input type="hidden" name="id_setoran" value="<?= $row['id_setoran'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-primary me-1" title="Tandai Dijemput / Diproses">
                                                                <i class="bi bi-truck"></i> Jemput
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($row['status'] !== 'Selesai' && $row['status'] !== 'Dibatalkan'): ?>
                                                        <button type="button" class="btn btn-sm btn-success me-1 btn-modal-timbang"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalTimbang"
                                                                data-id="<?= $row['id_setoran'] ?>"
                                                                data-nasabah="<?= sanitize($row['nama_lengkap']) ?>"
                                                                data-kategori="<?= sanitize($row['nama_kategori']) ?>"
                                                                data-harga="<?= $row['harga_per_kg'] ?>"
                                                                data-estimasi="<?= $row['berat_estimasi'] ?>">
                                                            <i class="bi bi-calculator"></i> Timbang
                                                        </button>

                                                        <form action="<?= base_url('admin/verifikasi_setoran.php') ?>" method="POST" class="d-inline">
                                                            <input type="hidden" name="action_cancel" value="1">
                                                            <input type="hidden" name="id_setoran" value="<?= $row['id_setoran'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                    data-confirm="Apakah Anda yakin ingin membatalkan transaksi setoran ini?"
                                                                    data-confirm-title="Batalkan Setoran"
                                                                    data-confirm-type="danger"
                                                                    data-confirm-btn="Ya, Batalkan">
                                                                <i class="bi bi-x-lg"></i>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted small"><i class="bi bi-check2-all text-success me-1"></i>Tuntas</span>
                                                    <?php endif; ?>
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

<!-- Modal Input Penimbangan & Kalkulasi Saldo (Tampilan 5 Prototype) -->
<div class="modal fade" id="modalTimbang" tabindex="-1" aria-labelledby="modalTimbangLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('admin/verifikasi_setoran.php') ?>" method="POST">
                <input type="hidden" name="action_verify" value="1">
                <input type="hidden" name="id_setoran" id="timbang_id_setoran">
                <input type="hidden" id="admin_harga_per_kg">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTimbangLabel">Input Hasil Timbangan Fisik Sampah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 bg-light border rounded mb-3">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <span class="text-muted">Nama Nasabah:</span>
                                <strong class="d-block text-dark" id="timbang_nasabah">-</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted">Kategori Sampah:</span>
                                <strong class="d-block text-dark" id="timbang_kategori">-</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted">Harga Resmi / Kg:</span>
                                <strong class="d-block text-success" id="timbang_harga_text">-</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted">Estimasi Nasabah:</span>
                                <strong class="d-block text-dark" id="timbang_estimasi_text">-</strong>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="admin_berat_aktual" class="form-label small fw-semibold">Berat Aktual Timbangan Riil (Kg) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.05" min="0.1" class="form-control" id="admin_berat_aktual" name="berat_aktual" placeholder="Contoh: 8.50" required autofocus>
                            <span class="input-group-text">Kg</span>
                        </div>
                        <div class="form-text small">Masukkan angka hasil timbangan riil petugas.</div>
                    </div>

                    <div class="p-3 border rounded border-success-subtle bg-light text-center">
                        <small class="text-muted d-block mb-1">Total Nilai Sampah yang Otomatis Masuk Saldo Nasabah:</small>
                        <span class="fs-3 fw-bold text-success" id="admin_total_display">Rp 0</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" data-confirm="Verifikasi setoran ini dan tambahkan saldo otomatis ke rekening nasabah?">
                        <i class="bi bi-check2-circle me-1"></i> Verifikasi &amp; Tambah Saldo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const timbangBtns = document.querySelectorAll('.btn-modal-timbang');
    const inputId = document.getElementById('timbang_id_setoran');
    const inputHarga = document.getElementById('admin_harga_per_kg');
    const inputBerat = document.getElementById('admin_berat_aktual');
    const txtNasabah = document.getElementById('timbang_nasabah');
    const txtKategori = document.getElementById('timbang_kategori');
    const txtHarga = document.getElementById('timbang_harga_text');
    const txtEstimasi = document.getElementById('timbang_estimasi_text');
    const txtTotal = document.getElementById('admin_total_display');

    function formatRupiah(val) {
        return 'Rp ' + Math.round(val).toLocaleString('id-ID');
    }

    function recompute() {
        const h = parseFloat(inputHarga.value || 0);
        const b = parseFloat(inputBerat.value || 0);
        const tot = h * b;
        txtTotal.textContent = formatRupiah(tot);
    }

    timbangBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const nasabah = this.getAttribute('data-nasabah');
            const kategori = this.getAttribute('data-kategori');
            const harga = this.getAttribute('data-harga');
            const estimasi = this.getAttribute('data-estimasi');

            inputId.value = id;
            inputHarga.value = harga;
            inputBerat.value = estimasi; // Prefill with customer estimate for convenience
            txtNasabah.textContent = nasabah;
            txtKategori.textContent = kategori;
            txtHarga.textContent = formatRupiah(harga) + ' / kg';
            txtEstimasi.textContent = estimasi + ' kg';

            recompute();
        });
    });

    inputBerat.addEventListener('input', recompute);

    // Auto-open modal if verif_id query param is present
    const urlParams = new URLSearchParams(window.location.search);
    const verifId = urlParams.get('verif_id');
    if (verifId) {
        const targetBtn = document.querySelector(`.btn-modal-timbang[data-id="${verifId}"]`);
        if (targetBtn) {
            targetBtn.click();
        }
    }
});
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
