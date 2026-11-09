<?php
/**
 * Nasabah Registration Page
 * Drop2Earn - Smart Waste Management
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth_check.php';

if (is_logged_in()) {
    header("Location: " . base_url('nasabah/dashboard.php'));
    exit;
}

$error_message = '';
$nama_lengkap = '';
$email = '';
$no_hp = '';
$alamat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($nama_lengkap) || empty($email) || empty($no_hp) || empty($password)) {
        $error_message = "Semua bidang wajib (Nama, Email, No. HP, Password) harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format alamat email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error_message = "Kata sandi minimal harus terdiri dari 6 karakter.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Konfirmasi kata sandi tidak cocok.";
    } else {
        // Check if email already exists
        $stmt_check = mysqli_prepare($conn, "SELECT id_user FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt_check, "s", $email);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error_message = "Alamat email sudah terdaftar. Silakan gunakan email lain atau login.";
            mysqli_stmt_close($stmt_check);
        } else {
            mysqli_stmt_close($stmt_check);

            $password_hashed = password_hash($password, PASSWORD_BCRYPT);
            $role = 'nasabah';
            $saldo_awal = 0;

            $stmt_insert = mysqli_prepare(
                $conn,
                "INSERT INTO users (nama_lengkap, email, password, no_hp, alamat, role, saldo) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt_insert, "ssssssi", $nama_lengkap, $email, $password_hashed, $no_hp, $alamat, $role, $saldo_awal);

            if (mysqli_stmt_execute($stmt_insert)) {
                mysqli_stmt_close($stmt_insert);
                $_SESSION['flash_success'] = "Pendaftaran akun nasabah berhasil! Silakan masuk dengan email dan kata sandi Anda.";
                header("Location: " . base_url('login.php'));
                exit;
            } else {
                $error_message = "Gagal memproses pendaftaran: " . mysqli_error($conn);
                mysqli_stmt_close($stmt_insert);
            }
        }
    }
}

$page_title = 'Daftar Akun Nasabah';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5 flex-grow-1">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 animate-fade-up">
                <div class="d2e-card shadow-sm">
                    <div class="text-center mb-4">
                        <div class="d2e-monoline-icon icon-pastel-mint mx-auto mb-3" style="width: 56px; height: 56px;">
                            <img src="<?= base_url('assets/img/logo.svg') ?>" alt="Logo Drop2Earn" width="34" height="34">
                        </div>
                        <h1 class="h4 fw-bold text-dark mb-1">Daftar Akun Nasabah Drop2Earn</h1>
                        <p class="text-muted small">Pilah sampah dari rumah, kumpulkan saldo tabungan uang tunai</p>
                    </div>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger py-2 small d-flex align-items-center rounded-3 mb-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                            <div><?= sanitize($error_message) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('register.php') ?>" method="POST" novalidate>
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label small fw-semibold">Nama Lengkap Sesuai KTP / Identitas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= sanitize($nama_lengkap) ?>" placeholder="Contoh: Budi Santoso" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-sm-6">
                                <label for="email" class="form-label small fw-semibold">Email Aktif <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= sanitize($email) ?>" placeholder="nama@email.com" required autocomplete="email">
                            </div>
                            <div class="col-12 col-sm-6">
                                <label for="no_hp" class="form-label small fw-semibold">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="no_hp" name="no_hp" value="<?= sanitize($no_hp) ?>" placeholder="08xxxxxxxxxx" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="alamat" class="form-label small fw-semibold">Alamat Rumah / Penjemputan Sampah</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan atau patokan"><?= sanitize($alamat) ?></textarea>
                            <div class="form-text small">Alamat ini akan otomatis menjadi rujukan saat Anda memesan jemput sampah ke rumah.</div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-sm-6">
                                <label for="password" class="form-label small fw-semibold">Kata Sandi (Min. 6 Karakter) <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter" required autocomplete="new-password">
                            </div>
                            <div class="col-12 col-sm-6">
                                <label for="confirm_password" class="form-label small fw-semibold">Ulangi Kata Sandi <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Ketik ulang kata sandi" required autocomplete="new-password">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-person-plus me-1"></i> Selesaikan Pendaftaran
                        </button>
                    </form>

                    <div class="text-center small text-muted pt-3 border-top">
                        Sudah memiliki akun nasabah? 
                        <a href="<?= base_url('login.php') ?>" class="text-success fw-semibold text-decoration-none">Masuk di Sini</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
