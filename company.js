// Recruiter/Company Dashboard Module
function renderCompanyDashboard(container) {
    const isDark = document.documentElement.classList.contains("dark");
    const labelColor = isDark ? "#94a3b8" : "#64748b";
    const gridColor = isDark ? "#1e293b" : "#f1f5f9";

    // Recruiter-specific calculations
    const companyName = "Google"; // Logged in recruiter simulation
    const activeJobs = mockData.jobs.filter(j => j.companyName.toLowerCase() === companyName.toLowerCase());
    const applications = mockData.applications.filter(app => app.companyName.toLowerCase() === companyName.toLowerCase());
    
    const shortlistedCount = applications.filter(app => ["Shortlisted", "Interview", "Selected"].includes(app.status)).length;
    const selectedCount = applications.filter(app => app.status === "Selected").length;

    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Recruiter Workspace: ${companyName}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Post job listings, manage applications, and conduct candidates shortlisting.</p>
            </div>
            <button onclick="openPostJobModal()" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-blue-500/10 hover:opacity-95 transition flex items-center gap-1.5">
                <i data-lucide="plus" class="w-4 h-4"></i> Post New Opening
            </button>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="dashboard-card p-5 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Active Listings</span>
                    <h3 class="text-2xl font-bold mt-1 text-slate-800 dark:text-white">${activeJobs.length}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                    <i data-lucide="briefcase" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="dashboard-card p-5 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Total Applications</span>
                    <h3 class="text-2xl font-bold mt-1 text-slate-800 dark:text-white">${applications.length}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="dashboard-card p-5 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Shortlisted Candidates</span>
                    <h3 class="text-2xl font-bold mt-1 text-slate-800 dark:text-white">${shortlistedCount}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
                    <i data-lucide="check-square" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="dashboard-card p-5 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Hired Students</span>
                    <h3 class="text-2xl font-bold mt-1 text-slate-800 dark:text-white">${selectedCount}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                    <i data-lucide="award" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Analytics Chart -->
            <div class="dashboard-card p-5 lg:col-span-8 flex flex-col justify-between">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Branch-wise Application Distribution</h3>
                <div id="chart-company-branch-distribution" class="w-full"></div>
            </div>

            <!-- Manage Active Listings List -->
            <div class="dashboard-card p-5 lg:col-span-4 flex flex-col justify-between">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800/80 pb-2 mb-3">Your active postings</h3>
                <div class="space-y-3.5 max-h-[220px] overflow-y-auto pr-1">
                    ${activeJobs.length === 0 
                        ? `<p class="text-xs text-slate-400 py-6 text-center">No active jobs posted.</p>`
                        : activeJobs.map(j => `
                            <div class="flex items-center justify-between gap-3 text-xs border-b border-slate-50 dark:border-slate-800 pb-2 last:border-b-0">
                                <div>
                                    <p class="font-bold text-slate-700 dark:text-slate-300 truncate">${j.title}</p>
                                    <span class="text-[10px] text-slate-455">CTC: ${j.package} &bull; Deadline: ${j.deadline}</span>
                                </div>
                                <span class="px-2 py-0.5 text-[9px] font-bold bg-emerald-500/10 text-emerald-500 rounded-full">Active</span>
                            </div>
                        `).join("")
                    }
                </div>
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80">
                    <button onclick="openPostJobModal()" class="w-full py-2 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-850 transition">
                        Create New Listing
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Applications Table widget -->
        <div class="dashboard-card p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Pending Student Applications</h3>
                <a href="#" data-view="candidates" class="text-xs font-semibold text-blue-500 hover:underline">View All Candidates</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-2.5 px-4">Student</th>
                            <th class="py-2.5 px-4">Branch</th>
                            <th class="py-2.5 px-4">CGPA</th>
                            <th class="py-2.5 px-4">Job Role</th>
                            <th class="py-2.5 px-4">Status</th>
                            <th class="py-2.5 px-4 text-right">Resume / Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50 text-xs font-medium text-slate-600 dark:text-slate-350">
                        ${applications.length === 0 
                            ? `<tr><td colspan="6" class="text-center py-6 text-slate-450">No applications received yet.</td></tr>`
                            : applications.slice(0, 3).map(app => {
                                const badges = {
                                    Selected: "bg-emerald-500/10 text-emerald-500",
                                    Applied: "bg-blue-500/10 text-blue-500",
                                    "Under Review": "bg-yellow-500/10 text-yellow-500",
                                    Shortlisted: "bg-purple-500/10 text-purple-500",
                                    Interview: "bg-cyan-500/10 text-cyan-500"
                                };
                                return `
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10 transition-colors">
                                        <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-350">${app.studentName}</td>
                                        <td class="py-3 px-4">${app.studentBranch}</td>
                                        <td class="py-3 px-4">${app.studentCGPA.toFixed(2)}</td>
                                        <td class="py-3 px-4">${app.jobTitle}</td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${badges[app.status] || 'bg-slate-150 text-slate-500'}">${app.status}</span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button onclick="downloadStudentResume('${app.studentResume}')" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-400 hover:text-blue-500 transition tooltip" data-tooltip="Download Resume">
                                                    <i data-lucide="download" class="w-4 h-4"></i>
                                                </button>
                                                ${app.status === 'Applied' || app.status === 'Under Review'
                                                    ? `<button onclick="shortlistCandidate('${app.id}', 'Shortlisted')" class="px-2 py-1 bg-blue-500 text-white rounded-lg text-[10px] font-bold hover:bg-blue-600 transition">Shortlist</button>`
                                                    : app.status === 'Shortlisted' || app.status === 'Interview'
                                                    ? `<button onclick="shortlistCandidate('${app.id}', 'Selected')" class="px-2 py-1 bg-emerald-500 text-white rounded-lg text-[10px] font-bold hover:bg-emerald-600 transition">Offer Select</button>`
                                                    : `<span class="text-[10px] text-slate-400 font-semibold px-2 py-1">Evaluated</span>`
                                                }
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            }).join("")
                        }
                    </tbody>
                </table>
            </div>
        </div>
    `;

    if (window.lucide) window.lucide.createIcons();

    // Initialize Company analytics branch application counts
    setTimeout(() => {
        // Collect statistics
        const branches = ["CSE", "IT", "ECE", "Mech"];
        const counts = [
            applications.filter(a => a.studentBranch.includes("Computer")).length + 2,
            applications.filter(a => a.studentBranch.includes("Information")).length + 1,
            applications.filter(a => a.studentBranch.includes("Electronics")).length + 1,
            applications.filter(a => a.studentBranch.includes("Mechanical")).length
        ];

        new ApexCharts(document.querySelector("#chart-company-branch-distribution"), {
            series: [{ name: "Applications", data: counts }],
            chart: { type: "bar", height: 240, toolbar: { show: false }, foreColor: labelColor },
            colors: ["#2563EB"],
            plotOptions: { bar: { columnWidth: "35%", borderRadius: 6 } },
            xaxis: { categories: branches },
            grid: { borderColor: gridColor },
            tooltip: { theme: isDark ? "dark" : "light" }
        }).render();
    }, 150);
}

// Render eligible students applied management page
function renderCandidatesModule(container) {
    const companyName = "Google";
    const applications = mockData.applications.filter(app => app.companyName.toLowerCase() === companyName.toLowerCase());

    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <a href="#" data-view="dashboard" class="text-xs font-semibold text-blue-500 hover:underline flex items-center gap-1 mb-1"><i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to Dashboard</a>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Applied Candidates Directory</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Review student records, check CGPA profiles, and shortlist candidates.</p>
            </div>
        </div>

        <div class="dashboard-card p-5 space-y-4">
            <!-- Search & Filters -->
            <div class="flex flex-col md:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" id="cand-search" placeholder="Search candidates by name, branch..." oninput="filterCandidates()" class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div class="w-full md:w-48">
                    <select id="cand-status-filter" onchange="filterCandidates()" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 dark:text-slate-350">
                        <option value="">All Statuses</option>
                        <option value="Applied">Applied</option>
                        <option value="Shortlisted">Shortlisted</option>
                        <option value="Selected">Selected</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Student Name</th>
                            <th class="py-3 px-4">Branch</th>
                            <th class="py-3 px-4">CGPA</th>
                            <th class="py-3 px-4">Applied Job</th>
                            <th class="py-3 px-4">Applied Date</th>
                            <th class="py-3 px-4">Current Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="candidates-table-body" class="divide-y divide-slate-100 dark:divide-slate-800/50 text-xs font-medium text-slate-600 dark:text-slate-350">
                        <!-- Loaded dynamically -->
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
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-8 text-slate-400">No candidate applications found</td></tr>`;
        return;
    }

    tbody.innerHTML = list.map(app => {
        const badges = {
            Selected: "bg-emerald-500/10 text-emerald-500",
            Applied: "bg-blue-500/10 text-blue-500",
            "Under Review": "bg-yellow-500/10 text-yellow-500",
            Shortlisted: "bg-purple-500/10 text-purple-500",
            Interview: "bg-cyan-500/10 text-cyan-500"
        };

        return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10 transition-colors">
                <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-slate-350">${app.studentName}</td>
                <td class="py-3.5 px-4">${app.studentBranch}</td>
                <td class="py-3.5 px-4 font-semibold text-blue-600 dark:text-blue-450">${app.studentCGPA.toFixed(2)}</td>
                <td class="py-3.5 px-4">${app.jobTitle}</td>
                <td class="py-3.5 px-4 text-slate-400">${app.appliedDate}</td>
                <td class="py-3.5 px-4">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold ${badges[app.status] || 'bg-slate-100 text-slate-500'}">${app.status}</span>
                </td>
                <td class="py-3.5 px-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <button onclick="downloadStudentResume('${app.studentResume}')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-400 hover:text-blue-500 transition tooltip" data-tooltip="Download Resume">
                            <i data-lucide="download" class="w-4 h-4"></i>
                        </button>
                        ${app.status === 'Applied' || app.status === 'Under Review'
                            ? `<button onclick="shortlistCandidate('${app.id}', 'Shortlisted')" class="px-2 py-1.5 bg-blue-500 text-white rounded-xl text-[10px] font-bold hover:bg-blue-600 transition">Shortlist</button>`
                            : app.status === 'Shortlisted' || app.status === 'Interview'
                            ? `<button onclick="shortlistCandidate('${app.id}', 'Selected')" class="px-2 py-1.5 bg-emerald-500 text-white rounded-xl text-[10px] font-bold hover:bg-emerald-600 transition">Offer Hire</button>`
                            : `<span class="text-[10px] text-slate-450 font-semibold px-2 py-1 bg-slate-100 dark:bg-slate-900 rounded-lg">Completed</span>`
                        }
                    </div>
                </td>
            </tr>
        `;
    }).join("");

    if (window.lucide) window.lucide.createIcons();
}

function filterCandidates() {
    const query = document.getElementById("cand-search")?.value.toLowerCase() || "";
    const status = document.getElementById("cand-status-filter")?.value || "";
    const companyName = "Google";

    const filtered = mockData.applications.filter(app => {
        if (app.companyName.toLowerCase() !== companyName.toLowerCase()) return false;
        
        const matchesQuery = app.studentName.toLowerCase().includes(query) || 
                             app.studentBranch.toLowerCase().includes(query) || 
                             app.jobTitle.toLowerCase().includes(query);
        const matchesStatus = !status || app.status === status;
        
        return matchesQuery && matchesStatus;
    });

    populateCandidatesTable(filtered);
}

function shortlistCandidate(appId, newStatus) {
    const appObj = mockData.applications.find(a => a.id === appId);
    if (!appObj) return;

    appObj.status = newStatus;
    
    // Add to timeline
    const timelineEntry = appObj.timeline.find(t => t.stage === newStatus);
    if (timelineEntry) {
        timelineEntry.done = true;
        timelineEntry.date = new Date().toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
    }

    // Sync student status if selected
    if (newStatus === "Selected") {
        const student = mockData.students.find(s => s.name === appObj.studentName);
        if (student) student.status = "Placed";
        if (appObj.studentId === mockData.currentStudent.id) {
            mockData.currentStudent.placementStatus = "Placed";
        }
    }

    app.showToast(`Candidate ${appObj.studentName} status updated to: ${newStatus}`, "success");
    
    // Reload candidates table or dashboard
    if (mockData.session.role === 'company') {
        const content = document.getElementById("main-content");
        if (document.getElementById("cand-search")) {
            renderCandidatesModule(content);
        } else {
            renderCompanyDashboard(content);
        }
    }
}

// Open Recruiter's Job Posting Modal
function openPostJobModal() {
    openAdminModal("add-job"); // reuse admin form mapping but with default comp value preset
    const select = document.getElementById("modal-job-comp");
    if (select) {
        select.value = "Google";
        select.disabled = true; // restrict to recruiter's corporate scope
    }
}

// Global exports
window.openPostJobModal = openPostJobModal;
window.shortlistCandidate = shortlistCandidate;
window.filterCandidates = filterCandidates;
