// Authentication Views Module — TPMS v2.0 Premium
function renderAuth(container, view) {
    container.className = "min-h-screen flex items-center justify-center p-4 animate-fade-in relative overflow-hidden";

    container.innerHTML = `
        <div class="glass-shape glass-shape-1" style="opacity:0.10;"></div>
        <div class="glass-shape glass-shape-2" style="opacity:0.08;"></div>

        <!-- Auth Card -->
        <div class="w-full max-w-md relative z-10 animate-scale-in">
            <div id="auth-form-container"></div>
        </div>
    `;

    const formWrapper = document.getElementById("auth-form-container");
    if (!formWrapper) return;

    if (view === "login")              renderLoginForm(formWrapper);
    else if (view === "register-student") renderStudentRegisterForm(formWrapper);
    else if (view === "register-company") renderCompanyRegisterForm(formWrapper);
    else if (view === "forgot-password")  renderForgotForm(formWrapper);

    if (window.lucide) window.lucide.createIcons();
}

/* ──────────────────────────────────────────────────
   AUTH CARD WRAPPER HELPER
   ────────────────────────────────────────────────── */
function authCard(content) {
    return `
        <div class="bg-[var(--bg-card)] border border-[var(--border-color)] rounded-[var(--radius-2xl)] shadow-[var(--shadow-xl)] overflow-hidden">
            <!-- Top gradient accent -->
            <div class="h-1 gradient-bg"></div>
            <div class="p-8">
                ${content}
            </div>
        </div>`;
}

/* ──────────────────────────────────────────────────
   1. LOGIN FORM
   ────────────────────────────────────────────────── */
function renderLoginForm(wrapper) {
    wrapper.innerHTML = authCard(`
        <!-- Logo & Title -->
        <div class="text-center mb-8 space-y-3">
            <div class="w-12 h-12 rounded-2xl gradient-bg flex items-center justify-center text-white font-black text-xl mx-auto shadow-[var(--shadow-brand)]">T</div>
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-[var(--text-primary)]">Welcome Back</h2>
                <p class="text-xs text-[var(--text-faint)] mt-1">Select your workspace and sign in to continue.</p>
            </div>
        </div>

        <!-- Role Selector Tabs -->
        <div class="flex p-1 gap-1 bg-[var(--bg-subtle)] rounded-[var(--radius-md)] mb-6 border border-[var(--border-color)]">
            ${["student","company","admin"].map(r => `
                <button onclick="setActiveLoginRole('${r}')" id="tab-${r}"
                        class="flex-1 py-2 text-xs font-semibold rounded-[var(--radius-sm)] transition-all duration-200
                               ${r === 'student' ? 'bg-[var(--bg-card)] text-[var(--primary)] shadow-[var(--shadow-xs)]' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]'}">
                    ${r.charAt(0).toUpperCase() + r.slice(1)}
                </button>
            `).join("")}
        </div>

        <input type="hidden" id="login-role" value="student" />

        <form id="login-form" class="space-y-4" onsubmit="handleLoginSubmit(event)">
            <!-- Email -->
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon-left"><i data-lucide="mail" class="w-4 h-4"></i></span>
                    <input type="email" id="login-email" required
                           placeholder="aarav.sharma@college.edu"
                           class="form-input" autocomplete="email" />
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <div class="flex items-center justify-between">
                    <label class="form-label">Password</label>
                    <a href="#" data-view="forgot-password"
                       class="text-xs font-semibold text-[var(--primary)] hover:underline">
                        Forgot password?
                    </a>
                </div>
                <div class="input-icon-wrapper">
                    <span class="input-icon-left"><i data-lucide="lock" class="w-4 h-4"></i></span>
                    <input type="password" id="login-password" required
                           placeholder="••••••••"
                           class="form-input" autocomplete="current-password" />
                    <button type="button"
                            onclick="togglePasswordVisibility('login-password')"
                            class="input-icon-right">
                        <i data-lucide="eye" id="login-password-eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Remember me -->
            <label class="flex items-center gap-2.5 cursor-pointer py-0.5">
                <input type="checkbox" checked class="w-4 h-4 rounded" />
                <span class="text-xs text-[var(--text-secondary)]">Keep me signed in</span>
            </label>

            <!-- Submit -->
            <button type="submit" class="btn btn-primary w-full py-2.5 text-sm mt-2 shadow-[var(--shadow-brand)]">
                <i data-lucide="log-in" class="w-4 h-4"></i> Sign In to Dashboard
            </button>
        </form>

        <!-- Divider -->
        <div class="divider-text my-6">
            <span>or continue with</span>
        </div>

        <!-- SSO Buttons -->
        <div class="grid grid-cols-2 gap-3 mb-6">
            <button onclick="simulateSocialSSO('Google')"
                    class="btn btn-secondary py-2.5 text-xs justify-center">
                <img src="https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg"
                     class="w-14 object-contain" alt="Google" />
            </button>
            <button onclick="simulateSocialSSO('Microsoft')"
                    class="btn btn-secondary py-2.5 text-xs justify-center">
                <img src="https://upload.wikimedia.org/wikipedia/commons/9/96/Microsoft_logo_%282012%29.svg"
                     class="w-20 object-contain" alt="Microsoft" />
            </button>
        </div>

        <!-- Register Link -->
        <p class="text-center text-xs text-[var(--text-faint)]">
            New here? Register as&nbsp;
            <a href="#" data-view="register-student" class="text-[var(--primary)] font-semibold hover:underline">Student</a>
            &nbsp;·&nbsp;
            <a href="#" data-view="register-company" class="text-[var(--primary)] font-semibold hover:underline">Recruiter</a>
        </p>
    `);
}

/* ──────────────────────────────────────────────────
   2. STUDENT REGISTRATION FORM
   ────────────────────────────────────────────────── */
function renderStudentRegisterForm(wrapper) {
    wrapper.innerHTML = authCard(`
        <div class="text-center mb-7 space-y-2">
            <h2 class="text-2xl font-extrabold tracking-tight text-[var(--text-primary)]">Student Registration</h2>
            <p class="text-xs text-[var(--text-faint)]">Join the college recruitment pipeline in 3 simple steps.</p>
        </div>

        <!-- Step Indicator -->
        <div class="flex items-center justify-between mb-8 px-2">
            ${["Account","Academic","Resume"].map((step, i) => `
                <div class="flex flex-col items-center gap-1.5 flex-1 relative">
                    <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center font-bold text-xs transition-all
                                ${i === 0 ? 'bg-[var(--primary)] border-[var(--primary)] text-white shadow-[var(--shadow-brand)]'
                                          : 'bg-[var(--bg-subtle)] border-[var(--border-color)] text-[var(--text-faint)]'}">
                        ${i === 0 ? '<i data-lucide="check" class="w-3.5 h-3.5"></i>' : i + 1}
                    </div>
                    <span class="text-[10px] font-semibold ${i === 0 ? 'text-[var(--primary)]' : 'text-[var(--text-faint)]'}">${step}</span>
                    ${i < 2 ? `<div class="absolute top-4 left-full -translate-x-1/2 w-full h-0.5 ${i === 0 ? 'bg-[var(--primary)]' : 'bg-[var(--border-color)]'}"></div>` : ""}
                </div>
            `).join("")}
        </div>

        <form id="student-reg-form" class="space-y-4" onsubmit="handleRegistrationSubmit(event,'student')">
            <div class="grid grid-cols-2 gap-3">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" required placeholder="Aarav Sharma" class="form-input" />
                </div>
                <div class="form-group">
                    <label class="form-label">Roll Number</label>
                    <input type="text" required placeholder="CSE2601" class="form-input" />
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">University Email</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon-left"><i data-lucide="mail" class="w-4 h-4"></i></span>
                    <input type="email" required placeholder="aarav.sharma@college.edu" class="form-input" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group">
                    <label class="form-label">Branch</label>
                    <select class="form-select">
                        <option>Computer Science</option>
                        <option>Information Tech</option>
                        <option>Electronics & Comm</option>
                        <option>Mechanical Eng</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">CGPA</label>
                    <input type="number" step="0.01" min="0" max="10" required placeholder="9.24" class="form-input" />
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon-left"><i data-lucide="lock" class="w-4 h-4"></i></span>
                    <input type="password" required placeholder="Create a secure password" class="form-input" />
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-full py-2.5 text-sm shadow-[var(--shadow-brand)]">
                <i data-lucide="user-plus" class="w-4 h-4"></i> Create Account
            </button>
        </form>

        <p class="text-center text-xs text-[var(--text-faint)] mt-5">
            Already registered?&nbsp;
            <a href="#" data-view="login" class="text-[var(--primary)] font-semibold hover:underline">Sign in here</a>
        </p>
    `);
}

/* ──────────────────────────────────────────────────
   3. COMPANY REGISTRATION FORM
   ────────────────────────────────────────────────── */
function renderCompanyRegisterForm(wrapper) {
    wrapper.innerHTML = authCard(`
        <div class="text-center mb-7 space-y-2">
            <h2 class="text-2xl font-extrabold tracking-tight text-[var(--text-primary)]">Recruiter Registration</h2>
            <p class="text-xs text-[var(--text-faint)]">Create a company portal to post drives and shortlist candidates.</p>
        </div>

        <form id="company-reg-form" class="space-y-4" onsubmit="handleRegistrationSubmit(event,'company')">
            <div class="form-group">
                <label class="form-label">Company Legal Name</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon-left"><i data-lucide="building-2" class="w-4 h-4"></i></span>
                    <input type="text" required placeholder="e.g. Google Inc." class="form-input" />
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Corporate Website</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon-left"><i data-lucide="globe" class="w-4 h-4"></i></span>
                    <input type="url" required placeholder="https://careers.google.com" class="form-input" />
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Industry Sector</label>
                <select class="form-select">
                    <option>Product Technology</option>
                    <option>IT & Consulting Services</option>
                    <option>Finance & Banking</option>
                    <option>Core Engineering</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group">
                    <label class="form-label">HR Contact Name</label>
                    <input type="text" required placeholder="Sundar HR" class="form-input" />
                </div>
                <div class="form-group">
                    <label class="form-label">HR Email</label>
                    <input type="email" required placeholder="hr@company.com" class="form-input" />
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon-left"><i data-lucide="lock" class="w-4 h-4"></i></span>
                    <input type="password" required placeholder="Create a secure password" class="form-input" />
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-full py-2.5 text-sm shadow-[var(--shadow-brand)]">
                <i data-lucide="building-2" class="w-4 h-4"></i> Register Company
            </button>
        </form>

        <p class="text-center text-xs text-[var(--text-faint)] mt-5">
            Already registered?&nbsp;
            <a href="#" data-view="login" class="text-[var(--primary)] font-semibold hover:underline">Sign in here</a>
        </p>
    `);
}

/* ──────────────────────────────────────────────────
   4. FORGOT PASSWORD FORM
   ────────────────────────────────────────────────── */
function renderForgotForm(wrapper) {
    wrapper.innerHTML = authCard(`
        <div class="text-center mb-7 space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-[var(--warning-light)] flex items-center justify-center mx-auto">
                <i data-lucide="key-round" class="w-6 h-6 text-[var(--warning)]"></i>
            </div>
            <h2 class="text-2xl font-extrabold tracking-tight text-[var(--text-primary)]">Recover Password</h2>
            <p class="text-xs text-[var(--text-faint)]">Enter your registered email address and we'll send you a password reset OTP.</p>
        </div>

        <form id="forgot-form" class="space-y-4" onsubmit="handleForgotSubmit(event)">
            <div class="form-group">
                <label class="form-label">Registered Email Address</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon-left"><i data-lucide="mail" class="w-4 h-4"></i></span>
                    <input type="email" required placeholder="user@college.edu" class="form-input" autocomplete="email" />
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-full py-2.5 text-sm mt-2 shadow-[var(--shadow-brand)]">
                <i data-lucide="send" class="w-4 h-4"></i> Send Recovery OTP
            </button>
        </form>

        <p class="text-center text-xs text-[var(--text-faint)] mt-6">
            Remembered your password?&nbsp;
            <a href="#" data-view="login" class="text-[var(--primary)] font-semibold hover:underline">Back to sign in</a>
        </p>
    `);
}

/* ──────────────────────────────────────────────────
   INTERACTION HANDLERS
   ────────────────────────────────────────────────── */
function setActiveLoginRole(role) {
    document.getElementById("login-role").value = role;

    ["student", "company", "admin"].forEach(r => {
        const tab = document.getElementById(`tab-${r}`);
        if (!tab) return;
        if (r === role) {
            tab.className = tab.className.replace(
                /text-\[var\(--text-muted\)\] hover:text-\[var\(--text-primary\)\]/g, ""
            );
            tab.className += " bg-[var(--bg-card)] text-[var(--primary)] shadow-[var(--shadow-xs)]";
        } else {
            tab.className = `flex-1 py-2 text-xs font-semibold rounded-[var(--radius-sm)] transition-all duration-200 text-[var(--text-muted)] hover:text-[var(--text-primary)]`;
        }
    });

    const emailField = document.getElementById("login-email");
    if (emailField) {
        const placeholders = {
            student: "aarav.sharma@college.edu",
            company: "hr@google.com",
            admin:   "admin@tpms.edu"
        };
        emailField.placeholder = placeholders[role] || "";
    }
}

function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const eye   = document.getElementById(`${inputId}-eye`);
    if (!input || !eye) return;

    if (input.type === "password") {
        input.type = "text";
        eye.setAttribute("data-lucide", "eye-off");
    } else {
        input.type = "password";
        eye.setAttribute("data-lucide", "eye");
    }
    if (window.lucide) window.lucide.createIcons();
}

function handleLoginSubmit(e) {
    e.preventDefault();
    const role = document.getElementById("login-role").value;
    app.login(role);
}

function handleRegistrationSubmit(e, role) {
    e.preventDefault();
    app.showToast("Registration request received. Admin approval pending.", "warning");
    app.navigate("login");
}

function handleForgotSubmit(e) {
    e.preventDefault();
    app.showToast("Recovery OTP sent successfully to your email inbox.", "success");
    app.navigate("login");
}

function simulateSocialSSO(provider) {
    app.showToast(`Connecting via ${provider} SSO…`, "info");
    setTimeout(() => app.login("student"), 900);
}

// Global exports
window.setActiveLoginRole      = setActiveLoginRole;
window.togglePasswordVisibility= togglePasswordVisibility;
window.handleLoginSubmit       = handleLoginSubmit;
window.handleRegistrationSubmit= handleRegistrationSubmit;
window.handleForgotSubmit      = handleForgotSubmit;
window.simulateSocialSSO       = simulateSocialSSO;
