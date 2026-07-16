// Landing Page Module
function renderLanding(container) {
    // Custom landing page formatting - bypass standard page classes to make it full screen
    container.className = "min-h-screen animate-fade-in text-slate-800 dark:text-slate-100 relative overflow-hidden";
    
    // Inject floating background graphics
    container.innerHTML = `
        <div class="glass-shape glass-shape-1"></div>
        <div class="glass-shape glass-shape-2"></div>
        <div class="glass-shape glass-shape-3"></div>

        <!-- Sticky Header -->
        <header class="w-full bg-glass border-glass border-b sticky top-0 z-40 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl gradient-bg flex items-center justify-center text-white font-bold shadow-md">
                    T
                </div>
                <span class="font-bold text-xl tracking-tight text-slate-900 dark:text-white">TPMS Portal</span>
            </div>
            
            <div class="flex items-center gap-4">
                <button id="theme-toggle-landing" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm transition hover:scale-105">
                    <i data-lucide="moon" class="w-5 h-5 text-slate-700 dark:text-slate-200"></i>
                </button>
                <a href="#" data-view="login" class="px-5 py-2 rounded-xl font-medium text-white gradient-bg hover:opacity-90 transition shadow-md shadow-blue-500/20">
                    Sign In
                </a>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="max-w-7xl mx-auto px-6 pt-16 pb-20 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-8 animate-slide-up">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 font-semibold text-xs border border-blue-100 dark:border-blue-900/60 uppercase tracking-wider">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Redefining Campus Placements
                </div>
                
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight leading-none text-slate-900 dark:text-white">
                    Training & Placement <br/>
                    <span class="gradient-text">Management System</span>
                </h1>
                
                <p class="text-lg md:text-xl text-slate-600 dark:text-slate-400 max-w-xl font-light">
                    Manage student placements, internships, training programs, and higher studies efficiently in one elegant, cohesive platform.
                </p>

                <!-- CTA Buttons Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-2xl">
                    <a href="#" data-view="login" class="col-span-2 sm:col-span-1 px-5 py-3.5 text-center rounded-xl font-medium text-white gradient-bg hover:opacity-95 transition shadow-lg shadow-blue-500/25 flex items-center justify-center gap-2">
                        Get Started <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <button onclick="app.login('student')" class="px-5 py-3.5 text-center rounded-xl font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/60 shadow-sm text-slate-700 dark:text-slate-200 transition">
                        Student Login
                    </button>
                    <button onclick="app.login('company')" class="px-5 py-3.5 text-center rounded-xl font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/60 shadow-sm text-slate-700 dark:text-slate-200 transition">
                        Company Login
                    </button>
                    <button onclick="app.login('admin')" class="px-5 py-3.5 text-center rounded-xl font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/60 shadow-sm text-slate-700 dark:text-slate-200 transition">
                        Admin Login
                    </button>
                </div>
            </div>

            <!-- Hero Graphic Illustration -->
            <div class="lg:col-span-5 relative flex justify-center">
                <div class="relative w-full max-w-md aspect-square bg-gradient-to-tr from-blue-500/20 to-purple-500/20 rounded-[40px] flex items-center justify-center p-8 border border-white/20 shadow-2xl backdrop-blur-3xl animate-scale-in">
                    <!-- Dynamic Glass Overlay Cards -->
                    <div class="absolute -top-6 -left-6 bg-glass border-glass p-4 rounded-2xl shadow-lg flex items-center gap-3 animate-bounce" style="animation-duration: 5s">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center text-white">
                            <i data-lucide="award" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-medium">Highest Package</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white">48.2 LPA</p>
                        </div>
                    </div>

                    <div class="absolute -bottom-6 -right-6 bg-glass border-glass p-4 rounded-2xl shadow-lg flex items-center gap-3 animate-bounce" style="animation-duration: 6s; animation-delay: 1s">
                        <div class="w-10 h-10 rounded-xl bg-purple-500 flex items-center justify-center text-white">
                            <i data-lucide="building-2" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-medium">Recruiting Partners</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white">200+ Active</p>
                        </div>
                    </div>

                    <!-- Inner illustration visual -->
                    <div class="w-full h-full rounded-3xl bg-glass border-glass flex flex-col justify-between p-6 shadow-inner">
                        <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800/80 pb-4">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Placement Overview</span>
                            <span class="text-xs text-emerald-500 font-semibold px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/30 rounded-full">Live Stats</span>
                        </div>
                        
                        <div class="space-y-4 my-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Placement Rate</span>
                                <span class="font-bold text-blue-600 dark:text-blue-400">95%</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-2.5 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-full rounded-full" style="width: 95%"></div>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Registered Students</span>
                                <span class="font-bold text-purple-600 dark:text-purple-400">5,420</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-2.5 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-purple-500 to-cyan-500 h-full rounded-full" style="width: 82%"></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 justify-center py-2 px-4 bg-blue-500/10 rounded-xl border border-blue-500/20 text-xs text-blue-600 dark:text-blue-400 font-medium">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 animate-pulse"></i> Automated synchronization with recruiter portals
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="border-y border-slate-200 dark:border-slate-800 bg-white/40 dark:bg-slate-900/40 py-12">
            <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <h3 class="text-4xl font-extrabold text-blue-600 dark:text-blue-400">5000+</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 font-medium">Students Enrolled</p>
                </div>
                <div>
                    <h3 class="text-4xl font-extrabold text-purple-600 dark:text-purple-400">200+</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 font-medium">Corporate Partners</p>
                </div>
                <div>
                    <h3 class="text-4xl font-extrabold text-cyan-600 dark:text-cyan-400">95%</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 font-medium">Placement Success Rate</p>
                </div>
                <div>
                    <h3 class="text-4xl font-extrabold text-emerald-600 dark:text-emerald-400">300+</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 font-medium">Annual Job Offers</p>
                </div>
            </div>
        </section>

        <!-- Feature cards with icons -->
        <section class="max-w-7xl mx-auto px-6 py-20">
            <div class="text-center max-w-xl mx-auto mb-16 space-y-3">
                <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Smart Recruitment Workflows</h2>
                <p class="text-slate-500 dark:text-slate-400">Advanced enterprise-grade tooling built specifically to streamline campus placements for colleges, students, and corporate partners.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="dashboard-card p-6 flex flex-col items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center">
                        <i data-lucide="file-text" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Resume & Profile Manager</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">
                        Students build interactive academic profiles, upload verified grade reports, and attach single-click resumes ready for corporate evaluation.
                    </p>
                </div>
                <!-- Card 2 -->
                <div class="dashboard-card p-6 flex flex-col items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-600 flex items-center justify-center">
                        <i data-lucide="sliders" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Realtime Job Matchmaking</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">
                        Recruiters publish jobs and filter candidates instantly based on CGPA criteria, branch specializations, skill lists, and backlog thresholds.
                    </p>
                </div>
                <!-- Card 3 -->
                <div class="dashboard-card p-6 flex flex-col items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 text-cyan-600 flex items-center justify-center">
                        <i data-lucide="check-square" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Structured Application Tracker</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">
                        Follow recruitment rounds from application reviews to shortlists, technical interviews, and final job selection offers.
                    </p>
                </div>
            </div>
        </section>

        <!-- Footer Section -->
        <footer class="border-t border-slate-200 dark:border-slate-800 bg-white/20 dark:bg-slate-900/20 py-12 px-6">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg gradient-bg flex items-center justify-center text-white font-bold">T</div>
                        <span class="font-bold text-lg text-slate-800 dark:text-white">TPMS Portal</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Comprehensive web dashboard coordinating higher educational placements, skill training, and university pathways.
                    </p>
                </div>
                
                <div>
                    <h4 class="font-bold text-sm text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-4">Portals</h4>
                    <ul class="space-y-2.5 text-xs text-slate-500 dark:text-slate-400">
                        <li><a href="#" onclick="app.login('student')" class="hover:underline">Student Dashboard</a></li>
                        <li><a href="#" onclick="app.login('company')" class="hover:underline">Company Recruiter</a></li>
                        <li><a href="#" onclick="app.login('admin')" class="hover:underline">Office Administrator</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold text-sm text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-4">Contact TPO</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Block A, Placement Cell office<br/>
                        State Engineering University campus<br/>
                        Email: placement-cell@university.edu<br/>
                        Tel: +91 80 4455 6677
                    </p>
                </div>

                <div>
                    <h4 class="font-bold text-sm text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-4">Social Connections</h4>
                    <div class="flex items-center gap-4">
                        <a href="#" class="p-2 bg-slate-200 dark:bg-slate-800 rounded-xl hover:text-blue-500 transition"><i data-lucide="twitter" class="w-4 h-4"></i></a>
                        <a href="#" class="p-2 bg-slate-200 dark:bg-slate-800 rounded-xl hover:text-blue-600 transition"><i data-lucide="linkedin" class="w-4 h-4"></i></a>
                        <a href="#" class="p-2 bg-slate-200 dark:bg-slate-800 rounded-xl hover:text-rose-500 transition"><i data-lucide="instagram" class="w-4 h-4"></i></a>
                        <a href="#" class="p-2 bg-slate-200 dark:bg-slate-800 rounded-xl hover:text-slate-400 transition"><i data-lucide="github" class="w-4 h-4"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="max-w-7xl mx-auto border-t border-slate-200 dark:border-slate-850 mt-10 pt-6 text-center text-xs text-slate-400">
                &copy; 2026 Training & Placement Management System. Engineered for Excellence.
            </div>
        </footer>
    `;

    // Connect theme toggle in landing page
    const toggleBtn = document.getElementById("theme-toggle-landing");
    const isDark = document.documentElement.classList.contains("dark");
    if (toggleBtn) {
        toggleBtn.innerHTML = isDark 
            ? `<i data-lucide="sun" class="w-5 h-5 text-amber-500"></i>`
            : `<i data-lucide="moon" class="w-5 h-5 text-slate-700"></i>`;
        
        toggleBtn.addEventListener("click", () => {
            app.toggleTheme();
            const nowDark = document.documentElement.classList.contains("dark");
            toggleBtn.innerHTML = nowDark 
                ? `<i data-lucide="sun" class="w-5 h-5 text-amber-500"></i>`
                : `<i data-lucide="moon" class="w-5 h-5 text-slate-700"></i>`;
            if (window.lucide) window.lucide.createIcons();
        });
    }

    if (window.lucide) {
        window.lucide.createIcons();
    }
}
