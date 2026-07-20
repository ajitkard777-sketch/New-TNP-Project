// Company / Recruiter Dashboard Module — TPMS v2.0 Premium
function renderCompanyDashboard(container) {
    const isDark     = document.documentElement.classList.contains("dark");
    const labelColor = isDark ? "#94a3b8" : "#64748b";
    const gridColor  = isDark ? "#1e293b" : "#f1f5f9";

    const companyName     = "Google";
    const activeJobs      = mockData.jobs.filter(j => j.companyName.toLowerCase() === companyName.toLowerCase());
    const applications    = mockData.applications.filter(a => a.companyName.toLowerCase() === companyName.toLowerCase());
    const shortlistedCount= applications.filter(a => ["Shortlisted","Interview","Selected"].includes(a.status)).length;
    const selectedCount   = applications.filter(a => a.status === "Selected").length;

    container.innerHTML = `
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-[var(--text-primary)]">Recruiter Workspace</h1>
                <p class="text-sm text-[var(--text-faint)] mt-0.5">
                    <span class="font-semibold text-[var(--primary)]">${companyName}</span>
                    — Manage openings, review candidates, and publish offers.
                </p>
            </div>
            <button onclick="openPostJobModal()"
                    class="btn btn-primary shadow-[var(--shadow-brand)]">
                <i data-lucide="plus" class="w-4 h-4"></i> Post New Opening
            </button>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 stagger-children">
            ${[
                { label: "Active Listings",      value: activeJobs.length,   icon: "briefcase",   color: "var(--primary)",  bg: "var(--primary-light)"        },
                { label: "Total Applications",   value: applications.length,  icon: "users",       color: "var(--secondary)",bg: "rgba(124,58,237,0.10)"       },
                { label: "Shortlisted",          value: shortlistedCount,     icon: "check-square",color: "var(--accent)",   bg: "rgba(6,182,212,0.10)"        },
                { label: "Hired",                value: selectedCount,        icon: "award",       color: "var(--success)",  bg: "var(--success-light)"        }
            ].map(k => `
                <div class="dashboard-card p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-[var(--text-faint)]">${k.label}</p>
                            <h3 class="text-2xl font-extrabold mt-1.5 text-[var(--text-primary)] tracking-tight">${k.value}</h3>
                        </div>
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:${k.bg};">
                            <i data-lucide="${k.icon}" class="w-5 h-5" style="color:${k.color};"></i>
                        </div>
                    </div>
                </div>
            `).join("")}
        </div>

        <!-- Charts + Listings -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <!-- Chart -->
            <div class="dashboard-card p-5 lg:col-span-8">
                <div class="mb-4">
                    <h3 class="font-bold text-sm text-[var(--text-primary)]">Branch-wise Application Distribution</h3>
                    <p class="text-xs text-[var(--text-faint)] mt-0.5">Applicant count by academic department</p>
                </div>
                <div id="chart-company-branch-distribution"></div>
            </div>

            <!-- Active Listings -->
            <div class="dashboard-card p-5 lg:col-span-4 flex flex-col">
                <div class="flex items-center justify-between mb-3 pb-3 border-b border-[var(--border-color)]">
                    <h3 class="font-bold text-sm text-[var(--text-primary)]">Active Postings</h3>
                    <span class="badge badge-primary">${activeJobs.length}</span>
                </div>

                <div class="flex-1 space-y-3 overflow-y-auto max-h-[200px] pr-1">
                    ${activeJobs.length === 0
                        ? `<div class="empty-state py-8">
                               <div class="empty-state-icon"><i data-lucide="briefcase" class="w-5 h-5"></i></div>
                               <p class="text-xs font-semibold text-[var(--text-secondary)]">No active jobs</p>
                           </div>`
                        : activeJobs.map(j => `
                            <div class="flex items-center justify-between gap-3 text-xs py-2.5 border-b border-[var(--border-color)] last:border-0">
                                <div class="min-w-0">
                                    <p class="font-bold text-[var(--text-primary)] truncate">${j.title}</p>
                                    <p class="text-[10px] text-[var(--text-faint)] mt-0.5">${j.package} · ${j.deadline}</p>
                                </div>
                                <span class="badge badge-success text-[10px] flex-shrink-0">Active</span>
                            </div>
                        `).join("")}
                </div>

                <div class="mt-4 pt-3 border-t border-[var(--border-color)]">
                    <button onclick="openPostJobModal()"
                            class="btn btn-primary w-full btn-sm shadow-[var(--shadow-brand)]">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> New Listing
                    </button>
                </div>
            </div>
        </div>

        <!-- Pending Applications Table -->
        <div class="dashboard-card overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-[var(--border-color)]">
                <div>
                    <h3 class="font-bold text-sm text-[var(--text-primary)]">Pending Student Applications</h3>
                    <p class="text-xs text-[var(--text-faint)] mt-0.5">Review, shortlist, or hire candidates</p>
                </div>
                <a href="#" data-view="candidates" class="btn btn-secondary btn-sm">
                    View All <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Branch</th>
                            <th>CGPA</th>
                            <th>Job Role</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${applications.length === 0
                            ? `<tr><td colspan="6">
                                   <div class="empty-state">
                                       <div class="empty-state-icon"><i data-lucide="users" class="w-6 h-6"></i></div>
                                       <p class="font-semibold text-sm text-[var(--text-secondary)]">No applications yet</p>
                                   </div>
                               </td></tr>`
                            : applications.slice(0, 4).map(app => {
                                const badges = {
                                    Selected:     "badge-success",
                                    Applied:      "badge-info",
                                    "Under Review": "badge-warning",
                                    Shortlisted:  "badge-purple",
                                    Interview:    "badge-cyan"
                                };
                                return `
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-lg gradient-bg flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                                    ${app.studentName.charAt(0)}
                                                </div>
                                                <span class="font-bold text-[var(--text-primary)]">${app.studentName}</span>
                                            </div>
                                        </td>
                                        <td class="text-[var(--text-muted)]">${app.studentBranch}</td>
                                        <td><span class="font-bold text-[var(--primary)]">${app.studentCGPA.toFixed(2)}</span></td>
                                        <td class="text-[var(--text-muted)]">${app.jobTitle}</td>
                                        <td><span class="badge ${badges[app.status] || 'badge-muted'}">${app.status}</span></td>
                                        <td>
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button onclick="downloadStudentResume('${app.studentResume}')"
                                                        class="btn btn-icon btn-ghost tooltip" data-tooltip="Resume">
                                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                                </button>
                                                ${app.status === 'Applied' || app.status === 'Under Review'
                                                    ? `<button onclick="shortlistCandidate('${app.id}','Shortlisted')"
                                                               class="btn btn-primary btn-sm">Shortlist</button>`
                                                    : app.status === 'Shortlisted' || app.status === 'Interview'
                                                    ? `<button onclick="shortlistCandidate('${app.id}','Selected')"
                                                               class="btn btn-sm" style="background:var(--success);color:#fff;">Hire</button>`
                                                    : `<span class="badge badge-success">Hired</span>`
                                                }
                                            </div>
                                        </td>
                                    </tr>`;
                            }).join("")}
                    </tbody>
                </table>
            </div>
        </div>
    `;

    if (window.lucide) window.lucide.createIcons();

    setTimeout(() => {
        const branches = ["CSE","IT","ECE","Mech"];
        const counts   = [
            applications.filter(a => a.studentBranch.includes("Computer")).length + 2,
            applications.filter(a => a.studentBranch.includes("Information")).length + 1,
            applications.filter(a => a.studentBranch.includes("Electronics")).length + 1,
            applications.filter(a => a.studentBranch.includes("Mechanical")).length
        ];

        try {
            new ApexCharts(document.querySelector("#chart-company-branch-distribution"), {
                series: [{ name: "Applications", data: counts }],
                chart: { type: "bar", height: 240, toolbar: { show: false }, foreColor: labelColor, background: "transparent" },
                colors: ["#4F46E5"],
                plotOptions: { bar: { columnWidth: "35%", borderRadius: 6 } },
                xaxis: { categories: branches, axisBorder: { show: false }, axisTicks: { show: false } },
                grid: { borderColor: gridColor, strokeDashArray: 4 },
                dataLabels: { enabled: false },
                tooltip: { theme: isDark ? "dark" : "light" }
            }).render();
        } catch(e) { console.warn("Company branch chart error:", e); }
    }, 150);
}

/* ──────────────────────────────────────────────────
   CANDIDATES MANAGEMENT
   ────────────────────────────────────────────────── */
function renderCandidatesModule(container) {
    const companyName  = "Google";
    const applications = mockData.applications.filter(a => a.companyName.toLowerCase() === companyName.toLowerCase());

    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <button onclick="app.navigate('dashboard')" class="btn btn-ghost btn-sm text-[var(--primary)] mb-1">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Dashboard
                </button>
                <h1 class="text-2xl font-extrabold text-[var(--text-primary)]">Applied Candidates</h1>
                <p class="text-sm text-[var(--text-faint)] mt-0.5">Review profiles, check CGPAs, and manage candidate statuses.</p>
            </div>
        </div>

        <div class="dashboard-card overflow-hidden">
            <!-- Filters -->
            <div class="flex flex-col md:flex-row gap-3 p-5 border-b border-[var(--border-color)]">
                <div class="search-wrapper flex-1">
                    <span class="search-icon"><i data-lucide="search" class="w-4 h-4"></i></span>
                    <input type="text" id="cand-search" placeholder="Search by name, branch, or role…"
                           oninput="filterCandidates()" class="search-input text-xs" />
                </div>
                <select id="cand-status-filter" onchange="filterCandidates()"
                        class="form-select w-full md:w-44 text-xs py-2">
                    <option value="">All Statuses</option>
                    <option value="Applied">Applied</option>
                    <option value="Under Review">Under Review</option>
                    <option value="Shortlisted">Shortlisted</option>
                    <option value="Interview">Interview</option>
                    <option value="Selected">Selected</option>
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Branch</th>
                            <th>CGPA</th>
                            <th>Applied Job</th>
                            <th>Applied Date</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="candidates-table-body">
                        <!-- Populated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    `;

    populateCandidatesTable(applications);
    if (window.lucide) window.lucide.createIcons();
}

function populateCandidatesTable(list) {
    const tbody = document.getElementById("candidates-table-body");
    if (!tbody) return;

    if (list.length === 0) {
        tbody.innerHTML = `
            <tr><td colspan="7">
                <div class="empty-state">
                    <div class="empty-state-icon"><i data-lucide="users" class="w-6 h-6"></i></div>
                    <p class="font-semibold text-sm text-[var(--text-secondary)]">No candidates found</p>
                    <p class="text-xs text-[var(--text-faint)]">Try adjusting your search or filters.</p>
                </div>
            </td></tr>`;
        if (window.lucide) window.lucide.createIcons();
        return;
    }

    const badges = {
        Selected:      "badge-success",
        Applied:       "badge-info",
        "Under Review":"badge-warning",
        Shortlisted:   "badge-purple",
        Interview:     "badge-cyan"
    };

    tbody.innerHTML = list.map(app => `
        <tr>
            <td>
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg gradient-bg flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        ${app.studentName.charAt(0)}
                    </div>
                    <span class="font-bold text-[var(--text-primary)]">${app.studentName}</span>
                </div>
            </td>
            <td class="text-[var(--text-muted)]">${app.studentBranch}</td>
            <td><span class="font-bold text-[var(--primary)]">${app.studentCGPA.toFixed(2)}</span></td>
            <td class="text-[var(--text-muted)]">${app.jobTitle}</td>
            <td class="text-[var(--text-faint)] text-xs">${app.appliedDate}</td>
            <td><span class="badge ${badges[app.status] || 'badge-muted'}">${app.status}</span></td>
            <td>
                <div class="flex items-center justify-end gap-1.5">
                    <button onclick="downloadStudentResume('${app.studentResume}')"
                            class="btn btn-icon btn-ghost tooltip" data-tooltip="Resume">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    </button>
                    ${app.status === 'Applied' || app.status === 'Under Review'
                        ? `<button onclick="shortlistCandidate('${app.id}','Shortlisted')"
                                   class="btn btn-primary btn-sm">Shortlist</button>`
                        : app.status === 'Shortlisted' || app.status === 'Interview'
                        ? `<button onclick="shortlistCandidate('${app.id}','Selected')"
                                   class="btn btn-sm shadow" style="background:var(--success);color:#fff;">Hire</button>`
                        : `<span class="badge badge-success">Completed</span>`
                    }
                </div>
            </td>
        </tr>
    `).join("");

    if (window.lucide) window.lucide.createIcons();
}

function filterCandidates() {
    const query   = document.getElementById("cand-search")?.value.toLowerCase() || "";
    const status  = document.getElementById("cand-status-filter")?.value || "";
    // Use real company name from session, not hardcoded
    const cName   = mockData.session.companyName || "";

    const filtered = mockData.applications.filter(a => {
        if (cName && a.companyName.toLowerCase() !== cName.toLowerCase()) return false;
        const matchQ = (a.studentName || "").toLowerCase().includes(query) ||
                       (a.studentBranch || "").toLowerCase().includes(query) ||
                       (a.jobTitle || "").toLowerCase().includes(query);
        return matchQ && (!status || a.status === status);
    });

    populateCandidatesTable(filtered);
}

async function shortlistCandidate(appId, newStatus) {
    const appObj = mockData.applications.find(a => a.id === appId);
    if (!appObj) return;

    // Call real API
    const res = await ApiService.updateAppStatus(appId, newStatus);
    if (!res.success) {
        app.showToast(res.message || "Failed to update status.", "danger");
        return;
    }

    // Update local state
    appObj.status = newStatus;
    const tEntry = appObj.timeline?.find(t => t.stage === newStatus);
    if (tEntry) {
        tEntry.done = true;
        tEntry.date = new Date().toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
    }

    if (newStatus === "Selected") {
        const stu = mockData.students.find(s => s.name === appObj.studentName);
        if (stu) stu.status = "Placed";
    }

    app.showToast(`${appObj.studentName} updated to: ${newStatus}`, "success");

    const content = document.getElementById("main-content");
    if (document.getElementById("cand-search")) renderCandidatesModule(content);
    else renderCompanyDashboard(content);
}

function openPostJobModal() {
    openAdminModal("add-job");
    setTimeout(() => {
        const select = document.getElementById("modal-job-comp");
        if (select) {
            select.value    = "Google";
            select.disabled = true;
        }
    }, 100);
}

// Global exports
window.openPostJobModal    = openPostJobModal;
window.shortlistCandidate  = shortlistCandidate;
window.filterCandidates    = filterCandidates;
window.populateCandidatesTable = populateCandidatesTable;
