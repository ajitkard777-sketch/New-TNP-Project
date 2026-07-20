// Admin Dashboard & Management Views — TPMS v2.0 Premium
function renderAdminDashboard(container) {
    const isDark    = document.documentElement.classList.contains("dark");
    const gridColor = isDark ? "#1e293b" : "#f1f5f9";
    const labelColor= isDark ? "#94a3b8" : "#64748b";

    container.innerHTML = `
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-[var(--text-primary)]">Admin Dashboard</h1>
                <p class="text-sm text-[var(--text-faint)] mt-0.5">Campus Training &amp; Placement central control panel</p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                ${[
                    { action: "add-company",        icon: "building-2",    label: "Add Company",    color: "var(--secondary)" },
                    { action: "add-job",             icon: "briefcase",     label: "Add Job",        color: "var(--primary)"   },
                    { action: "schedule-interview",  icon: "calendar",      label: "Schedule Interview",color: "var(--accent)"  },
                    { action: "create-training",     icon: "graduation-cap",label: "Create Training",color: "var(--success)"  },
                    { action: "publish-results",     icon: "award",         label: "Publish Results",color: "var(--warning)"  }
                ].map(a => `
                    <button onclick="openAdminModal('${a.action}')"
                            class="btn btn-secondary btn-sm">
                        <i data-lucide="${a.icon}" class="w-3.5 h-3.5" style="color:${a.color};"></i>
                        ${a.label}
                    </button>
                `).join("")}
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 stagger-children">
            ${[
                { label: "Total Students",      value: "5,420",  sub: "+4.2% YoY",    icon: "users",       color: "var(--primary)",   accent: "rgba(79,70,229,0.10)"  },
                { label: "Recruiting Companies",value: "182",    sub: "+14 this month",icon: "building-2",  color: "var(--secondary)", accent: "rgba(124,58,237,0.10)" },
                { label: "Active Job Listings", value: "48",     sub: "8 closing soon",icon: "briefcase",   color: "var(--accent)",    accent: "rgba(6,182,212,0.10)"  },
                { label: "Overall Placed",      value: "95.4%",  sub: "Exceeds target",icon: "award",       color: "var(--success)",   accent: "rgba(16,185,129,0.10)" }
            ].map(k => `
                <div class="dashboard-card p-5" style="--card-accent: linear-gradient(90deg, ${k.color}, ${k.color}88);">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-[var(--text-faint)]">${k.label}</p>
                            <h3 class="text-2xl font-extrabold mt-1.5 text-[var(--text-primary)] tracking-tight">${k.value}</h3>
                            <p class="text-[11px] mt-1 font-medium" style="color:${k.color};">${k.sub}</p>
                        </div>
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:${k.accent};">
                            <i data-lucide="${k.icon}" class="w-5 h-5" style="color:${k.color};"></i>
                        </div>
                    </div>
                </div>
            `).join("")}
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <!-- Placement Trend Line Chart -->
            <div class="dashboard-card p-5 lg:col-span-8">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="font-bold text-sm text-[var(--text-primary)]">Placement Rate Trend</h3>
                        <p class="text-xs text-[var(--text-faint)] mt-0.5">Historical performance over 6 academic years</p>
                    </div>
                    <span class="badge badge-primary">6-Year View</span>
                </div>
                <div id="chart-placement-trend"></div>
            </div>

            <!-- Donut Company Hiring -->
            <div class="dashboard-card p-5 lg:col-span-4">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="font-bold text-sm text-[var(--text-primary)]">Placement Share</h3>
                        <p class="text-xs text-[var(--text-faint)] mt-0.5">By company</p>
                    </div>
                </div>
                <div id="chart-company-hiring" class="flex justify-center"></div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="dashboard-card p-5">
                <div class="mb-5">
                    <h3 class="font-bold text-sm text-[var(--text-primary)]">Department-wise Placement Stats</h3>
                    <p class="text-xs text-[var(--text-faint)] mt-0.5">Placed vs total batch strength</p>
                </div>
                <div id="chart-dept-placements"></div>
            </div>

            <div class="dashboard-card p-5">
                <div class="mb-5">
                    <h3 class="font-bold text-sm text-[var(--text-primary)]">Monthly Platform Registrations</h3>
                    <p class="text-xs text-[var(--text-faint)] mt-0.5">Students and companies onboarded</p>
                </div>
                <div id="chart-registrations"></div>
            </div>
        </div>

        <!-- Activities + Quick Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

            <!-- Recent Activity Feed -->
            <div class="dashboard-card p-5 lg:col-span-7">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-sm text-[var(--text-primary)]">Recent Campus Activities</h3>
                        <p class="text-xs text-[var(--text-faint)] mt-0.5">Live feed of portal events</p>
                    </div>
                    <button onclick="app.showToast('Activities refreshed!', 'success')"
                            class="btn btn-ghost btn-sm text-[var(--primary)]">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Refresh
                    </button>
                </div>
                <div id="activities-list" class="space-y-1 max-h-[340px] overflow-y-auto -mr-2 pr-2"></div>
            </div>

            <!-- Quick Operational Stats -->
            <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                ${[
                    { label: "Training Programs", value: "12", sub: "4 Active",        subColor: "var(--primary)",   icon: "graduation-cap", color: "var(--primary)",   bg: "var(--primary-light)"  },
                    { label: "Interviews Today",  value: "8",  sub: "28 this week",    subColor: "var(--warning)",   icon: "calendar-check", color: "var(--warning)",   bg: "var(--warning-light)"  },
                    { label: "Pending Approvals", value: "14", sub: "3 urgent",        subColor: "var(--danger)",    icon: "clock",          color: "var(--danger)",    bg: "var(--danger-light)"   },
                    { label: "Higher Studies Apps",value:"154", sub: "65 Accepted",    subColor: "var(--success)",   icon: "globe",          color: "var(--success)",   bg: "var(--success-light)"  }
                ].map(s => `
                    <div class="dashboard-card p-5 flex flex-col justify-between gap-3">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-[var(--text-faint)]">${s.label}</p>
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:${s.bg};">
                                <i data-lucide="${s.icon}" class="w-4 h-4" style="color:${s.color};"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-3xl font-extrabold text-[var(--text-primary)] tracking-tight">${s.value}</h3>
                            <p class="text-xs mt-1 font-medium" style="color:${s.subColor};">${s.sub}</p>
                        </div>
                    </div>
                `).join("")}
            </div>
        </div>
    `;

    // Populate activities
    const actList = document.getElementById("activities-list");
    if (actList) {
        const iconColors = {
            placement:    "var(--success)",
            job:          "var(--primary)",
            interview:    "var(--accent)",
            registration: "var(--secondary)",
            training:     "var(--warning)"
        };
        const iconBgs = {
            placement:    "var(--success-light)",
            job:          "var(--primary-light)",
            interview:    "rgba(6,182,212,0.10)",
            registration: "rgba(124,58,237,0.10)",
            training:     "var(--warning-light)"
        };
        const icons = {
            award: "award", briefcase: "briefcase", calendar: "calendar",
            "user-plus": "user-plus", "book-open": "book-open"
        };

        actList.innerHTML = mockData.activities.map(act => `
            <div class="activity-item">
                <div class="activity-icon" style="background:${iconBgs[act.type] || 'var(--border-color)'};">
                    <i data-lucide="${icons[act.icon] || 'info'}" class="w-4 h-4" style="color:${iconColors[act.type] || 'var(--text-muted)'};"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-[var(--text-secondary)] leading-relaxed truncate">${act.text}</p>
                    <p class="text-[11px] text-[var(--text-faint)] mt-0.5">${act.time}</p>
                </div>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[var(--text-faint)] flex-shrink-0"></i>
            </div>
        `).join("");
    }

    if (window.lucide) window.lucide.createIcons();

    // Initialize charts — wrapped in try/catch to guard against CDN failures
    setTimeout(() => {
        const trendData = mockData.analytics.placementTrend;
        try {
            new ApexCharts(document.querySelector("#chart-placement-trend"), {
                series: [{ name: "Placement Rate (%)", data: trendData.rates }],
                chart: { type: "area", height: 240, toolbar: { show: false }, zoom: { enabled: false }, foreColor: labelColor, background: "transparent" },
                stroke: { curve: "smooth", width: 3, colors: ["#4F46E5"] },
                colors: ["#4F46E5"],
                fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, colorStops: [{ offset: 0, color: "#4F46E5", opacity: 0.35 }, { offset: 100, color: "#4F46E5", opacity: 0 }] } },
                markers: { size: 5, colors: ["#4F46E5"], strokeColors: isDark ? "#0F1629" : "#fff", strokeWidth: 2 },
                xaxis: { categories: trendData.years, axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: { min: 70, max: 100, labels: { formatter: v => v + "%" } },
                grid: { borderColor: gridColor, strokeDashArray: 4 },
                tooltip: { theme: isDark ? "dark" : "light" }
            }).render();
        } catch(e) { console.warn("Admin trend chart error:", e); }

        const deptData = mockData.analytics.departmentPlacements;
        try {
            new ApexCharts(document.querySelector("#chart-dept-placements"), {
                series: [
                    { name: "Placed",   data: deptData.placedCount  },
                    { name: "Strength", data: deptData.totalCount   }
                ],
                chart: { type: "bar", height: 240, toolbar: { show: false }, foreColor: labelColor, background: "transparent" },
                plotOptions: { bar: { horizontal: false, columnWidth: "45%", borderRadius: 6 } },
                stroke: { show: true, width: 2, colors: ["transparent"] },
                colors: ["#4F46E5", isDark ? "#1E2D4A" : "#E2E8F0"],
                xaxis: { categories: deptData.branches, axisBorder: { show: false }, axisTicks: { show: false } },
                grid: { borderColor: gridColor, strokeDashArray: 4 },
                fill: { opacity: 1 },
                legend: { show: true, position: "top", fontSize: "12px" },
                tooltip: { theme: isDark ? "dark" : "light" }
            }).render();
        } catch(e) { console.warn("Admin dept chart error:", e); }

        const compData = mockData.analytics.companyHiring;
        try {
            new ApexCharts(document.querySelector("#chart-company-hiring"), {
                series: compData.counts,
                chart: { type: "donut", height: 240, foreColor: labelColor, background: "transparent" },
                labels: compData.names,
                colors: ["#4F46E5","#7C3AED","#06B6D4","#10B981","#F59E0B","#94A3B8"],
                legend: { position: "bottom", fontSize: "11px", markers: { width: 8, height: 8, radius: 4 } },
                stroke: { show: false },
                dataLabels: { enabled: false },
                plotOptions: { pie: { donut: { size: "72%", labels: { show: true, total: { show: true, label: "Total", fontSize: "11px", fontWeight: 600, color: labelColor } } } } },
                tooltip: { theme: isDark ? "dark" : "light" }
            }).render();
        } catch(e) { console.warn("Admin company hiring chart error:", e); }

        const regData = mockData.analytics.monthlyRegistrations;
        try {
            new ApexCharts(document.querySelector("#chart-registrations"), {
                series: [
                    { name: "Students",  data: regData.studentRegistrations  },
                    { name: "Companies", data: regData.companyRegistrations }
                ],
                chart: { type: "area", height: 240, toolbar: { show: false }, foreColor: labelColor, background: "transparent" },
                colors: ["#06B6D4","#F59E0B"],
                xaxis: { categories: regData.months, axisBorder: { show: false }, axisTicks: { show: false } },
                grid: { borderColor: gridColor, strokeDashArray: 4 },
                dataLabels: { enabled: false },
                stroke: { curve: "smooth", width: 2 },
                fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02 } },
                legend: { show: true, position: "top", fontSize: "12px" },
                tooltip: { theme: isDark ? "dark" : "light" }
            }).render();
        } catch(e) { console.warn("Admin registrations chart error:", e); }
    }, 200);
}

/* ──────────────────────────────────────────────────
   STUDENTS MANAGEMENT TABLE
   ────────────────────────────────────────────────── */
function renderStudentsManagement(container) {
    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-[var(--text-primary)]">Registered Students</h1>
                <p class="text-sm text-[var(--text-faint)] mt-0.5">Browse academic profiles, placement statuses, and resume files.</p>
            </div>
        </div>

        <div class="dashboard-card overflow-hidden">
            <!-- Search & Filters -->
            <div class="flex flex-col md:flex-row gap-3 p-5 border-b border-[var(--border-color)]">
                <div class="search-wrapper flex-1">
                    <span class="search-icon"><i data-lucide="search" class="w-4 h-4"></i></span>
                    <input type="text" id="student-search" placeholder="Search by name, ID, or department…"
                           oninput="filterStudentsTable()" class="search-input text-xs" />
                </div>
                <select id="student-branch-filter" onchange="filterStudentsTable()"
                        class="form-select w-full md:w-44 text-xs py-2">
                    <option value="">All Branches</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Electronics &amp; Comm">Electronics &amp; Comm</option>
                    <option value="Mechanical Eng">Mechanical Eng</option>
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Branch</th>
                            <th>CGPA</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="students-table-body"></tbody>
                </table>
            </div>
        </div>
    `;

    populateStudentsTable(mockData.students);
    if (window.lucide) window.lucide.createIcons();
}

function populateStudentsTable(list) {
    const tbody = document.getElementById("students-table-body");
    if (!tbody) return;

    if (list.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i data-lucide="users" class="w-6 h-6"></i></div>
                        <p class="font-semibold text-sm text-[var(--text-secondary)]">No students found</p>
                        <p class="text-xs text-[var(--text-faint)]">Try adjusting your search or filter criteria.</p>
                    </div>
                </td>
            </tr>`;
        if (window.lucide) window.lucide.createIcons();
        return;
    }

    const badges = {
        Placed:       "badge-success",
        "In Progress": "badge-primary",
        "Not Interested": "badge-muted"
    };

    tbody.innerHTML = list.map(stu => `
        <tr>
            <td><span class="font-mono text-xs font-semibold text-[var(--text-muted)]">${stu.id}</span></td>
            <td>
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg gradient-bg flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        ${stu.name.charAt(0)}
                    </div>
                    <span class="font-semibold text-[var(--text-primary)]">${stu.name}</span>
                </div>
            </td>
            <td class="text-[var(--text-muted)]">${stu.branch}</td>
            <td><span class="font-bold text-[var(--primary)]">${stu.cgpa.toFixed(2)}</span></td>
            <td><span class="badge ${badges[stu.status] || 'badge-muted'}">${stu.status}</span></td>
            <td>
                <div class="flex items-center justify-end gap-1.5">
                    <button onclick="downloadStudentResume('${stu.resume}')"
                            class="btn btn-icon btn-ghost tooltip" data-tooltip="Download Resume">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    </button>
                    <button onclick="deleteStudent('${stu.id}')"
                            class="btn btn-icon btn-ghost tooltip text-[var(--danger)] hover:bg-[var(--danger-light)]" data-tooltip="Delete Student">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join("");

    if (window.lucide) window.lucide.createIcons();
}

function filterStudentsTable() {
    const query  = document.getElementById("student-search")?.value.toLowerCase() || "";
    const branch = document.getElementById("student-branch-filter")?.value || "";
    const filtered = mockData.students.filter(stu => {
        const matchQ = stu.name.toLowerCase().includes(query) || stu.id.toLowerCase().includes(query) || stu.branch.toLowerCase().includes(query);
        const matchB = !branch || stu.branch.includes(branch);
        return matchQ && matchB;
    });
    populateStudentsTable(filtered);
}

function deleteStudent(id) {
    if (!confirm(`Are you sure you want to remove student ${id} from the registry? This action cannot be undone.`)) return;
    mockData.students = mockData.students.filter(s => s.id !== id);
    app.showToast(`Student ${id} removed from registry.`, "danger");
    filterStudentsTable();
}

function downloadStudentResume(filename) {
    app.showToast(`Downloading: ${filename}`, "success");
}

/* ──────────────────────────────────────────────────
   COMPANIES MANAGEMENT TABLE
   ────────────────────────────────────────────────── */
function renderCompaniesManagement(container) {
    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-[var(--text-primary)]">Recruiting Partners</h1>
                <p class="text-sm text-[var(--text-faint)] mt-0.5">Manage registered corporate entities and contact directories.</p>
            </div>
            <button onclick="openAdminModal('add-company')" class="btn btn-primary btn-sm shadow-[var(--shadow-brand)]">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Partner
            </button>
        </div>

        <div class="dashboard-card overflow-hidden">
            <div class="p-5 border-b border-[var(--border-color)]">
                <div class="search-wrapper">
                    <span class="search-icon"><i data-lucide="search" class="w-4 h-4"></i></span>
                    <input type="text" id="company-search" placeholder="Search companies by name or sector…"
                           oninput="filterCompaniesTable()" class="search-input text-xs" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Industry</th>
                            <th>Registered</th>
                            <th>Jobs</th>
                            <th>HR Contact</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="companies-table-body"></tbody>
                </table>
            </div>
        </div>
    `;

    populateCompaniesTable(mockData.companies);
    if (window.lucide) window.lucide.createIcons();
}

function populateCompaniesTable(list) {
    const tbody = document.getElementById("companies-table-body");
    if (!tbody) return;

    if (list.length === 0) {
        tbody.innerHTML = `
            <tr><td colspan="6">
                <div class="empty-state">
                    <div class="empty-state-icon"><i data-lucide="building-2" class="w-6 h-6"></i></div>
                    <p class="font-semibold text-sm text-[var(--text-secondary)]">No companies found</p>
                </div>
            </td></tr>`;
        if (window.lucide) window.lucide.createIcons();
        return;
    }

    tbody.innerHTML = list.map(comp => `
        <tr>
            <td>
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-[var(--border-color)] flex items-center justify-center flex-shrink-0">
                        <i data-lucide="building-2" class="w-3.5 h-3.5 text-[var(--text-faint)]"></i>
                    </div>
                    <a href="${comp.website}" target="_blank"
                       class="font-bold text-[var(--primary)] hover:underline">${comp.name}</a>
                </div>
            </td>
            <td class="text-[var(--text-muted)]">${comp.industry}</td>
            <td class="text-[var(--text-muted)]">${comp.registeredDate}</td>
            <td><span class="badge badge-primary">${comp.jobCount} Jobs</span></td>
            <td class="text-[var(--text-muted)] text-xs">${comp.contact}</td>
            <td>
                <div class="flex items-center justify-end gap-1.5">
                    <button onclick="deleteCompany('${comp.id}')"
                            class="btn btn-icon btn-ghost tooltip text-[var(--danger)] hover:bg-[var(--danger-light)]" data-tooltip="Remove Partner">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join("");

    if (window.lucide) window.lucide.createIcons();
}

function filterCompaniesTable() {
    const query = document.getElementById("company-search")?.value.toLowerCase() || "";
    const filtered = mockData.companies.filter(c =>
        c.name.toLowerCase().includes(query) || c.industry.toLowerCase().includes(query) || c.id.toLowerCase().includes(query)
    );
    populateCompaniesTable(filtered);
}

function deleteCompany(id) {
    if (!confirm(`Are you sure you want to remove company ${id}? This action cannot be undone.`)) return;
    mockData.companies = mockData.companies.filter(c => c.id !== id);
    app.showToast(`Company ${id} removed.`, "danger");
    filterCompaniesTable();
}

/* ──────────────────────────────────────────────────
   ADMIN MODAL SYSTEM
   ────────────────────────────────────────────────── */
function openAdminModal(actionType) {
    const overlay  = document.getElementById("admin-modal-overlay");
    const body     = document.getElementById("admin-modal-body");
    const titleEl  = document.getElementById("admin-modal-title");
    if (!overlay || !body || !titleEl) return;

    overlay.classList.remove("hidden");

    const inputCls  = "form-input text-sm";
    const selectCls = "form-select text-sm";
    const groupCls  = "form-group";
    const cancelBtn = `<button type="button" onclick="closeAdminModal()" class="btn btn-secondary btn-sm">Cancel</button>`;
    const submitBtn = (label) => `<button type="submit" class="btn btn-primary btn-sm shadow-[var(--shadow-brand)]">${label}</button>`;

    const forms = {
        "add-company": {
            title: "Add Recruiting Partner",
            html: `
                <form onsubmit="handleAdminModalSubmit(event,'add-company')" class="space-y-4">
                    <div class="${groupCls}"><label class="form-label">Company Name</label>
                        <input type="text" id="modal-comp-name" required placeholder="Intel Corp" class="${inputCls}" /></div>
                    <div class="${groupCls}"><label class="form-label">Corporate Website</label>
                        <input type="url" id="modal-comp-web" required placeholder="https://intel.com" class="${inputCls}" /></div>
                    <div class="${groupCls}"><label class="form-label">Industry Segment</label>
                        <input type="text" id="modal-comp-ind" required placeholder="Hardware & Semiconductors" class="${inputCls}" /></div>
                    <div class="${groupCls}"><label class="form-label">HR Contact Email</label>
                        <input type="email" id="modal-comp-con" required placeholder="recruiting@intel.com" class="${inputCls}" /></div>
                    <div class="flex justify-end gap-2 pt-2">${cancelBtn}${submitBtn("Add Partner")}</div>
                </form>`
        },
        "add-job": {
            title: "Publish New Job Opening",
            html: `
                <form onsubmit="handleAdminModalSubmit(event,'add-job')" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="${groupCls}"><label class="form-label">Company</label>
                            <select id="modal-job-comp" class="${selectCls}">
                                ${mockData.companies.map(c => `<option value="${c.name}">${c.name}</option>`).join("")}
                            </select></div>
                        <div class="${groupCls}"><label class="form-label">Job Title</label>
                            <input type="text" id="modal-job-title" required placeholder="Security Specialist" class="${inputCls}" /></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="${groupCls}"><label class="form-label">Package (CTC)</label>
                            <input type="text" id="modal-job-ctc" required placeholder="14.0 LPA" class="${inputCls}" /></div>
                        <div class="${groupCls}"><label class="form-label">Location</label>
                            <input type="text" id="modal-job-loc" required placeholder="Bangalore" class="${inputCls}" /></div>
                    </div>
                    <div class="${groupCls}"><label class="form-label">Skills (comma separated)</label>
                        <input type="text" id="modal-job-skills" required placeholder="Python, Security, Wireshark" class="${inputCls}" /></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="${groupCls}"><label class="form-label">Min CGPA</label>
                            <input type="number" step="0.1" id="modal-job-cgpa" required placeholder="7.0" class="${inputCls}" /></div>
                        <div class="${groupCls}"><label class="form-label">Deadline</label>
                            <input type="date" id="modal-job-date" required class="${inputCls}" /></div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">${cancelBtn}${submitBtn("Publish Job")}</div>
                </form>`
        },
        "schedule-interview": {
            title: "Schedule Interview Slot",
            html: `
                <form onsubmit="handleAdminModalSubmit(event,'schedule-interview')" class="space-y-4">
                    <div class="${groupCls}"><label class="form-label">Candidate Student</label>
                        <select id="modal-int-student" class="${selectCls}">
                            ${mockData.students.map(s => `<option value="${s.name} (${s.branch})">${s.name} (${s.branch})</option>`).join("")}
                        </select></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="${groupCls}"><label class="form-label">Hiring Company</label>
                            <select id="modal-int-comp" class="${selectCls}">
                                ${mockData.companies.map(c => `<option value="${c.name}">${c.name}</option>`).join("")}
                            </select></div>
                        <div class="${groupCls}"><label class="form-label">Date &amp; Time</label>
                            <input type="datetime-local" id="modal-int-time" required class="${inputCls}" /></div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">${cancelBtn}${submitBtn("Schedule")}</div>
                </form>`
        },
        "create-training": {
            title: "Launch Training Program",
            html: `
                <form onsubmit="handleAdminModalSubmit(event,'create-training')" class="space-y-4">
                    <div class="${groupCls}"><label class="form-label">Training Title</label>
                        <input type="text" id="modal-train-title" required placeholder="Cloud Architecture Foundations" class="${inputCls}" /></div>
                    <div class="${groupCls}"><label class="form-label">Lead Trainer</label>
                        <input type="text" id="modal-train-trainer" required placeholder="Prof. Jane Doe" class="${inputCls}" /></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="${groupCls}"><label class="form-label">Duration</label>
                            <input type="text" id="modal-train-dur" required placeholder="24 Hours" class="${inputCls}" /></div>
                        <div class="${groupCls}"><label class="form-label">Schedule</label>
                            <input type="text" id="modal-train-date" required placeholder="Aug 02 - Aug 20" class="${inputCls}" /></div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">${cancelBtn}${submitBtn("Create Program")}</div>
                </form>`
        },
        "publish-results": {
            title: "Publish Placement Results",
            html: `
                <form onsubmit="handleAdminModalSubmit(event,'publish-results')" class="space-y-4">
                    <div class="${groupCls}"><label class="form-label">Select Job Drive</label>
                        <select id="modal-res-job" class="${selectCls}">
                            ${mockData.jobs.map(j => `<option value="${j.companyName} - ${j.title}">${j.companyName} — ${j.title}</option>`).join("")}
                        </select></div>
                    <div class="${groupCls}"><label class="form-label">Selected Candidate</label>
                        <select id="modal-res-stud" class="${selectCls}">
                            ${mockData.students.map(s => `<option value="${s.name} (${s.branch})">${s.name} (${s.branch})</option>`).join("")}
                        </select></div>
                    <div class="${groupCls}"><label class="form-label">Offered Package (LPA)</label>
                        <input type="text" id="modal-res-lpa" required placeholder="18.5 LPA" class="${inputCls}" /></div>
                    <div class="flex justify-end gap-2 pt-2">${cancelBtn}${submitBtn("Publish Result")}</div>
                </form>`
        }
    };

    const form = forms[actionType] || { title: "Action", html: "<p>Loading…</p>" };
    titleEl.textContent = form.title;
    body.innerHTML      = form.html;
    if (window.lucide) window.lucide.createIcons();
}

function closeAdminModal() {
    document.getElementById("admin-modal-overlay")?.classList.add("hidden");
}

function handleAdminModalSubmit(e, actionType) {
    e.preventDefault();
    closeAdminModal();

    if (actionType === "add-company") {
        const name = document.getElementById("modal-comp-name").value;
        const web  = document.getElementById("modal-comp-web").value;
        const ind  = document.getElementById("modal-comp-ind").value;
        const con  = document.getElementById("modal-comp-con").value;
        // Use timestamp to avoid ID collision after delete+add cycles
        const newId = `COMP${Date.now().toString().slice(-6)}`;
        mockData.companies.push({ id: newId, name, website: web, industry: ind, registeredDate: new Date().toISOString().split("T")[0], jobCount: 0, contact: con });
        mockData.activities.unshift({ id: mockData.activities.length + 1, type: "registration", text: `New recruiter registered: ${name}`, time: "Just now", icon: "user-plus" });
        app.showToast(`Partner "${name}" registered successfully!`, "success");
        // Re-render companies table if currently visible, otherwise go to dashboard
        if (document.getElementById("companies-table-body")) renderCompaniesManagement(document.getElementById("main-content"));
        else app.navigate("companies");
    }
    else if (actionType === "add-job") {
        const comp  = document.getElementById("modal-job-comp").value;
        const title = document.getElementById("modal-job-title").value;
        const ctc   = document.getElementById("modal-job-ctc").value;
        const loc   = document.getElementById("modal-job-loc").value;
        const skills= document.getElementById("modal-job-skills").value;
        const cgpa  = parseFloat(document.getElementById("modal-job-cgpa").value);
        const date  = document.getElementById("modal-job-date").value;
        const newId = `JOB${Date.now().toString().slice(-6)}`;
        mockData.jobs.push({ id: newId, companyId: "COMP999", companyName: comp, companyLogo: "https://upload.wikimedia.org/wikipedia/commons/1/18/Markenlogo_Intel.svg", title, package: ctc, location: loc, eligibility: `B.Tech, CGPA >= ${cgpa.toFixed(1)}`, skills: skills.split(",").map(s => s.trim()), deadline: date, status: "Active", description: "Opening published via administrator panel." });
        mockData.activities.unshift({ id: mockData.activities.length + 1, type: "job", text: `${comp} published: ${title} — CTC ${ctc}`, time: "Just now", icon: "briefcase" });
        app.showToast(`Job listing published for ${comp}!`, "success");
        app.navigate("dashboard");
    }
    else if (actionType === "schedule-interview") {
        const stud = document.getElementById("modal-int-student").value;
        const comp = document.getElementById("modal-int-comp").value;
        const time = new Date(document.getElementById("modal-int-time").value).toLocaleString();
        mockData.activities.unshift({ id: mockData.activities.length + 1, type: "interview", text: `Interview: ${stud} with ${comp} on ${time}`, time: "Just now", icon: "calendar" });
        app.showToast(`Interview scheduled for ${stud} with ${comp}`, "success");
        app.navigate("dashboard");
    }
    else if (actionType === "create-training") {
        const title   = document.getElementById("modal-train-title").value;
        const trainer = document.getElementById("modal-train-trainer").value;
        const dur     = document.getElementById("modal-train-dur").value;
        const date    = document.getElementById("modal-train-date").value;
        const newId   = `TRN${Date.now().toString().slice(-6)}`;
        mockData.training.push({ id: newId, title, trainer, date, duration: dur, status: "Upcoming", description: "Program launched by administrator." });
        mockData.activities.unshift({ id: mockData.activities.length + 1, type: "training", text: `New Training: ${title}`, time: "Just now", icon: "book-open" });
        app.showToast(`Training "${title}" created!`, "success");
        app.navigate("dashboard");
    }
    else if (actionType === "publish-results") {
        const job  = document.getElementById("modal-res-job").value;
        const stud = document.getElementById("modal-res-stud").value;
        const lpa  = document.getElementById("modal-res-lpa").value;
        mockData.activities.unshift({ id: mockData.activities.length + 1, type: "placement", text: `${stud} selected at ${job} — CTC ${lpa}`, time: "Just now", icon: "award" });
        app.showToast(`Result published: ${stud} — ${lpa}!`, "success");
        app.navigate("dashboard");
    }
}

// Global exports
window.openAdminModal         = openAdminModal;
window.closeAdminModal        = closeAdminModal;
window.handleAdminModalSubmit = handleAdminModalSubmit;
window.filterStudentsTable    = filterStudentsTable;
window.filterCompaniesTable   = filterCompaniesTable;
window.deleteStudent          = deleteStudent;
window.deleteCompany          = deleteCompany;
window.downloadStudentResume  = downloadStudentResume;
// Note: openJobDetailsModal is defined in modules.js — do not re-define here
