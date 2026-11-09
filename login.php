<?php
/**
 * Login Page
 * Drop2Earn - Smart Waste Management
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth_check.php';

// If already logged in, redirect to respective dashboard
if (is_logged_in()) {
    $current_role = $_SESSION['role'] ?? 'nasabah';
    if ($current_role === 'admin') {
        header("Location: " . base_url('admin/index.php'));
        exit;
    }
    header("Location: " . base_url('nasabah/dashboard.php'));
    exit;
}

$error_message = '';
$success_message = '';

if (isset($_SESSION['flash_error'])) {
    $error_message = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}
if (isset($_SESSION['flash_success'])) {
    $success_message = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error_message = "Silakan isi email dan password Anda.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id_user, nama_lengkap, email, password, role, saldo FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['saldo'] = $user['saldo'];

            if ($user['role'] === 'admin') {
                header("Location: " . base_url('admin/index.php'));
            } else {
                header("Location: " . base_url('nasabah/dashboard.php'));
            }
            exit;
        } else {
            $error_message = "Kombinasi email atau password tidak sesuai.";
        }
    }
}

$page_title = 'Masuk Akun';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5 flex-grow-1 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5 animate-fade-up">
                <div class="d2e-card shadow-sm">
                    <div class="text-center mb-4">
                        <div class="d2e-monoline-icon icon-pastel-mint mx-auto mb-3" style="width: 56px; height: 56px;">
                            <img src="<?= base_url('assets/img/logo.svg') ?>" alt="Logo Drop2Earn" width="34" height="34">
                        </div>
                        <h1 class="h4 fw-bold text-dark mb-1">Masuk ke Drop2Earn</h1>
                        <p class="text-muted small">Kelola setoran dan tabungan sampah daur ulang Anda</p>
                    </div>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger py-2 small d-flex align-items-center rounded-3 mb-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                            <div><?= sanitize($error_message) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success py-2 small d-flex align-items-center rounded-3 mb-3" role="alert">
                            <i class="bi bi-check-circle-fill me-2 fs-6"></i>
                            <div><?= sanitize($success_message) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('login.php') ?>" method="POST" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold">Alamat Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= isset($email) ? sanitize($email) : '' ?>" placeholder="nama@email.com" required autocomplete="email">
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label small fw-semibold">Kata Sandi</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan kata sandi" required autocomplete="current-password">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                        </button>
                    </form>

                    <div class="text-center small text-muted pt-3 border-top">
                        Belum punya akun nasabah? 
                        <a href="<?= base_url('register.php') ?>" class="text-success fw-semibold text-decoration-none">Daftar Akun Baru</a>
                    </div>
                </div>

                <!-- Info Kredensial Pengujian Praktik (Quick Access) -->
                <div class="mt-4 p-3 bg-white border rounded-4 shadow-sm">
                    <div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-2">
                        <span class="pastel-badge pastel-badge-sky">Info Pengujian</span>
                        <span>Akun Uji Coba Cepat (Evaluasi Praktik)</span>
                    </div>
                    <div class="row g-2 small">
                        <div class="col-12 col-sm-6">
                            <div class="p-2 border rounded-3 bg-light">
                                <div class="text-muted fw-semibold" style="font-size: 0.75rem;">Admin Pengelola:</div>
                                <code class="d-block text-dark fw-bold" style="font-size: 0.78rem;">admin@drop2earn.id</code>
                                <div class="text-muted" style="font-size: 0.72rem;">Password: <code>admin123</code></div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="p-2 border rounded-3 bg-light">
                                <div class="text-muted fw-semibold" style="font-size: 0.75rem;">Nasabah Warga:</div>
                                <code class="d-block text-dark fw-bold" style="font-size: 0.78rem;">budi@gmail.com</code>
                                <div class="text-muted" style="font-size: 0.72rem;">Password: <code>password123</code></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
