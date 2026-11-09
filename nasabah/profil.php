<?php
/**
 * Nasabah Profile Page (View & Edit Profile)
 * Drop2Earn - Smart Waste Management
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth_check.php';

$user = check_nasabah($conn);
$id_user = $user['id_user'];

$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Handle Edit Profile Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_edit_profile'])) {
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if (empty($no_hp)) {
        $_SESSION['flash_error'] = "Nomor WhatsApp aktif wajib diisi.";
    } else {
        // Nama lengkap & email strictly preserved (read-only for user)
        $stmt_upd = mysqli_prepare($conn, "UPDATE users SET no_hp = ?, alamat = ? WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt_upd, "ssi", $no_hp, $alamat, $id_user);
        if (mysqli_stmt_execute($stmt_upd)) {
            $_SESSION['flash_success'] = "Data profil Anda berhasil diperbarui!";
        } else {
            $_SESSION['flash_error'] = "Gagal memperbarui profil: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt_upd);
    }
    header("Location: " . base_url('nasabah/profil.php'));
    exit;
}

// Handle Change Password Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_change_password'])) {
    $pass_lama = $_POST['pass_lama'] ?? '';
    $pass_baru = $_POST['pass_baru'] ?? '';
    $pass_konfirm = $_POST['pass_konfirm'] ?? '';

    if (empty($pass_lama) || empty($pass_baru)) {
        $_SESSION['flash_error'] = "Silakan lengkapi kata sandi saat ini dan kata sandi baru.";
    } elseif (strlen($pass_baru) < 6) {
        $_SESSION['flash_error'] = "Kata sandi baru minimal 6 karakter.";
    } elseif ($pass_baru !== $pass_konfirm) {
        $_SESSION['flash_error'] = "Konfirmasi kata sandi baru tidak cocok.";
    } else {
        // Verify current password
        $stmt_pw = mysqli_prepare($conn, "SELECT password FROM users WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt_pw, "i", $id_user);
        mysqli_stmt_execute($stmt_pw);
        $res = mysqli_stmt_get_result($stmt_pw);
        $pw_row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt_pw);

        if (!$pw_row || !password_verify($pass_lama, $pw_row['password'])) {
            $_SESSION['flash_error'] = "Kata sandi saat ini salah.";
        } else {
            $new_hash = password_hash($pass_baru, PASSWORD_BCRYPT);
            $stmt_hash = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id_user = ?");
            mysqli_stmt_bind_param($stmt_hash, "si", $new_hash, $id_user);
            if (mysqli_stmt_execute($stmt_hash)) {
                $_SESSION['flash_success'] = "Kata sandi berhasil diperbarui!";
            } else {
                $_SESSION['flash_error'] = "Gagal mengubah kata sandi: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_hash);
        }
    }
    header("Location: " . base_url('nasabah/profil.php'));
    exit;
}

// Re-fetch fresh user profile
$user = get_auth_user($conn);

$page_title = 'Profil Saya';
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
            <div class="container-fluid p-0" style="max-width: 980px;">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb small">
                        <li class="breadcrumb-item"><a href="<?= base_url('nasabah/dashboard.php') ?>" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Profil Nasabah</li>
                    </ol>
                </nav>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h1 class="h4 fw-bold text-dark mb-1">Profil Nasabah</h1>
                        <p class="text-muted small mb-0">Informasi akun nasabah dan detail kontak penjemputan sampah</p>
                    </div>
                </div>

                <?php if (!empty($flash_success)): ?>
                    <div class="alert alert-success alert-dismissible fade show small d-flex align-items-center" role="alert">
                        <i class="bi bi-check-circle-fill me-2 fs-6"></i>
                        <div><?= sanitize($flash_success) ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($flash_error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show small d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                        <div><?= sanitize($flash_error) ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- Card Ringkasan Profil (View Profile) -->
                    <div class="col-12 col-md-5">
                        <div class="d2e-card text-center">
                            <div class="rounded-circle bg-success-subtle text-success mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.2rem; border: 2px solid var(--d2e-primary-border);">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <h2 class="h5 fw-bold text-dark mb-1"><?= sanitize($user['nama_lengkap']) ?></h2>
                            <p class="text-muted small mb-2"><?= sanitize($user['email']) ?></p>
                            <span class="badge bg-light text-success border border-success-subtle px-3 py-1 mb-3">
                                Nasabah Warga
                            </span>

                            <div class="border-top pt-3 text-start small">
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted">Nomor WhatsApp:</span>
                                    <strong class="text-dark"><?= sanitize($user['no_hp']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted">Saldo Tabungan:</span>
                                    <strong class="text-success"><?= rupiah($user['saldo']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted">Terdaftar Sejak:</span>
                                    <span class="text-dark"><?= format_tanggal($user['created_at']) ?></span>
                                </div>
                                <div class="py-2">
                                    <span class="text-muted d-block mb-1">Alamat Penjemputan:</span>
                                    <span class="text-dark"><?= sanitize($user['alamat'] ?: 'Belum diatur') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Form Edit Profil & Ganti Sandi (Edit Profile) -->
                    <div class="col-12 col-md-7">
                        <!-- Form Edit Profil Data -->
                        <div class="d2e-card mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                <h2 class="h6 fw-bold text-dark mb-0">
                                    <i class="bi bi-pencil-square text-success me-1"></i> Edit Data Kontak &amp; Alamat
                                </h2>
                                <small class="text-muted" style="font-size: 0.75rem;">Nama &amp; Email bersifat permanen</small>
                            </div>

                            <form action="<?= base_url('nasabah/profil.php') ?>" method="POST">
                                <input type="hidden" name="action_edit_profile" value="1">

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Nama Lengkap (Tidak Dapat Diubah)</label>
                                    <input type="text" class="form-control bg-light" value="<?= sanitize($user['nama_lengkap']) ?>" readonly disabled>
                                    <div class="form-text small" style="font-size: 0.75rem;">Nama lengkap dikunci demi kesesuaian identitas buku tabungan.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Alamat Email (Tidak Dapat Diubah)</label>
                                    <input type="email" class="form-control bg-light" value="<?= sanitize($user['email']) ?>" readonly disabled>
                                    <div class="form-text small" style="font-size: 0.75rem;">Email akun digunakan sebagai identitas login utama.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="no_hp" class="form-label small fw-semibold text-dark">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="no_hp" name="no_hp" value="<?= sanitize($user['no_hp']) ?>" required>
                                    <div class="form-text small">Nomor ini digunakan petugas bank sampah saat koordinasi penjemputan.</div>
                                </div>

                                <div class="mb-4">
                                    <label for="alamat" class="form-label small fw-semibold text-dark">Alamat Lengkap Rujukan</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, dan patokan"><?= sanitize($user['alamat'] ?? '') ?></textarea>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Profil
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Form Ganti Kata Sandi -->
                        <div class="d2e-card">
                            <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-2">
                                <i class="bi bi-shield-lock text-success fs-5"></i>
                                <h2 class="h6 fw-bold text-dark mb-0">Ubah Kata Sandi</h2>
                            </div>

                            <form action="<?= base_url('nasabah/profil.php') ?>" method="POST">
                                <input type="hidden" name="action_change_password" value="1">

                                <div class="mb-3">
                                    <label for="pass_lama" class="form-label small fw-semibold">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="pass_lama" name="pass_lama" placeholder="Masukkan kata sandi lama" required>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-12 col-sm-6">
                                        <label for="pass_baru" class="form-label small fw-semibold">Kata Sandi Baru <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="pass_baru" name="pass_baru" placeholder="Min. 6 karakter" required>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label for="pass_konfirm" class="form-label small fw-semibold">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="pass_konfirm" name="pass_konfirm" placeholder="Ketik ulang kata sandi baru" required>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-outline-dark btn-sm">
                                        <i class="bi bi-key me-1"></i> Perbarui Kata Sandi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>

