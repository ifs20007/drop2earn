<?php
/**
 * Showcase Kampanye Pemasaran Digital (Menjawab Instruksi 4 IK 4517)
 * Platform: Drop2Earn - Smart Waste Management
 */
require_once __DIR__ . '/config/database.php';
$page_title = 'Kampanye Pemasaran Digital & Konten Promosi';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="py-4 bg-white border-bottom mb-4">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="pastel-badge pastel-badge-amber text-uppercase">Instruksi Akademik 4</span>
                    <span class="text-muted small">Mata Kuliah Pemasaran &amp; Bisnis Digital Praktik</span>
                </div>
                <h1 class="h3 fw-bold text-dark mb-1">Rancangan Konten Promosi &amp; Strategi Digital</h1>
                <p class="text-muted small mb-0">
                    Spesifikasi visual poster, storyboard video pendek, target audiens, dan draft copywriting resmi Drop2Earn
                </p>
            </div>
            <div>
                <a href="<?= base_url('index.php') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>

<main class="container mb-5">
    <!-- Ringkasan Strategi Pemasaran Digital 4 Pilar -->
    <div class="row g-4 mb-5">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="d2e-card h-100">
                <div class="d2e-monoline-icon icon-pastel-mint mb-3">
                    <i class="bi bi-bullseye fs-4"></i>
                </div>
                <h2 class="h6 fw-bold text-dark mb-1">Target Segmen Primer</h2>
                <p class="text-muted small mb-0">
                    Mahasiswa kos, penghuni rantau, dan ibu rumah tangga urban (usia 20-45 tahun) yang aktif menggunakan smartphone.
                </p>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="d2e-card h-100">
                <div class="d2e-monoline-icon icon-pastel-sky mb-3">
                    <i class="bi bi-share fs-4"></i>
                </div>
                <h2 class="h6 fw-bold text-dark mb-1">Kanal Distribusi</h2>
                <p class="text-muted small mb-0">
                    Instagram (Carousel &amp; Reels), TikTok edukasi mikro, dan penyiaran WhatsApp Group RT/RW serta komunitas kampus.
                </p>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="d2e-card h-100">
                <div class="d2e-monoline-icon icon-pastel-amber mb-3">
                    <i class="bi bi-lightbulb fs-4"></i>
                </div>
                <h2 class="h6 fw-bold text-dark mb-1">Nilai Pesan Utama</h2>
                <p class="text-muted small mb-0">
                    "Pilah dari Rumah, Panen Berkah" menonjolkan konversi langsung dari barang bekas yang menyita tempat menjadi saldo uang tunai.
                </p>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="d2e-card h-100">
                <div class="d2e-monoline-icon icon-pastel-rose mb-3">
                    <i class="bi bi-graph-up-arrow fs-4"></i>
                </div>
                <h2 class="h6 fw-bold text-dark mb-1">Fokus Konversi (CTA)</h2>
                <p class="text-muted small mb-0">
                    Mengajak pendaftaran akun baru melalui web ringan berbasis responsif tanpa kewajiban mengunduh aplikasi berbayar.
                </p>
            </div>
        </div>
    </div>

    <!-- Section Preview Visual & Simulasi Media Sosial -->
    <div class="row g-4 mb-5">
        <!-- Mockup Feed Poster Instagram 1:1 -->
        <div class="col-12 col-lg-6">
            <div class="d2e-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-instagram text-danger fs-5"></i>
                        <span class="fw-bold small text-dark">Simulasi Poster Feed Instagram (1:1 Aspect Ratio)</span>
                    </div>
                    <span class="badge bg-light text-muted border">Format Feed</span>
                </div>

                <!-- Konten Visual Poster Rendered in Flat 2.0 -->
                <div class="p-4 rounded-4 text-white position-relative overflow-hidden mb-3" style="background: linear-gradient(145deg, #15803d 0%, #166534 60%, #0f172a 100%);">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-white p-1 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="bi bi-recycle text-success fs-5"></i>
                            </div>
                            <span class="fw-bold tracking-tight text-white small">Drop2Earn</span>
                        </div>
                        <span class="badge bg-warning text-dark fw-bold px-2 py-1" style="font-size: 0.7rem;">Cuan Lingkungan</span>
                    </div>

                    <div class="text-center my-3">
                        <span class="badge bg-white text-success px-3 py-1 rounded-pill fw-semibold mb-2" style="font-size: 0.75rem;">
                            Solusi Sampah Rumah &amp; Kos
                        </span>
                        <h3 class="h5 fw-bold text-white mb-1">Kardus Menumpuk di Kamar?</h3>
                        <h4 class="h4 fw-extrabold text-warning mb-2">Tukarkan Jadi Saldo Jajan!</h4>
                        <p class="small text-white-50 mb-3" style="font-size: 0.82rem;">
                            Pilah sampah keringmu, ajukan jemput atau antar mandiri. Dapatkan saldo e-wallet langsung.
                        </p>
                    </div>

                    <!-- Split Komparasi Visual -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2 rounded-3 text-center" style="background: rgba(255,255,255,0.12); backdrop-filter: blur(4px);">
                                <div class="text-white-50 small" style="font-size: 0.7rem;">SEBELUM:</div>
                                <div class="text-danger fw-bold small"><i class="bi bi-x-circle me-1"></i>Kamar Kos Penuh</div>
                                <div class="text-white-50" style="font-size: 0.68rem;">Kardus paket &amp; botol plastik berantakan</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3 text-center" style="background: rgba(34, 197, 94, 0.25); border: 1px solid rgba(255,255,255,0.2);">
                                <div class="text-warning small" style="font-size: 0.7rem;">SESUDAH:</div>
                                <div class="text-white fw-bold small"><i class="bi bi-check-circle-fill text-warning me-1"></i>Saldo E-Wallet</div>
                                <div class="text-white-50" style="font-size: 0.68rem;">Cair ke Gopay, OVO, Dana, atau Tunai</div>
                            </div>
                        </div>
                    </div>

                    <!-- Ticker Harga Sampah Populer -->
                    <div class="p-2 rounded-3 bg-black bg-opacity-25 d-flex justify-content-around text-center mb-3">
                        <div>
                            <div class="text-white-50" style="font-size: 0.65rem;">KARDUS</div>
                            <div class="fw-bold text-white small">Rp 1.500<span class="text-white-50" style="font-size: 0.6rem;">/kg</span></div>
                        </div>
                        <div class="border-start border-white border-opacity-25"></div>
                        <div>
                            <div class="text-white-50" style="font-size: 0.65rem;">BOTOL PLASTIK</div>
                            <div class="fw-bold text-warning small">Rp 3.200<span class="text-white-50" style="font-size: 0.6rem;">/kg</span></div>
                        </div>
                        <div class="border-start border-white border-opacity-25"></div>
                        <div>
                            <div class="text-white-50" style="font-size: 0.65rem;">KALENG</div>
                            <div class="fw-bold text-white small">Rp 12.000<span class="text-white-50" style="font-size: 0.6rem;">/kg</span></div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2 border-top border-white border-opacity-25">
                        <div class="small text-white-50" style="font-size: 0.72rem;">
                            <i class="bi bi-globe me-1"></i> drop2earn.id
                        </div>
                        <span class="badge bg-white text-dark small px-3 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;">
                            Daftar Gratis Sekarang
                        </span>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between small text-muted">
                    <span><i class="bi bi-palette me-1"></i>Tema: Modern Eco-Card</span>
                    <span>Siap diterapkan di Canva &amp; Instagram Post</span>
                </div>
            </div>
        </div>

        <!-- Storyboard Video Singkat Reels / TikTok 9:16 -->
        <div class="col-12 col-lg-6">
            <div class="d2e-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-tiktok text-dark fs-5"></i>
                        <span class="fw-bold small text-dark">Storyboard Konten Video Singkat (TikTok &amp; Reels 15 Detik)</span>
                    </div>
                    <span class="badge bg-light text-muted border">Durasi 15s</span>
                </div>

                <div class="list-group list-group-flush small mb-3">
                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex gap-2">
                            <span class="badge bg-danger text-white rounded-pill px-2 py-1 align-self-start">00:00 - 00:03</span>
                            <div>
                                <div class="fw-bold text-dark">Scene 1 (Hook Awal)</div>
                                <div class="text-muted">Visual sudut kamar kos penuh kardus paket olshop. Suara sound trending: "Pernah gak sih ngerasa kamar makin sempit gara-gara sampah kardus?"</div>
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex gap-2">
                            <span class="badge bg-warning text-dark rounded-pill px-2 py-1 align-self-start">00:03 - 00:07</span>
                            <div>
                                <div class="fw-bold text-dark">Scene 2 (Transisi Solusi)</div>
                                <div class="text-muted">Aktor membuka website Drop2Earn di ponsel, memperlihatkan katalog harga (misal: botol PET Rp 3.200/kg). Tekan tombol "Ajukan Jemput".</div>
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex gap-2">
                            <span class="badge bg-success text-white rounded-pill px-2 py-1 align-self-start">00:07 - 00:11</span>
                            <div>
                                <div class="fw-bold text-dark">Scene 3 (Validasi &amp; Timbangan)</div>
                                <div class="text-muted">Petugas bank sampah menimbang timbangan digital secara transparan. Notifikasi saldo langsung muncul di smartphone nasabah.</div>
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex gap-2">
                            <span class="badge bg-primary text-white rounded-pill px-2 py-1 align-self-start">00:11 - 00:15</span>
                            <div>
                                <div class="fw-bold text-dark">Scene 4 (Call to Action)</div>
                                <div class="text-muted">Aktor menikmati kopi hasil pencairan saldo e-wallet. Teks di layar: "Ubah sampahmu jadi rupiah sekarang di Drop2Earn. Link di bio!"</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-2 bg-light border rounded small text-secondary">
                    <i class="bi bi-music-note-beamed text-success me-1"></i> Rekomendasi Audio: Lo-fi upbeat dengan efek suara kasir (*cha-ching*) pada detik ke-11.
                </div>
            </div>
        </div>
    </div>

    <!-- Salin Copywriting Siap Pakai -->
    <div class="d2e-card mb-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
            <div>
                <h2 class="h5 fw-bold text-dark mb-0">Naskah Copywriting &amp; Broadcast Resmi</h2>
                <p class="text-muted small mb-0">Salin naskah promosi dengan satu klik untuk penyiaran di berbagai media sosial</p>
            </div>
            <span class="badge bg-success-subtle text-success border border-success-subtle">Siap Tayang</span>
        </div>

        <div class="row g-4">
            <!-- Naskah Feed Instagram -->
            <div class="col-12 col-md-6">
                <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold small text-dark"><i class="bi bi-instagram text-danger me-1"></i> Caption Feed / Carousel Instagram</span>
                            <button type="button" class="btn btn-sm btn-outline-success copy-btn" data-target="#captionIg">
                                <i class="bi bi-clipboard me-1"></i> Salin
                            </button>
                        </div>
                        <div class="p-3 bg-white border rounded small text-secondary" id="captionIg" style="white-space: pre-wrap; font-size: 0.82rem; line-height: 1.5;">Kardus paket menumpuk di pojok kos? Botol plastik bekas minum cuma bikin sempit ruangan? Jangan dibuang begitu saja! 📦♻️

Sekarang kamu bisa cek harga jual sampahmu dan tukarkan jadi saldo uang tunai lewat Drop2Earn. Cukup pilah dari rumah, ajukan jemput lewat web, dan biarkan sampahmu menghasilkan cuan sambil menjaga kelestarian bumi.

Yuk, daftar gratis di website Drop2Earn sekarang:
🌐 Pilah Sampah, Panen Berkah!

#Drop2Earn #SmartWasteManagement #UbahSampahJadiRupiah #BankSampahDigital #AnakKosHemat</div>
                    </div>
                </div>
            </div>

            <!-- Naskah Broadcast WhatsApp Group -->
            <div class="col-12 col-md-6">
                <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold small text-dark"><i class="bi bi-whatsapp text-success me-1"></i> Template Broadcast WhatsApp Group RT/RW &amp; Kampus</span>
                            <button type="button" class="btn btn-sm btn-outline-success copy-btn" data-target="#captionWa">
                                <i class="bi bi-clipboard me-1"></i> Salin
                            </button>
                        </div>
                        <div class="p-3 bg-white border rounded small text-secondary" id="captionWa" style="white-space: pre-wrap; font-size: 0.82rem; line-height: 1.5;">Bapak, Ibu, dan Rekan-Rekan Warga RT/RW yang kami hormati,

Apakah di rumah menumpuk botol plastik, kardus belanja, atau kaleng yang belum sempat dibuang?

Kini hadir layanan Bank Sampah Digital *Drop2Earn*:
1. Transparansi harga per kilogram jelas di website
2. Sampah bisa diantar ke Posko Drop Point atau dijemput ke rumah
3. Hasil penjualan langsung masuk ke saldo buku tabungan dan bisa dicairkan ke rekening bank/e-wallet

Mari bersama menjaga kebersihan lingkungan sekaligus menambah kas keluarga.
Daftar mandiri melalui tautan resmi kami: [http://localhost/Drop2Earn]

Terima kasih atas kepedulian bersama menjaga bumi! 🌿</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analisis Persona Audiens & Metrik Keberhasilan Kampanye -->
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="d2e-card h-100">
                <h3 class="h6 fw-bold text-dark mb-3"><i class="bi bi-people me-2 text-primary"></i>Matriks Karakteristik Audiens Sasaran</h3>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered small align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Profil Audiens</th>
                                <th>Masalah Utama</th>
                                <th>Pendekatan Komunikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold">Mahasiswa Kos (18-24 th)</td>
                                <td>Kardus kiriman paket menumpuk, butuh tambahan jajan.</td>
                                <td>Bahasa santai, fokus pada penambahan saldo e-wallet dan kemudahan tanpa instal aplikasi besar.</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Ibu Rumah Tangga (25-45 th)</td>
                                <td>Botol sabun/minyak goreng bekas menumpuk di dapur, tidak tahu harga jual.</td>
                                <td>Transparansi harga per kg, jadwal penjemputan pasti, keamanan timbangan digital.</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Pelaku Usaha Mikro (Warung/Kafe)</td>
                                <td>Volume limbah botol/kardus harian tinggi, pengepul konvensional tidak teratur.</td>
                                <td>Layanan langganan penjemputan terjadwal dan laporan rekapitulasi setoran bulanan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="d2e-card h-100">
                <h3 class="h6 fw-bold text-dark mb-3"><i class="bi bi-speedometer me-2 text-success"></i>Indikator Kinerja Utama (KPI Kampanye)</h3>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span>Jangkauan Penayangan (Impressions &amp; Views)</span>
                        <span class="badge bg-light text-dark border">Target: &gt; 5.000 jangkauan/bulan</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span>Tingkat Klik Menuju Website (Click-Through Rate)</span>
                        <span class="badge bg-light text-dark border">Target: &gt; 3.5%</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span>Konversi Registrasi Nasabah Baru</span>
                        <span class="badge bg-light text-dark border">Target: 100 nasabah aktif pada bulan ke-1</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span>Volume Sampah Terkumpul</span>
                        <span class="badge bg-light text-dark border">Target: 500 kg sampah terpilah pada bulan ke-1</span>
                    </li>
                </ul>
                <div class="mt-3 p-2 bg-success-subtle border border-success-subtle rounded small text-success">
                    <i class="bi bi-check-circle-fill me-1"></i> Data kampanye dapat disesuaikan dengan laporan evaluasi bisnis mingguan.
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const copyBtns = document.querySelectorAll('.copy-btn');
    copyBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetEl = document.querySelector(targetId);
            if (!targetEl) return;

            const textToCopy = targetEl.innerText;
            navigator.clipboard.writeText(textToCopy).then(() => {
                if (typeof window.showToast === 'function') {
                    window.showToast('Teks naskah promosi berhasil disalin ke clipboard!', 'success');
                } else {
                    alert('Teks naskah promosi berhasil disalin ke clipboard!');
                }
            }).catch(err => {
                console.error('Gagal menyalin:', err);
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

