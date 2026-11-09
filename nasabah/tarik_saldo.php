<?php
/**
 * Saldo Withdrawal Request Page
 * Drop2Earn - Smart Waste Management
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth_check.php';

$user = check_nasabah($conn);
$id_user = $user['id_user'];
$saldo_aktif = (int)$user['saldo'];

$error_message = '';
$jumlah_tarik = '';
$metode_pencairan = 'DANA';
$nomor_rekening = $user['no_hp']; // Default prefilled with user's phone for e-wallet convenience

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jumlah_tarik = (int)($_POST['jumlah_tarik'] ?? 0);
    $metode_pencairan = trim($_POST['metode_pencairan'] ?? '');
    $nomor_rekening = trim($_POST['nomor_rekening'] ?? '');

    // Validation
    if ($jumlah_tarik < 10000) {
        $error_message = "Batas minimal penarikan saldo adalah Rp 10.000.";
    } elseif ($jumlah_tarik > $saldo_aktif) {
        // Essential check from TC-06: reject if withdrawal exceeds balance
        $error_message = "Saldo Anda tidak mencukupi! Saldo aktif Anda saat ini adalah " . rupiah($saldo_aktif) . ".";
    } elseif (empty($metode_pencairan)) {
        $error_message = "Silakan pilih metode pencairan dana.";
    } elseif ($metode_pencairan !== 'Ambil Tunai di Posko Bank Sampah' && empty($nomor_rekening)) {
        $error_message = "Nomor E-Wallet atau nomor rekening tujuan wajib diisi.";
    } else {
        $status_tarik = 'Pending';

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO penarikan_saldo (id_user, jumlah_tarik, metode_pencairan, nomor_rekening, status_tarik) VALUES (?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "iisss", $id_user, $jumlah_tarik, $metode_pencairan, $nomor_rekening, $status_tarik);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            $_SESSION['flash_success'] = "Pengajuan penarikan dana sebesar " . rupiah($jumlah_tarik) . " berhasil diajukan! Admin akan memvalidasi dan mengirimkan dana ke akun Anda.";
            header("Location: " . base_url('nasabah/dashboard.php'));
            exit;
        } else {
            $error_message = "Gagal memproses permohonan penarikan: " . mysqli_error($conn);
            mysqli_stmt_close($stmt);
        }
    }
}

$page_title = 'Tarik Saldo Tabungan';
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
            <div class="container-fluid p-0" style="max-width: 680px;">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb small">
                        <li class="breadcrumb-item"><a href="<?= base_url('nasabah/dashboard.php') ?>" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Pencairan Saldo</li>
                    </ol>
                </nav>

                <div class="d2e-card shadow-sm">
                    <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fs-4" style="width: 48px; height: 48px;">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div>
                            <h1 class="h4 fw-bold text-dark mb-1">Pengajuan Penarikan Saldo</h1>
                            <p class="text-muted small mb-0">Cairkan hasil tabungan sampah ke E-Wallet, Rekening Bank, atau Tunai</p>
                        </div>
                    </div>

                    <!-- Saldo Status Box -->
                    <div class="p-3 bg-light border border-success-subtle rounded-3 mb-4 d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.78rem;">Saldo Tabungan Anda Saat Ini:</small>
                            <span class="fs-4 fw-bold text-success"><?= rupiah($saldo_aktif) ?></span>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                            <i class="bi bi-check2-circle me-1"></i> Siap Ditarik
                        </span>
                    </div>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger py-2 small d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                            <div><?= sanitize($error_message) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($saldo_aktif < 10000): ?>
                        <div class="alert alert-warning py-3 small" role="alert">
                            <h2 class="h6 fw-bold mb-1"><i class="bi bi-info-circle me-1"></i> Saldo Belum Mencapai Batas Minimal</h2>
                            Batas minimal pencairan saldo adalah <strong>Rp 10.000</strong>. Anda memiliki saldo <strong><?= rupiah($saldo_aktif) ?></strong>. Silakan setor sampah terpilah Anda terlebih dahulu untuk menambah tabungan!
                            <div class="mt-2">
                                <a href="<?= base_url('nasabah/setor.php') ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i> Setor Sampah Sekarang
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <form action="<?= base_url('nasabah/tarik_saldo.php') ?>" method="POST">
                            <div class="mb-3">
                                <label for="jumlah_tarik" class="form-label small fw-semibold">Nominal Penarikan (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" step="1000" min="10000" max="<?= $saldo_aktif ?>" class="form-control" id="jumlah_tarik" name="jumlah_tarik" value="<?= $jumlah_tarik ?: min(20000, $saldo_aktif) ?>" placeholder="Contoh: 20000" required>
                                </div>
                                <div class="form-text small">Minimal penarikan Rp 10.000 (Maksimal: <?= rupiah($saldo_aktif) ?>).</div>
                            </div>

                            <div class="mb-3">
                                <label for="metode_pencairan" class="form-label small fw-semibold">Metode Pencairan <span class="text-danger">*</span></label>
                                <select class="form-select" id="metode_pencairan" name="metode_pencairan" required>
                                    <option value="DANA" <?= $metode_pencairan === 'DANA' ? 'selected' : '' ?>>DANA (Dompet Digital)</option>
                                    <option value="GoPay" <?= $metode_pencairan === 'GoPay' ? 'selected' : '' ?>>GoPay (Dompet Digital)</option>
                                    <option value="OVO" <?= $metode_pencairan === 'OVO' ? 'selected' : '' ?>>OVO (Dompet Digital)</option>
                                    <option value="ShopeePay" <?= $metode_pencairan === 'ShopeePay' ? 'selected' : '' ?>>ShopeePay (Dompet Digital)</option>
                                    <option value="Transfer Bank BRI" <?= $metode_pencairan === 'Transfer Bank BRI' ? 'selected' : '' ?>>Transfer Bank BRI</option>
                                    <option value="Transfer Bank Mandiri" <?= $metode_pencairan === 'Transfer Bank Mandiri' ? 'selected' : '' ?>>Transfer Bank Mandiri</option>
                                    <option value="Transfer Bank BCA" <?= $metode_pencairan === 'Transfer Bank BCA' ? 'selected' : '' ?>>Transfer Bank BCA</option>
                                    <option value="Ambil Tunai di Posko Bank Sampah" <?= $metode_pencairan === 'Ambil Tunai di Posko Bank Sampah' ? 'selected' : '' ?>>Ambil Uang Tunai di Posko Bank Sampah</option>
                                </select>
                            </div>

                            <div class="mb-4" id="group_no_rek">
                                <label for="nomor_rekening" class="form-label small fw-semibold">Nomor E-Wallet / Nomor Rekening Tujuan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nomor_rekening" name="nomor_rekening" value="<?= sanitize($nomor_rekening) ?>" placeholder="08xxxxxxxxxx atau nomor rekening" required>
                                <div class="form-text small">Pastikan nama pemilik akun atau rekening sesuai agar transfer tidak terkendala.</div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                <a href="<?= base_url('nasabah/dashboard.php') ?>" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-success" 
                                        data-confirm="Apakah Anda yakin ingin mengajukan penarikan saldo ini?"
                                        data-confirm-title="Konfirmasi Penarikan Saldo"
                                        data-confirm-type="primary"
                                        data-confirm-btn="Ya, Ajukan Penarikan">
                                    <i class="bi bi-check-circle me-1"></i> Ajukan Penarikan
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectMetode = document.getElementById('metode_pencairan');
    const groupNoRek = document.getElementById('group_no_rek');
    const inputNoRek = document.getElementById('nomor_rekening');

    if (selectMetode && groupNoRek && inputNoRek) {
        function checkMetode() {
            if (selectMetode.value === 'Ambil Tunai di Posko Bank Sampah') {
                groupNoRek.classList.add('d-none');
                inputNoRek.removeAttribute('required');
            } else {
                groupNoRek.classList.remove('d-none');
                inputNoRek.setAttribute('required', 'required');
            }
        }
        selectMetode.addEventListener('change', checkMetode);
        checkMetode();
    }
});
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
