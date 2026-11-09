<?php
/**
 * Landing Page & Public Waste Catalog
 * Drop2Earn - Smart Waste Management
 * Design: Modern Flat 2.0 (Semi-Flat), Card-Based Minimalist & Clean Startup UI
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth_check.php';

// Fetch all active waste categories from database
$query_kategori = "SELECT * FROM kategori_sampah ORDER BY jenis_induk ASC, nama_kategori ASC";
$result_kategori = mysqli_query($conn, $query_kategori);
$kategori_list = [];
while ($row = mysqli_fetch_assoc($result_kategori)) {
    $kategori_list[] = $row;
}

$page_title = 'Pilah dari Rumah, Ubah Sampah Jadi Berkah';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- Hero Banner (Clean Startup Landing Page UI) -->
<header class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-12 col-lg-7 animate-fade-up">
                <!-- Pastel Nature Badge -->
                <div class="hero-badge">
                    <i class="bi bi-tree-fill text-success"></i>
                    <span>Solusi Cerdas Pengelolaan Sampah Komunitas</span>
                </div>

                <h1 class="hero-title">
                    Pilah dari Rumah, Ubah Sampah Jadi <span class="hero-highlight">Berkah &amp; Rupiah</span>
                </h1>
                
                <p class="hero-subtitle">
                    Ketahui harga jual sampah daur ulang secara transparan, pelajari cara memilah yang benar, dan ajukan penjemputan langsung ke rumah Anda tanpa repot.
                </p>

                <div class="d-flex flex-wrap gap-3 mb-4">
                    <?php if (is_logged_in() && ($_SESSION['role'] ?? '') === 'nasabah'): ?>
                        <a href="<?= base_url('nasabah/setor.php') ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-down me-1"></i> Ajukan Setor Sampah
                        </a>
                        <a href="<?= base_url('nasabah/dashboard.php') ?>" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-journal-check me-1"></i> Buka Buku Tabungan
                        </a>
                    <?php elseif (is_logged_in() && ($_SESSION['role'] ?? '') === 'admin'): ?>
                        <a href="<?= base_url('admin/index.php') ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-speedometer2 me-1"></i> Masuk Panel Pengelola
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('register.php') ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-person-plus me-1"></i> Mulai Jadi Nasabah
                        </a>
                        <a href="#katalog" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-tags me-1"></i> Cek Katalog Harga
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Trust Points Bar (Real Features) -->
                <div class="row pt-4 border-top g-4">
                    <div class="col-auto">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d2e-monoline-icon icon-pastel-mint" style="width: 38px; height: 38px;">
                                <i class="bi bi-check2-circle fs-5"></i>
                            </div>
                            <div>
                                <strong class="d-block text-dark small">Timbangan Jujur</strong>
                                <span class="text-muted" style="font-size: 0.78rem;">Divalidasi riil oleh petugas</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d2e-monoline-icon icon-pastel-sky" style="width: 38px; height: 38px;">
                                <i class="bi bi-wallet2 fs-5"></i>
                            </div>
                            <div>
                                <strong class="d-block text-dark small">Pencairan Fleksibel</strong>
                                <span class="text-muted" style="font-size: 0.78rem;">E-Wallet atau Uang Tunai</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interaktif: Live Calculator Card (Modern SaaS Squircle Card) -->
            <div class="col-12 col-lg-5 animate-fade-up stagger-2" id="kalkulator">
                <div class="calculator-box">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="d2e-monoline-icon icon-pastel-mint">
                            <i class="bi bi-calculator fs-4 text-success"></i>
                        </div>
                        <div>
                            <h2 class="h5 fw-bold text-dark mb-0">Simulasi Nilai Sampah</h2>
                            <small class="text-muted">Hitung taksiran cuan dari sampah rumah tangga</small>
                        </div>
                    </div>

                    <form id="calculator-form" onsubmit="return false;">
                        <div class="mb-3">
                            <label for="calc-kategori" class="form-label small fw-semibold">Pilih Kategori Sampah</label>
                            <select id="calc-kategori" class="form-select">
                                <?php foreach ($kategori_list as $index => $item): ?>
                                    <option value="<?= $item['id_kategori'] ?>" data-harga="<?= $item['harga_per_kg'] ?>" <?= $index === 0 ? 'selected' : '' ?>>
                                        <?= sanitize($item['nama_kategori']) ?> (<?= rupiah($item['harga_per_kg']) ?>/kg)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="calc-berat" class="form-label small fw-semibold">Estimasi Berat Fisik</label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0.5" class="form-control" id="calc-berat" value="5" placeholder="Contoh: 5">
                                <span class="input-group-text bg-light">Kg</span>
                            </div>
                        </div>

                        <div class="calc-result-box mb-4">
                            <small class="text-muted d-block mb-1" style="font-size: 0.78rem;">Estimasi Saldo yang Masuk ke Tabungan:</small>
                            <div class="fs-2 fw-bold text-success" id="calc-result">Rp 22.500</div>
                            <small class="text-muted" id="calc-price-rate" style="font-size: 0.75rem;">Berdasarkan tarif resmi bank sampah</small>
                        </div>

                        <div class="d-grid">
                            <?php if (is_logged_in()): ?>
                                <a href="<?= base_url('nasabah/setor.php') ?>" class="btn btn-primary">
                                    <i class="bi bi-send-check me-1"></i> Ajukan Setoran Ini Sekarang
                                </a>
                            <?php else: ?>
                                <a href="<?= base_url('register.php') ?>" class="btn btn-primary">
                                    <i class="bi bi-arrow-right-circle me-1"></i> Tukar Sampah Jadi Saldo Sekarang
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<main class="py-5">
    <!-- Alur Kerja 3 Tahap (Card-Based Minimalist UI) -->
    <section class="container mb-5 py-3">
        <div class="text-center mb-5">
            <span class="pastel-badge pastel-badge-mint text-uppercase">Tahapan Sederhana</span>
            <h2 class="h3 fw-bold text-dark mt-2 mb-2">Tiga Langkah Mengubah Sampah Menjadi Cuan</h2>
            <p class="text-muted" style="max-width: 580px; margin: 0 auto;">Alur praktis tanpa repot untuk warga, mahasiswa, dan pemilik warung/kafe</p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="d2e-card h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d2e-monoline-icon icon-pastel-mint">
                            <i class="bi bi-funnel fs-4"></i>
                        </div>
                        <span class="badge bg-light text-muted border">Langkah 01</span>
                    </div>
                    <h3 class="h6 fw-bold text-dark mb-2">Pilah Sampah dari Rumah</h3>
                    <p class="text-muted small mb-0">
                        Pisahkan kardus, botol plastik, kaleng, atau kertas sesuai kategori dan panduan. Pastikan dalam keadaan kering dan bersih dari sisa makanan.
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="d2e-card h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d2e-monoline-icon icon-pastel-sky">
                            <i class="bi bi-truck fs-4"></i>
                        </div>
                        <span class="badge bg-light text-muted border">Langkah 02</span>
                    </div>
                    <h3 class="h6 fw-bold text-dark mb-2">Jemput ke Rumah atau Antar Mandiri</h3>
                    <p class="text-muted small mb-0">
                        Pesan penjemputan lewat website jika memiliki volume banyak, atau bawa langsung ke titik posko drop point resmi di sekitar tempat tinggal Anda.
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="d2e-card h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d2e-monoline-icon icon-pastel-amber">
                            <i class="bi bi-cash-stack fs-4"></i>
                        </div>
                        <span class="badge bg-light text-muted border">Langkah 03</span>
                    </div>
                    <h3 class="h6 fw-bold text-dark mb-2">Timbang Akurat &amp; Cairkan Saldo</h3>
                    <p class="text-muted small mb-0">
                        Petugas menimbang sampah secara jujur. Saldo otomatis masuk ke buku tabungan digital dan siap dicairkan ke saldo e-wallet atau diambil tunai.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Katalog Harga & Panduan Pemilahan (Pastel Accent Badges & Monoline Icons) -->
    <section class="container py-4 mb-5" id="katalog">
        <div class="d-flex flex-wrap align-items-end justify-content-between mb-4 gap-3">
            <div>
                <span class="pastel-badge pastel-badge-mint text-uppercase">Katalog Terbuka</span>
                <h2 class="h3 fw-bold text-dark mt-2 mb-1">Daftar Harga Beli Sampah per Kilogram</h2>
                <p class="text-muted small mb-0">Harga beli resmi terhubung dengan jaringan daur ulang lokal (diperbarui berkala oleh pengelola)</p>
            </div>
            <div>
                <?php if (!is_logged_in()): ?>
                    <a href="<?= base_url('register.php') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Mulai Menabung Sampah
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($kategori_list as $kat): ?>
                <?php
                $badge_class = 'pastel-badge-lilac';
                $icon_class = 'icon-pastel-lilac';
                if ($kat['jenis_induk'] === 'Plastik') {
                    $badge_class = 'pastel-badge-mint';
                    $icon_class = 'icon-pastel-mint';
                } elseif ($kat['jenis_induk'] === 'Kertas & Kardus') {
                    $badge_class = 'pastel-badge-amber';
                    $icon_class = 'icon-pastel-amber';
                } elseif ($kat['jenis_induk'] === 'Logam') {
                    $badge_class = 'pastel-badge-sky';
                    $icon_class = 'icon-pastel-sky';
                } elseif ($kat['jenis_induk'] === 'Kaca') {
                    $badge_class = 'pastel-badge-teal';
                    $icon_class = 'icon-pastel-teal';
                }

                $icon_file = base_url('assets/img/' . ($kat['gambar'] ?: 'plastik-pet.svg'));
                ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="waste-cat-card">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="pastel-badge <?= $badge_class ?>"><?= sanitize($kat['jenis_induk']) ?></span>
                            <span class="waste-price-tag"><?= rupiah($kat['harga_per_kg']) ?><small class="text-muted fw-normal" style="font-size: 0.72rem;">/kg</small></span>
                        </div>

                        <div class="d-flex align-items-center gap-3 my-2">
                            <div class="d2e-monoline-icon <?= $icon_class ?>">
                                <img src="<?= $icon_file ?>" alt="" width="28" height="28">
                            </div>
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-0"><?= sanitize($kat['nama_kategori']) ?></h3>
                            </div>
                        </div>

                        <div class="mt-auto pt-3 border-top mt-3">
                            <div class="text-muted fw-semibold small mb-1" style="font-size: 0.72rem; letter-spacing: 0.04em;">PANDUAN PEMILAHAN:</div>
                            <p class="text-muted small mb-0" style="font-size: 0.82rem; line-height: 1.45;">
                                <?= sanitize($kat['panduan_pilah']) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Edukasi Pemilahan 3C (Clean, Clear, Compact) -->
    <section class="container py-4 mb-5" id="panduan">
        <div class="p-4 p-md-5 bg-white border rounded-4 shadow-sm">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg-6">
                    <span class="pastel-badge pastel-badge-mint text-uppercase mb-2">Edukasi Praktis</span>
                    <h2 class="h3 fw-bold text-dark mt-2 mb-3">Mengapa Sampah Harus Dipilah dari Rumah?</h2>
                    <p class="text-muted">
                        Sampah rumah tangga yang tercampur dengan sisa makanan basah akan mengalami penurunan mutu bahan hingga 80%, bahkan sering kali ditolak pabrik daur ulang. Dengan memilah sampah sejak dari dapur:
                    </p>
                    <ul class="text-secondary small d-flex flex-column gap-2 mb-4 ps-3">
                        <li><strong>Nilai Jual Terjaga Maksimal:</strong> Kertas dan kardus tidak lembek atau bernoda minyak sehingga laku dengan tarif tertinggi.</li>
                        <li><strong>Kebersihan Lingkungan Tempat Tinggal:</strong> Mengurangi tumpukan sampah berbau busuk di TPS lingkungan.</li>
                        <li><strong>Kemanusiaan &amp; Keselamatan:</strong> Membantu para pekerja bank sampah menyortir material secara bersih dan higienis.</li>
                    </ul>
                    <?php if (!is_logged_in()): ?>
                        <a href="<?= base_url('register.php') ?>" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-1"></i> Mulai Jadi Pahlawan Lingkungan
                        </a>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="p-4 bg-light rounded-4 border">
                        <h3 class="h6 fw-bold text-dark mb-3"><i class="bi bi-lightbulb text-warning me-2"></i>Panduan Singkat 3C (Clean, Clear, Compact)</h3>
                        <div class="d-flex gap-3 mb-3">
                            <div class="d2e-monoline-icon icon-pastel-mint" style="width: 40px; height: 40px;">
                                <span class="fw-bold text-success">1</span>
                            </div>
                            <div>
                                <strong class="d-block text-dark small">Clean (Bersihkan)</strong>
                                <span class="text-muted small">Bilas botol susu, kaleng jus, atau gelas kopi dari sisa cairan manis agar tidak basi atau mengundang semut.</span>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <div class="d2e-monoline-icon icon-pastel-sky" style="width: 40px; height: 40px;">
                                <span class="fw-bold text-primary">2</span>
                            </div>
                            <div>
                                <strong class="d-block text-dark small">Clear (Pisahkan Benda Asing)</strong>
                                <span class="text-muted small">Lepas selotip tebal dari kardus packaging, singkirkan staples kawat dari tumpukan berkas kertas ujian.</span>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <div class="d2e-monoline-icon icon-pastel-amber" style="width: 40px; height: 40px;">
                                <span class="fw-bold text-warning">3</span>
                            </div>
                            <div>
                                <strong class="d-block text-dark small">Compact (Pipihkan &amp; Ikat)</strong>
                                <span class="text-muted small">Injak kaleng atau botol plastik, lipat kardus hingga pipih dan ikat rapi. Ini menghemat volume kantong jemput.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Informasi Lokasi Drop Point -->
    <section class="container py-4 mb-5" id="lokasi">
        <div class="text-center mb-5">
            <span class="pastel-badge pastel-badge-sky text-uppercase">Akses Penyaluran</span>
            <h2 class="h3 fw-bold text-dark mt-2 mb-2">Titik Drop Point &amp; Jadwal Operasional</h2>
            <p class="text-muted" style="max-width: 580px; margin: 0 auto;">Bagi warga yang memilih opsi Antar Mandiri, silakan kunjungi posko resmi berikut</p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-6">
                <div class="d2e-card h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="pastel-badge pastel-badge-mint">Posko Induk</span>
                        <span class="text-muted small"><i class="bi bi-clock me-1"></i>08.00 - 16.00 WIB</span>
                    </div>
                    <h3 class="h5 fw-bold text-dark mb-2">Bank Sampah Induk Siantar</h3>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>Jl. Sutomo No. 18, Pematangsiantar (Dekat Kantor Kelurahan)
                    </p>
                    <p class="text-secondary small mb-3">
                        Menerima semua kategori sampah terpilah skala rumah tangga maupun grosir. Fasilitas timbangan digital terkalibrasi dan pencairan tunai langsung di tempat.
                    </p>
                    <div class="p-2 bg-light border rounded small text-muted">
                        <i class="bi bi-telephone text-success me-1"></i> Petugas Posko: Pak Joko (0812-3456-7890)
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="d2e-card h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="pastel-badge pastel-badge-sky">Drop Point Kampus &amp; Kos</span>
                        <span class="text-muted small"><i class="bi bi-clock me-1"></i>13.00 - 18.00 WIB</span>
                    </div>
                    <h3 class="h5 fw-bold text-dark mb-2">Pos Penampungan Komunitas Kampus</h3>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>Jl. Merdeka Samping Kampus, Area Parkir Timur
                    </p>
                    <p class="text-secondary small mb-3">
                        Fokus pada pengumpulan botol plastik air mineral kemasan, kertas ujian/buku bekas, dan kardus paket belanja online milik mahasiswa/anak kos.
                    </p>
                    <div class="p-2 bg-light border rounded small text-muted">
                        <i class="bi bi-telephone text-success me-1"></i> Petugas Posko: Kak Dimas (0821-9876-5432)
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Rancangan Kampanye & Poster Promosi Digital (Menjawab Instruksi 4) -->
    <section class="container py-4 mb-4">
        <div class="p-4 p-md-5 bg-white border rounded-4 shadow-sm">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg-7">
                    <span class="pastel-badge pastel-badge-amber text-uppercase mb-2">Strategi Pemasaran Digital (Instruksi 4)</span>
                    <h2 class="h4 fw-bold text-dark mt-2 mb-3">Konsep Kampanye: Dari Tumpukan Kardus Jadi Saldo E-Wallet</h2>
                    <p class="text-muted small mb-3">
                        Rancangan konten promosi digital media sosial (Instagram Reels/Carousel, TikTok, dan Broadcast WhatsApp Group Komunitas RT/RW &amp; Kampus) dengan sasaran audiens mahasiswa kos dan ibu rumah tangga:
                    </p>

                    <div class="p-3 bg-light border rounded-3 mb-3">
                        <div class="fw-bold small text-dark mb-1"><i class="bi bi-quote text-success fs-5 me-1"></i>Draft Caption Pemasaran Resmi:</div>
                        <p class="text-secondary small fst-italic mb-0" style="line-height: 1.5;">
                            "Kardus paket menumpuk di pojok kos? Botol plastik bekas minum cuma bikin sempit ruangan? Jangan dibuang begitu saja! Sekarang kamu bisa cek harga jual sampahmu dan tukarkan jadi saldo uang tunai lewat Drop2Earn. Cukup pilah dari rumah, ajukan jemput lewat web, dan biarkan sampahmu menghasilkan cuan sambil menjaga kelestarian bumi. Yuk, daftar gratis di website Drop2Earn: Pilah Sampah, Panen Berkah!"
                        </p>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border">#Drop2Earn</span>
                        <span class="badge bg-light text-dark border">#SmartWasteManagement</span>
                        <span class="badge bg-light text-dark border">#UbahSampahJadiRupiah</span>
                        <span class="badge bg-light text-dark border">#BankSampahDigital</span>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="p-3 border rounded-4 text-center bg-light shadow-sm">
                        <div class="badge bg-success mb-2">Simulasi Poster Digital Feed</div>
                        <div class="p-4 bg-white border rounded-3 mb-3">
                            <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Perbandingan Visual</div>
                            <h4 class="h6 fw-bold text-dark mb-3">Sampah Kos Menumpuk vs Saldo Jajan E-Wallet</h4>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-light text-muted small">
                                        <i class="bi bi-trash fs-4 d-block text-secondary mb-1"></i>
                                        <strong>Sebelum:</strong><br>Kardus berantakan di kamar
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-success-subtle text-success small">
                                        <i class="bi bi-wallet2 fs-4 d-block text-success mb-1"></i>
                                        <strong>Sesudah:</strong><br>Saldo e-wallet bertambah
                                    </div>
                                </div>
                            </div>
                            <div class="small text-muted border-top pt-2" style="font-size: 0.75rem;">
                                Scan QR Code atau kunjungi <strong>Drop2Earn</strong>
                            </div>
                        </div>
                        <small class="text-muted" style="font-size: 0.75rem;">Materi promosi siap eksekusi di Canva &amp; Instagram</small>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
