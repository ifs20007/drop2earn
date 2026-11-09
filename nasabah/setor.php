<?php
/**
 * Deposit Request Form (Setor / Jemput Sampah)
 * Drop2Earn - Smart Waste Management
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth_check.php';

$user = check_nasabah($conn);
$id_user = $user['id_user'];

$error_message = '';
$kategori_options = [];
$res_kat = mysqli_query($conn, "SELECT id_kategori, nama_kategori, jenis_induk, harga_per_kg, panduan_pilah FROM kategori_sampah ORDER BY jenis_induk ASC, nama_kategori ASC");
while ($row = mysqli_fetch_assoc($res_kat)) {
    $kategori_options[] = $row;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kategori = (int)($_POST['id_kategori'] ?? 0);
    $metode_setor = trim($_POST['metode_setor'] ?? 'Jemput ke Rumah');
    $tanggal_jadwal = trim($_POST['tanggal_jadwal'] ?? '');
    $berat_estimasi = (float)($_POST['berat_estimasi'] ?? 0);
    $alamat_jemput = trim($_POST['alamat_jemput'] ?? '');

    // If method is drop point, default the location description
    if ($metode_setor === 'Antar ke Drop Point') {
        $drop_point_pilihan = trim($_POST['drop_point_select'] ?? 'Bank Sampah Induk Siantar (Jl. Sutomo No. 18)');
        $alamat_jemput = 'Antar ke: ' . $drop_point_pilihan;
    }

    // Validation
    if ($id_kategori <= 0) {
        $error_message = "Silakan pilih salah satu kategori sampah yang akan disetor.";
    } elseif ($berat_estimasi <= 0) {
        $error_message = "Perkiraan berat sampah minimal harus lebih besar dari 0 kg.";
    } elseif (empty($tanggal_jadwal)) {
        $error_message = "Silakan tentukan tanggal jadwal penjemputan atau pengantaran.";
    } elseif ($metode_setor === 'Jemput ke Rumah' && empty($alamat_jemput)) {
        $error_message = "Alamat penjemputan lengkap wajib diisi agar petugas dapat menemukan lokasi Anda.";
    } else {
        // Verify valid kategori
        $stmt_check = mysqli_prepare($conn, "SELECT id_kategori, harga_per_kg FROM kategori_sampah WHERE id_kategori = ?");
        mysqli_stmt_bind_param($stmt_check, "i", $id_kategori);
        mysqli_stmt_execute($stmt_check);
        $res_check = mysqli_stmt_get_result($stmt_check);
        $kat_valid = mysqli_fetch_assoc($res_check);
        mysqli_stmt_close($stmt_check);

        if (!$kat_valid) {
            $error_message = "Kategori sampah tidak valid.";
        } else {
            $status_awal = 'Menunggu';
            $berat_aktual = 0.00;
            $total_pendapatan = 0;

            $stmt_insert = mysqli_prepare(
                $conn,
                "INSERT INTO setoran_sampah (id_user, id_kategori, metode_setor, tanggal_jadwal, alamat_jemput, berat_estimasi, berat_aktual, total_pendapatan, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param(
                $stmt_insert,
                "iisssddis",
                $id_user,
                $id_kategori,
                $metode_setor,
                $tanggal_jadwal,
                $alamat_jemput,
                $berat_estimasi,
                $berat_aktual,
                $total_pendapatan,
                $status_awal
            );

            if (mysqli_stmt_execute($stmt_insert)) {
                mysqli_stmt_close($stmt_insert);
                $_SESSION['flash_success'] = "Pengajuan setoran sampah berhasil dibuat! Petugas kami akan memverifikasi dan memproses jadwal Anda.";
                header("Location: " . base_url('nasabah/dashboard.php'));
                exit;
            } else {
                $error_message = "Gagal menyimpan transaksi: " . mysqli_error($conn);
                mysqli_stmt_close($stmt_insert);
            }
        }
    }
}

$page_title = 'Formulir Setor Sampah';
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
            <div class="container-fluid p-0" style="max-width: 820px;">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb small">
                        <li class="breadcrumb-item"><a href="<?= base_url('nasabah/dashboard.php') ?>" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Pengajuan Setor Sampah</li>
                    </ol>
                </nav>

                <div class="d2e-card shadow-sm">
                    <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fs-4" style="width: 48px; height: 48px;">
                            <i class="bi bi-box-arrow-in-down"></i>
                        </div>
                        <div>
                            <h1 class="h4 fw-bold text-dark mb-1">Form Pengajuan Setor / Jemput Sampah</h1>
                            <p class="text-muted small mb-0">Isi detail sampah yang telah Anda pilah untuk ditimbang dan dicatat ke buku tabungan</p>
                        </div>
                    </div>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger py-2 small d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                            <div><?= sanitize($error_message) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('nasabah/setor.php') ?>" method="POST">
                        <!-- Pilihan Kategori Sampah -->
                        <div class="mb-3">
                            <label for="id_kategori" class="form-label small fw-semibold">Pilih Kategori Sampah yang Disetor <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_kategori" name="id_kategori" required>
                                <option value="" data-harga="0">-- Pilih Jenis Sampah Terpilah --</option>
                                <?php foreach ($kategori_options as $item): ?>
                                    <option value="<?= $item['id_kategori'] ?>" data-harga="<?= $item['harga_per_kg'] ?>" data-tips="<?= sanitize($item['panduan_pilah']) ?>">
                                        <?= sanitize($item['nama_kategori']) ?> (<?= sanitize($item['jenis_induk']) ?>) - <?= rupiah($item['harga_per_kg']) ?>/kg
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="kat-panduan-info" class="p-2 bg-light border rounded mt-2 small text-muted d-none">
                                <i class="bi bi-info-circle text-primary me-1"></i>
                                <strong>Tips Pilah:</strong> <span id="kat-panduan-text">-</span>
                            </div>
                        </div>

                        <!-- Estimasi Berat & Kalkulator Realtime -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="berat_estimasi" class="form-label small fw-semibold">Perkiraan Berat Sampah (Kg) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0.5" class="form-control" id="berat_estimasi" name="berat_estimasi" placeholder="Contoh: 5.0" required>
                                    <span class="input-group-text">Kg</span>
                                </div>
                                <div class="form-text small">Berat riil akan dipastikan saat petugas menimbang fisik sampah.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Perkiraan Pendapatan Saldo</label>
                                <div class="p-2 border rounded bg-light text-center" style="min-height: 44px;">
                                    <span class="fs-5 fw-bold text-success" id="calc-result">Rp 0</span>
                                    <small class="d-block text-muted" id="calc-price-rate">Pilih jenis &amp; berat sampah</small>
                                </div>
                            </div>
                        </div>

                        <!-- Pilihan Metode Setor -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold d-block">Metode Penyerahan Sampah <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <div class="form-check p-3 border rounded bg-light">
                                        <input class="form-check-input" type="radio" name="metode_setor" id="metode_jemput" value="Jemput ke Rumah" checked>
                                        <label class="form-check-label fw-semibold text-dark" for="metode_jemput">
                                            <i class="bi bi-truck text-primary me-1"></i> Jemput ke Rumah
                                            <small class="d-block text-muted fw-normal">Petugas bank sampah datang ke lokasi Anda</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="form-check p-3 border rounded bg-light">
                                        <input class="form-check-input" type="radio" name="metode_setor" id="metode_antar" value="Antar ke Drop Point">
                                        <label class="form-check-label fw-semibold text-dark" for="metode_antar">
                                            <i class="bi bi-building text-success me-1"></i> Antar ke Drop Point
                                            <small class="d-block text-muted fw-normal">Bawa sendiri ke posko pengumpulan resmi</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pilihan Tanggal Jadwal -->
                        <div class="mb-3">
                            <label for="tanggal_jadwal" class="form-label small fw-semibold">Tanggal Rencana Setor / Jemput <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_jadwal" name="tanggal_jadwal" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <!-- Bagian Alamat Penjemputan (Tampil jika Jemput ke Rumah) -->
                        <div class="mb-3" id="group_alamat_jemput">
                            <label for="alamat_jemput" class="form-label small fw-semibold">Alamat Lengkap Penjemputan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alamat_jemput" name="alamat_jemput" rows="2" placeholder="Tuliskan jalan, nomor rumah, RT/RW, dan patokan"><?= sanitize($user['alamat'] ?? '') ?></textarea>
                            <div class="form-text small">Nomor kontak WhatsApp yang dihubungi petugas: <strong><?= sanitize($user['no_hp']) ?></strong></div>
                        </div>

                        <!-- Bagian Drop Point (Tampil jika Antar Sendiri) -->
                        <div class="mb-3 d-none" id="group_drop_point">
                            <label for="drop_point_select" class="form-label small fw-semibold">Pilih Posko Drop Point Tujuan <span class="text-danger">*</span></label>
                            <select class="form-select" id="drop_point_select" name="drop_point_select">
                                <option value="Bank Sampah Induk Siantar (Jl. Sutomo No. 18)">Bank Sampah Induk Siantar - Jl. Sutomo No. 18 (Buka 08.00 - 16.00 WIB)</option>
                                <option value="Pos Drop Point Mahasiswa & Kos (Jl. Merdeka Samping Kampus)">Pos Drop Point Kampus & Kos - Jl. Merdeka (Buka 13.00 - 18.00 WIB)</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-4">
                            <a href="<?= base_url('nasabah/dashboard.php') ?>" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send-check me-1"></i> Ajukan Penyetoran Sampah
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const radioJemput = document.getElementById('metode_jemput');
    const radioAntar = document.getElementById('metode_antar');
    const groupAlamat = document.getElementById('group_alamat_jemput');
    const groupDropPoint = document.getElementById('group_drop_point');
    const textareaAlamat = document.getElementById('alamat_jemput');
    const selectKat = document.getElementById('id_kategori');
    const infoPanduanBox = document.getElementById('kat-panduan-info');
    const infoPanduanText = document.getElementById('kat-panduan-text');

    function toggleMetode() {
        if (radioAntar.checked) {
            groupAlamat.classList.add('d-none');
            groupDropPoint.classList.remove('d-none');
            textareaAlamat.removeAttribute('required');
        } else {
            groupAlamat.classList.remove('d-none');
            groupDropPoint.classList.add('d-none');
            textareaAlamat.setAttribute('required', 'required');
        }
    }

    radioJemput.addEventListener('change', toggleMetode);
    radioAntar.addEventListener('change', toggleMetode);

    // Dynamic guide text display when selecting category
    selectKat.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const tips = selected.getAttribute('data-tips');
        if (tips) {
            infoPanduanText.textContent = tips;
            infoPanduanBox.classList.remove('d-none');
        } else {
            infoPanduanBox.classList.add('d-none');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
