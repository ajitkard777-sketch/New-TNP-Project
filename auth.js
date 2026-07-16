// Authentication Views Module (Login, Register Student, Register Recruiter, Forgot Password)
function renderAuth(container, view) {
    // Custom auth page styling
    container.className = "min-h-[calc(100vh-80px)] flex items-center justify-center p-4 animate-fade-in relative overflow-hidden";
    
    // Inject floating shapes
    container.innerHTML = `
        <div class="glass-shape glass-shape-1"></div>
        <div class="glass-shape glass-shape-2"></div>
        
        <div class="w-full max-w-md bg-glass border-glass p-8 rounded-[28px] shadow-2xl relative z-15 backdrop-blur-3xl animate-scale-in text-slate-800 dark:text-slate-100">
            <div id="auth-form-container"></div>
        </div>
    `;

    const formWrapper = document.getElementById("auth-form-container");
    if (!formWrapper) return;

    if (view === "login") {
        renderLoginForm(formWrapper);
    } else if (view === "register-student") {
        renderStudentRegisterForm(formWrapper);
    } else if (view === "register-company") {
        renderCompanyRegisterForm(formWrapper);
    } else if (view === "forgot-password") {
        renderForgotForm(formWrapper);
    }

    if (window.lucide) {
        window.lucide.createIcons();
    }
}

// 1. Render Login Card
function renderLoginForm(wrapper) {
    wrapper.innerHTML = `
        <div class="text-center mb-8 space-y-2">
            <div class="w-12 h-12 rounded-2xl gradient-bg flex items-center justify-center text-white font-bold mx-auto shadow-md">T</div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Welcome Back</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Select your workspace and enter credentials to sign in.</p>
        </div>

        <!-- Role Tabs Selector -->
        <div class="flex p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl mb-6 border border-slate-200/55 dark:border-slate-850">
            <button onclick="setActiveLoginRole('student')" id="tab-student" class="flex-1 py-2 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-200 transition bg-white dark:bg-slate-700 shadow-sm">
                Student
            </button>
            <button onclick="setActiveLoginRole('company')" id="tab-company" class="flex-1 py-2 text-xs font-semibold rounded-lg text-slate-500 dark:text-slate-400 transition hover:text-slate-800 dark:hover:text-slate-200">
                Company
            </button>
            <button onclick="setActiveLoginRole('admin')" id="tab-admin" class="flex-1 py-2 text-xs font-semibold rounded-lg text-slate-500 dark:text-slate-400 transition hover:text-slate-800 dark:hover:text-slate-200">
                Admin
            </button>
        </div>

        <!-- Hidden input for selected role -->
        <input type="hidden" id="login-role" value="student" />

        <form id="login-form" class="space-y-4" onsubmit="handleLoginSubmit(event)">
            <!-- Email -->
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </span>
                    <input type="email" id="login-email" required placeholder="aarav.sharma@college.edu" class="w-full pl-10 pr-4 py-2.5 bg-white/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-purple-500 transition-all" />
                </div>
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <div class="flex justify-between items-center">
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Password</label>
                    <a href="#" data-view="forgot-password" class="text-xs font-semibold text-blue-600 dark:text-purple-400 hover:underline">Forgot password?</a>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </span>
                    <input type="password" id="login-password" required placeholder="••••••••" class="w-full pl-10 pr-10 py-2.5 bg-white/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-purple-500 transition-all" />
                    <button type="button" onclick="togglePasswordVisibility('login-password')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="eye" id="login-password-eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Remember me -->
            <div class="flex items-center justify-between py-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" checked class="w-4 h-4 rounded text-blue-600 dark:text-purple-500 border-slate-350 focus:ring-blue-500 dark:focus:ring-purple-500" />
                    <span class="text-xs text-slate-500 dark:text-slate-400 select-none">Remember Me</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-2.5 rounded-xl font-bold text-white gradient-bg hover:opacity-95 transition shadow-lg shadow-blue-500/20 text-sm">
                Login to Dashboard
            </button>
        </form>

        <div class="relative my-6 text-center">
            <span class="absolute inset-x-0 top-1/2 border-b border-slate-200 dark:border-slate-800/80 -z-10"></span>
            <span class="px-3 bg-white dark:bg-[#151F32] text-xs text-slate-400 font-medium">Or continue with SSO</span>
        </div>

        <!-- Social login -->
        <div class="grid grid-cols-2 gap-3 mb-6">
            <button onclick="simulateSocialSSO('Google')" class="flex items-center justify-center gap-2 py-2 px-4 bg-white/40 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold transition hover:bg-slate-50 dark:hover:bg-slate-850">
                <img src="https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg" class="w-4 h-4" /> Google
            </button>
            <button onclick="simulateSocialSSO('Microsoft')" class="flex items-center justify-center gap-2 py-2 px-4 bg-white/40 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold transition hover:bg-slate-50 dark:hover:bg-slate-850">
                <img src="https://upload.wikimedia.org/wikipedia/commons/9/96/Microsoft_logo_%282012%29.svg" class="w-4 h-4" /> Azure AD
            </button>
        </div>

        <div class="text-center text-xs text-slate-500 dark:text-slate-400">
            New here? Register as: 
            <a href="#" data-view="register-student" class="text-blue-600 dark:text-purple-400 font-semibold hover:underline">Student</a>
            &bull;
            <a href="#" data-view="register-company" class="text-blue-600 dark:text-purple-400 font-semibold hover:underline">Recruiter</a>
        </div>
    `;
}

// 2. Render Student Register Wizard
function renderStudentRegisterForm(wrapper) {
    wrapper.innerHTML = `
        <div class="text-center mb-6 space-y-2">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Student Registration</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Join the college recruitment pipeline in 3 simple steps.</p>
        </div>

        <!-- Registration Steps Indicator -->
        <div class="flex items-center justify-between mb-8 px-4">
            <div class="flex flex-col items-center gap-1">
                <div class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center border-2 border-blue-600 shadow-md shadow-blue-500/20">1</div>
                <span class="text-[10px] font-semibold text-blue-600 dark:text-blue-400">Account</span>
            </div>
            <div class="flex-1 h-0.5 bg-slate-200 dark:bg-slate-800 mx-2 mb-4"></div>
            <div class="flex flex-col items-center gap-1">
                <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-semibold text-xs flex items-center justify-center border-2 border-slate-200 dark:border-slate-800">2</div>
                <span class="text-[10px] font-medium text-slate-400">Academic</span>
            </div>
            <div class="flex-1 h-0.5 bg-slate-200 dark:bg-slate-800 mx-2 mb-4"></div>
            <div class="flex flex-col items-center gap-1">
                <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-semibold text-xs flex items-center justify-center border-2 border-slate-200 dark:border-slate-800">3</div>
                <span class="text-[10px] font-medium text-slate-400">Resume</span>
            </div>
        </div>

        <form id="student-reg-form" class="space-y-4" onsubmit="handleRegistrationSubmit(event, 'student')">
            <div class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Full Name</label>
                        <input type="text" required placeholder="Aarav Sharma" class="w-full px-3 py-2 bg-white/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Roll Number</label>
                        <input type="text" required placeholder="CSE2601" class="w-full px-3 py-2 bg-white/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400">University Email</label>
                    <input type="email" required placeholder="aarav.sharma@college.edu" class="w-full px-3 py-2 bg-white/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Branch</label>
                        <select class="w-full px-3 py-2 bg-white/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option>Computer Science</option>
                            <option>Information Tech</option>
                            <option>Electronics & Comm</option>
                            <option>Mechanical Eng</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400">CGPA</label>
                        <input type="number" step="0.01" min="0" max="10" required placeholder="9.24" class="w-full px-3 py-2 bg-white/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Password</label>
                    <input type="password" required placeholder="Create Password" class="w-full px-3 py-2 bg-white/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
            </div>

            <button type="submit" class="w-full py-2.5 rounded-xl font-bold text-white gradient-bg hover:opacity-95 transition shadow-md shadow-blue-500/20 text-sm mt-4">
                Submit Registration
            </button>
        </form>

        <div class="text-center text-xs text-slate-500 dark:text-slate-400 mt-6">
            Already registered? 
            <a href="#" data-view="login" class="text-blue-600 dark:text-purple-400 font-semibold hover:underline">Login here</a>
        </div>
    `;
}

// 3. Render Recruiter Register Wizard
function renderCompanyRegisterForm(wrapper) {
    wrapper.innerHTML = `
        <div class="text-center mb-6 space-y-2">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Recruiter Registration</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Create a company portal to schedule campus drives.</p>
        </div>

        <form id="company-reg-form" class="space-y-4" onsubmit="handleRegistrationSubmit(event, 'company')">
            <div class="space-y-3">
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Company Legal Name</label>
                    <input type="text" required placeholder="e.g. Google Inc" class="w-full px-3 py-2 bg-white/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Corporate Website</label>
                    <input type="url" required placeholder="https://careers.google.com" class="w-full px-3 py-2 bg-white/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Industry Sector</label>
                    <select class="w-full px-3 py-2 bg-white/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option>Product Technology</option>
                        <option>IT & Consulting Services</option>
                        <option>Finance & Banking</option>
                        <option>Core Engineering</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400">HR Contact Name</label>
                        <input type="text" required placeholder="Sundar HR" class="w-full px-3 py-2 bg-white/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400">HR Email Address</label>
                        <input type="email" required placeholder="recruitment@google.com" class="w-full px-3 py-2 bg-white/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Password</label>
                    <input type="password" required placeholder="Password" class="w-full px-3 py-2 bg-white/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
            </div>

            <button type="submit" class="w-full py-2.5 rounded-xl font-bold text-white gradient-bg hover:opacity-95 transition shadow-md shadow-blue-500/20 text-sm mt-4">
                Register Company
            </button>
        </form>

        <div class="text-center text-xs text-slate-500 dark:text-slate-400 mt-6">
            Already registered? 
            <a href="#" data-view="login" class="text-blue-600 dark:text-purple-400 font-semibold hover:underline">Login here</a>
        </div>
    `;
}

// 4. Render Forgot Password
function renderForgotForm(wrapper) {
    wrapper.innerHTML = `
        <div class="text-center mb-6 space-y-2">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Recover Password</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Enter your university/corporate email and we will send a password reset OTP.</p>
        </div>

        <form id="forgot-form" class="space-y-4" onsubmit="handleForgotSubmit(event)">
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Registered Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </span>
                    <input type="email" required placeholder="e.g. user@college.edu" class="w-full pl-10 pr-4 py-2.5 bg-white/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" />
                </div>
            </div>

            <button type="submit" class="w-full py-2.5 rounded-xl font-bold text-white gradient-bg hover:opacity-95 transition shadow-md shadow-blue-500/20 text-sm mt-2">
                Send Recovery OTP
            </button>
        </form>

        <div class="text-center text-xs text-slate-500 dark:text-slate-400 mt-6">
            Remembered password? 
            <a href="#" data-view="login" class="text-blue-600 dark:text-purple-400 font-semibold hover:underline">Back to Login</a>
        </div>
    `;
}

// Interactivity Handlers
function setActiveLoginRole(role) {
    document.getElementById("login-role").value = role;
    
    // Toggle active tab style classes
    const tabs = {
        student: document.getElementById("tab-student"),
        company: document.getElementById("tab-company"),
        admin: document.getElementById("tab-admin")
    };

    Object.keys(tabs).forEach(key => {
        if (!tabs[key]) return;
        if (key === role) {
            tabs[key].className = "flex-1 py-2 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-200 transition bg-white dark:bg-slate-700 shadow-sm border-glass";
        } else {
            tabs[key].className = "flex-1 py-2 text-xs font-semibold rounded-lg text-slate-500 dark:text-slate-400 transition hover:text-slate-800 dark:hover:text-slate-200";
        }
    });

    // Populate placeholder email helper
    const emailField = document.getElementById("login-email");
    if (emailField) {
        if (role === "student") emailField.placeholder = "aarav.sharma@college.edu";
        if (role === "company") emailField.placeholder = "recruitment@google.com";
        if (role === "admin") emailField.placeholder = "admin@tpms.com";
    }
}

function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(`${inputId}-eye`);
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
    const email = document.getElementById("login-email").value;
    
    // Auto-login simulations
    app.login(role);
}

function handleRegistrationSubmit(e, role) {
    e.preventDefault();
    app.showToast(`Registration request received. Admin approval pending.`, "warning");
    app.navigate("login");
}

function handleForgotSubmit(e) {
    e.preventDefault();
    app.showToast("Recovery OTP sent successfully to your email inbox.", "success");
    app.navigate("login");
}

function simulateSocialSSO(provider) {
    app.showToast(`Connecting SSO credentials via ${provider}...`, "info");
    setTimeout(() => {
        // Log in as student as a default demo fallback
        app.login("student");
    }, 800);
}

// Global exposure
window.setActiveLoginRole = setActiveLoginRole;
window.togglePasswordVisibility = togglePasswordVisibility;
window.handleLoginSubmit = handleLoginSubmit;
window.handleRegistrationSubmit = handleRegistrationSubmit;
window.handleForgotSubmit = handleForgotSubmit;
window.simulateSocialSSO = simulateSocialSSO;
