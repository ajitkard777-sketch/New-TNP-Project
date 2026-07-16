// Profile Settings & Preferences Module
function renderSettingsPanel(container) {
    const role = mockData.session.role;
    const student = mockData.currentStudent;
    const isDark = document.documentElement.classList.contains("dark");

    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Workspace Settings</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Configure personal credentials, security keys and system theme preferences.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Navigation selectors -->
            <div class="lg:col-span-4 space-y-3">
                <div class="dashboard-card p-3 space-y-1">
                    <button onclick="switchSettingsSection('profile')" id="btn-settings-profile" class="w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold bg-blue-500/10 text-blue-500 flex items-center gap-2.5">
                        <i data-lucide="user" class="w-4 h-4"></i> Profile Details
                    </button>
                    <button onclick="switchSettingsSection('security')" id="btn-settings-security" class="w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold text-slate-650 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-900 flex items-center gap-2.5">
                        <i data-lucide="shield-check" class="w-4 h-4"></i> Account Security
                    </button>
                    <button onclick="switchSettingsSection('preferences')" id="btn-settings-preferences" class="w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold text-slate-650 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-900 flex items-center gap-2.5">
                        <i data-lucide="sliders" class="w-4 h-4"></i> System Preferences
                    </button>
                </div>
            </div>

            <!-- Settings fields panels -->
            <div class="lg:col-span-8">
                <!-- Section 1: Profile -->
                <div id="section-settings-profile" class="dashboard-card p-6 space-y-6">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800 pb-2">Profile Configuration</h3>
                    
                    <form onsubmit="handleSettingsUpdate(event, 'profile')" class="space-y-4 text-xs">
                        <div class="flex items-center gap-4 py-1">
                            <div class="relative w-14 h-14 rounded-2xl overflow-hidden bg-slate-100 border-glass">
                                <img id="settings-avatar-preview" src="${role === 'student' ? student.avatar : 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80'}" class="w-full h-full object-cover" />
                            </div>
                            <div>
                                <button type="button" onclick="app.showToast('Photo uploading simulator triggered', 'info')" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition">Change Photo</button>
                                <p class="text-[9px] text-slate-400 mt-1">PNG or JPG formats up to 2MB</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="font-semibold text-slate-500">Contact Name</label>
                                <input type="text" id="settings-name" required value="${role === 'student' ? student.name : 'TPO Administrator'}" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white" />
                            </div>
                            <div class="space-y-1">
                                <label class="font-semibold text-slate-500">Email Address</label>
                                <input type="email" id="settings-email" required value="${role === 'student' ? student.email : 'admin@tpms.com'}" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white" />
                            </div>
                        </div>

                        ${role === 'student' 
                            ? `
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="font-semibold text-slate-500">Branch (Read-Only)</label>
                                    <input type="text" disabled value="${student.branch}" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/80 rounded-xl text-slate-400 dark:text-slate-500 cursor-not-allowed" />
                                </div>
                                <div class="space-y-1">
                                    <label class="font-semibold text-slate-500">Academic CGPA (Read-Only)</label>
                                    <input type="text" disabled value="${student.cgpa.toFixed(2)}" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/80 rounded-xl text-slate-400 dark:text-slate-500 cursor-not-allowed" />
                                </div>
                            </div>
                            `
                            : ''
                        }

                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-xl shadow-md hover:opacity-95 transition">
                            Save Changes
                        </button>
                    </form>
                </div>

                <!-- Section 2: Security -->
                <div id="section-settings-security" class="dashboard-card p-6 space-y-6 hidden animate-fade-in">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800 pb-2">Account Security</h3>
                    
                    <form onsubmit="handleSettingsUpdate(event, 'security')" class="space-y-4 text-xs">
                        <div class="space-y-1">
                            <label class="font-semibold text-slate-500">Current Password</label>
                            <input type="password" required placeholder="••••••••" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="font-semibold text-slate-500">New Password</label>
                                <input type="password" required id="settings-new-pass" placeholder="Create new password" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="space-y-1">
                                <label class="font-semibold text-slate-500">Confirm Password</label>
                                <input type="password" required id="settings-confirm-pass" placeholder="Retype new password" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>

                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-xl shadow-md hover:opacity-95 transition">
                            Update Password
                        </button>
                    </form>
                </div>

                <!-- Section 3: Preferences -->
                <div id="section-settings-preferences" class="dashboard-card p-6 space-y-6 hidden animate-fade-in">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800 pb-2">System Preferences</h3>
                    
                    <div class="space-y-5 text-xs">
                        <!-- Theme Toggle Switch Layout -->
                        <div class="flex items-center justify-between border-b border-slate-50 dark:border-slate-800/80 pb-3">
                            <div>
                                <h4 class="font-bold text-slate-750 dark:text-slate-300">System Visual Theme</h4>
                                <p class="text-[10px] text-slate-400 mt-0.5">Toggle light/dark display configurations.</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="settings-theme-switch" ${isDark ? 'checked' : ''} onchange="app.toggleTheme(); syncThemeToggleUI();" />
                                <span class="slider"></span>
                            </label>
                        </div>

                        <!-- Notification checkboxes -->
                        <div class="space-y-3.5 border-b border-slate-50 dark:border-slate-800/80 pb-4">
                            <div>
                                <h4 class="font-bold text-slate-750 dark:text-slate-300">Email Notifications Preferences</h4>
                                <p class="text-[10px] text-slate-400 mt-0.5">Define what updates you wish to receive in your email inbox.</p>
                            </div>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="checkbox" checked class="w-4 h-4 rounded text-blue-600 dark:text-purple-500 border-slate-300 focus:ring-blue-500" />
                                    <span class="font-semibold text-slate-655">New campus job listings alert</span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="checkbox" checked class="w-4 h-4 rounded text-blue-600 dark:text-purple-500 border-slate-300 focus:ring-blue-500" />
                                    <span class="font-semibold text-slate-655">Scheduled interview reminders</span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="checkbox" class="w-4 h-4 rounded text-blue-600 dark:text-purple-500 border-slate-300 focus:ring-blue-500" />
                                    <span class="font-semibold text-slate-655">Weekly campus newsletter</span>
                                </label>
                            </div>
                        </div>

                        <!-- Language Selector -->
                        <div class="space-y-2">
                            <div>
                                <h4 class="font-bold text-slate-750 dark:text-slate-300">Workspace Language</h4>
                                <p class="text-[10px] text-slate-400 mt-0.5">Select preferred layout localization.</p>
                            </div>
                            <select class="w-48 px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-750 dark:text-slate-300">
                                <option>English (US)</option>
                                <option>Hindi (India)</option>
                                <option>Spanish (ES)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    if (window.lucide) window.lucide.createIcons();
}

function switchSettingsSection(section) {
    const sections = ['profile', 'security', 'preferences'];
    
    sections.forEach(sec => {
        const secEl = document.getElementById(`section-settings-${sec}`);
        const btn = document.getElementById(`btn-settings-${sec}`);
        
        if (!secEl || !btn) return;
        
        if (sec === section) {
            secEl.classList.remove("hidden");
            btn.className = "w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold bg-blue-500/10 text-blue-500 flex items-center gap-2.5";
        } else {
            secEl.classList.add("hidden");
            btn.className = "w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold text-slate-650 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-900 flex items-center gap-2.5";
        }
    });
}

function handleSettingsUpdate(e, section) {
    e.preventDefault();
    
    if (section === 'profile') {
        const name = document.getElementById("settings-name").value;
        const email = document.getElementById("settings-email").value;
        
        if (mockData.session.role === 'student') {
            mockData.currentStudent.name = name;
            mockData.currentStudent.email = email;
            // update header name display directly
            const label = document.getElementById("user-role-label");
            if (label) label.textContent = name;
        }

        app.showToast("Profile details updated successfully!", "success");
    } else if (section === 'security') {
        const p1 = document.getElementById("settings-new-pass").value;
        const p2 = document.getElementById("settings-confirm-pass").value;
        
        if (p1 !== p2) {
            app.showToast("Passwords do not match!", "danger");
            return;
        }
        
        app.showToast("Password updated successfully!", "success");
    }
}

function syncThemeToggleUI() {
    const isDark = document.documentElement.classList.contains("dark");
    const sw = document.getElementById("settings-theme-switch");
    if (sw) sw.checked = isDark;
}

// Global exports
window.switchSettingsSection = switchSettingsSection;
window.handleSettingsUpdate = handleSettingsUpdate;
window.syncThemeToggleUI = syncThemeToggleUI;
