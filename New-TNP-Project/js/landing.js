// Landing Page Module — TPMS v2.0 Premium
function renderLanding(container) {
    container.className = "min-h-screen animate-fade-in relative overflow-hidden";

    container.innerHTML = `
        <!-- Ambient Background Shapes -->
        <div class="glass-shape glass-shape-1" style="opacity:0.10;"></div>
        <div class="glass-shape glass-shape-2" style="opacity:0.08;"></div>
        <div class="glass-shape glass-shape-3" style="opacity:0.07;"></div>

        <!-- =========================================
             STICKY HEADER
             ========================================= -->
        <header class="w-full bg-glass border-glass border-b sticky top-0 z-40 px-6 py-3.5">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl gradient-bg flex items-center justify-center text-white font-black text-base shadow-[var(--shadow-brand)]">T</div>
                    <div>
                        <span class="font-bold text-base tracking-tight text-[var(--text-primary)]">TPMS Portal</span>
                        <span class="hidden sm:block text-[10px] text-[var(--text-faint)] -mt-0.5">Enterprise Platform</span>
                    </div>
                </div>

                <!-- Nav Actions -->
                <div class="flex items-center gap-3">
                    <button id="theme-toggle-landing" class="navbar-btn" aria-label="Toggle theme">
                        <i data-lucide="moon" class="w-4 h-4"></i>
                    </button>
                    <a href="#" data-view="login"
                       class="btn btn-primary btn-sm shadow-[var(--shadow-brand)]">
                        <i data-lucide="log-in" class="w-3.5 h-3.5"></i> Sign In
                    </a>
                </div>
            </div>
        </header>

        <!-- =========================================
             HERO SECTION
             ========================================= -->
        <section class="max-w-7xl mx-auto px-6 pt-20 pb-24 grid grid-cols-1 lg:grid-cols-12 gap-14 items-center">

            <!-- Hero Text -->
            <div class="lg:col-span-6 space-y-8 animate-slide-up">
                <div class="hero-badge">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    Redefining Campus Placements
                </div>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-[var(--text-primary)] leading-none tracking-tight">
                    Training &amp; Placement <br/>
                    <span class="gradient-text">Management System</span>
                </h1>

                <p class="text-lg text-[var(--text-secondary)] max-w-lg leading-relaxed font-light">
                    Manage student placements, internships, training programs, and higher studies with an intelligent, all-in-one enterprise platform.
                </p>

                <!-- Quick Login CTAs -->
                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="#" data-view="login"
                       class="btn btn-primary btn-lg shadow-[var(--shadow-brand-lg)]">
                        Get Started <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <button onclick="app.login('student')"
                            class="btn btn-secondary btn-lg">
                        <i data-lucide="user-circle" class="w-4 h-4 text-[var(--primary)]"></i> Student Login
                    </button>
                    <button onclick="app.login('admin')"
                            class="btn btn-secondary btn-lg">
                        <i data-lucide="shield" class="w-4 h-4 text-[var(--secondary)]"></i> Admin Login
                    </button>
                    <button onclick="app.login('company')"
                            class="btn btn-secondary btn-lg">
                        <i data-lucide="building-2" class="w-4 h-4 text-[var(--accent)]"></i> Company Login
                    </button>
                </div>

                <!-- Trust indicators -->
                <div class="flex items-center gap-5 pt-2">
                    <div class="flex -space-x-2">
                        ${["1539571696357-5a69c17a67c6","1507003211169-0a1dd7228f2d","1438761681033-6461ffad8d80","1472099645785-5658abf4ff4e"].map(id =>
                            `<img src="https://images.unsplash.com/photo-${id}?auto=format&fit=facearea&facepad=2&w=40&h=40&q=80"
                                  class="w-8 h-8 rounded-full border-2 border-white dark:border-[var(--bg-main)] object-cover" />`
                        ).join("")}
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-[var(--text-primary)]">Trusted by 5,000+ students</p>
                        <div class="flex items-center gap-1 mt-0.5">
                            ${[1,2,3,4,5].map(() => `<i data-lucide="star" class="w-3 h-3 text-amber-400" style="fill:currentColor;"></i>`).join("")}
                            <span class="text-[11px] text-[var(--text-faint)] ml-1">4.9/5 rating</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hero Illustration Card -->
            <div class="lg:col-span-6 relative flex justify-center animate-scale-in" style="animation-delay:0.1s;">
                <div class="relative w-full max-w-lg">
                    <!-- Main Dashboard Preview Card -->
                    <div class="bg-glass border-glass rounded-3xl p-6 shadow-[var(--shadow-xl)] backdrop-blur-xl relative overflow-hidden">
                        <!-- Top accent line -->
                        <div class="absolute top-0 left-0 right-0 h-1 gradient-bg"></div>

                        <div class="flex items-center justify-between mb-5 pb-4 border-b border-[var(--border-color)]">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg gradient-bg flex items-center justify-center text-white text-xs font-bold">T</div>
                                <div>
                                    <p class="text-xs font-bold text-[var(--text-primary)]">Placement Overview</p>
                                    <p class="text-[10px] text-[var(--text-faint)]">Academic Year 2025–26</p>
                                </div>
                            </div>
                            <span class="badge badge-success text-[10px]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[var(--success)] animate-pulse-slow"></span>
                                Live
                            </span>
                        </div>

                        <div class="space-y-4">
                            ${[
                                { label: "Placement Rate", value: "95%", pct: 95, color: "var(--primary)" },
                                { label: "Offers Released", value: "342 / 420", pct: 81, color: "var(--secondary)" },
                                { label: "Avg. Package", value: "₹14.2 LPA", pct: 70, color: "var(--accent)" }
                            ].map(s => `
                                <div>
                                    <div class="flex justify-between text-xs mb-1.5">
                                        <span class="text-[var(--text-secondary)] font-medium">${s.label}</span>
                                        <span class="font-bold text-[var(--text-primary)]">${s.value}</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width:${s.pct}%; background: linear-gradient(90deg, ${s.color}, ${s.color}cc);"></div>
                                    </div>
                                </div>
                            `).join("")}
                        </div>

                        <!-- Bottom KPI row -->
                        <div class="grid grid-cols-3 gap-3 mt-5 pt-4 border-t border-[var(--border-color)]">
                            ${[
                                { n: "5,420", l: "Students", icon: "users", c: "var(--primary)" },
                                { n: "182",   l: "Partners", icon: "building-2", c: "var(--secondary)" },
                                { n: "48",    l: "Jobs Open", icon: "briefcase", c: "var(--accent)" }
                            ].map(k => `
                                <div class="text-center">
                                    <div class="w-8 h-8 rounded-xl mx-auto mb-1.5 flex items-center justify-center" style="background:${k.c}18;">
                                        <i data-lucide="${k.icon}" class="w-3.5 h-3.5" style="color:${k.c};"></i>
                                    </div>
                                    <p class="text-sm font-extrabold text-[var(--text-primary)]">${k.n}</p>
                                    <p class="text-[10px] text-[var(--text-faint)]">${k.l}</p>
                                </div>
                            `).join("")}
                        </div>
                    </div>

                    <!-- Floating badge: Highest Package -->
                    <div class="absolute -top-5 -left-5 bg-[var(--bg-card)] border border-[var(--border-color)] rounded-2xl p-3 shadow-[var(--shadow-lg)] flex items-center gap-3 animate-float" style="animation-delay:0s;">
                        <div class="w-9 h-9 rounded-xl bg-[var(--success-light)] flex items-center justify-center">
                            <i data-lucide="trending-up" class="w-4.5 h-4.5 text-[var(--success)]"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-[var(--text-faint)] font-medium">Highest Package</p>
                            <p class="text-sm font-extrabold text-[var(--text-primary)]">₹48.2 LPA</p>
                        </div>
                    </div>

                    <!-- Floating badge: Companies -->
                    <div class="absolute -bottom-5 -right-5 bg-[var(--bg-card)] border border-[var(--border-color)] rounded-2xl p-3 shadow-[var(--shadow-lg)] flex items-center gap-3 animate-float-slow" style="animation-delay:1.5s;">
                        <div class="w-9 h-9 rounded-xl bg-[var(--primary-light)] flex items-center justify-center">
                            <i data-lucide="building-2" class="w-4.5 h-4.5 text-[var(--primary)]"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-[var(--text-faint)] font-medium">Recruiting Partners</p>
                            <p class="text-sm font-extrabold text-[var(--text-primary)]">200+ Active</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =========================================
             STATS TICKER SECTION
             ========================================= -->
        <section class="border-y border-[var(--border-color)] bg-[var(--bg-card)] py-10">
            <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8 text-center stagger-children">
                ${[
                    { val: "5,000+", label: "Students Enrolled",     color: "var(--primary)"  },
                    { val: "200+",   label: "Corporate Partners",     color: "var(--secondary)"},
                    { val: "95%",    label: "Placement Success Rate", color: "var(--accent)"   },
                    { val: "₹14.2L", label: "Average Package",       color: "var(--success)"  }
                ].map(s => `
                    <div class="space-y-2">
                        <p class="stat-counter" style="color:${s.color};">${s.val}</p>
                        <p class="text-sm text-[var(--text-muted)] font-medium">${s.label}</p>
                    </div>
                `).join("")}
            </div>
        </section>

        <!-- =========================================
             FEATURES SECTION
             ========================================= -->
        <section class="max-w-7xl mx-auto px-6 py-24">
            <div class="text-center max-w-2xl mx-auto mb-16 space-y-4">
                <span class="hero-badge">Platform Features</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-[var(--text-primary)]">Smart Recruitment Workflows</h2>
                <p class="text-[var(--text-secondary)] text-lg leading-relaxed">Advanced enterprise-grade tooling built to streamline campus placements for colleges, students, and corporate partners.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 stagger-children">
                ${[
                    {
                        icon: "file-text", color: "var(--primary)", bg: "var(--primary-light)",
                        title: "Resume & Profile Manager",
                        desc: "Students build interactive academic profiles, upload verified grade reports, and attach one-click resumes ready for corporate evaluation."
                    },
                    {
                        icon: "sliders", color: "var(--secondary)", bg: "rgba(124,58,237,0.10)",
                        title: "Realtime Job Matchmaking",
                        desc: "Recruiters publish jobs and filter candidates instantly based on CGPA, branch, skills, and backlog thresholds with zero friction."
                    },
                    {
                        icon: "check-square-2", color: "var(--accent)", bg: "rgba(6,182,212,0.10)",
                        title: "Application Pipeline Tracker",
                        desc: "Follow recruitment rounds from application reviews to shortlists, technical interviews, and final job selection offers in real time."
                    },
                    {
                        icon: "graduation-cap", color: "var(--success)", bg: "var(--success-light)",
                        title: "Training Module Manager",
                        desc: "TPO office publishes skill training programs, bootcamps, and aptitude sessions. Students register and track their progress seamlessly."
                    },
                    {
                        icon: "globe", color: "var(--warning)", bg: "var(--warning-light)",
                        title: "Higher Studies Navigator",
                        desc: "Explore world-class universities with QS rankings, scholarship details, application deadlines, and personalized eligibility checks."
                    },
                    {
                        icon: "bar-chart-3", color: "var(--danger)", bg: "var(--danger-light)",
                        title: "Analytics & Reports",
                        desc: "Comprehensive placement analytics with department-wise breakdowns, package distributions, and year-on-year trend comparisons."
                    }
                ].map(f => `
                    <div class="feature-card">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:${f.bg};">
                            <i data-lucide="${f.icon}" class="w-5.5 h-5.5" style="color:${f.color};"></i>
                        </div>
                        <div class="space-y-2">
                            <h3 class="font-bold text-[var(--text-primary)]">${f.title}</h3>
                            <p class="text-sm text-[var(--text-secondary)] leading-relaxed">${f.desc}</p>
                        </div>
                    </div>
                `).join("")}
            </div>
        </section>

        <!-- =========================================
             FOOTER
             ========================================= -->
        <footer class="border-t border-[var(--border-color)] bg-[var(--bg-card)] py-14 px-6">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-10">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg gradient-bg flex items-center justify-center text-white font-bold text-sm">T</div>
                        <span class="font-bold text-base text-[var(--text-primary)]">TPMS Portal</span>
                    </div>
                    <p class="text-xs text-[var(--text-faint)] leading-relaxed max-w-48">
                        Comprehensive platform coordinating higher educational placements, skill training, and university pathways.
                    </p>
                    <div class="flex items-center gap-2">
                        ${[
                            { icon: "twitter",  label: "Twitter"  },
                            { icon: "linkedin", label: "LinkedIn" },
                            { icon: "github",   label: "GitHub"   }
                        ].map(s => `
                            <a href="#" aria-label="${s.label}"
                               class="w-8 h-8 rounded-lg border border-[var(--border-color)] bg-[var(--bg-subtle)] flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--primary)] hover:border-[rgba(79,70,229,0.30)] transition">
                                <i data-lucide="${s.icon}" class="w-3.5 h-3.5"></i>
                            </a>
                        `).join("")}
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-xs text-[var(--text-primary)] uppercase tracking-wider mb-4">Portals</h4>
                    <ul class="space-y-2.5">
                        ${[
                            { label: "Student Dashboard", action: "app.login('student')" },
                            { label: "Company Recruiter", action: "app.login('company')" },
                            { label: "Office Administrator", action: "app.login('admin')" }
                        ].map(l => `
                            <li><a href="#" onclick="${l.action}" class="text-xs text-[var(--text-faint)] hover:text-[var(--primary)] transition">${l.label}</a></li>
                        `).join("")}
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-xs text-[var(--text-primary)] uppercase tracking-wider mb-4">Resources</h4>
                    <ul class="space-y-2.5">
                        ${["Documentation", "API Guide", "Privacy Policy", "Terms of Service"].map(l =>
                            `<li><a href="#" class="text-xs text-[var(--text-faint)] hover:text-[var(--primary)] transition">${l}</a></li>`
                        ).join("")}
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-xs text-[var(--text-primary)] uppercase tracking-wider mb-4">Contact TPO</h4>
                    <div class="space-y-2.5 text-xs text-[var(--text-faint)] leading-relaxed">
                        <p>Block A, Placement Cell<br>State Engineering University</p>
                        <a href="mailto:placement-cell@university.edu" class="hover:text-[var(--primary)] transition block">
                            placement-cell@university.edu
                        </a>
                        <p>+91 80 4455 6677</p>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto border-t border-[var(--border-color)] mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-[var(--text-faint)]">
                <p>&copy; 2026 Training &amp; Placement Management System. All rights reserved.</p>
                <p>Engineered for Excellence · Enterprise Edition</p>
            </div>
        </footer>
    `;

    // Wire up landing theme toggle
    const toggleBtn = document.getElementById("theme-toggle-landing");
    const isDark = document.documentElement.classList.contains("dark");

    if (toggleBtn) {
        toggleBtn.innerHTML = isDark
            ? `<i data-lucide="sun" class="w-4 h-4 text-amber-400"></i>`
            : `<i data-lucide="moon" class="w-4 h-4"></i>`;

        toggleBtn.addEventListener("click", () => {
            app.toggleTheme();
            const nowDark = document.documentElement.classList.contains("dark");
            toggleBtn.innerHTML = nowDark
                ? `<i data-lucide="sun" class="w-4 h-4 text-amber-400"></i>`
                : `<i data-lucide="moon" class="w-4 h-4"></i>`;
            if (window.lucide) window.lucide.createIcons();
        });
    }

    if (window.lucide) window.lucide.createIcons();
}
