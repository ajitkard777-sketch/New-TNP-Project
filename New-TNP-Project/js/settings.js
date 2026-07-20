// Settings & Profile Preferences — TPMS v2.0 Premium
function renderSettingsPanel(container) {
    const role    = mockData.session.role;
    const student = mockData.currentStudent;
    const isDark  = document.documentElement.classList.contains("dark");

    container.innerHTML = `
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-[var(--text-primary)]">Workspace Settings</h1>
                <p class="text-sm text-[var(--text-faint)] mt-0.5">Manage your personal credentials, security keys, and system preferences.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

            <!-- Left: Navigation Panel -->
            <div class="lg:col-span-3">
                <div class="dashboard-card p-3 space-y-1">
                    ${[
                        { id: "profile",     icon: "user-circle",   label: "Profile Details"    },
                        { id: "security",    icon: "shield-check",  label: "Account Security"   },
                        { id: "preferences", icon: "sliders",       label: "Preferences"        }
                    ].map((s, i) => `
                        <button onclick="switchSettingsSection('${s.id}')" id="btn-settings-${s.id}"
                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all
                                       ${i === 0 ? 'bg-[var(--primary-light)] text-[var(--primary)]' : 'text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] hover:text-[var(--primary)]'}">
                            <i data-lucide="${s.icon}" class="w-4 h-4 flex-shrink-0"></i>
                            ${s.label}
                        </button>
                    `).join("")}
                </div>
            </div>

            <!-- Right: Form Panel -->
            <div class="lg:col-span-9 space-y-5">

                <!-- Profile Section -->
                <div id="section-settings-profile" class="dashboard-card p-6 space-y-6">
                    <h3 class="font-bold text-sm text-[var(--text-primary)] pb-3 border-b border-[var(--border-color)] flex items-center gap-2">
                        <i data-lucide="user-circle" class="w-4.5 h-4.5 text-[var(--primary)]"></i> Profile Configuration
                    </h3>

                    <form onsubmit="handleSettingsUpdate(event,'profile')" class="space-y-5">
                        <!-- Avatar Row -->
                        <div class="flex items-center gap-4">
                            <div class="relative w-16 h-16 flex-shrink-0">
                                <img id="settings-avatar-preview"
                                     src="${role === 'student' ? student.avatar : 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=80&h=80&q=80'}"
                                     class="w-16 h-16 rounded-2xl object-cover border-2 border-[var(--border-color)] shadow-[var(--shadow-sm)]" />
                                <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full gradient-bg flex items-center justify-center">
                                    <i data-lucide="camera" class="w-2.5 h-2.5 text-white"></i>
                                </div>
                            </div>
                            <div>
                                <button type="button"
                                        onclick="app.showToast('Photo upload dialog triggered', 'info')"
                                        class="btn btn-secondary btn-sm">
                                    Change Photo
                                </button>
                                <p class="text-[11px] text-[var(--text-faint)] mt-1.5">PNG or JPG up to 2 MB. Recommended: 256×256px.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <div class="input-icon-wrapper">
                                    <span class="input-icon-left"><i data-lucide="user" class="w-4 h-4"></i></span>
                                    <input type="text" id="settings-name" required
                                           value="${role === 'student' ? student.name : 'TPO Administrator'}"
                                           class="form-input pl-10" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <div class="input-icon-wrapper">
                                    <span class="input-icon-left"><i data-lucide="mail" class="w-4 h-4"></i></span>
                                    <input type="email" id="settings-email" required
                                           value="${role === 'student' ? student.email : 'admin@tpms.edu'}"
                                           class="form-input pl-10" />
                                </div>
                            </div>
                        </div>

                        ${role === 'student' ? `
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label">Academic Branch <span class="text-[var(--text-faint)] font-normal">(Read-only)</span></label>
                                    <input type="text" disabled value="${student.branch}" class="form-input" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">CGPA <span class="text-[var(--text-faint)] font-normal">(Read-only)</span></label>
                                    <input type="text" disabled value="${student.cgpa.toFixed(2)}" class="form-input" />
                                </div>
                            </div>
                        ` : ""}

                        <div class="pt-2">
                            <button type="submit" class="btn btn-primary shadow-[var(--shadow-brand)]">
                                <i data-lucide="save" class="w-4 h-4"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Security Section -->
                <div id="section-settings-security" class="dashboard-card p-6 space-y-6 hidden animate-fade-in">
                    <h3 class="font-bold text-sm text-[var(--text-primary)] pb-3 border-b border-[var(--border-color)] flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4.5 h-4.5 text-[var(--success)]"></i> Account Security
                    </h3>

                    <form onsubmit="handleSettingsUpdate(event,'security')" class="space-y-4">
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <div class="input-icon-wrapper">
                                <span class="input-icon-left"><i data-lucide="lock" class="w-4 h-4"></i></span>
                                <input type="password" required placeholder="••••••••" class="form-input pl-10" autocomplete="current-password" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">New Password</label>
                                <div class="input-icon-wrapper">
                                    <span class="input-icon-left"><i data-lucide="key-round" class="w-4 h-4"></i></span>
                                    <input type="password" required id="settings-new-pass"
                                           placeholder="New secure password" class="form-input pl-10" autocomplete="new-password" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-icon-wrapper">
                                    <span class="input-icon-left"><i data-lucide="key-round" class="w-4 h-4"></i></span>
                                    <input type="password" required id="settings-confirm-pass"
                                           placeholder="Retype password" class="form-input pl-10" autocomplete="new-password" />
                                </div>
                            </div>
                        </div>

                        <!-- Password Strength Hint -->
                        <p class="text-[11px] text-[var(--text-faint)] flex items-center gap-1.5">
                            <i data-lucide="info" class="w-3.5 h-3.5"></i>
                            Use 8+ characters with a mix of uppercase, numbers, and symbols.
                        </p>

                        <div class="pt-2">
                            <button type="submit" class="btn btn-primary shadow-[var(--shadow-brand)]">
                                <i data-lucide="shield-check" class="w-4 h-4"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Preferences Section -->
                <div id="section-settings-preferences" class="dashboard-card p-6 space-y-6 hidden animate-fade-in">
                    <h3 class="font-bold text-sm text-[var(--text-primary)] pb-3 border-b border-[var(--border-color)] flex items-center gap-2">
                        <i data-lucide="sliders" class="w-4.5 h-4.5 text-[var(--accent)]"></i> System Preferences
                    </h3>

                    <div class="space-y-5">
                        <!-- Theme Toggle -->
                        <div class="flex items-center justify-between py-4 border-b border-[var(--border-color)]">
                            <div class="min-w-0 pr-4">
                                <h4 class="text-sm font-bold text-[var(--text-primary)]">System Visual Theme</h4>
                                <p class="text-xs text-[var(--text-faint)] mt-1">Toggle between light and dark display mode for this workspace.</p>
                            </div>
                            <label class="switch flex-shrink-0">
                                <input type="checkbox" id="settings-theme-switch"
                                       ${isDark ? "checked" : ""}
                                       onchange="app.toggleTheme(); syncThemeToggleUI();" />
                                <span class="slider"></span>
                            </label>
                        </div>

                        <!-- Notifications -->
                        <div class="space-y-4 pb-4 border-b border-[var(--border-color)]">
                            <div>
                                <h4 class="text-sm font-bold text-[var(--text-primary)]">Email Notifications</h4>
                                <p class="text-xs text-[var(--text-faint)] mt-1">Choose which events trigger email alerts to your inbox.</p>
                            </div>
                            <div class="space-y-3">
                                ${[
                                    { label: "New campus job listing alerts",     checked: true  },
                                    { label: "Scheduled interview reminders",    checked: true  },
                                    { label: "Application status updates",       checked: true  },
                                    { label: "Weekly campus newsletter digest",  checked: false }
                                ].map(n => `
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="checkbox" ${n.checked ? "checked" : ""}
                                               class="w-4 h-4 rounded flex-shrink-0" />
                                        <span class="text-xs text-[var(--text-secondary)] group-hover:text-[var(--text-primary)] transition">${n.label}</span>
                                    </label>
                                `).join("")}
                            </div>
                        </div>

                        <!-- Language -->
                        <div class="space-y-3">
                            <div>
                                <h4 class="text-sm font-bold text-[var(--text-primary)]">Language</h4>
                                <p class="text-xs text-[var(--text-faint)] mt-1">Select your preferred display language.</p>
                            </div>
                            <select class="form-select w-full sm:w-56 text-xs">
                                <option>English (United States)</option>
                                <option>Hindi (India)</option>
                                <option>Spanish (España)</option>
                                <option>French (France)</option>
                            </select>
                        </div>

                        <div class="pt-2">
                            <button onclick="app.showToast('Preferences saved!', 'success')"
                                    class="btn btn-primary shadow-[var(--shadow-brand)]">
                                <i data-lucide="save" class="w-4 h-4"></i> Save Preferences
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    `;

    if (window.lucide) window.lucide.createIcons();
}

function switchSettingsSection(section) {
    const sections = ["profile", "security", "preferences"];

    sections.forEach(sec => {
        const secEl = document.getElementById(`section-settings-${sec}`);
        const btn   = document.getElementById(`btn-settings-${sec}`);
        if (!secEl || !btn) return;

        if (sec === section) {
            secEl.classList.remove("hidden");
            btn.className = "w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all bg-[var(--primary-light)] text-[var(--primary)]";
        } else {
            secEl.classList.add("hidden");
            btn.className = "w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] hover:text-[var(--primary)]";
        }
    });
}

async function handleSettingsUpdate(e, section) {
    e.preventDefault();
    const btn = e.target.querySelector('[type="submit"]');

    if (section === "profile") {
        const name  = document.getElementById("settings-name").value.trim();
        const email = document.getElementById("settings-email").value.trim();

        if (!name || name.length < 2) {
            app.showToast("Please enter a valid full name (min 2 characters).", "warning");
            return;
        }

        if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }
        const res = await ApiService.updateProfile(name, email);
        if (btn) { btn.disabled = false; btn.textContent = 'Save Changes'; }

        if (res.success) {
            if (mockData.session.role === "student") {
                mockData.currentStudent.name  = name;
                mockData.currentStudent.email = email;
            }
            const nameLabel = document.getElementById("user-name-label");
            if (nameLabel) nameLabel.textContent = name;
            app.showToast("Profile saved successfully!", "success");
        } else {
            app.showToast(res.message || "Failed to save profile.", "danger");
        }
    }
    else if (section === "security") {
        const current = document.getElementById("settings-current-pass")?.value;
        const p1      = document.getElementById("settings-new-pass").value;
        const p2      = document.getElementById("settings-confirm-pass").value;

        if (p1 !== p2) {
            app.showToast("Passwords do not match. Please try again.", "danger");
            return;
        }
        if (p1.length < 8) {
            app.showToast("Password must be at least 8 characters.", "warning");
            return;
        }

        if (btn) { btn.disabled = true; btn.textContent = 'Updating…'; }
        const res = await ApiService.changePassword(current, p1, p2);
        if (btn) { btn.disabled = false; btn.textContent = 'Update Password'; }

        if (res.success) {
            app.showToast("Password updated successfully!", "success");
            // Clear password fields
            ['settings-current-pass','settings-new-pass','settings-confirm-pass']
                .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
        } else {
            app.showToast(res.message || "Failed to update password.", "danger");
        }
    }
}

function syncThemeToggleUI() {
    const isDark = document.documentElement.classList.contains("dark");
    const sw     = document.getElementById("settings-theme-switch");
    if (sw) sw.checked = isDark;
}

// Global exports
window.switchSettingsSection = switchSettingsSection;
window.handleSettingsUpdate  = handleSettingsUpdate;
window.syncThemeToggleUI     = syncThemeToggleUI;
