// App State Manager & Router — TPMS v2.0
document.addEventListener("DOMContentLoaded", () => {
    // Restore theme from localStorage
    if (localStorage.getItem("theme") === "dark" ||
        (!("theme" in localStorage) && window.matchMedia("(prefers-color-scheme: dark)").matches)) {
        document.documentElement.classList.add("dark");
        mockData.currentStudent.theme = "dark";
    } else {
        document.documentElement.classList.remove("dark");
        mockData.currentStudent.theme = "light";
    }

    app.init();
});

const app = {
    // Navigation routes per role
    views: {
        guest:   ['landing', 'login', 'register-student', 'register-company', 'forgot-password'],
        student: ['dashboard', 'jobs', 'training', 'higher-studies', 'settings'],
        company: ['dashboard', 'candidates', 'settings'],
        admin:   ['dashboard', 'students', 'companies', 'reports', 'settings']
    },

    init() {
        this.bindEvents();
        this.navigate("landing");
        this.updateThemeUI();
    },

    bindEvents() {
        // Theme toggles
        document.getElementById("theme-toggle")?.addEventListener("click", () => this.toggleTheme());
        document.getElementById("theme-toggle-sidebar")?.addEventListener("click", () => this.toggleTheme());

        // Mobile hamburger
        document.getElementById("hamburger-btn")?.addEventListener("click", () => this.openSidebar());
        document.getElementById("sidebar-close-btn")?.addEventListener("click", () => this.closeSidebar());

        // Sidebar overlay
        document.getElementById("sidebar-overlay")?.addEventListener("click", () => this.closeSidebar());

        // Notifications toggle
        const notifyBtn      = document.getElementById("notifications-btn");
        const notifyDropdown = document.getElementById("notifications-dropdown");
        notifyBtn?.addEventListener("click", (e) => {
            e.stopPropagation();
            notifyDropdown?.classList.toggle("hidden");
            // Close profile dropdown if open
            document.getElementById("profile-dropdown")?.classList.add("hidden");
        });

        // Profile dropdown toggle
        const profileBtn      = document.getElementById("profile-btn");
        const profileDropdown = document.getElementById("profile-dropdown");
        profileBtn?.addEventListener("click", (e) => {
            e.stopPropagation();
            profileDropdown?.classList.toggle("hidden");
            // Close notification dropdown if open
            notifyDropdown?.classList.add("hidden");
        });

        // Close dropdowns on outside click
        document.addEventListener("click", () => {
            notifyDropdown?.classList.add("hidden");
            profileDropdown?.classList.add("hidden");
        });

        // Global data-view router
        document.addEventListener("click", (e) => {
            const target = e.target.closest("[data-view]");
            if (target) {
                e.preventDefault();
                this.navigate(target.getAttribute("data-view"));
            }
        });
    },

    openSidebar() {
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("sidebar-overlay");
        sidebar?.classList.remove("-translate-x-full");
        overlay?.classList.remove("hidden");
    },

    closeSidebar() {
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("sidebar-overlay");
        sidebar?.classList.add("-translate-x-full");
        overlay?.classList.add("hidden");
    },

    navigate(view) {
        const state = mockData.session;

        // Role permission guards
        if (state.role === "guest" && !this.views.guest.includes(view)) {
            view = "landing";
        } else if (state.role !== "guest" && !this.views[state.role]?.includes(view) && view !== "landing") {
            view = "dashboard";
        }

        // Close mobile sidebar
        this.closeSidebar();

        // Render shell layout + view content
        this.renderLayout(state.role, view);
        this.renderViewContent(state.role, view);

        // Re-render Lucide icons
        if (window.lucide) window.lucide.createIcons();

        // Scroll to top
        window.scrollTo({ top: 0, behavior: "instant" });
    },

    renderLayout(role, view) {
        const navbar  = document.getElementById("top-navbar");
        const sidebar = document.getElementById("sidebar");
        const wrapper = document.getElementById("main-content-wrapper");

        const isGuest = role === "guest" || this.views.guest.includes(view);

        if (isGuest) {
            navbar?.classList.add("hidden");
            navbar && (navbar.style.display = "none");
            sidebar?.classList.add("hidden");
            wrapper?.classList.remove("lg:pl-[260px]", "pt-16");
        } else {
            navbar?.classList.remove("hidden");
            navbar && (navbar.style.display = "");
            sidebar?.classList.remove("hidden");
            wrapper?.classList.add("lg:pl-[260px]", "pt-16");

            this.updateNavbar(role);
            this.updateSidebar(role, view);
        }
    },

    updateNavbar(role) {
        const roleLabel     = document.getElementById("user-role-label");
        const userAvatar    = document.getElementById("user-avatar");
        const dropdownName  = document.getElementById("profile-dropdown-name");
        const searchContainer = document.getElementById("navbar-search-container");

        const roleNames = {
            student: "Student",
            company: "Recruiter",
            admin:   "TPO Admin"
        };

        if (roleLabel) roleLabel.textContent = roleNames[role] || role;

        if (dropdownName) {
            if (role === "student") dropdownName.textContent = mockData.currentStudent.name;
            else if (role === "company") dropdownName.textContent = "Google Recruiter";
            else dropdownName.textContent = "TPO Administrator";
        }

        if (userAvatar) {
            if (role === "student") {
                userAvatar.src = mockData.currentStudent.avatar;
            } else if (role === "company") {
                userAvatar.src = "https://images.unsplash.com/photo-1549737481-c3b85d9c2a3d?auto=format&fit=crop&q=80&w=80";
            } else {
                userAvatar.src = "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=80&h=80&q=80";
            }
        }

        if (searchContainer) {
            searchContainer.className = (role === "student" || role === "admin")
                ? "relative max-w-sm w-full hidden md:block"
                : "hidden";
        }
    },

    updateSidebar(role, activeView) {
        const sidebarLinks = document.getElementById("sidebar-links");
        if (!sidebarLinks) return;

        const link = (view, icon, label) => `
            <a href="#" data-view="${view}" class="sidebar-link ${activeView === view ? 'active' : ''}">
                <i data-lucide="${icon}" class="w-4.5 h-4.5 flex-shrink-0"></i>
                <span>${label}</span>
            </a>`;

        let html = "";

        if (role === "admin") {
            html = `
                <p class="sidebar-section-label mt-1 mb-2">Main</p>
                ${link("dashboard", "layout-dashboard", "Dashboard")}
                ${link("students",  "users",            "Students")}
                ${link("companies", "building-2",       "Companies")}
                <p class="sidebar-section-label mt-4 mb-2">Analytics</p>
                ${link("reports",  "bar-chart-3", "Reports & Analytics")}
                <p class="sidebar-section-label mt-4 mb-2">Account</p>
                ${link("settings", "settings-2", "Settings")}
            `;
        } else if (role === "student") {
            html = `
                <p class="sidebar-section-label mt-1 mb-2">Overview</p>
                ${link("dashboard",      "layout-dashboard", "Dashboard")}
                <p class="sidebar-section-label mt-4 mb-2">Placement</p>
                ${link("jobs",           "briefcase",        "Available Jobs")}
                ${link("training",       "graduation-cap",   "Training Modules")}
                ${link("higher-studies", "globe",            "Higher Studies")}
                <p class="sidebar-section-label mt-4 mb-2">Account</p>
                ${link("settings",       "settings-2",       "Profile & Settings")}
            `;
        } else if (role === "company") {
            html = `
                <p class="sidebar-section-label mt-1 mb-2">Workspace</p>
                ${link("dashboard",  "layout-dashboard", "Dashboard")}
                ${link("candidates", "users-round",      "Eligible Candidates")}
                <p class="sidebar-section-label mt-4 mb-2">Account</p>
                ${link("settings",   "settings-2",       "Recruiter Settings")}
            `;
        }

        // Logout at bottom
        html += `
            <div class="pt-4 mt-4 border-t border-[var(--border-color)]">
                <a href="#" onclick="app.logout(event)"
                   class="sidebar-link text-[var(--danger)] hover:bg-[var(--danger-light)] hover:text-[var(--danger)]">
                    <i data-lucide="log-out" class="w-4.5 h-4.5 flex-shrink-0"></i>
                    <span>Sign Out</span>
                </a>
            </div>`;

        sidebarLinks.innerHTML = html;
    },

    renderViewContent(role, view) {
        const container = document.getElementById("main-content");
        if (!container) return;

        container.innerHTML = "";

        if (role === "guest" || this.views.guest.includes(view)) {
            container.className = "animate-fade-in";
            if (view === "landing") {
                renderLanding(container);
            } else {
                renderAuth(container, view);
            }
        } else {
            container.className = "p-4 md:p-6 max-w-7xl mx-auto space-y-5 animate-fade-in";

            if (view === "dashboard") {
                if (role === "admin")   renderAdminDashboard(container);
                if (role === "student") renderStudentDashboard(container);
                if (role === "company") renderCompanyDashboard(container);
            } else if (view === "jobs")           renderJobsBoard(container);
            else if (view === "training")          renderTrainingModule(container);
            else if (view === "higher-studies")    renderHigherStudiesModule(container);
            else if (view === "candidates")        renderCandidatesModule(container);
            else if (view === "students")          renderStudentsManagement(container);
            else if (view === "companies")         renderCompaniesManagement(container);
            else if (view === "reports")           renderReportsDashboard(container);
            else if (view === "settings")          renderSettingsPanel(container);
        }
    },

    login(role) {
        mockData.session.role   = role;
        mockData.session.userId = role === "student" ? mockData.currentStudent.id : "ADMIN001";
        this.showToast(`Welcome back! Logged in as ${role.charAt(0).toUpperCase() + role.slice(1)}.`, "success");
        this.navigate("dashboard");
    },

    logout(e) {
        if (e) e.preventDefault();
        mockData.session.role   = "guest";
        mockData.session.userId = null;
        this.showToast("You have been signed out successfully.", "info");
        this.navigate("landing");
    },

    toggleTheme() {
        const isDark = document.documentElement.classList.toggle("dark");
        localStorage.setItem("theme", isDark ? "dark" : "light");
        mockData.currentStudent.theme = isDark ? "dark" : "light";
        this.updateThemeUI();
        this.showToast(`Switched to ${isDark ? "Dark" : "Light"} Mode`, "info");

        // Re-render current view for chart theme updates
        const state = mockData.session;
        if (state.role !== "guest") {
            const currentView = state.role === "admin" ? "dashboard" : "dashboard";
            this.navigate(currentView);
        }
    },

    updateThemeUI() {
        const isDark = document.documentElement.classList.contains("dark");
        const toggles = [
            document.getElementById("theme-toggle"),
            document.getElementById("theme-toggle-sidebar")
        ];

        toggles.forEach(btn => {
            if (!btn) return;
            if (btn.id === "theme-toggle") {
                btn.innerHTML = isDark
                    ? `<i data-lucide="sun" class="w-4 h-4 text-amber-400"></i>`
                    : `<i data-lucide="moon" class="w-4 h-4"></i>`;
            } else {
                btn.innerHTML = `
                    <i data-lucide="${isDark ? 'sun' : 'moon'}" class="w-4 h-4 ${isDark ? 'text-amber-400' : ''}"></i>
                    <span>Switch to ${isDark ? 'Light' : 'Dark'} Mode</span>`;
            }
        });

        if (window.lucide) window.lucide.createIcons();
    },

    showToast(message, type = "info") {
        const container = document.getElementById("toast-container");
        if (!container) return;

        const typeMap = {
            success: { cls: "toast-success", icon: "check-circle-2" },
            warning: { cls: "toast-warning", icon: "alert-triangle"  },
            danger:  { cls: "toast-danger",  icon: "x-circle"        },
            info:    { cls: "toast-info",     icon: "info"            }
        };

        const { cls, icon } = typeMap[type] || typeMap.info;

        const toast = document.createElement("div");
        toast.className = `toast-item ${cls} opacity-0 translate-x-4 pointer-events-all transition-all duration-300`;
        toast.innerHTML = `
            <i data-lucide="${icon}" class="w-4.5 h-4.5 flex-shrink-0"></i>
            <span class="flex-1 text-xs leading-relaxed">${message}</span>
            <button onclick="this.parentElement.remove()" class="opacity-60 hover:opacity-100 transition flex-shrink-0 ml-1">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>`;

        container.appendChild(toast);
        if (window.lucide) window.lucide.createIcons();

        // Animate in
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                toast.classList.remove("opacity-0", "translate-x-4");
            });
        });

        // Auto-dismiss
        setTimeout(() => {
            toast.classList.add("opacity-0", "translate-x-4");
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
};

window.app = app;
