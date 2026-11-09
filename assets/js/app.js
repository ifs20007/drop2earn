/**
 * Drop2Earn - Client-side Utilities, Animations, Toasts & Custom Confirmation Modals
 * Professional Flat 2.0 Micro-Interactions
 */

// 1. Universal Toast Notification Engine
window.showToast = function (arg1, arg2, arg3) {
    let type = 'info';
    let title = 'Pemberitahuan';
    let message = '';

    // Handle flexible arguments
    if (arg3 !== undefined) {
        // signature: showToast(type, title, message)
        type = arg1 || 'info';
        title = arg2 || 'Pemberitahuan';
        message = arg3 || '';
    } else if (arg2 !== undefined) {
        // signature: showToast(type, message) OR showToast(message, type)
        const validTypes = ['success', 'warning', 'error', 'info', 'danger'];
        if (validTypes.includes(arg1.toLowerCase())) {
            type = arg1.toLowerCase();
            message = arg2;
        } else if (validTypes.includes(arg2.toLowerCase())) {
            type = arg2.toLowerCase();
            message = arg1;
        } else {
            title = arg1;
            message = arg2;
        }

        if (type === 'danger') type = 'error';
        if (title === 'Pemberitahuan') {
            if (type === 'success') title = 'Berhasil';
            else if (type === 'warning') title = 'Perhatian';
            else if (type === 'error') title = 'Gagal';
            else title = 'Informasi';
        }
    } else if (arg1) {
        message = arg1;
    }

    if (!message || message.trim() === '') return;

    let container = document.getElementById('d2e-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'd2e-toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `d2e-toast d2e-toast-${type}`;

    let iconClass = 'bi-check-circle-fill';
    if (type === 'warning') iconClass = 'bi-exclamation-triangle-fill';
    else if (type === 'error') iconClass = 'bi-x-circle-fill';
    else if (type === 'info') iconClass = 'bi-info-circle-fill';

    toast.innerHTML = `
        <div class="d2e-toast-icon">
            <i class="bi ${iconClass}"></i>
        </div>
        <div class="d2e-toast-content">
            <div class="d2e-toast-title">${title}</div>
            <div class="d2e-toast-message">${message}</div>
        </div>
        <button type="button" class="d2e-toast-close" aria-label="Tutup notifikasi">&times;</button>
        <div class="d2e-toast-progress"></div>
    `;

    container.appendChild(toast);

    let isDismissed = false;
    const dismiss = () => {
        if (isDismissed) return;
        isDismissed = true;
        toast.classList.add('hiding');
        setTimeout(() => toast.remove(), 260);
    };

    const closeBtn = toast.querySelector('.d2e-toast-close');
    if (closeBtn) closeBtn.addEventListener('click', dismiss);

    const autoTimer = setTimeout(dismiss, 4500);

    // Pause on hover
    toast.addEventListener('mouseenter', () => clearTimeout(autoTimer));
    toast.addEventListener('mouseleave', () => setTimeout(dismiss, 2000));
};

document.addEventListener('DOMContentLoaded', function () {
    // 2. Staggered Show-Up Animations on Cards
    const cards = document.querySelectorAll('.d2e-card, .stat-card');
    cards.forEach((card, index) => {
        if (index < 12) {
            card.classList.add('d2e-animate-card');
            card.style.animationDelay = `${Math.min(index * 0.04, 0.4)}s`;
        }
    });

    // 3. Mobile Sidebar Toggle
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const dashboardSidebar = document.getElementById('dashboardSidebar');

    if (sidebarToggleBtn && dashboardSidebar) {
        sidebarToggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            dashboardSidebar.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            if (window.innerWidth < 992) {
                if (!dashboardSidebar.contains(e.target) && !sidebarToggleBtn.contains(e.target)) {
                    dashboardSidebar.classList.remove('show');
                }
            }
        });
    }

    // 4. Custom Flat 2.0 Confirmation Modal (Replaces native browser confirm())
    const confirmModalEl = document.getElementById('d2eConfirmModal');
    let confirmModal = null;
    if (confirmModalEl && typeof bootstrap !== 'undefined') {
        confirmModal = new bootstrap.Modal(confirmModalEl);
    }

    let pendingAction = null;

    document.querySelectorAll('[data-confirm]').forEach(function (element) {
        element.addEventListener('click', function (e) {
            e.preventDefault();
            const message = this.getAttribute('data-confirm') || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            const title = this.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
            const confirmType = (this.getAttribute('data-confirm-type') || 'warning').toLowerCase();
            const confirmBtnText = this.getAttribute('data-confirm-btn') || 'Lanjutkan';

            const modalTitleEl = document.getElementById('d2eConfirmModalTitle');
            const modalMsgEl = document.getElementById('d2eConfirmModalMsg');
            const modalIconBox = document.getElementById('d2eConfirmIconBox');
            const modalIcon = document.getElementById('d2eConfirmIcon');
            const btnProceed = document.getElementById('d2eConfirmBtnProceed');

            if (modalTitleEl) modalTitleEl.textContent = title;
            if (modalMsgEl) modalMsgEl.textContent = message;

            if (btnProceed) {
                btnProceed.textContent = confirmBtnText;
                btnProceed.className = 'btn btn-sm px-3';
                if (confirmType === 'danger') {
                    btnProceed.classList.add('btn-danger');
                } else if (confirmType === 'warning') {
                    btnProceed.classList.add('btn-warning');
                } else if (confirmType === 'success') {
                    btnProceed.classList.add('btn-primary');
                } else {
                    btnProceed.classList.add('btn-primary');
                }
            }

            if (modalIconBox && modalIcon) {
                modalIconBox.className = 'd2e-modal-icon-box';
                modalIcon.className = 'bi fs-3';
                if (confirmType === 'danger') {
                    modalIconBox.classList.add('icon-pastel-rose');
                    modalIcon.classList.add('bi-trash3-fill', 'text-danger');
                } else if (confirmType === 'warning') {
                    modalIconBox.classList.add('icon-pastel-amber');
                    modalIcon.classList.add('bi-exclamation-triangle-fill', 'text-warning');
                } else if (confirmType === 'success') {
                    modalIconBox.classList.add('icon-pastel-mint');
                    modalIcon.classList.add('bi-check-circle-fill', 'text-success');
                } else {
                    modalIconBox.classList.add('icon-pastel-sky');
                    modalIcon.classList.add('bi-question-circle-fill', 'text-primary');
                }
            }

            pendingAction = () => {
                if (this.tagName === 'A') {
                    window.location.href = this.href;
                } else if (this.form) {
                    if (this.name) {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = this.name;
                        hidden.value = this.value;
                        this.form.appendChild(hidden);
                    }
                    this.form.submit();
                } else if (this.getAttribute('type') === 'submit') {
                    const parentForm = this.closest('form');
                    if (parentForm) parentForm.submit();
                }
            };

            if (confirmModal) {
                confirmModal.show();
            } else {
                // Fallback if modal DOM is missing
                if (confirm(message)) {
                    if (typeof pendingAction === 'function') pendingAction();
                }
            }
        });
    });

    const proceedBtn = document.getElementById('d2eConfirmBtnProceed');
    if (proceedBtn) {
        proceedBtn.addEventListener('click', function () {
            if (confirmModal) confirmModal.hide();
            if (typeof pendingAction === 'function') {
                const action = pendingAction;
                pendingAction = null;
                action();
            }
        });
    }

    // 5. Live Calculator on Landing Page & Setor Form
    const kategoriSelect = document.getElementById('calc-kategori') || document.getElementById('id_kategori');
    const beratInput = document.getElementById('calc-berat') || document.getElementById('berat_estimasi');
    const resultDisplay = document.getElementById('calc-result');
    const priceDisplay = document.getElementById('calc-price-rate');

    function formatRupiah(amount) {
        return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
    }

    function calculateEstimate() {
        if (!kategoriSelect || !beratInput) return;

        const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex];
        const pricePerKg = parseFloat(selectedOption?.getAttribute('data-harga') || 0);
        const weight = parseFloat(beratInput.value || 0);

        if (priceDisplay && pricePerKg > 0) {
            priceDisplay.textContent = formatRupiah(pricePerKg) + ' / kg';
        }

        if (resultDisplay) {
            if (pricePerKg > 0 && weight > 0) {
                const total = pricePerKg * weight;
                resultDisplay.textContent = formatRupiah(total);
                resultDisplay.classList.add('text-success');
            } else {
                resultDisplay.textContent = 'Rp 0';
                resultDisplay.classList.remove('text-success');
            }
        }
    }

    if (kategoriSelect) {
        kategoriSelect.addEventListener('change', calculateEstimate);
    }
    if (beratInput) {
        beratInput.addEventListener('input', calculateEstimate);
    }
    calculateEstimate();

    // 6. Admin Verification Live Calculator
    const adminBeratAktual = document.getElementById('admin_berat_aktual');
    const adminTotalDisplay = document.getElementById('admin_total_display');
    const adminHargaPerKg = document.getElementById('admin_harga_per_kg');

    if (adminBeratAktual && adminTotalDisplay && adminHargaPerKg) {
        function calcAdminTotal() {
            const harga = parseFloat(adminHargaPerKg.value || 0);
            const berat = parseFloat(adminBeratAktual.value || 0);
            const total = harga * berat;
            adminTotalDisplay.textContent = formatRupiah(total);
        }
        adminBeratAktual.addEventListener('input', calcAdminTotal);
        calcAdminTotal();
    }

    // 7. Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId && targetId.length > 1) {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // 8. Automatic Trigger of Flash Toasts from Body Attributes
    const flashSuccess = document.body.getAttribute('data-toast-success');
    if (flashSuccess && flashSuccess.trim() !== '') {
        window.showToast('success', 'Operasi Berhasil', flashSuccess);
    }
    const flashError = document.body.getAttribute('data-toast-error');
    if (flashError && flashError.trim() !== '') {
        window.showToast('error', 'Terjadi Kendala', flashError);
    }
    const flashWarning = document.body.getAttribute('data-toast-warning');
    if (flashWarning && flashWarning.trim() !== '') {
        window.showToast('warning', 'Pemberitahuan Penting', flashWarning);
    }
    const flashInfo = document.body.getAttribute('data-toast-info');
    if (flashInfo && flashInfo.trim() !== '') {
        window.showToast('info', 'Informasi', flashInfo);
    }
});
