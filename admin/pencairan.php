<?php
/**
 * Nasabah Withdrawal Approval Panel
 * Drop2Earn - Smart Waste Management
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth_check.php';

$admin = check_admin($conn);

$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Handle Approval (TC-07: Deduct Saldo & Update Status Atomically)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_approve'])) {
    $id_penarikan = (int)($_POST['id_penarikan'] ?? 0);

    // Fetch withdrawal request details
    $stmt_p = mysqli_prepare($conn, "
        SELECT p.*, u.saldo, u.nama_lengkap 
        FROM penarikan_saldo p
        JOIN users u ON p.id_user = u.id_user
        WHERE p.id_penarikan = ? AND p.status_tarik = 'Pending'
    ");
    mysqli_stmt_bind_param($stmt_p, "i", $id_penarikan);
    mysqli_stmt_execute($stmt_p);
    $res_p = mysqli_stmt_get_result($stmt_p);
    $tarik_data = mysqli_fetch_assoc($res_p);
    mysqli_stmt_close($stmt_p);

    if (!$tarik_data) {
        $_SESSION['flash_error'] = "Permohonan penarikan tidak ditemukan atau sudah diproses.";
    } elseif ($tarik_data['saldo'] < $tarik_data['jumlah_tarik']) {
        $_SESSION['flash_error'] = "Gagal menyetujui: Saldo nasabah saat ini (" . rupiah($tarik_data['saldo']) . ") lebih kecil dari jumlah penarikan (" . rupiah($tarik_data['jumlah_tarik']) . ").";
    } else {
        $id_user = (int)$tarik_data['id_user'];
        $jumlah_tarik = (int)$tarik_data['jumlah_tarik'];

        // Atomic Transaction Execution
        mysqli_begin_transaction($conn);
        try {
            // 1. Deduct balance from user
            $stmt_deduct = mysqli_prepare($conn, "UPDATE users SET saldo = saldo - ? WHERE id_user = ?");
            mysqli_stmt_bind_param($stmt_deduct, "ii", $jumlah_tarik, $id_user);
            if (!mysqli_stmt_execute($stmt_deduct)) {
                throw new Exception("Gagal memotong saldo nasabah: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt_deduct);

            // 2. Mark withdrawal as Disetujui
            $stmt_approve = mysqli_prepare($conn, "UPDATE penarikan_saldo SET status_tarik = 'Disetujui' WHERE id_penarikan = ?");
            mysqli_stmt_bind_param($stmt_approve, "i", $id_penarikan);
            if (!mysqli_stmt_execute($stmt_approve)) {
                throw new Exception("Gagal memperbarui status penarikan: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt_approve);

            mysqli_commit($conn);
            $_SESSION['flash_success'] = "Pencairan dana sebesar " . rupiah($jumlah_tarik) . " untuk nasabah " . sanitize($tarik_data['nama_lengkap']) . " berhasil disetujui dan saldo telah otomatis dipotong.";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['flash_error'] = "Terjadi kesalahan saat memproses persetujuan penarikan: " . $e->getMessage();
        }
    }
    header("Location: " . base_url('admin/pencairan.php'));
    exit;
}

// Handle Rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_reject'])) {
    $id_penarikan = (int)($_POST['id_penarikan'] ?? 0);
    $stmt_rej = mysqli_prepare($conn, "UPDATE penarikan_saldo SET status_tarik = 'Ditolak' WHERE id_penarikan = ? AND status_tarik = 'Pending'");
    mysqli_stmt_bind_param($stmt_rej, "i", $id_penarikan);
    if (mysqli_stmt_execute($stmt_rej)) {
        $_SESSION['flash_success'] = "Pengajuan penarikan #{$id_penarikan} telah ditolak. Saldo nasabah tetap utuh.";
    } else {
        $_SESSION['flash_error'] = "Gagal menolak pengajuan: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt_rej);
    header("Location: " . base_url('admin/pencairan.php'));
    exit;
}

// Filter handling
$filter_status = trim($_GET['status'] ?? 'Semua');
$where_clause = "";
$params = [];
$types = "";

if ($filter_status !== 'Semua' && in_array($filter_status, ['Pending', 'Disetujui', 'Ditolak'])) {
    $where_clause = "WHERE p.status_tarik = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$query = "
    SELECT p.*, u.nama_lengkap, u.email, u.no_hp, u.saldo as saldo_saat_ini
    FROM penarikan_saldo p
    JOIN users u ON p.id_user = u.id_user
    $where_clause
    ORDER BY CASE WHEN p.status_tarik = 'Pending' THEN 1 ELSE 2 END, p.tanggal_pengajuan DESC
";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
} else {
    $res = mysqli_query($conn, $query);
}

$withdrawals = [];
while ($row = mysqli_fetch_assoc($res)) {
    $withdrawals[] = $row;
}

$page_title = 'Persetujuan Pencairan Dana';
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
                                <li class="breadcrumb-item active" aria-current="page">Pencairan Dana</li>
                            </ol>
                        </nav>
                        <h1 class="h4 fw-bold text-dark mb-0">Persetujuan Pencairan Saldo Nasabah</h1>
                        <small class="text-muted">Validasi permohonan tarik uang tunai / transfer e-wallet nasabah</small>
                    </div>

                    <!-- Filter Status -->
                    <div class="btn-group btn-group-sm" role="group" aria-label="Filter status pencairan">
                        <a href="<?= base_url('admin/pencairan.php?status=Semua') ?>" class="btn <?= $filter_status === 'Semua' ? 'btn-primary' : 'btn-outline-secondary' ?>">Semua</a>
                        <a href="<?= base_url('admin/pencairan.php?status=Pending') ?>" class="btn <?= $filter_status === 'Pending' ? 'btn-primary' : 'btn-outline-secondary' ?>">Pending</a>
                        <a href="<?= base_url('admin/pencairan.php?status=Disetujui') ?>" class="btn <?= $filter_status === 'Disetujui' ? 'btn-primary' : 'btn-outline-secondary' ?>">Disetujui</a>
                        <a href="<?= base_url('admin/pencairan.php?status=Ditolak') ?>" class="btn <?= $filter_status === 'Ditolak' ? 'btn-primary' : 'btn-outline-secondary' ?>">Ditolak</a>
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
                        <?php if (empty($withdrawals)): ?>
                            <div class="empty-state py-5">
                                <i class="bi bi-credit-card-2-back empty-state-icon"></i>
                                <h2 class="h6 fw-bold text-dark mb-1">Tidak Ada Data Permohonan Penarikan</h2>
                                <p class="small text-muted mb-0">Belum ada pengajuan pencairan saldo dari nasabah.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive border-0">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Nama Nasabah &amp; Kontak</th>
                                            <th>Jumlah Penarikan</th>
                                            <th>Saldo Aktif Saat Ini</th>
                                            <th>Metode &amp; No. Tujuan</th>
                                            <th>Status</th>
                                            <th class="text-end">Aksi Pengelola</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($withdrawals as $w): ?>
                                            <?php
                                            $w_status_cls = 'status-pending';
                                            if ($w['status_tarik'] === 'Disetujui') $w_status_cls = 'status-disetujui';
                                            elseif ($w['status_tarik'] === 'Ditolak') $w_status_cls = 'status-ditolak';
                                            ?>
                                            <tr>
                                                <td>#<?= $w['id_penarikan'] ?></td>
                                                <td><?= format_tanggal($w['tanggal_pengajuan']) ?></td>
                                                <td>
                                                    <strong class="d-block text-dark small"><?= sanitize($w['nama_lengkap']) ?></strong>
                                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $w['no_hp']) ?>" target="_blank" class="text-decoration-none text-success small">
                                                        <i class="bi bi-whatsapp"></i> <?= sanitize($w['no_hp']) ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <strong class="text-success fs-6"><?= rupiah($w['jumlah_tarik']) ?></strong>
                                                </td>
                                                <td>
                                                    <span class="text-dark small fw-semibold"><?= rupiah($w['saldo_saat_ini']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-primary border mb-1"><?= sanitize($w['metode_pencairan']) ?></span>
                                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                                        <?= sanitize($w['nomor_rekening'] ?: 'Ambil Tunai di Posko') ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?= $w_status_cls ?>">
                                                        <i class="bi bi-circle-fill" style="font-size: 0.45rem;"></i>
                                                        <?= sanitize($w['status_tarik']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <?php if ($w['status_tarik'] === 'Pending'): ?>
                                                        <form action="<?= base_url('admin/pencairan.php') ?>" method="POST" class="d-inline">
                                                            <input type="hidden" name="action_approve" value="1">
                                                            <input type="hidden" name="id_penarikan" value="<?= $w['id_penarikan'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-success me-1" 
                                                                    data-confirm="Setujui pencairan dana sebesar <?= rupiah($w['jumlah_tarik']) ?>? Saldo nasabah akan otomatis terpotong."
                                                                    data-confirm-title="Setujui Pencairan Saldo"
                                                                    data-confirm-type="success"
                                                                    data-confirm-btn="Ya, Setujui">
                                                                <i class="bi bi-check2"></i> Setujui
                                                            </button>
                                                        </form>

                                                        <form action="<?= base_url('admin/pencairan.php') ?>" method="POST" class="d-inline">
                                                            <input type="hidden" name="action_reject" value="1">
                                                            <input type="hidden" name="id_penarikan" value="<?= $w['id_penarikan'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                    data-confirm="Tolak permohonan penarikan saldo ini? Saldo nasabah akan tetap utuh."
                                                                    data-confirm-title="Tolak Pencairan Saldo"
                                                                    data-confirm-type="danger"
                                                                    data-confirm-btn="Ya, Tolak">
                                                                <i class="bi bi-x-lg"></i> Tolak
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted small"><i class="bi bi-check2-all me-1"></i>Selesai Diproses</span>
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

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
