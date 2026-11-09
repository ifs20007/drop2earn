<?php
/**
 * User Management Module (Manajemen Pengguna)
 * Drop2Earn - Smart Waste Management (Requirement 3)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth_check.php';

$admin = check_admin($conn);
$current_admin_id = (int)$admin['id_user'];

$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// 1. Handle Update User Profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_edit_user'])) {
    $target_id = (int)($_POST['id_user'] ?? 0);
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $role = trim($_POST['role'] ?? 'nasabah');
    $saldo = (int)($_POST['saldo'] ?? 0);

    if ($target_id <= 0 || empty($nama_lengkap) || empty($no_hp)) {
        $_SESSION['flash_error'] = "Nama lengkap dan nomor WhatsApp tidak boleh kosong.";
    } elseif (!in_array($role, ['nasabah', 'admin'])) {
        $_SESSION['flash_error'] = "Role pengguna tidak valid.";
    } else {
        $stmt_upd = mysqli_prepare(
            $conn,
            "UPDATE users SET nama_lengkap = ?, no_hp = ?, alamat = ?, role = ?, saldo = ? WHERE id_user = ?"
        );
        mysqli_stmt_bind_param($stmt_upd, "ssssii", $nama_lengkap, $no_hp, $alamat, $role, $saldo, $target_id);
        if (mysqli_stmt_execute($stmt_upd)) {
            $_SESSION['flash_success'] = "Data pengguna '{$nama_lengkap}' berhasil diperbarui!";
        } else {
            $_SESSION['flash_error'] = "Gagal memperbarui data pengguna: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt_upd);
    }
    header("Location: " . base_url('admin/pengguna.php'));
    exit;
}

// 2. Handle Force Reset Password (Ganti Paksa Password Pengguna)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_force_password'])) {
    $target_id = (int)($_POST['id_user'] ?? 0);
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    if ($target_id <= 0) {
        $_SESSION['flash_error'] = "Pengguna tidak valid.";
    } elseif (strlen($new_password) < 6) {
        $_SESSION['flash_error'] = "Kata sandi baru minimal 6 karakter.";
    } elseif ($new_password !== $confirm_new_password) {
        $_SESSION['flash_error'] = "Konfirmasi kata sandi baru tidak cocok.";
    } else {
        $hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt_pwd = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt_pwd, "si", $hash, $target_id);
        if (mysqli_stmt_execute($stmt_pwd)) {
            $_SESSION['flash_success'] = "Kata sandi pengguna ID #{$target_id} berhasil diganti paksa!";
        } else {
            $_SESSION['flash_error'] = "Gagal mengganti kata sandi: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt_pwd);
    }
    header("Location: " . base_url('admin/pengguna.php'));
    exit;
}

// 3. Handle Delete User Account
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    if ($delete_id === $current_admin_id) {
        $_SESSION['flash_error'] = "Tindakan ditolak: Anda tidak dapat menghapus akun admin Anda sendiri yang sedang aktif digunakan.";
    } elseif ($delete_id > 0) {
        // Fetch user name for feedback
        $stmt_chk = mysqli_prepare($conn, "SELECT nama_lengkap FROM users WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt_chk, "i", $delete_id);
        mysqli_stmt_execute($stmt_chk);
        $res_chk = mysqli_stmt_get_result($stmt_chk);
        $u_del = mysqli_fetch_assoc($res_chk);
        mysqli_stmt_close($stmt_chk);

        if ($u_del) {
            $stmt_del = mysqli_prepare($conn, "DELETE FROM users WHERE id_user = ?");
            mysqli_stmt_bind_param($stmt_del, "i", $delete_id);
            if (mysqli_stmt_execute($stmt_del)) {
                $_SESSION['flash_success'] = "Akun pengguna '{$u_del['nama_lengkap']}' beserta riwayatnya berhasil dihapus dari sistem.";
            } else {
                $_SESSION['flash_error'] = "Gagal menghapus pengguna: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_del);
        }
    }
    header("Location: " . base_url('admin/pengguna.php'));
    exit;
}

// Analytics Metrics for Users
$res_total = mysqli_query($conn, "SELECT COUNT(*) as total_users, SUM(CASE WHEN role = 'nasabah' THEN 1 ELSE 0 END) as total_nasabah, SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as total_admin, COALESCE(SUM(CASE WHEN role = 'nasabah' THEN saldo ELSE 0 END), 0) as total_saldo_nasabah FROM users");
$user_stats = mysqli_fetch_assoc($res_total);

// Search & Filter
$search_query = trim($_GET['q'] ?? '');
$filter_role = trim($_GET['role'] ?? 'Semua');

$where_clauses = [];
$params = [];
$types = "";

if (!empty($search_query)) {
    $where_clauses[] = "(nama_lengkap LIKE ? OR email LIKE ? OR no_hp LIKE ?)";
    $like = "%" . $search_query . "%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

if ($filter_role !== 'Semua' && in_array($filter_role, ['nasabah', 'admin'])) {
    $where_clauses[] = "role = ?";
    $params[] = $filter_role;
    $types .= "s";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";
$query_users = "SELECT * FROM users {$where_sql} ORDER BY role ASC, created_at DESC";

if (!empty($params)) {
    $stmt_users = mysqli_prepare($conn, $query_users);
    mysqli_stmt_bind_param($stmt_users, $types, ...$params);
    mysqli_stmt_execute($stmt_users);
    $res_users = mysqli_stmt_get_result($stmt_users);
} else {
    $res_users = mysqli_query($conn, $query_users);
}

$users_list = [];
while ($row = mysqli_fetch_assoc($res_users)) {
    $users_list[] = $row;
}

$page_title = 'Manajemen Pengguna';
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
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb small">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/index.php') ?>" class="text-decoration-none">Dashboard Admin</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Manajemen Pengguna</li>
                    </ol>
                </nav>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <h1 class="h4 fw-bold text-dark mb-1">Manajemen Pengguna Sistem</h1>
                        <p class="text-muted small mb-0">Kelola akun nasabah dan admin, edit profil pengguna, dan ganti paksa kata sandi</p>
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

                <!-- Stat Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-label">Total Pengguna</div>
                            <div class="stat-value text-dark"><?= $user_stats['total_users'] ?></div>
                            <small class="text-muted" style="font-size: 0.78rem;">Akun terdaftar dalam database</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card border-success">
                            <div class="stat-label text-success">Nasabah Warga</div>
                            <div class="stat-value text-success"><?= $user_stats['total_nasabah'] ?></div>
                            <small class="text-muted" style="font-size: 0.78rem;">Penghasil sampah aktif</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-label">Pengelola Admin</div>
                            <div class="stat-value text-primary"><?= $user_stats['total_admin'] ?></div>
                            <small class="text-muted" style="font-size: 0.78rem;">Petugas bank sampah</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="stat-card border-warning">
                            <div class="stat-label text-warning">Total Saldo Beredar</div>
                            <div class="stat-value text-dark"><?= rupiah($user_stats['total_saldo_nasabah']) ?></div>
                            <small class="text-muted" style="font-size: 0.78rem;">Akumulasi tabungan nasabah</small>
                        </div>
                    </div>
                </div>

                <!-- Filter & Search Toolbar -->
                <div class="card border mb-4 shadow-sm">
                    <div class="card-body p-3">
                        <form action="<?= base_url('admin/pengguna.php') ?>" method="GET" class="row g-2 align-items-center">
                            <div class="col-12 col-md-5">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" name="q" value="<?= sanitize($search_query) ?>" placeholder="Cari nama lengkap, email, atau no. WhatsApp...">
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <select class="form-select form-select-sm" name="role">
                                    <option value="Semua" <?= $filter_role === 'Semua' ? 'selected' : '' ?>>Semua Role Akun</option>
                                    <option value="nasabah" <?= $filter_role === 'nasabah' ? 'selected' : '' ?>>Nasabah Warga Saja</option>
                                    <option value="admin" <?= $filter_role === 'admin' ? 'selected' : '' ?>>Administrator Saja</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-funnel me-1"></i> Terapkan
                                </button>
                            </div>
                            <div class="col-12 col-md-2">
                                <a href="<?= base_url('admin/pengguna.php') ?>" class="btn btn-outline-secondary btn-sm w-100">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table Pengguna -->
                <div class="card border shadow-sm">
                    <div class="card-body p-0">
                        <?php if (empty($users_list)): ?>
                            <div class="empty-state py-5">
                                <i class="bi bi-people empty-state-icon"></i>
                                <h2 class="h6 fw-bold text-dark mb-1">Pengguna Tidak Ditemukan</h2>
                                <p class="small text-muted mb-0">Tidak ada data pengguna yang sesuai dengan kata kunci pencarian Anda.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive border-0">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama &amp; Email</th>
                                            <th>No. WhatsApp</th>
                                            <th>Role</th>
                                            <th>Saldo Tabungan</th>
                                            <th>Alamat</th>
                                            <th>Terdaftar</th>
                                            <th class="text-end">Aksi Pengelola</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users_list as $u): ?>
                                            <tr>
                                                <td>#<?= $u['id_user'] ?></td>
                                                <td>
                                                    <strong class="d-block text-dark small"><?= sanitize($u['nama_lengkap']) ?></strong>
                                                    <span class="text-muted" style="font-size: 0.75rem;"><?= sanitize($u['email']) ?></span>
                                                </td>
                                                <td>
                                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $u['no_hp']) ?>" target="_blank" class="text-decoration-none text-success small">
                                                        <i class="bi bi-whatsapp"></i> <?= sanitize($u['no_hp']) ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php if ($u['role'] === 'admin'): ?>
                                                        <span class="badge bg-dark">Admin</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Nasabah</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($u['role'] === 'nasabah'): ?>
                                                        <strong class="text-success small"><?= rupiah($u['saldo']) ?></strong>
                                                    <?php else: ?>
                                                        <span class="text-muted small">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="small text-muted d-inline-block text-truncate" style="max-width: 160px;" title="<?= sanitize($u['alamat'] ?: 'Belum diisi') ?>">
                                                        <?= sanitize($u['alamat'] ?: '-') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?= date('d/m/Y', strtotime($u['created_at'])) ?></small>
                                                </td>
                                                <td class="text-end">
                                                    <!-- Edit User Data Modal Trigger -->
                                                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 me-1 btn-edit-user"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalEditUser"
                                                            data-id="<?= $u['id_user'] ?>"
                                                            data-nama="<?= sanitize($u['nama_lengkap']) ?>"
                                                            data-email="<?= sanitize($u['email']) ?>"
                                                            data-phone="<?= sanitize($u['no_hp']) ?>"
                                                            data-alamat="<?= sanitize($u['alamat'] ?? '') ?>"
                                                            data-role="<?= $u['role'] ?>"
                                                            data-saldo="<?= $u['saldo'] ?>"
                                                            title="Edit Profil Pengguna">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>

                                                    <!-- Force Reset Password Modal Trigger -->
                                                    <button type="button" class="btn btn-sm btn-outline-warning py-1 px-2 me-1 btn-force-pw"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalForcePw"
                                                            data-id="<?= $u['id_user'] ?>"
                                                            data-nama="<?= sanitize($u['nama_lengkap']) ?>"
                                                            title="Ganti Paksa Kata Sandi">
                                                        <i class="bi bi-key"></i>
                                                    </button>

                                                    <!-- Delete User Trigger -->
                                                    <?php if ($u['id_user'] !== $current_admin_id): ?>
                                                        <a href="<?= base_url('admin/pengguna.php?delete_id=' . $u['id_user']) ?>" 
                                                           class="btn btn-sm btn-outline-danger py-1 px-2"
                                                           data-confirm="PERINGATAN: Apakah Anda yakin ingin menghapus akun '<?= sanitize($u['nama_lengkap']) ?>'? Seluruh data riwayat setoran dan tabungan terkait akun ini akan terhapus permanen."
                                                           data-confirm-title="Hapus Akun Pengguna"
                                                           data-confirm-type="danger"
                                                           data-confirm-btn="Ya, Hapus Akun"
                                                           title="Hapus Akun Pengguna">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-muted border py-1 px-2" title="Akun Anda Saat Ini">Aktif</span>
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

<!-- Modal 1: Edit Data Profil Pengguna -->
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-labelledby="modalEditUserLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('admin/pengguna.php') ?>" method="POST">
                <input type="hidden" name="action_edit_user" value="1">
                <input type="hidden" name="id_user" id="edit_id_user">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalEditUserLabel">Edit Profil Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_email" class="form-label small fw-semibold text-muted">Email Pengguna (Permanen)</label>
                        <input type="email" class="form-control bg-light" id="edit_email" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label for="edit_nama" class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nama" name="nama_lengkap" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_phone" class="form-label small fw-semibold">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="edit_phone" name="no_hp" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="edit_role" class="form-label small fw-semibold">Role Hak Akses <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="nasabah">Nasabah Warga</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="edit_saldo" class="form-label small fw-semibold">Saldo Tabungan (Rp)</label>
                            <input type="number" class="form-control" id="edit_saldo" name="saldo" min="0" step="500">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_alamat" class="form-label small fw-semibold">Alamat Rumah / Penjemputan</label>
                        <textarea class="form-control" id="edit_alamat" name="alamat" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Ganti Paksa Password Pengguna -->
<div class="modal fade" id="modalForcePw" tabindex="-1" aria-labelledby="modalForcePwLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('admin/pengguna.php') ?>" method="POST">
                <input type="hidden" name="action_force_password" value="1">
                <input type="hidden" name="id_user" id="force_pw_id_user">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalForcePwLabel">Ganti Paksa Password Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 bg-light border rounded mb-3">
                        <small class="text-muted d-block">Pengguna yang diatur:</small>
                        <strong class="text-dark fs-6" id="force_pw_nama_user">-</strong>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label small fw-semibold">Kata Sandi Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Minimal 6 karakter" required>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_new_password" class="form-label small fw-semibold">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" placeholder="Ketik ulang kata sandi baru" required>
                    </div>

                    <div class="alert alert-warning py-2 small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        Pengguna akan langsung dapat masuk menggunakan kata sandi baru ini tanpa memerlukan verifikasi kata sandi lama.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" 
                            data-confirm="Ganti paksa kata sandi pengguna ini?"
                            data-confirm-title="Reset Password Pengguna"
                            data-confirm-type="warning"
                            data-confirm-btn="Ya, Terapkan">
                        <i class="bi bi-key-fill me-1"></i> Terapkan Kata Sandi Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Populate Edit User Modal
    const editBtns = document.querySelectorAll('.btn-edit-user');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_id_user').value = this.getAttribute('data-id');
            document.getElementById('edit_nama').value = this.getAttribute('data-nama');
            document.getElementById('edit_email').value = this.getAttribute('data-email');
            document.getElementById('edit_phone').value = this.getAttribute('data-phone');
            document.getElementById('edit_alamat').value = this.getAttribute('data-alamat');
            document.getElementById('edit_role').value = this.getAttribute('data-role');
            document.getElementById('edit_saldo').value = this.getAttribute('data-saldo');
        });
    });

    // Populate Force Password Modal
    const forcePwBtns = document.querySelectorAll('.btn-force-pw');
    forcePwBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('force_pw_id_user').value = this.getAttribute('data-id');
            document.getElementById('force_pw_nama_user').textContent = this.getAttribute('data-nama');
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_new_password').value = '';
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>

