/**
 * Lightweight Components - Replaces Bootstrap JS
 * Pure vanilla JavaScript, no dependencies
 */

(function() {
    'use strict';

    // ========== DROPDOWN FUNCTIONALITY ==========
    function initDropdowns() {
        document.querySelectorAll('[data-bs-toggle="dropdown"], .dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const dropdown = this.nextElementSibling;
                if (dropdown && dropdown.classList.contains('dropdown-menu')) {
                    // Close all other dropdowns
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        if (menu !== dropdown) {
                            menu.classList.remove('show');
                        }
                    });
                    
                    // Toggle current dropdown
                    dropdown.classList.toggle('show');
                }
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    }

    // ========== MOBILE NAVBAR TOGGLE ==========
    function initNavbar() {
        const togglers = document.querySelectorAll('.navbar-toggler, [data-bs-toggle="collapse"]');
        togglers.forEach(toggler => {
            toggler.addEventListener('click', function() {
                const targetId = this.getAttribute('data-bs-target') || '#navbarNav';
                const target = document.querySelector(targetId);
                if (target) {
                    target.classList.toggle('show');
                    target.classList.toggle('collapse');
                }
            });
        });

        // Close navbar when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.navbar')) {
                document.querySelectorAll('.navbar-collapse.show').forEach(nav => {
                    nav.classList.remove('show');
                    nav.classList.add('collapse');
                });
            }
        });
    }

    // ========== ALERT DISMISSAL ==========
    function initAlerts() {
        document.querySelectorAll('[data-bs-dismiss="alert"], .btn-close').forEach(btn => {
            btn.addEventListener('click', function() {
                const alert = this.closest('.alert');
                if (alert) {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }
            });
        });

        // Auto-dismiss alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            if (!alert.classList.contains('alert-permanent')) {
                setTimeout(() => {
                    const closeBtn = alert.querySelector('.btn-close');
                    if (closeBtn) {
                        closeBtn.click();
                    }
                }, 5000);
            }
        });
    }

    // ========== MODAL FUNCTIONALITY ==========
    function initModals() {
        // Close modals with close button or backdrop click
        document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) {
                    closeModal(modal);
                }
            });
        });

        // Modal backdrop click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal(this);
                }
            });
        });
    }

    function closeModal(modal) {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }, 300);
    }

    // ========== FADE EFFECT ==========
    function initFadeEffects() {
        document.querySelectorAll('.fade').forEach(el => {
            setTimeout(() => {
                el.classList.add('show');
            }, 10);
        });
    }

    // ========== INITIALIZE ALL ==========
    function init() {
        initDropdowns();
        initNavbar();
        initAlerts();
        initModals();
        initFadeEffects();
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Export for manual initialization if needed
    window.LightweightComponents = {
        initDropdowns,
        initNavbar,
        initAlerts,
        initModals
    };
})();

