/**
 * TPMS - Main Application JavaScript
 */

const TPMS = {
    baseUrl: document.querySelector('meta[name="base-url"]')?.content || '/Internship%20Project/New-TNP-Project',
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',

    init() {
        this.initTheme();
        this.initSidebar();
        this.initToasts();
        this.initAjaxDefaults();
        this.initNotifications();
        this.initNotificationSearch();
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
    },

    fetchNotifications() {
        if (typeof $ === 'undefined') return;

        $.get(this.baseUrl + '/notifications/fetch', (response) => {
            if (response.success) {
                this.updateNotificationBadge(response.count);
                this.updateNotificationDropdown(response.notifications);
            }
        }).fail(() => {});

        $.get(this.baseUrl + '/messages/unread-count', (response) => {
            if (response && response.success) {
                const count = parseInt(response.unread_count || 0);
                document.querySelectorAll('.chat-unread-badge').forEach(b => {
                    b.textContent = count > 99 ? '99+' : count;
                    b.style.display = count > 0 ? 'inline-block' : 'none';
                });
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
            <a href="${this.baseUrl}/notifications/read-redirect/${n.id}" class="notification-item ${n.is_read ? '' : 'unread'}">
                <div class="n-icon bg-${this.getNotificationColor(n.type)}-soft">
                    <i class="fas fa-${this.getNotificationIcon(n.type)} text-${this.getNotificationColor(n.type)}"></i>
                </div>
                <div class="n-content">
                    <div class="n-title">${this.escapeHtml(n.title)}</div>
                    <div class="n-text">${this.escapeHtml(n.message)}</div>
                    <div class="n-time"><i class="far fa-clock me-1"></i>${n.time_ago || ''}</div>
                </div>
            </a>
        `).join('');
    },

    markNotificationRead(id, btnElem = null) {
        let originalHtml = '';
        if (btnElem) {
            originalHtml = btnElem.innerHTML;
            btnElem.disabled = true;
            btnElem.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>';
        }

        if (typeof $ !== 'undefined') {
            $.post(this.baseUrl + '/notifications/mark-read/' + id, { csrf_token: this.csrfToken }, (res) => {
                if (res && res.success) {
                    // Update card UI if present on page
                    const item = document.getElementById('notif-item-' + id);
                    if (item) {
                        item.classList.remove('bg-light', 'border-primary', 'border-start', 'border-3', 'unread');
                        item.classList.add('bg-white');
                        const unreadBadge = item.querySelector('.unread-pill');
                        if (unreadBadge) unreadBadge.remove();
                    }

                    if (btnElem) {
                        btnElem.className = 'btn btn-sm btn-light text-muted disabled border-0';
                        btnElem.innerHTML = '<i class="fas fa-check text-success me-1"></i> Read';
                    }

                    if (typeof res.count !== 'undefined') {
                        this.updateNotificationBadge(res.count);
                    } else {
                        this.fetchNotifications();
                    }

                    this.showToast(res.message || 'Notification marked as read', 'success');
                } else {
                    if (btnElem) {
                        btnElem.disabled = false;
                        btnElem.innerHTML = originalHtml;
                    }
                    this.showToast('Failed to update notification status', 'error');
                }
            }).fail(() => {
                if (btnElem) {
                    btnElem.disabled = false;
                    btnElem.innerHTML = originalHtml;
                }
                this.showToast('Server error marking notification as read', 'error');
            });
        }
    },

    markAllNotificationsRead(btnElem = null) {
        let originalHtml = '';
        if (btnElem) {
            originalHtml = btnElem.innerHTML;
            btnElem.disabled = true;
            btnElem.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
        }

        if (typeof $ !== 'undefined') {
            $.post(this.baseUrl + '/notifications/mark-all-read', { csrf_token: this.csrfToken }, (res) => {
                if (res && res.success) {
                    // Update all items on screen
                    document.querySelectorAll('.notif-card').forEach(item => {
                        item.classList.remove('bg-light', 'border-primary', 'border-start', 'border-3', 'unread');
                        item.classList.add('bg-white');
                        const badge = item.querySelector('.unread-pill');
                        if (badge) badge.remove();
                    });

                    document.querySelectorAll('.btn-mark-read').forEach(btn => {
                        btn.className = 'btn btn-sm btn-light text-muted disabled border-0';
                        btn.innerHTML = '<i class="fas fa-check text-success me-1"></i> Read';
                    });

                    this.updateNotificationBadge(0);
                    this.showToast('All notifications marked as read', 'success');

                    if (btnElem) {
                        btnElem.disabled = false;
                        btnElem.innerHTML = '<i class="fas fa-check-double me-1"></i> All Read';
                    }
                } else {
                    if (btnElem) {
                        btnElem.disabled = false;
                        btnElem.innerHTML = originalHtml;
                    }
                    this.showToast('Failed to mark notifications as read', 'error');
                }
            }).fail(() => {
                if (btnElem) {
                    btnElem.disabled = false;
                    btnElem.innerHTML = originalHtml;
                }
                this.showToast('Server error processing request', 'error');
            });
        }
    },

    initNotificationSearch() {
        const searchInput = document.getElementById('notifSearchInput');
        const categoryFilter = document.getElementById('notifCategoryFilter');
        const emptyState = document.getElementById('notifEmptyState');
        const searchEmptyState = document.getElementById('notifSearchEmptyState');

        if (!searchInput && !categoryFilter) return;

        const filterNotifications = () => {
            const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
            const selectedCat = (categoryFilter ? categoryFilter.value : 'all').toLowerCase();
            const cards = document.querySelectorAll('.notif-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const title = (card.getAttribute('data-title') || card.textContent || '').toLowerCase();
                const message = (card.getAttribute('data-message') || '').toLowerCase();
                const company = (card.getAttribute('data-company') || '').toLowerCase();
                const category = (card.getAttribute('data-category') || '').toLowerCase();
                const type = (card.getAttribute('data-type') || '').toLowerCase();

                const matchesQuery = !query || title.includes(query) || message.includes(query) || company.includes(query) || category.includes(query) || type.includes(query);
                const matchesCategory = selectedCat === 'all' || category === selectedCat || type === selectedCat;

                if (matchesQuery && matchesCategory) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (searchEmptyState) {
                if (visibleCount === 0 && cards.length > 0) {
                    searchEmptyState.style.display = 'block';
                    if (emptyState) emptyState.style.display = 'none';
                } else {
                    searchEmptyState.style.display = 'none';
                }
            }
        };

        if (searchInput) searchInput.addEventListener('input', filterNotifications);
        if (categoryFilter) categoryFilter.addEventListener('change', filterNotifications);

        filterNotifications();
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
        let selectedIndex = -1;

        if (!input || !dropdown) return;

        // Prevent input blur when clicking items inside dropdown
        dropdown.addEventListener('mousedown', (e) => {
            e.preventDefault();
        });

        // Direct click handler on dropdown items to guarantee instant navigation
        dropdown.addEventListener('click', (e) => {
            const item = e.target.closest('.search-result-item');
            if (item) {
                const targetUrl = item.getAttribute('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            }
        });

        input.addEventListener('input', (e) => {
            clearTimeout(timeout);
            const query = e.target.value.trim();
            selectedIndex = -1;
            
            if (query.length < 2) {
                dropdown.classList.remove('show');
                return;
            }

            timeout = setTimeout(() => {
                if (typeof $ !== 'undefined') {
                    $.get(this.baseUrl + '/search/global', { q: query }, (response) => {
                        if (response.success && response.results.length > 0) {
                            dropdown.innerHTML = response.results.map((r, idx) => `
                                <a href="${r.url}" class="search-result-item" data-index="${idx}">
                                    <div class="search-result-icon bg-${r.color || 'primary'}-soft me-2">
                                        <i class="${r.icon || 'fas fa-search'} text-${r.color || 'primary'}"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="search-result-title text-truncate" style="font-weight:600;font-size:0.85rem">${this.escapeHtml(r.title)}</div>
                                        <div class="search-result-subtitle text-truncate" style="font-size:0.75rem;color:var(--text-muted)">${this.escapeHtml(r.subtitle)}</div>
                                    </div>
                                    <span class="badge bg-light text-dark border ms-2" style="font-size:0.68rem;">${this.escapeHtml(r.type || 'Item')}</span>
                                </a>
                            `).join('');
                            dropdown.classList.add('show');
                        } else {
                            dropdown.innerHTML = `
                                <div class="p-4 text-center text-muted">
                                    <i class="fas fa-search-minus mb-2 d-block fs-3 opacity-50"></i>
                                    <small class="fw-semibold">No results found for "${this.escapeHtml(query)}"</small>
                                </div>`;
                            dropdown.classList.add('show');
                        }
                    });
                }
            }, 250);
        });

        // Keyboard navigation: ArrowUp, ArrowDown, Enter, Escape
        input.addEventListener('keydown', (e) => {
            const items = dropdown.querySelectorAll('.search-result-item');
            if (!dropdown.classList.contains('show') || items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = (selectedIndex + 1) % items.length;
                this.highlightSearchItem(items, selectedIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = (selectedIndex - 1 + items.length) % items.length;
                this.highlightSearchItem(items, selectedIndex);
            } else if (e.key === 'Enter') {
                if (selectedIndex >= 0 && items[selectedIndex]) {
                    e.preventDefault();
                    items[selectedIndex].click();
                }
            } else if (e.key === 'Escape') {
                dropdown.classList.remove('show');
            }
        });

        input.addEventListener('blur', () => {
            setTimeout(() => dropdown.classList.remove('show'), 200);
        });
    },

    highlightSearchItem(items, index) {
        items.forEach((item, i) => {
            if (i === index) {
                item.classList.add('active');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('active');
            }
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

// Global toggle for Save to Playlist / Bookmarks
window.toggleSaveJob = window.toggleBookmark = function(jobId, btnElement) {
    if (!jobId) return;

    const btn = btnElement || (event ? event.currentTarget : null);
    const icon = btn ? btn.querySelector('i') : null;
    const textSpan = btn ? btn.querySelector('.save-btn-text') : null;
    const isCurrentlySaved = icon ? (icon.classList.contains('fas') && !icon.classList.contains('fa-spinner')) : false;

    // Show loading state on button
    if (btn) btn.disabled = true;
    if (icon) {
        icon.className = 'fas fa-spinner fa-spin me-1';
    }

    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/team1';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const url = isCurrentlySaved 
        ? baseUrl + '/api/saved-jobs?job_id=' + jobId 
        : baseUrl + '/api/saved-jobs';
    const method = isCurrentlySaved ? 'DELETE' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: isCurrentlySaved ? null : ('job_id=' + jobId + '&csrf_token=' + encodeURIComponent(csrfToken))
    })
    .then(r => r.json())
    .then(data => {
        if (btn) btn.disabled = false;
        
        if (data.success) {
            const nowSaved = data.saved;
            if (icon) {
                if (nowSaved) {
                    icon.className = 'fas fa-bookmark text-primary me-1';
                } else {
                    icon.className = 'far fa-bookmark me-1';
                }
            }

            if (textSpan) {
                textSpan.innerText = nowSaved ? 'Saved' : 'Save to Playlist';
            }

            if (btn) {
                if (nowSaved) {
                    btn.classList.add('saved-active');
                    btn.title = 'Saved to Playlist';
                } else {
                    btn.classList.remove('saved-active');
                    btn.title = 'Save to Playlist';
                }
            }

            TPMS.toast(nowSaved ? 'success' : 'info', data.message || (nowSaved ? 'Saved to playlist!' : 'Removed from playlist'));
        } else {
            if (icon) {
                icon.className = isCurrentlySaved ? 'fas fa-bookmark text-primary me-1' : 'far fa-bookmark me-1';
            }
            TPMS.toast('danger', data.error || data.message || 'Failed to update playlist');
        }
    })
    .catch(err => {
        if (btn) btn.disabled = false;
        if (icon) {
            icon.className = isCurrentlySaved ? 'fas fa-bookmark text-primary me-1' : 'far fa-bookmark me-1';
        }
        TPMS.toast('danger', 'Network error. Please try again.');
    });
};



// Guard: if DOM is already parsed when this script loads (scripts at end of body),
// DOMContentLoaded may have already fired – call init() directly in that case.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => TPMS.init());
} else {
    TPMS.init();
}
