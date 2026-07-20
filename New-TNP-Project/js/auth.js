// Authentication Views Module — TPMS v3.0 Premium (PHP Integrated)
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
                           placeholder="student@tpms.com"
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

            <!-- Submit -->
            <button type="submit" id="login-btn-submit" class="btn btn-primary w-full py-2.5 text-sm mt-4 shadow-[var(--shadow-brand)]">
                <i data-lucide="log-in" class="w-4 h-4"></i> Sign In to Dashboard
            </button>
        </form>

        <div class="divider-text my-6">
            <span>Demo Accounts</span>
        </div>
        <div class="bg-[var(--bg-subtle)] border border-[var(--border-color)] rounded-[var(--radius-md)] p-3 space-y-1.5 text-[11px] text-[var(--text-faint)]">
            <div class="flex justify-between"><span>Admin:</span><code class="text-[var(--primary)]">admin@tpms.com / Admin@123</code></div>
            <div class="flex justify-between"><span>Student:</span><code class="text-[var(--primary)]">student@tpms.com / Student@123</code></div>
            <div class="flex justify-between"><span>Company:</span><code class="text-[var(--primary)]">company@tpms.com / Company@123</code></div>
        </div>

        <!-- Register Link -->
        <p class="text-center text-xs text-[var(--text-faint)] mt-6">
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
            <p class="text-xs text-[var(--text-faint)]">Join the college recruitment pipeline.</p>
        </div>

        <form id="student-reg-form" class="space-y-4" onsubmit="handleRegistrationSubmit(event,'student')">
            <div class="grid grid-cols-2 gap-3">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" id="reg-stu-name" required placeholder="Aarav Sharma" class="form-input" />
                </div>
                <div class="form-group">
                    <label class="form-label">Roll Number</label>
                    <input type="text" id="reg-stu-roll" required placeholder="CSE2601" class="form-input" />
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">University Email</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon-left"><i data-lucide="mail" class="w-4 h-4"></i></span>
                    <input type="email" id="reg-stu-email" required placeholder="aarav.sharma@college.edu" class="form-input" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select id="reg-stu-branch" class="form-select">
                        <option>Computer Science</option>
                        <option>Information Tech</option>
                        <option>Electronics & Comm</option>
                        <option>Mechanical Eng</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Year</label>
                    <select id="reg-stu-year" class="form-select">
                        <option>1st Year</option>
                        <option>2nd Year</option>
                        <option>3rd Year</option>
                        <option>4th Year</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" id="reg-stu-phone" placeholder="9876543210" class="form-input" />
                </div>
                <div class="form-group">
                    <label class="form-label">CGPA</label>
                    <input type="number" step="0.01" min="0" max="10" id="reg-stu-cgpa" required placeholder="9.24" class="form-input" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" id="reg-stu-pass" required placeholder="Min 8 characters" class="form-input" />
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" id="reg-stu-confirm" required placeholder="Confirm Password" class="form-input" />
                </div>
            </div>

            <button type="submit" id="reg-stu-btn" class="btn btn-primary w-full py-2.5 text-sm shadow-[var(--shadow-brand)]">
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
            <p class="text-xs text-[var(--text-faint)]">Create a company portal to post drives.</p>
        </div>

        <form id="company-reg-form" class="space-y-4" onsubmit="handleRegistrationSubmit(event,'company')">
            <div class="form-group">
                <label class="form-label">Company Legal Name</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon-left"><i data-lucide="building-2" class="w-4 h-4"></i></span>
                    <input type="text" id="reg-comp-name" required placeholder="e.g. Google Inc." class="form-input" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group">
                    <label class="form-label">HR Contact Name</label>
                    <input type="text" id="reg-comp-hr" required placeholder="Sundar HR" class="form-input" />
                </div>
                <div class="form-group">
                    <label class="form-label">HR Official Email</label>
                    <input type="email" id="reg-comp-email" required placeholder="hr@company.com" class="form-input" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group">
                    <label class="form-label">Corporate Website</label>
                    <input type="url" id="reg-comp-web" required placeholder="https://careers.google.com" class="form-input" />
                </div>
                <div class="form-group">
                    <label class="form-label">Location / City</label>
                    <input type="text" id="reg-comp-loc" placeholder="Bangalore, Karnataka" class="form-input" />
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon-left"><i data-lucide="phone" class="w-4 h-4"></i></span>
                    <input type="text" id="reg-comp-phone" placeholder="9876543210" class="form-input" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" id="reg-comp-pass" required placeholder="Min 8 characters" class="form-input" />
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" id="reg-comp-confirm" required placeholder="Confirm Password" class="form-input" />
                </div>
            </div>

            <button type="submit" id="reg-comp-btn" class="btn btn-primary w-full py-2.5 text-sm shadow-[var(--shadow-brand)]">
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
            <p class="text-xs text-[var(--text-faint)]">Enter your registered email address and we'll generate a password reset link.</p>
        </div>

        <form id="forgot-form" class="space-y-4" onsubmit="handleForgotSubmit(event)">
            <div class="form-group">
                <label class="form-label">Registered Email Address</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon-left"><i data-lucide="mail" class="w-4 h-4"></i></span>
                    <input type="email" id="forgot-email" required placeholder="user@college.edu" class="form-input" autocomplete="email" />
                </div>
            </div>

            <button type="submit" id="forgot-btn" class="btn btn-primary w-full py-2.5 text-sm mt-2 shadow-[var(--shadow-brand)]">
                <i data-lucide="send" class="w-4 h-4"></i> Generate Reset Link
            </button>
        </form>

        <div class="auth-footer">
            <p class="text-center text-xs text-[var(--text-faint)] mt-6">
                Remembered your password?&nbsp;
                <a href="#" data-view="login" class="text-[var(--primary)] font-semibold hover:underline">Back to sign in</a>
            </p>
        </div>
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
            student: "student@tpms.com",
            company: "company@tpms.com",
            admin:   "admin@tpms.com"
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
    const btn = document.getElementById("login-btn-submit");
    const role = document.getElementById("login-role").value;
    const email = document.getElementById("login-email").value;
    const password = document.getElementById("login-password").value;

    if (btn) btn.disabled = true;

    const formData = new FormData();
    formData.append("action", "login");
    formData.append("email", email);
    formData.append("password", password);
    formData.append("role", role);

    fetch("api/auth.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            app.showToast("Signed in successfully!", "success");
            // Set the bridge and reload/redirect so index.php picks it up securely
            window.location.href = "index.php";
        } else {
            app.showToast(data.message || "Invalid email or password.", "danger");
            if (btn) btn.disabled = false;
        }
    })
    .catch(err => {
        app.showToast("Failed to connect to the server.", "danger");
        if (btn) btn.disabled = false;
    });
}

function handleRegistrationSubmit(e, role) {
    e.preventDefault();

    if (role === "student") {
        const btn = document.getElementById("reg-stu-btn");
        const name = document.getElementById("reg-stu-name").value;
        const email = document.getElementById("reg-stu-email").value;
        const password = document.getElementById("reg-stu-pass").value;
        const confirmPass = document.getElementById("reg-stu-confirm").value;
        const roll = document.getElementById("reg-stu-roll").value;
        const branch = document.getElementById("reg-stu-branch").value;
        const year = document.getElementById("reg-stu-year").value;
        const phone = document.getElementById("reg-stu-phone").value;
        const cgpa = document.getElementById("reg-stu-cgpa").value;

        if (password !== confirmPass) {
            app.showToast("Passwords do not match.", "warning");
            return;
        }

        if (btn) btn.disabled = true;

        const fd = new FormData();
        fd.append("action", "register");
        fd.append("type", "student");
        fd.append("name", name);
        fd.append("email", email);
        fd.append("password", password);
        fd.append("confirm_password", confirmPass);
        fd.append("roll_number", roll);
        fd.append("branch", branch);
        fd.append("year", year);
        fd.append("phone", phone);
        fd.append("cgpa", cgpa);

        fetch("api/auth.php", {
            method: "POST",
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                app.showToast(data.message || "Student registered successfully!", "success");
                app.navigate("login");
            } else {
                app.showToast(data.message || "Registration failed.", "danger");
                if (btn) btn.disabled = false;
            }
        })
        .catch(() => {
            app.showToast("Error connecting to the server.", "danger");
            if (btn) btn.disabled = false;
        });

    } else if (role === "company") {
        const btn = document.getElementById("reg-comp-btn");
        const name = document.getElementById("reg-comp-name").value;
        const hr = document.getElementById("reg-comp-hr").value;
        const email = document.getElementById("reg-comp-email").value;
        const web = document.getElementById("reg-comp-web").value;
        const loc = document.getElementById("reg-comp-loc").value;
        const phone = document.getElementById("reg-comp-phone").value;
        const password = document.getElementById("reg-comp-pass").value;
        const confirmPass = document.getElementById("reg-comp-confirm").value;

        if (password !== confirmPass) {
            app.showToast("Passwords do not match.", "warning");
            return;
        }

        if (btn) btn.disabled = true;

        const fd = new FormData();
        fd.append("action", "register");
        fd.append("type", "company");
        fd.append("company_name", name);
        fd.append("hr_name", hr);
        fd.append("email", email);
        fd.append("website", web);
        fd.append("location", loc);
        fd.append("phone", phone);
        fd.append("password", password);
        fd.append("confirm_password", confirmPass);

        fetch("api/auth.php", {
            method: "POST",
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                app.showToast(data.message || "Company registered successfully!", "success");
                app.navigate("login");
            } else {
                app.showToast(data.message || "Registration failed.", "danger");
                if (btn) btn.disabled = false;
            }
        })
        .catch(() => {
            app.showToast("Error connecting to server.", "danger");
            if (btn) btn.disabled = false;
        });
    }
}

function handleForgotSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById("forgot-btn");
    const email = document.getElementById("forgot-email").value;

    if (btn) btn.disabled = true;

    const fd = new FormData();
    fd.append("action", "forgot_password");
    fd.append("email", email);

    fetch("api/auth.php", {
        method: "POST",
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            app.showToast(data.message || "Reset link generated successfully.", "success");
            if (data.dev_link) {
                // If in development/testing mode, print/alert reset link for convenience
                console.log("Dev reset link:", data.dev_link);
                // Also append temporary dev alert below button
                let container = document.getElementById("forgot-form");
                let devAlert = document.createElement("div");
                devAlert.className = "mt-4 p-2 bg-amber-500/10 border border-amber-500/30 rounded text-[11px] text-amber-500 break-all text-center";
                devAlert.innerHTML = `<strong>Dev Mode Link:</strong><br><a href="${data.dev_link}" style="text-decoration:underline">${data.dev_link}</a>`;
                container.appendChild(devAlert);
            }
        } else {
            app.showToast(data.message || "Error generating reset link.", "danger");
            if (btn) btn.disabled = false;
        }
    })
    .catch(() => {
        app.showToast("Server error requesting recovery.", "danger");
        if (btn) btn.disabled = false;
    });
}

function simulateSocialSSO(provider) {
    app.showToast(`Connecting via ${provider} SSO…`, "info");
    setTimeout(() => {
        // Find default credentials
        const credentials = {
            student: { email: "student@tpms.com", pass: "Student@123" }
        };
        const cred = credentials.student;
        const fd = new FormData();
        fd.append("action", "login");
        fd.append("email", cred.email);
        fd.append("password", cred.pass);
        fd.append("role", "student");
        fetch("api/auth.php", { method: "POST", body: fd })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = "index.php";
                }
            });
    }, 900);
}

// Global exports
window.setActiveLoginRole      = setActiveLoginRole;
window.togglePasswordVisibility= togglePasswordVisibility;
window.handleLoginSubmit       = handleLoginSubmit;
window.handleRegistrationSubmit= handleRegistrationSubmit;
window.handleForgotSubmit      = handleForgotSubmit;
window.simulateSocialSSO       = simulateSocialSSO;
