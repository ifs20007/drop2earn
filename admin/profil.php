<?php
/**
 * Admin Profile Page (View & Edit Profile)
 * Drop2Earn - Smart Waste Management
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth_check.php';

$admin = check_admin($conn);
$id_admin = $admin['id_user'];

$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Handle Edit Profile Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_edit_profile'])) {
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if (empty($no_hp)) {
        $_SESSION['flash_error'] = "Nomor WhatsApp operasional pengelola wajib diisi.";
    } else {
        // Nama lengkap & email strictly read-only
        $stmt_upd = mysqli_prepare($conn, "UPDATE users SET no_hp = ?, alamat = ? WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt_upd, "ssi", $no_hp, $alamat, $id_admin);
        if (mysqli_stmt_execute($stmt_upd)) {
            $_SESSION['flash_success'] = "Profil pengelola berhasil diperbarui!";
        } else {
            $_SESSION['flash_error'] = "Gagal memperbarui profil: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt_upd);
    }
    header("Location: " . base_url('admin/profil.php'));
    exit;
}

// Handle Change Password Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_change_password'])) {
    $pass_lama = $_POST['pass_lama'] ?? '';
    $pass_baru = $_POST['pass_baru'] ?? '';
    $pass_konfirm = $_POST['pass_konfirm'] ?? '';

    if (empty($pass_lama) || empty($pass_baru)) {
        $_SESSION['flash_error'] = "Silakan lengkapi kata sandi lama dan kata sandi baru.";
    } elseif (strlen($pass_baru) < 6) {
        $_SESSION['flash_error'] = "Kata sandi baru minimal 6 karakter.";
    } elseif ($pass_baru !== $pass_konfirm) {
        $_SESSION['flash_error'] = "Konfirmasi kata sandi baru tidak cocok.";
    } else {
        $stmt_pw = mysqli_prepare($conn, "SELECT password FROM users WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt_pw, "i", $id_admin);
        mysqli_stmt_execute($stmt_pw);
        $res = mysqli_stmt_get_result($stmt_pw);
        $pw_row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt_pw);

        if (!$pw_row || !password_verify($pass_lama, $pw_row['password'])) {
            $_SESSION['flash_error'] = "Kata sandi saat ini tidak sesuai.";
        } else {
            $new_hash = password_hash($pass_baru, PASSWORD_BCRYPT);
            $stmt_hash = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id_user = ?");
            mysqli_stmt_bind_param($stmt_hash, "si", $new_hash, $id_admin);
            if (mysqli_stmt_execute($stmt_hash)) {
                $_SESSION['flash_success'] = "Kata sandi pengelola berhasil diperbarui!";
            } else {
                $_SESSION['flash_error'] = "Gagal memperbarui kata sandi: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_hash);
        }
    }
    header("Location: " . base_url('admin/profil.php'));
    exit;
}

// Re-fetch fresh admin profile
$admin = get_auth_user($conn);

$page_title = 'Profil Pengelola Admin';
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
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb small">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/index.php') ?>" class="text-decoration-none">Dashboard Admin</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Profil Pengelola</li>
                    </ol>
                </nav>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h1 class="h4 fw-bold text-dark mb-1">Profil Pengelola</h1>
                        <p class="text-muted small mb-0">Informasi kredensial administrator dan kontak posko operasional</p>
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
                    <!-- Card Ringkasan Profil Pengelola -->
                    <div class="col-12 col-md-5">
                        <div class="d2e-card text-center">
                            <div class="rounded-circle bg-dark text-white mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.2rem;">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <h2 class="h5 fw-bold text-dark mb-1"><?= sanitize($admin['nama_lengkap']) ?></h2>
                            <p class="text-muted small mb-2"><?= sanitize($admin['email']) ?></p>
                            <span class="badge bg-dark px-3 py-1 mb-3">
                                Administrator Pengelola
                            </span>

                            <div class="border-top pt-3 text-start small">
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted">Nomor WhatsApp:</span>
                                    <strong class="text-dark"><?= sanitize($admin['no_hp']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted">Hak Akses:</span>
                                    <span class="text-success fw-bold">Full Access (CMS)</span>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted">Terdaftar Sejak:</span>
                                    <span class="text-dark"><?= format_tanggal($admin['created_at']) ?></span>
                                </div>
                                <div class="py-2">
                                    <span class="text-muted d-block mb-1">Alamat Kantor / Posko:</span>
                                    <span class="text-dark"><?= sanitize($admin['alamat'] ?: 'Kantor Bank Sampah Induk') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Edit Data & Ganti Kata Sandi -->
                    <div class="col-12 col-md-7">
                        <div class="d2e-card mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                <h2 class="h6 fw-bold text-dark mb-0">
                                    <i class="bi bi-pencil-square text-success me-1"></i> Edit Data Kontak &amp; Posko
                                </h2>
                                <small class="text-muted" style="font-size: 0.75rem;">Nama &amp; Email bersifat permanen</small>
                            </div>

                            <form action="<?= base_url('admin/profil.php') ?>" method="POST">
                                <input type="hidden" name="action_edit_profile" value="1">

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Nama Lengkap (Terkunci)</label>
                                    <input type="text" class="form-control bg-light" value="<?= sanitize($admin['nama_lengkap']) ?>" readonly disabled>
                                    <div class="form-text small" style="font-size: 0.75rem;">Identitas nama pengelola utama sistem.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Email Pengelola (Terkunci)</label>
                                    <input type="email" class="form-control bg-light" value="<?= sanitize($admin['email']) ?>" readonly disabled>
                                    <div class="form-text small" style="font-size: 0.75rem;">Email akses utama CMS admin.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="admin_no_hp" class="form-label small fw-semibold text-dark">Nomor WhatsApp Operasional <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="admin_no_hp" name="no_hp" value="<?= sanitize($admin['no_hp']) ?>" required>
                                </div>

                                <div class="mb-4">
                                    <label for="admin_alamat" class="form-label small fw-semibold text-dark">Alamat Kantor / Posko Operasional</label>
                                    <textarea class="form-control" id="admin_alamat" name="alamat" rows="3" placeholder="Alamat posko penerimaan sampah"><?= sanitize($admin['alamat'] ?? '') ?></textarea>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Pengelola
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Form Ganti Kata Sandi Admin -->
                        <div class="d2e-card">
                            <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-2">
                                <i class="bi bi-shield-lock text-success fs-5"></i>
                                <h2 class="h6 fw-bold text-dark mb-0">Ubah Kata Sandi Admin</h2>
                            </div>

                            <form action="<?= base_url('admin/profil.php') ?>" method="POST">
                                <input type="hidden" name="action_change_password" value="1">

                                <div class="mb-3">
                                    <label for="admin_pass_lama" class="form-label small fw-semibold">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="admin_pass_lama" name="pass_lama" placeholder="Masukkan kata sandi saat ini" required>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-12 col-sm-6">
                                        <label for="admin_pass_baru" class="form-label small fw-semibold">Kata Sandi Baru <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="admin_pass_baru" name="pass_baru" placeholder="Minimal 6 karakter" required>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label for="admin_pass_konfirm" class="form-label small fw-semibold">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="admin_pass_konfirm" name="pass_konfirm" placeholder="Ketik ulang kata sandi baru" required>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-outline-dark btn-sm">
                                        <i class="bi bi-key me-1"></i> Perbarui Kata Sandi Admin
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>

