// App State Manager & Router
document.addEventListener("DOMContentLoaded", () => {
    // Check local storage for dark mode
    if (localStorage.getItem("theme") === "dark" || 
        (!("theme" in localStorage) && window.matchMedia("(prefers-color-scheme: dark)").matches)) {
        document.documentElement.classList.add("dark");
        mockData.currentStudent.theme = "dark";
    } else {
        document.documentElement.classList.remove("dark");
        mockData.currentStudent.theme = "light";
    }

    // Initialize application routing and elements
    app.init();
});

const app = {
    // Navigation routes mapping
    views: {
        guest: ['landing', 'login', 'register-student', 'register-company', 'forgot-password'],
        student: ['dashboard', 'jobs', 'training', 'higher-studies', 'settings'],
        company: ['dashboard', 'candidates', 'settings'],
        admin: ['dashboard', 'students', 'companies', 'reports', 'settings']
    },

    init() {
        this.bindEvents();
        this.navigate("landing");
        this.updateThemeUI();
    },

    bindEvents() {
        // Toggle Dark Mode
        document.getElementById("theme-toggle")?.addEventListener("click", () => this.toggleTheme());
        document.getElementById("theme-toggle-sidebar")?.addEventListener("click", () => this.toggleTheme());

        // Mobile Hamburger Menu
        document.getElementById("hamburger-btn")?.addEventListener("click", () => {
            const sidebar = document.getElementById("sidebar");
            sidebar?.classList.toggle("-translate-x-full");
        });

        // Close sidebar when clicking outside (on mobile overlay)
        document.getElementById("sidebar-overlay")?.addEventListener("click", () => {
            const sidebar = document.getElementById("sidebar");
            sidebar?.classList.add("-translate-x-full");
            document.getElementById("sidebar-overlay")?.classList.add("hidden");
        });

        // Notifications Center Toggle
        const notifyBtn = document.getElementById("notifications-btn");
        const notifyDropdown = document.getElementById("notifications-dropdown");
        
        notifyBtn?.addEventListener("click", (e) => {
            e.stopPropagation();
            notifyDropdown?.classList.toggle("hidden");
        });

        document.addEventListener("click", () => {
            notifyDropdown?.classList.add("hidden");
        });

        // Event delegation for general action links
        document.addEventListener("click", (e) => {
            const target = e.target.closest("[data-view]");
            if (target) {
                e.preventDefault();
                const view = target.getAttribute("data-view");
                this.navigate(view);
            }
        });
    },

    navigate(view) {
        const state = mockData.session;
        
        // Safety checks for role permissions
        const allowedViews = this.views[state.role];
        if (state.role !== "guest" && !allowedViews.includes(view) && view !== "landing") {
            view = "dashboard";
        } else if (state.role === "guest" && !this.views.guest.includes(view)) {
            view = "landing";
        }

        // Close sidebar on mobile navigation
        const sidebar = document.getElementById("sidebar");
        sidebar?.classList.add("-translate-x-full");
        document.getElementById("sidebar-overlay")?.classList.add("hidden");

        // Swap Content
        this.renderLayout(state.role, view);
        
        // Dispatch custom view rendering
        this.renderViewContent(state.role, view);

        // Re-run Lucide icons translation
        if (window.lucide) {
            window.lucide.createIcons();
        }

        // Scroll to top
        window.scrollTo(0, 0);
    },

    renderLayout(role, view) {
        const navbar = document.getElementById("top-navbar");
        const sidebar = document.getElementById("sidebar");
        const bodyContent = document.getElementById("main-content-wrapper");

        if (role === "guest" || view === "landing" || this.views.guest.includes(view)) {
            // Hide navigation, full width view
            navbar?.classList.add("hidden");
            sidebar?.classList.add("hidden");
            bodyContent?.classList.remove("lg:pl-64", "pt-16");
        } else {
            // Show navigation layouts
            navbar?.classList.remove("hidden");
            sidebar?.classList.remove("hidden");
            bodyContent?.classList.add("lg:pl-64", "pt-16");
            
            this.updateNavbar(role);
            this.updateSidebar(role, view);
        }
    },

    updateNavbar(role) {
        const roleLabel = document.getElementById("user-role-label");
        const userAvatar = document.getElementById("user-avatar");
        const searchContainer = document.getElementById("navbar-search-container");

        if (roleLabel) {
            roleLabel.textContent = role.charAt(0).toUpperCase() + role.slice(1);
        }

        if (userAvatar) {
            if (role === "student") {
                userAvatar.src = mockData.currentStudent.avatar;
            } else if (role === "company") {
                userAvatar.src = "https://images.unsplash.com/photo-1549737481-c3b85d9c2a3d?auto=format&fit=crop&q=80&w=80"; // Tech Logo
            } else {
                userAvatar.src = "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"; // Admin Avatar
            }
        }

        // Context-aware search
        if (searchContainer) {
            searchContainer.className = (role === "student" || role === "admin") ? "relative max-w-xs w-full hidden md:block" : "hidden";
        }
    },

    updateSidebar(role, activeView) {
        const sidebarLinks = document.getElementById("sidebar-links");
        if (!sidebarLinks) return;

        let linksHTML = "";

        if (role === "admin") {
            linksHTML = `
                <a href="#" data-view="dashboard" class="sidebar-link ${activeView === 'dashboard' ? 'active' : ''}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
                </a>
                <a href="#" data-view="students" class="sidebar-link ${activeView === 'students' ? 'active' : ''}">
                    <i data-lucide="users" class="w-5 h-5"></i> Students
                </a>
                <a href="#" data-view="companies" class="sidebar-link ${activeView === 'companies' ? 'active' : ''}">
                    <i data-lucide="building-2" class="w-5 h-5"></i> Companies
                </a>
                <a href="#" data-view="reports" class="sidebar-link ${activeView === 'reports' ? 'active' : ''}">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i> Reports & Analytics
                </a>
                <a href="#" data-view="settings" class="sidebar-link ${activeView === 'settings' ? 'active' : ''}">
                    <i data-lucide="settings" class="w-5 h-5"></i> Settings
                </a>
            `;
        } else if (role === "student") {
            linksHTML = `
                <a href="#" data-view="dashboard" class="sidebar-link ${activeView === 'dashboard' ? 'active' : ''}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
                </a>
                <a href="#" data-view="jobs" class="sidebar-link ${activeView === 'jobs' ? 'active' : ''}">
                    <i data-lucide="briefcase" class="w-5 h-5"></i> Available Jobs
                </a>
                <a href="#" data-view="training" class="sidebar-link ${activeView === 'training' ? 'active' : ''}">
                    <i data-lucide="graduation-cap" class="w-5 h-5"></i> Training Modules
                </a>
                <a href="#" data-view="higher-studies" class="sidebar-link ${activeView === 'higher-studies' ? 'active' : ''}">
                    <i data-lucide="globe" class="w-5 h-5"></i> Higher Studies
                </a>
                <a href="#" data-view="settings" class="sidebar-link ${activeView === 'settings' ? 'active' : ''}">
                    <i data-lucide="settings" class="w-5 h-5"></i> Profile & Settings
                </a>
            `;
        } else if (role === "company") {
            linksHTML = `
                <a href="#" data-view="dashboard" class="sidebar-link ${activeView === 'dashboard' ? 'active' : ''}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
                </a>
                <a href="#" data-view="candidates" class="sidebar-link ${activeView === 'candidates' ? 'active' : ''}">
                    <i data-lucide="users-round" class="w-5 h-5"></i> Eligible Candidates
                </a>
                <a href="#" data-view="settings" class="sidebar-link ${activeView === 'settings' ? 'active' : ''}">
                    <i data-lucide="settings" class="w-5 h-5"></i> Recruiter Settings
                </a>
            `;
        }

        // Add Logout at bottom
        linksHTML += `
            <div class="pt-6 mt-6 border-t border-slate-200 dark:border-slate-800">
                <a href="#" onclick="app.logout(event)" class="sidebar-link text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20">
                    <i data-lucide="log-out" class="w-5 h-5"></i> Logout
                </a>
            </div>
        `;

        sidebarLinks.innerHTML = linksHTML;
    },

    renderViewContent(role, view) {
        const container = document.getElementById("main-content");
        if (!container) return;

        // Clear charts and layouts
        container.innerHTML = "";

        // Add animated container classes
        container.className = "p-4 md:p-6 max-w-7xl mx-auto space-y-6 animate-fade-in";

        // View router selector
        if (role === "guest") {
            if (view === "landing") {
                renderLanding(container);
            } else {
                renderAuth(container, view);
            }
        } else {
            // Dashboard views routing
            if (view === "dashboard") {
                if (role === "admin") renderAdminDashboard(container);
                if (role === "student") renderStudentDashboard(container);
                if (role === "company") renderCompanyDashboard(container);
            } else if (view === "jobs") {
                renderJobsBoard(container);
            } else if (view === "training") {
                renderTrainingModule(container);
            } else if (view === "higher-studies") {
                renderHigherStudiesModule(container);
            } else if (view === "candidates") {
                renderCandidatesModule(container);
            } else if (view === "students") {
                renderStudentsManagement(container);
            } else if (view === "companies") {
                renderCompaniesManagement(container);
            } else if (view === "reports") {
                renderReportsDashboard(container);
            } else if (view === "settings") {
                renderSettingsPanel(container);
            }
        }
    },

    login(role) {
        mockData.session.role = role;
        mockData.session.userId = role === "student" ? mockData.currentStudent.id : "ADMIN001";
        this.showToast(`Logged in successfully as ${role.toUpperCase()}`, "success");
        this.navigate("dashboard");
    },

    logout(e) {
        if (e) e.preventDefault();
        mockData.session.role = "guest";
        mockData.session.userId = null;
        this.showToast("Logged out successfully", "info");
        this.navigate("landing");
    },

    toggleTheme() {
        const isDark = document.documentElement.classList.toggle("dark");
        localStorage.setItem("theme", isDark ? "dark" : "light");
        mockData.currentStudent.theme = isDark ? "dark" : "light";
        this.updateThemeUI();
        this.showToast(`Switched to ${isDark ? 'Dark' : 'Light'} Mode`, "info");
        
        // Re-render current view if dashboard to rebuild charts under new theme colors
        const state = mockData.session;
        if (state.role !== 'guest' && this.views[state.role].includes('dashboard')) {
            this.navigate(state.role === 'admin' ? 'dashboard' : 'dashboard');
        }
    },

    updateThemeUI() {
        const themeToggles = [document.getElementById("theme-toggle"), document.getElementById("theme-toggle-sidebar")];
        const isDark = document.documentElement.classList.contains("dark");
        
        themeToggles.forEach(btn => {
            if (btn) {
                btn.innerHTML = isDark 
                    ? `<i data-lucide="sun" class="w-5 h-5 text-amber-500"></i>`
                    : `<i data-lucide="moon" class="w-5 h-5 text-slate-700"></i>`;
            }
        });
        
        if (window.lucide) {
            window.lucide.createIcons();
        }
    },

    showToast(message, type = "info") {
        const toastContainer = document.getElementById("toast-container");
        if (!toastContainer) return;

        const colors = {
            success: "bg-emerald-500 text-white",
            warning: "bg-amber-500 text-white",
            danger: "bg-rose-500 text-white",
            info: "bg-blue-500 text-white"
        };

        const icons = {
            success: "check-circle",
            warning: "alert-triangle",
            danger: "x-circle",
            info: "info"
        };

        const toast = document.createElement("div");
        toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border-glass bg-glass text-sm font-medium transform translate-y-2 opacity-0 transition-all duration-300 ${colors[type] || colors.info}`;
        toast.style.backdropFilter = "blur(14px)";

        toast.innerHTML = `
            <i data-lucide="${icons[type] || 'info'}" class="w-5 h-5 shrink-0"></i>
            <span>${message}</span>
        `;

        toastContainer.appendChild(toast);
        if (window.lucide) window.lucide.createIcons();

        // Animate in
        setTimeout(() => {
            toast.classList.remove("translate-y-2", "opacity-0");
        }, 10);

        // Animate out and remove
        setTimeout(() => {
            toast.classList.add("translate-y-2", "opacity-0");
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
};

// Global exports
window.app = app;
