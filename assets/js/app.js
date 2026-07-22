/**
 * TPMS - Main Application JavaScript
 */

const TPMS = {
    baseUrl: document.querySelector('meta[name="base-url"]')?.content || '/team1',
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',

    init() {
        this.initTheme();
        this.initSidebar();
        this.initToasts();
        this.initAjaxDefaults();
        this.initNotifications();
        this.initSearch();
        this.initDropdowns();
        this.initFormValidation();
        this.initFileInputs();
        this.initDeleteConfirmations();
        this.initToolTips();
    },

    // ========================
    // Theme Manager (8 Themes)
    // ========================
    initTheme() {
        // Read from localStorage first, then cookie, then default
        const saved = localStorage.getItem('tpms_theme')
                   || this.getCookie('tpms_theme')
                   || 'light';
        // Apply immediately but don't POST to backend (already applied by PHP on page load)
        this.setTheme(saved, false);

        // Reliable event handler
        const applySelectedTheme = (selectedBtn) => {
            const selected = selectedBtn.getAttribute('data-theme-val');
            if (selected) {
                this.setTheme(selected, true);
            }
        };

        // 1. Direct binding to all theme buttons present in the DOM
        const buttons = document.querySelectorAll('.theme-opt-btn');
        buttons.forEach(btn => {
            btn.setAttribute('data-theme-bound', 'true');
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                applySelectedTheme(btn);
            });
        });

        // 2. Delegated handler for fallback (skipping already bound buttons)
        if (typeof $ !== 'undefined') {
            $(document).off('click.tpmsTheme').on('click.tpmsTheme', '.theme-opt-btn:not([data-theme-bound])', function(e) {
                e.preventDefault();
                applySelectedTheme(this);
            });
        } else {
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.theme-opt-btn');
                if (btn && !btn.hasAttribute('data-theme-bound')) {
                    e.preventDefault();
                    applySelectedTheme(btn);
                }
            });
        }
    },

    setTheme(theme, saveToBackend = true) {
        // 1. Apply attribute on <html> immediately
        document.documentElement.setAttribute('data-theme', theme);
        // 2. Also set on <body> for specificity safety
        if (document.body) {
            document.body.setAttribute('data-theme', theme);
        }
        // 3. Persist in localStorage & cookie for instant restore on next page
        localStorage.setItem('tpms_theme', theme);
        document.cookie = 'tpms_theme=' + theme + '; path=/; max-age=31536000; SameSite=Lax';

        // 4. Update active checkmarks in dropdown
        document.querySelectorAll('.theme-opt-btn').forEach(btn => {
            const val = btn.getAttribute('data-theme-val');
            const check = btn.querySelector('.check-icon');
            if (val === theme) {
                btn.classList.add('active');
                if (check) check.classList.remove('d-none');
            } else {
                btn.classList.remove('active');
                if (check) check.classList.add('d-none');
            }
        });

        // 5. Persist to database (async, best-effort)
        if (saveToBackend && typeof $ !== 'undefined') {
            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/team1';
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            $.ajax({
                url: baseUrl + '/api/theme',
                type: 'POST',
                data: { theme: theme, csrf_token: csrf },
                error: function() { /* silent fail – localStorage is source of truth */ }
            });
        }

        // 6. Fire a custom event so page-level scripts can react (e.g. re-render charts)
        document.dispatchEvent(new CustomEvent('tpmsThemeChange', { detail: { theme } }));
    },

    getCookie(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? match[2] : null;
    },

    // ========================
    // Sidebar (Desktop Collapse & Mobile Toggle)
    // ========================
    initSidebar() {
        const toggle = document.querySelector('.menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const wrapper = document.querySelector('.app-wrapper');

        // Restore collapsed state on desktop
        const isCollapsed = localStorage.getItem('tpms_sidebar_collapsed') === 'true';
        if (isCollapsed && window.innerWidth >= 992 && wrapper) {
            wrapper.classList.add('sidebar-collapsed');
        }

        if (toggle) {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                if (window.innerWidth < 992) {
                    sidebar?.classList.toggle('show');
                    overlay?.classList.toggle('show');
                } else if (wrapper) {
                    const collapsed = wrapper.classList.toggle('sidebar-collapsed');
                    localStorage.setItem('tpms_sidebar_collapsed', collapsed ? 'true' : 'false');
                }
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar?.classList.remove('show');
                overlay.classList.remove('show');
            });
        }

        // Submenu Expand / Collapse (Treeview Accordion)
        document.querySelectorAll('.submenu-toggle').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const parent = btn.closest('.has-submenu');
                const submenu = parent?.querySelector('.sidebar-submenu');
                if (!parent || !submenu) return;

                const isOpen = parent.classList.contains('open');

                // Close other submenus (accordion feel)
                document.querySelectorAll('.has-submenu.open').forEach(item => {
                    if (item !== parent) {
                        item.classList.remove('open');
                        const sub = item.querySelector('.sidebar-submenu');
                        if (sub && typeof $ !== 'undefined') $(sub).slideUp(200);
                        else if (sub) sub.style.display = 'none';
                    }
                });

                if (isOpen) {
                    parent.classList.remove('open');
                    if (typeof $ !== 'undefined') $(submenu).slideUp(200);
                    else submenu.style.display = 'none';
                } else {
                    parent.classList.add('open');
                    if (typeof $ !== 'undefined') $(submenu).slideDown(200);
                    else submenu.style.display = 'block';
                }
            });
        });

        // Close sidebar on mobile when navigating
        document.querySelectorAll('.sidebar-nav-link:not(.submenu-toggle), .sidebar-submenu-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 991) {
                    sidebar?.classList.remove('show');
                    overlay?.classList.remove('show');
                }
            });
        });
    },

    // ========================
    // Toast Notifications
    // ========================
    initToasts() {
        // Auto-dismiss flash alerts
        document.querySelectorAll('.alert-dismissible').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    },

    showToast(message, type = 'info') {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const iconMap = {
            success: 'fas fa-check-circle text-success',
            danger: 'fas fa-times-circle text-danger',
            warning: 'fas fa-exclamation-triangle text-warning',
            info: 'fas fa-info-circle text-primary'
        };

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <i class="${iconMap[type] || iconMap.info}"></i>
            <span>${message}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100px)';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    },

    // ========================
    // AJAX Defaults
    // ========================
    initAjaxDefaults() {
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                error: (xhr) => {
                    if (xhr.status === 403) {
                        this.showToast('Session expired. Please refresh the page.', 'warning');
                    } else if (xhr.status === 500) {
                        this.showToast('Server error occurred. Please try again.', 'danger');
                    }
                }
            });
        }
    },

    // ========================
    // Notifications Polling
    // ========================
    initNotifications() {
        this.fetchNotifications();
        setInterval(() => this.fetchNotifications(), 30000);

        // Toggle dropdown
        const btn = document.querySelector('.notification-toggle');
        const dropdown = document.querySelector('.notification-dropdown');
        
        if (btn && dropdown) {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('show');
                document.querySelector('.search-results-dropdown')?.classList.remove('show');
            });

            document.addEventListener('click', () => {
                dropdown.classList.remove('show');
            });

            dropdown.addEventListener('click', (e) => e.stopPropagation());
        }
    },

    fetchNotifications() {
        if (typeof $ === 'undefined') return;
        
        $.get(this.baseUrl + '/notifications/fetch', (response) => {
            if (response.success) {
                this.updateNotificationBadge(response.count);
                this.updateNotificationDropdown(response.notifications);
            }
        }).fail(() => {});
    },

    updateNotificationBadge(count) {
        const badge = document.querySelector('.notification-count');
        const dot = document.querySelector('.badge-dot');
        
        if (badge) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        }
        if (dot) {
            dot.style.display = count > 0 ? 'block' : 'none';
        }
    },

    updateNotificationDropdown(notifications) {
        const list = document.querySelector('.notification-list');
        if (!list) return;

        if (!notifications || notifications.length === 0) {
            list.innerHTML = '<div class="p-4 text-center text-muted"><i class="fas fa-bell-slash mb-2 d-block" style="font-size:2rem"></i><small>No new notifications</small></div>';
            return;
        }

        list.innerHTML = notifications.slice(0, 5).map(n => `
            <a href="${n.link || '#'}" class="notification-item ${n.is_read ? '' : 'unread'}" 
               onclick="TPMS.markNotificationRead(${n.id})">
                <div class="n-icon bg-${this.getNotificationColor(n.type)}-soft">
                    <i class="fas fa-${this.getNotificationIcon(n.type)} text-${this.getNotificationColor(n.type)}"></i>
                </div>
                <div class="n-content">
                    <div class="n-title">${this.escapeHtml(n.title)}</div>
                    <div class="n-text">${this.escapeHtml(n.message)}</div>
                    <div class="n-time"><i class="far fa-clock me-1"></i>${n.time_ago}</div>
                </div>
            </a>
        `).join('');
    },

    markNotificationRead(id) {
        if (typeof $ !== 'undefined') {
            $.post(this.baseUrl + '/notifications/mark-read/' + id);
        }
    },

    markAllNotificationsRead() {
        if (typeof $ !== 'undefined') {
            $.post(this.baseUrl + '/notifications/mark-all-read', () => {
                this.fetchNotifications();
                this.showToast('All notifications marked as read', 'success');
            });
        }
    },

    getNotificationIcon(type) {
        const icons = {
            success: 'check-circle', danger: 'exclamation-circle',
            warning: 'exclamation-triangle', info: 'info-circle',
            announcement: 'bullhorn'
        };
        return icons[type] || 'bell';
    },

    getNotificationColor(type) {
        const colors = {
            success: 'success', danger: 'danger',
            warning: 'warning', info: 'primary',
            announcement: 'info'
        };
        return colors[type] || 'primary';
    },

    // ========================
    // Global Search
    // ========================
    initSearch() {
        const input = document.querySelector('.global-search input');
        const dropdown = document.querySelector('.search-results-dropdown');
        let timeout;

        if (!input || !dropdown) return;

        input.addEventListener('input', (e) => {
            clearTimeout(timeout);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                dropdown.classList.remove('show');
                return;
            }

            timeout = setTimeout(() => {
                if (typeof $ !== 'undefined') {
                    $.get(this.baseUrl + '/search/global', { q: query }, (response) => {
                        if (response.success && response.results.length > 0) {
                            dropdown.innerHTML = response.results.map(r => `
                                <a href="${r.url}" class="search-result-item">
                                    <div class="search-result-icon bg-${r.color}-soft">
                                        <i class="fas fa-${r.icon} text-${r.color}"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight:600;font-size:0.85rem">${this.escapeHtml(r.title)}</div>
                                        <div style="font-size:0.75rem;color:var(--text-muted)">${this.escapeHtml(r.subtitle)}</div>
                                    </div>
                                </a>
                            `).join('');
                            dropdown.classList.add('show');
                        } else {
                            dropdown.innerHTML = '<div class="p-3 text-center text-muted"><small>No results found</small></div>';
                            dropdown.classList.add('show');
                        }
                    });
                }
            }, 300);
        });

        input.addEventListener('blur', () => {
            setTimeout(() => dropdown.classList.remove('show'), 200);
        });
    },

    // ========================
    // Dropdowns
    // ========================
    initDropdowns() {
        document.addEventListener('click', () => {
            document.querySelectorAll('.custom-dropdown.show').forEach(d => d.classList.remove('show'));
        });
    },

    // ========================
    // Form Validation
    // ========================
    initFormValidation() {
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                let valid = true;
                form.querySelectorAll('[required]').forEach(input => {
                    if (!input.value.trim()) {
                        valid = false;
                        input.classList.add('is-invalid');
                        if (!input.nextElementSibling?.classList.contains('invalid-feedback')) {
                            const feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            feedback.textContent = 'This field is required.';
                            input.parentNode.appendChild(feedback);
                        }
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    this.showToast('Please fill in all required fields.', 'warning');
                }
            });

            // Clear validation on input
            form.querySelectorAll('.form-control, .form-select').forEach(input => {
                input.addEventListener('input', () => {
                    input.classList.remove('is-invalid');
                });
            });
        });
    },

    // ========================
    // File Inputs
    // ========================
    initFileInputs() {
        document.querySelectorAll('.custom-file-input').forEach(input => {
            input.addEventListener('change', function() {
                const fileName = this.files[0]?.name || 'Choose file';
                const label = this.closest('.custom-file')?.querySelector('.custom-file-label');
                if (label) label.textContent = fileName;
            });
        });
    },

    // ========================
    // Delete Confirmations
    // ========================
    initDeleteConfirmations() {
        document.querySelectorAll('[data-confirm]').forEach(el => {
            el.addEventListener('click', (e) => {
                const message = el.getAttribute('data-confirm') || 'Are you sure?';
                if (typeof Swal !== 'undefined') {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Confirm Action',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#6366f1',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Yes, proceed'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = el.getAttribute('href') || el.getAttribute('data-href');
                        }
                    });
                } else {
                    if (!confirm(message)) {
                        e.preventDefault();
                    }
                }
            });
        });
    },

    // ========================
    // Tooltips
    // ========================
    initToolTips() {
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
        }
    },

    // ========================
    // Utilities
    // ========================
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    showLoading() {
        let overlay = document.querySelector('.spinner-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'spinner-overlay';
            overlay.innerHTML = '<div class="spinner"></div>';
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';
    },

    hideLoading() {
        const overlay = document.querySelector('.spinner-overlay');
        if (overlay) overlay.style.display = 'none';
    },

    formatNumber(num) {
        return new Intl.NumberFormat('en-IN').format(num);
    },

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
};

// Guard: if DOM is already parsed when this script loads (scripts at end of body),
// DOMContentLoaded may have already fired – call init() directly in that case.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => TPMS.init());
} else {
    TPMS.init();
}
