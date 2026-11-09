<?php
/**
 * Master Waste Categories & Pricing CRUD
 * Drop2Earn - Smart Waste Management
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth_check.php';

$admin = check_admin($conn);

$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Handle CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_create'])) {
    $nama_kategori = trim($_POST['nama_kategori'] ?? '');
    $jenis_induk = trim($_POST['jenis_induk'] ?? 'Plastik');
    $harga_per_kg = (int)($_POST['harga_per_kg'] ?? 0);
    $panduan_pilah = trim($_POST['panduan_pilah'] ?? '');
    $gambar = trim($_POST['gambar'] ?? 'plastik-pet.svg');

    if (empty($nama_kategori) || $harga_per_kg <= 0 || empty($panduan_pilah)) {
        $_SESSION['flash_error'] = "Nama kategori, harga per kg (harus > 0), dan panduan pemilahan wajib diisi.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO kategori_sampah (nama_kategori, jenis_induk, harga_per_kg, panduan_pilah, gambar) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssiss", $nama_kategori, $jenis_induk, $harga_per_kg, $panduan_pilah, $gambar);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = "Kategori sampah baru '{$nama_kategori}' berhasil ditambahkan!";
        } else {
            $_SESSION['flash_error'] = "Gagal menambah kategori: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
    header("Location: " . base_url('admin/kategori.php'));
    exit;
}

// Handle UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update'])) {
    $id_kategori = (int)($_POST['id_kategori'] ?? 0);
    $nama_kategori = trim($_POST['nama_kategori'] ?? '');
    $jenis_induk = trim($_POST['jenis_induk'] ?? 'Plastik');
    $harga_per_kg = (int)($_POST['harga_per_kg'] ?? 0);
    $panduan_pilah = trim($_POST['panduan_pilah'] ?? '');
    $gambar = trim($_POST['gambar'] ?? 'plastik-pet.svg');

    if ($id_kategori <= 0 || empty($nama_kategori) || $harga_per_kg <= 0 || empty($panduan_pilah)) {
        $_SESSION['flash_error'] = "Data pembaruan tidak lengkap atau harga tidak valid.";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE kategori_sampah SET nama_kategori = ?, jenis_induk = ?, harga_per_kg = ?, panduan_pilah = ?, gambar = ? WHERE id_kategori = ?");
        mysqli_stmt_bind_param($stmt, "ssissi", $nama_kategori, $jenis_induk, $harga_per_kg, $panduan_pilah, $gambar, $id_kategori);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = "Kategori sampah '{$nama_kategori}' berhasil diperbarui!";
        } else {
            $_SESSION['flash_error'] = "Gagal memperbarui kategori: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
    header("Location: " . base_url('admin/kategori.php'));
    exit;
}

// Handle DELETE
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    if ($delete_id > 0) {
        $stmt_check = mysqli_prepare($conn, "SELECT COUNT(id_setoran) as cnt FROM setoran_sampah WHERE id_kategori = ?");
        mysqli_stmt_bind_param($stmt_check, "i", $delete_id);
        mysqli_stmt_execute($stmt_check);
        $res = mysqli_stmt_get_result($stmt_check);
        $cnt = mysqli_fetch_assoc($res)['cnt'] ?? 0;
        mysqli_stmt_close($stmt_check);

        if ($cnt > 0) {
            $_SESSION['flash_error'] = "Kategori tidak dapat dihapus karena sudah memiliki {$cnt} catatan transaksi setoran.";
        } else {
            $stmt_del = mysqli_prepare($conn, "DELETE FROM kategori_sampah WHERE id_kategori = ?");
            mysqli_stmt_bind_param($stmt_del, "i", $delete_id);
            if (mysqli_stmt_execute($stmt_del)) {
                $_SESSION['flash_success'] = "Kategori sampah berhasil dihapus.";
            } else {
                $_SESSION['flash_error'] = "Gagal menghapus kategori: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_del);
        }
    }
    header("Location: " . base_url('admin/kategori.php'));
    exit;
}

// Fetch all categories
$query = "SELECT * FROM kategori_sampah ORDER BY jenis_induk ASC, nama_kategori ASC";
$result = mysqli_query($conn, $query);
$categories = [];
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

$page_title = 'Kelola Kategori & Harga Sampah';
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
                                <li class="breadcrumb-item active" aria-current="page">Master Kategori Sampah</li>
                            </ol>
                        </nav>
                        <h1 class="h4 fw-bold text-dark mb-0">Master Kategori, Harga &amp; Panduan Sampah</h1>
                        <small class="text-muted">Perbarui harga beli per kg untuk disinkronkan langsung ke katalog publik</small>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Jenis Sampah
                    </button>
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
                        <div class="table-responsive border-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Ikon</th>
                                        <th>Nama Kategori</th>
                                        <th>Kelompok Induk</th>
                                        <th>Harga Beli / Kg</th>
                                        <th>Instruksi Pemilahan</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $index => $cat): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <img src="<?= base_url('assets/img/' . ($cat['gambar'] ?: 'plastik-pet.svg')) ?>" alt="" width="32" height="32">
                                            </td>
                                            <td>
                                                <strong class="text-dark d-block"><?= sanitize($cat['nama_kategori']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border"><?= sanitize($cat['jenis_induk']) ?></span>
                                            </td>
                                            <td>
                                                <strong class="text-success"><?= rupiah($cat['harga_per_kg']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="small text-muted d-inline-block text-truncate" style="max-width: 280px;" title="<?= sanitize($cat['panduan_pilah']) ?>">
                                                    <?= sanitize($cat['panduan_pilah']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-primary me-1 btn-edit-kategori" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditKategori"
                                                        data-id="<?= $cat['id_kategori'] ?>"
                                                        data-nama="<?= sanitize($cat['nama_kategori']) ?>"
                                                        data-induk="<?= sanitize($cat['jenis_induk']) ?>"
                                                        data-harga="<?= $cat['harga_per_kg'] ?>"
                                                        data-panduan="<?= sanitize($cat['panduan_pilah']) ?>"
                                                        data-gambar="<?= sanitize($cat['gambar'] ?: 'plastik-pet.svg') ?>">
                                                    <i class="bi bi-pencil-square"></i> Ubah
                                                </button>
                                                <a href="<?= base_url('admin/kategori.php?delete_id=' . $cat['id_kategori']) ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   data-confirm="Apakah Anda yakin ingin menghapus kategori '<?= sanitize($cat['nama_kategori']) ?>'?"
                                                   data-confirm-title="Hapus Kategori Sampah"
                                                   data-confirm-type="danger"
                                                   data-confirm-btn="Ya, Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </main>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('admin/kategori.php') ?>" method="POST">
                <input type="hidden" name="action_create" value="1">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTambahLabel">Tambah Kategori Sampah Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_kategori_add" class="form-label small fw-semibold">Nama Kategori Sampah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kategori_add" name="nama_kategori" placeholder="Contoh: Plastik HDPE Jerigen" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="jenis_induk_add" class="form-label small fw-semibold">Kelompok Induk <span class="text-danger">*</span></label>
                            <select class="form-select" id="jenis_induk_add" name="jenis_induk" required>
                                <option value="Plastik">Plastik</option>
                                <option value="Kertas & Kardus">Kertas &amp; Kardus</option>
                                <option value="Logam">Logam</option>
                                <option value="Kaca">Kaca</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="harga_per_kg_add" class="form-label small fw-semibold">Harga Beli / Kg (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="harga_per_kg_add" name="harga_per_kg" placeholder="Contoh: 3500" min="100" step="50" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="panduan_pilah_add" class="form-label small fw-semibold">Panduan Pemilahan Bagi Warga <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="panduan_pilah_add" name="panduan_pilah" rows="3" placeholder="Instruksi membersihkan atau melipat sampah sebelum disetor" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="gambar_add" class="form-label small fw-semibold">Ikon Ilustrasi (SVG)</label>
                        <select class="form-select" id="gambar_add" name="gambar">
                            <option value="plastik-pet.svg">Botol Plastik PET (plastik-pet.svg)</option>
                            <option value="plastik-pp.svg">Gelas Plastik / Cup (plastik-pp.svg)</option>
                            <option value="kardus.svg">Kardus / Karton (kardus.svg)</option>
                            <option value="kertas-hvs.svg">Kertas Dokumen (kertas-hvs.svg)</option>
                            <option value="kaleng-alu.svg">Kaleng Aluminium (kaleng-alu.svg)</option>
                            <option value="besi-logam.svg">Besi &amp; Logam Scrap (besi-logam.svg)</option>
                            <option value="botol-kaca.svg">Botol Kaca Utuh (botol-kaca.svg)</option>
                            <option value="minyak-jelantah.svg">Minyak Jelantah (minyak-jelantah.svg)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kategori -->
<div class="modal fade" id="modalEditKategori" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('admin/kategori.php') ?>" method="POST">
                <input type="hidden" name="action_update" value="1">
                <input type="hidden" name="id_kategori" id="edit_id_kategori">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalEditLabel">Perbarui Kategori Sampah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nama_kategori" class="form-label small fw-semibold">Nama Kategori Sampah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nama_kategori" name="nama_kategori" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="edit_jenis_induk" class="form-label small fw-semibold">Kelompok Induk <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_jenis_induk" name="jenis_induk" required>
                                <option value="Plastik">Plastik</option>
                                <option value="Kertas & Kardus">Kertas &amp; Kardus</option>
                                <option value="Logam">Logam</option>
                                <option value="Kaca">Kaca</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="edit_harga_per_kg" class="form-label small fw-semibold">Harga Beli / Kg (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_harga_per_kg" name="harga_per_kg" min="100" step="50" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_panduan_pilah" class="form-label small fw-semibold">Panduan Pemilahan Bagi Warga <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_panduan_pilah" name="panduan_pilah" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_gambar" class="form-label small fw-semibold">Ikon Ilustrasi (SVG)</label>
                        <select class="form-select" id="edit_gambar" name="gambar">
                            <option value="plastik-pet.svg">Botol Plastik PET (plastik-pet.svg)</option>
                            <option value="plastik-pp.svg">Gelas Plastik / Cup (plastik-pp.svg)</option>
                            <option value="kardus.svg">Kardus / Karton (kardus.svg)</option>
                            <option value="kertas-hvs.svg">Kertas Dokumen (kertas-hvs.svg)</option>
                            <option value="kaleng-alu.svg">Kaleng Aluminium (kaleng-alu.svg)</option>
                            <option value="besi-logam.svg">Besi &amp; Logam Scrap (besi-logam.svg)</option>
                            <option value="botol-kaca.svg">Botol Kaca Utuh (botol-kaca.svg)</option>
                            <option value="minyak-jelantah.svg">Minyak Jelantah (minyak-jelantah.svg)</option>
                        </select>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.btn-edit-kategori');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_id_kategori').value = this.getAttribute('data-id');
            document.getElementById('edit_nama_kategori').value = this.getAttribute('data-nama');
            document.getElementById('edit_jenis_induk').value = this.getAttribute('data-induk');
            document.getElementById('edit_harga_per_kg').value = this.getAttribute('data-harga');
            document.getElementById('edit_panduan_pilah').value = this.getAttribute('data-panduan');
            document.getElementById('edit_gambar').value = this.getAttribute('data-gambar');
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
