<?php
/**
 * CMS & Dashboard Footer Component
 * Drop2Earn - Smart Waste Management
 * Includes copyright, confirmation modal, toast stack, and script bundles for admin & nasabah layouts
 */
?>
        <footer class="d2e-dashboard-footer text-center">
            &copy; <?= date('Y') ?> Drop2Earn &bull; Sistem Tabungan Sampah Digital &bull; IK 4517
        </footer>
    </div>
</div>

<!-- Modern Flat 2.0 Confirmation & Warning Modal (Reusable for Dashboard Actions) -->
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

<!-- Floating Toast Container -->
<div id="d2e-toast-container"></div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Drop2Earn Application JS -->
<script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>

