<?php
/**
 * Public Footer Component
 * Drop2Earn - Smart Waste Management
 */
?>
<footer class="d2e-footer mt-auto">
    <div class="container">
        <!-- Footer Grid: Posko Layanan & Kontak tetap di kanan -->
        <div class="row justify-content-between g-4 mb-4">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="<?= base_url('assets/img/logo.svg') ?>" alt="Logo Drop2Earn" width="28" height="28">
                    <strong class="text-dark fs-5">Drop2Earn</strong>
                </div>
                <p class="text-muted small mb-3">
                    Platform digital tabungan sampah dan direktori jemput sampah daur ulang. Pilah sampah dari rumah, catat transaksi secara transparan, dan tukarkan menjadi saldo rupiah.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-light text-dark border">PHP 8 Native</span>
                    <span class="badge bg-light text-dark border">Bootstrap 5.3</span>
                    <span class="badge bg-light text-dark border">MySQL</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Smart Waste Management</span>
                </div>
            </div>

            <div class="col-12 col-md-5 col-lg-4">
                <h6 class="text-dark fw-bold mb-3">Posko Layanan &amp; Kontak</h6>
                <p class="text-muted small mb-2">
                    <i class="bi bi-geo-alt text-success me-2"></i>Bank Sampah Induk Siantar, Jl. Sutomo No. 18, Pematangsiantar
                </p>
                <p class="text-muted small mb-2">
                    <i class="bi bi-clock text-success me-2"></i>Operasional: Senin - Sabtu (08.00 - 16.00 WIB)
                </p>
                <p class="text-muted small mb-0">
                    <i class="bi bi-whatsapp text-success me-2"></i>WhatsApp: +62 812-3456-7890
                </p>
            </div>
        </div>

        <div class="border-top pt-3 text-center small text-muted">
            <p class="mb-0">
                &copy; <?= date('Y') ?> Drop2Earn. Dirancang untuk Ujian Praktik Pemasaran &amp; Bisnis Digital (IK 4517).
            </p>
        </div>
    </div>
</footer>

<!-- Modern Flat 2.0 Confirmation & Warning Modal (Universal Reusable) -->
<div class="modal fade" id="d2eConfirmModal" tabindex="-1" aria-labelledby="d2eConfirmModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">
            <div class="modal-body p-2">
                <div class="d2e-modal-icon-box icon-pastel-amber" id="d2eConfirmIconBox">
                    <i class="bi bi-question-circle text-warning fs-3" id="d2eConfirmIcon"></i>
                </div>
                <h5 class="h6 fw-bold text-dark mb-2" id="d2eConfirmModalTitle">Konfirmasi Tindakan</h5>
                <p class="text-muted small mb-4" id="d2eConfirmModalMsg">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm px-3" id="d2eConfirmBtnProceed">Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Toast Notification Stack -->
<div id="d2e-toast-container"></div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Drop2Earn Application JS -->
<script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
