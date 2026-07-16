// Admin Dashboard & Management Views
function renderAdminDashboard(container) {
    const isDark = document.documentElement.classList.contains("dark");
    const gridColor = isDark ? "#1e293b" : "#f1f5f9";
    const labelColor = isDark ? "#94a3b8" : "#64748b";

    // 1. Core Admin Layout HTML
    container.innerHTML = `
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Admin Dashboard</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Campus Training & Placements central overview dashboard.</p>
            </div>
            
            <!-- Quick Actions Grid Button Bar -->
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="openAdminModal('add-company')" class="flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i data-lucide="building-2" class="w-4 h-4 text-purple-500"></i> Add Company
                </button>
                <button onclick="openAdminModal('add-job')" class="flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i data-lucide="briefcase" class="w-4 h-4 text-blue-500"></i> Add Job
                </button>
                <button onclick="openAdminModal('schedule-interview')" class="flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i data-lucide="calendar" class="w-4 h-4 text-cyan-500"></i> Schedule Interview
                </button>
                <button onclick="openAdminModal('create-training')" class="flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i data-lucide="graduation-cap" class="w-4 h-4 text-emerald-500"></i> Create Training
                </button>
                <button onclick="openAdminModal('publish-results')" class="flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i data-lucide="award" class="w-4 h-4 text-amber-500"></i> Publish Results
                </button>
            </div>
        </div>

        <!-- 2. Statistics Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="dashboard-card p-5 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Total Students</span>
                    <h3 class="text-2xl font-bold mt-1 text-slate-800 dark:text-white">5,420</h3>
                    <span class="text-[10px] text-emerald-500 font-medium flex items-center gap-0.5 mt-1"><i data-lucide="trending-up" class="w-3 h-3"></i> +4.2% YoY</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="dashboard-card p-5 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Recruiting Companies</span>
                    <h3 class="text-2xl font-bold mt-1 text-slate-800 dark:text-white">182</h3>
                    <span class="text-[10px] text-emerald-500 font-medium flex items-center gap-0.5 mt-1"><i data-lucide="plus" class="w-3 h-3"></i> 14 this month</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
                    <i data-lucide="building-2" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="dashboard-card p-5 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Active Job Listings</span>
                    <h3 class="text-2xl font-bold mt-1 text-slate-800 dark:text-white">48</h3>
                    <span class="text-[10px] text-amber-500 font-medium flex items-center gap-0.5 mt-1"><i data-lucide="clock" class="w-3 h-3"></i> 8 closing soon</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
                    <i data-lucide="briefcase" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="dashboard-card p-5 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Overall Placed</span>
                    <h3 class="text-2xl font-bold mt-1 text-slate-800 dark:text-white">95.4%</h3>
                    <span class="text-[10px] text-emerald-500 font-medium flex items-center gap-0.5 mt-1"><i data-lucide="check" class="w-3 h-3"></i> Exceeds target</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                    <i data-lucide="award" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <!-- 3. Primary Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Placement Trend -->
            <div class="dashboard-card p-5 lg:col-span-8 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Placement Rate Progress (%)</h3>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-blue-500/10 text-blue-500 rounded-lg">Historical (6 Years)</span>
                </div>
                <div id="chart-placement-trend" class="w-full"></div>
            </div>

            <!-- Company Hiring Distribution -->
            <div class="dashboard-card p-5 lg:col-span-4 flex flex-col justify-between">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Company-wise Placements</h3>
                <div id="chart-company-hiring" class="flex justify-center w-full"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Department performance -->
            <div class="dashboard-card p-5 lg:col-span-6 flex flex-col justify-between">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Department-wise Placed Stats</h3>
                <div id="chart-dept-placements" class="w-full"></div>
            </div>

            <!-- Registrations Trend -->
            <div class="dashboard-card p-5 lg:col-span-6 flex flex-col justify-between">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Monthly Platform Registrations</h3>
                <div id="chart-registrations" class="w-full"></div>
            </div>
        </div>

        <!-- 4. Lower Dashboard Grid: Activity Log + Quick Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Recent Activities -->
            <div class="dashboard-card p-5 lg:col-span-7 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Recent Campus Activities</h3>
                    <button onclick="app.showToast('Refreshing activities...', 'info')" class="text-xs font-semibold text-blue-500 hover:underline">Refresh</button>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800/80 space-y-3.5 pt-2 max-h-[350px] overflow-y-auto pr-1" id="activities-list">
                    <!-- Loaded dynamically -->
                </div>
            </div>

            <!-- Operational Stat cards list -->
            <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="dashboard-card p-5 flex flex-col justify-between">
                    <span class="text-xs font-semibold text-slate-400">Training Programs</span>
                    <div class="flex justify-between items-end mt-4">
                        <span class="text-3xl font-extrabold text-slate-700 dark:text-white">12</span>
                        <span class="text-xs text-blue-500 font-semibold px-2 py-0.5 bg-blue-500/10 rounded-full">4 Active</span>
                    </div>
                </div>

                <div class="dashboard-card p-5 flex flex-col justify-between">
                    <span class="text-xs font-semibold text-slate-400">Interviews Scheduled</span>
                    <div class="flex justify-between items-end mt-4">
                        <span class="text-3xl font-extrabold text-slate-700 dark:text-white">28</span>
                        <span class="text-xs text-amber-500 font-semibold px-2 py-0.5 bg-amber-500/10 rounded-full">8 Today</span>
                    </div>
                </div>

                <div class="dashboard-card p-5 flex flex-col justify-between col-span-1 sm:col-span-2">
                    <span class="text-xs font-semibold text-slate-400">Higher Studies Applications</span>
                    <div class="flex justify-between items-end mt-4">
                        <span class="text-3xl font-extrabold text-slate-700 dark:text-white">154</span>
                        <div class="flex gap-2">
                            <span class="text-xs text-emerald-500 font-medium bg-emerald-500/10 px-2 py-0.5 rounded-full">65 Accepted</span>
                            <span class="text-xs text-slate-400 font-medium bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full">34 Pending</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // 5. Populate Activities
    const actList = document.getElementById("activities-list");
    if (actList) {
        actList.innerHTML = mockData.activities.map(act => {
            const icons = {
                award: "award",
                briefcase: "briefcase",
                calendar: "calendar",
                "user-plus": "user-plus",
                "book-open": "book-open"
            };
            const iconColors = {
                placement: "bg-emerald-500/10 text-emerald-500",
                job: "bg-blue-500/10 text-blue-500",
                interview: "bg-cyan-500/10 text-cyan-500",
                registration: "bg-purple-500/10 text-purple-500",
                training: "bg-amber-500/10 text-amber-500"
            };

            return `
                <div class="flex items-center gap-3 pt-3 first:pt-0">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 ${iconColors[act.type] || 'bg-slate-100 text-slate-500'}">
                        <i data-lucide="${icons[act.icon] || 'info'}" class="w-4.5 h-4.5"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-slate-700 dark:text-slate-350 truncate">${act.text}</p>
                        <span class="text-[10px] text-slate-400">${act.time}</span>
                    </div>
                </div>
            `;
        }).join("");
    }

    if (window.lucide) window.lucide.createIcons();

    // 6. Initialize ApexCharts with Delay for DOM Rendering
    setTimeout(() => {
        // A. Placement rate Trend (Line chart)
        const trendData = mockData.analytics.placementTrend;
        new ApexCharts(document.querySelector("#chart-placement-trend"), {
            series: [{ name: "Placement Rate (%)", data: trendData.rates }],
            chart: { type: "line", height: 260, toolbar: { show: false }, zoom: { enabled: false }, foreColor: labelColor },
            stroke: { curve: "smooth", width: 4.5, colors: ["#2563EB"] },
            colors: ["#2563EB"],
            xaxis: { categories: trendData.years },
            yaxis: { min: 70, max: 100 },
            grid: { borderColor: gridColor },
            tooltip: { theme: isDark ? "dark" : "light" }
        }).render();

        // B. Department performance (Stacked bar chart)
        const deptData = mockData.analytics.departmentPlacements;
        new ApexCharts(document.querySelector("#chart-dept-placements"), {
            series: [
                { name: "Placed", data: deptData.placedCount },
                { name: "Total Strengths", data: deptData.totalCount }
            ],
            chart: { type: "bar", height: 260, toolbar: { show: false }, foreColor: labelColor },
            plotOptions: { bar: { horizontal: false, columnWidth: "45%", borderRadius: 6 } },
            stroke: { show: true, width: 2, colors: ["transparent"] },
            colors: ["#7C3AED", "#CBD5E1"],
            xaxis: { categories: deptData.branches },
            grid: { borderColor: gridColor },
            fill: { opacity: 1 },
            tooltip: { theme: isDark ? "dark" : "light" }
        }).render();

        // C. Company hiring share (Pie chart)
        const compData = mockData.analytics.companyHiring;
        new ApexCharts(document.querySelector("#chart-company-hiring"), {
            series: compData.counts,
            chart: { type: "donut", height: 260, foreColor: labelColor },
            labels: compData.names,
            colors: ["#2563EB", "#7C3AED", "#06B6D4", "#10B981", "#F59E0B", "#94A3B8"],
            legend: { position: "bottom", fontSize: "11px" },
            stroke: { show: false },
            dataLabels: { enabled: false },
            plotOptions: { pie: { donut: { size: "75%" } } },
            tooltip: { theme: isDark ? "dark" : "light" }
        }).render();

        // D. Monthly Registrations (Area chart)
        const regData = mockData.analytics.monthlyRegistrations;
        new ApexCharts(document.querySelector("#chart-registrations"), {
            series: [
                { name: "Students", data: regData.studentRegistrations },
                { name: "Companies", data: regData.companyRegistrations }
            ],
            chart: { type: "area", height: 260, toolbar: { show: false }, foreColor: labelColor },
            colors: ["#06B6D4", "#F59E0B"],
            xaxis: { categories: regData.months },
            grid: { borderColor: gridColor },
            dataLabels: { enabled: false },
            stroke: { curve: "smooth", width: 2 },
            fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05 } },
            tooltip: { theme: isDark ? "dark" : "light" }
        }).render();
    }, 150);
}

// 7. Render Students Management Table view
function renderStudentsManagement(container) {
    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Registered Students</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Browse academic grades, placement statuses and resume files.</p>
            </div>
        </div>

        <div class="dashboard-card p-5 space-y-4">
            <!-- Search & Filters -->
            <div class="flex flex-col md:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" id="student-search" placeholder="Search by name, roll number, department..." oninput="filterStudentsTable()" class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div class="w-full md:w-48">
                    <select id="student-branch-filter" onchange="filterStudentsTable()" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 dark:text-slate-350">
                        <option value="">All Branches</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Electronics & Comm">Electronics & Comm</option>
                        <option value="Mechanical Eng">Mechanical Eng</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800/80 text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Student ID</th>
                            <th class="py-3 px-4">Name</th>
                            <th class="py-3 px-4">Branch</th>
                            <th class="py-3 px-4">CGPA</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="students-table-body" class="divide-y divide-slate-100 dark:divide-slate-800/50 text-xs font-medium text-slate-600 dark:text-slate-350">
                        <!-- Loaded dynamically -->
                    </tbody>
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
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-slate-400">No matching students found</td></tr>`;
        return;
    }

    tbody.innerHTML = list.map(stu => {
        const badges = {
            Placed: "bg-emerald-500/10 text-emerald-500",
            "In Progress": "bg-blue-500/10 text-blue-500"
        };
        return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-300">${stu.id}</td>
                <td class="py-3 px-4">${stu.name}</td>
                <td class="py-3 px-4">${stu.branch}</td>
                <td class="py-3 px-4">${stu.cgpa.toFixed(2)}</td>
                <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${badges[stu.status] || 'bg-slate-100 text-slate-500'}">${stu.status}</span>
                </td>
                <td class="py-3 px-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <button onclick="downloadStudentResume('${stu.resume}')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-400 hover:text-blue-500 transition tooltip" data-tooltip="Download Resume">
                            <i data-lucide="download" class="w-4 h-4"></i>
                        </button>
                        <button onclick="deleteStudent('${stu.id}')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-400 hover:text-rose-500 transition tooltip" data-tooltip="Delete Student">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join("");

    if (window.lucide) window.lucide.createIcons();
}

function filterStudentsTable() {
    const query = document.getElementById("student-search")?.value.toLowerCase() || "";
    const branch = document.getElementById("student-branch-filter")?.value || "";

    const filtered = mockData.students.filter(stu => {
        const matchesQuery = stu.name.toLowerCase().includes(query) || 
                             stu.id.toLowerCase().includes(query) || 
                             stu.branch.toLowerCase().includes(query);
        const matchesBranch = !branch || stu.branch.includes(branch);
        return matchesQuery && matchesBranch;
    });

    populateStudentsTable(filtered);
}

function deleteStudent(id) {
    mockData.students = mockData.students.filter(stu => stu.id !== id);
    app.showToast(`Deleted student ${id}`, "danger");
    filterStudentsTable();
}

function downloadStudentResume(filename) {
    app.showToast(`Downloading file: ${filename}`, "success");
}

// 8. Render Companies Management Table view
function renderCompaniesManagement(container) {
    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Recruiting Partners</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Manage registered corporate entities and contact directories.</p>
            </div>
            <button onclick="openAdminModal('add-company')" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-blue-500/10 hover:opacity-95 transition flex items-center gap-1.5">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Partner Company
            </button>
        </div>

        <div class="dashboard-card p-5 space-y-4">
            <!-- Search -->
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </span>
                <input type="text" id="company-search" placeholder="Search companies by name, sector or code..." oninput="filterCompaniesTable()" class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800/80 text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Company ID</th>
                            <th class="py-3 px-4">Company Name</th>
                            <th class="py-3 px-4">Industry Sector</th>
                            <th class="py-3 px-4">Registered Date</th>
                            <th class="py-3 px-4">HR Contact Info</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="companies-table-body" class="divide-y divide-slate-100 dark:divide-slate-800/50 text-xs font-medium text-slate-600 dark:text-slate-350">
                        <!-- Loaded dynamically -->
                    </tbody>
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
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-slate-400">No matching partners found</td></tr>`;
        return;
    }

    tbody.innerHTML = list.map(comp => `
        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
            <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-300">${comp.id}</td>
            <td class="py-3 px-4 font-bold text-blue-600 dark:text-purple-400 hover:underline">
                <a href="${comp.website}" target="_blank">${comp.name}</a>
            </td>
            <td class="py-3 px-4">${comp.industry}</td>
            <td class="py-3 px-4">${comp.registeredDate}</td>
            <td class="py-3 px-4">${comp.contact}</td>
            <td class="py-3 px-4 text-right">
                <button onclick="deleteCompany('${comp.id}')" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-400 hover:text-rose-500 transition tooltip" data-tooltip="Delete Company">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        </tr>
    `).join("");

    if (window.lucide) window.lucide.createIcons();
}

function filterCompaniesTable() {
    const query = document.getElementById("company-search")?.value.toLowerCase() || "";
    const filtered = mockData.companies.filter(comp => comp.name.toLowerCase().includes(query) || comp.industry.toLowerCase().includes(query) || comp.id.toLowerCase().includes(query));
    populateCompaniesTable(filtered);
}

function deleteCompany(id) {
    mockData.companies = mockData.companies.filter(comp => comp.id !== id);
    app.showToast(`Deleted company ${id}`, "danger");
    filterCompaniesTable();
}

// 9. Admin Dialog Modal Handlers
function openAdminModal(actionType) {
    const modalOverlay = document.getElementById("admin-modal-overlay");
    const modalContent = document.getElementById("admin-modal-body");
    const modalTitle = document.getElementById("admin-modal-title");
    
    if (!modalOverlay || !modalContent || !modalTitle) return;

    modalOverlay.classList.remove("hidden");
    
    const fieldsHTML = {
        'add-company': `
            <form onsubmit="handleAdminModalSubmit(event, 'add-company')" class="space-y-4 text-slate-800 dark:text-slate-100">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500">Company Name</label>
                    <input type="text" id="modal-comp-name" required placeholder="Intel Corp" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500">Corporate Website</label>
                    <input type="url" id="modal-comp-web" required placeholder="https://intel.com" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500">Industry Segment</label>
                    <input type="text" id="modal-comp-ind" required placeholder="Hardware & Semiconductors" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500">HR Contact Email</label>
                    <input type="email" id="modal-comp-con" required placeholder="recruiting@intel.com" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                </div>
                <div class="flex justify-end gap-2.5 pt-4">
                    <button type="button" onclick="closeAdminModal()" class="px-4 py-2 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-900">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/10">Add Partner</button>
                </div>
            </form>
        `,
        'add-job': `
            <form onsubmit="handleAdminModalSubmit(event, 'add-job')" class="space-y-4 text-slate-800 dark:text-slate-100">
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500">Company Name</label>
                        <select id="modal-job-comp" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm">
                            ${mockData.companies.map(c => `<option value="${c.name}">${c.name}</option>`).join("")}
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500">Job Title</label>
                        <input type="text" id="modal-job-title" required placeholder="Security Specialist" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500">Package (CTC)</label>
                        <input type="text" id="modal-job-ctc" required placeholder="14.0 LPA" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500">Location</label>
                        <input type="text" id="modal-job-loc" required placeholder="Bangalore" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500">Skills Needed (Comma separated)</label>
                    <input type="text" id="modal-job-skills" required placeholder="Python, Cyber Security, Wireshark" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500">CGPA Threshold</label>
                        <input type="number" step="0.1" id="modal-job-cgpa" required placeholder="7.0" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500">Application Deadline</label>
                        <input type="date" id="modal-job-date" required class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                    </div>
                </div>
                <div class="flex justify-end gap-2.5 pt-4">
                    <button type="button" onclick="closeAdminModal()" class="px-4 py-2 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-900">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/10">Publish Job</button>
                </div>
            </form>
        `,
        'schedule-interview': `
            <form onsubmit="handleAdminModalSubmit(event, 'schedule-interview')" class="space-y-4 text-slate-800 dark:text-slate-100">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500">Candidate Student</label>
                    <select id="modal-int-student" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm">
                        ${mockData.students.map(s => `<option value="${s.name} (${s.branch})">${s.name} (${s.branch})</option>`).join("")}
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500">Hiring Company</label>
                        <select id="modal-int-comp" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm">
                            ${mockData.companies.map(c => `<option value="${c.name}">${c.name}</option>`).join("")}
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500">Interview Date & Time</label>
                        <input type="datetime-local" id="modal-int-time" required class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                    </div>
                </div>
                <div class="flex justify-end gap-2.5 pt-4">
                    <button type="button" onclick="closeAdminModal()" class="px-4 py-2 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-900">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/10">Schedule</button>
                </div>
            </form>
        `,
        'create-training': `
            <form onsubmit="handleAdminModalSubmit(event, 'create-training')" class="space-y-4 text-slate-800 dark:text-slate-100">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500">Training Title</label>
                    <input type="text" id="modal-train-title" required placeholder="E.g., Cloud Architecture Foundations" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500">Lead Trainer / Instructor Name</label>
                    <input type="text" id="modal-train-trainer" required placeholder="Prof. Jane Doe" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500">Duration (Hours)</label>
                        <input type="text" id="modal-train-dur" required placeholder="24 Hours" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500">Schedule Date Ranges</label>
                        <input type="text" id="modal-train-date" required placeholder="Aug 02 - Aug 20" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                    </div>
                </div>
                <div class="flex justify-end gap-2.5 pt-4">
                    <button type="button" onclick="closeAdminModal()" class="px-4 py-2 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-900">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/10">Create Program</button>
                </div>
            </form>
        `,
        'publish-results': `
            <form onsubmit="handleAdminModalSubmit(event, 'publish-results')" class="space-y-4 text-slate-800 dark:text-slate-100">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500">Select Job Drive</label>
                    <select id="modal-res-job" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm">
                        ${mockData.jobs.map(j => `<option value="${j.companyName} - ${j.title}">${j.companyName} - ${j.title}</option>`).join("")}
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500">Selected Candidate (Student Name)</label>
                    <select id="modal-res-stud" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm">
                        ${mockData.students.map(s => `<option value="${s.name} (${s.branch})">${s.name} (${s.branch})</option>`).join("")}
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500">Offered Package (LPA)</label>
                    <input type="text" id="modal-res-lpa" required placeholder="18.5 LPA" class="w-full px-3 py-2 bg-white/40 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm" />
                </div>
                <div class="flex justify-end gap-2.5 pt-4">
                    <button type="button" onclick="closeAdminModal()" class="px-4 py-2 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-900">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/10">Publish</button>
                </div>
            </form>
        `
    };

    const titles = {
        'add-company': 'Add New Recruiting Partner',
        'add-job': 'Publish New Placement Job Opening',
        'schedule-interview': 'Schedule Placement Interview Slot',
        'create-training': 'Launch Technical Training Course',
        'publish-results': 'Publish Final Offer Selection Results'
    };

    modalTitle.textContent = titles[actionType] || "Action form";
    modalContent.innerHTML = fieldsHTML[actionType] || "<p>Loading form...</p>";
}

function closeAdminModal() {
    const modalOverlay = document.getElementById("admin-modal-overlay");
    if (modalOverlay) modalOverlay.classList.add("hidden");
}

function handleAdminModalSubmit(e, actionType) {
    e.preventDefault();
    closeAdminModal();

    if (actionType === 'add-company') {
        const name = document.getElementById("modal-comp-name").value;
        const web = document.getElementById("modal-comp-web").value;
        const ind = document.getElementById("modal-comp-ind").value;
        const con = document.getElementById("modal-comp-con").value;
        const newId = `COMP${String(mockData.companies.length + 1).padStart(3, '0')}`;
        
        const newComp = { id: newId, name, website: web, industry: ind, registeredDate: new Date().toISOString().split('T')[0], jobCount: 0, contact: con };
        mockData.companies.push(newComp);
        
        mockData.activities.unshift({
            id: mockData.activities.length + 1,
            type: "registration",
            text: `New recruiter registered: ${name}`,
            time: "Just now",
            icon: "user-plus"
        });

        app.showToast(`Partner company "${name}" successfully registered!`, "success");
        if (mockData.session.role === 'admin' && document.getElementById("company-search")) {
            renderCompaniesManagement(document.getElementById("main-content"));
        } else {
            app.navigate("dashboard");
        }
    } 
    
    else if (actionType === 'add-job') {
        const comp = document.getElementById("modal-job-comp").value;
        const title = document.getElementById("modal-job-title").value;
        const ctc = document.getElementById("modal-job-ctc").value;
        const loc = document.getElementById("modal-job-loc").value;
        const skillsRaw = document.getElementById("modal-job-skills").value;
        const cgpa = parseFloat(document.getElementById("modal-job-cgpa").value);
        const deadline = document.getElementById("modal-job-date").value;
        const newId = `JOB${String(mockData.jobs.length + 1).padStart(3, '0')}`;
        
        const newJob = {
            id: newId,
            companyId: "COMP999",
            companyName: comp,
            companyLogo: "https://upload.wikimedia.org/wikipedia/commons/1/18/Markenlogo_Intel.svg",
            title,
            package: ctc,
            location: loc,
            eligibility: `B.Tech, CGPA >= ${cgpa.toFixed(1)}, 0 Backlogs`,
            skills: skillsRaw.split(",").map(s => s.trim()),
            deadline,
            status: "Active",
            description: "Opening published via administrator quick panel. Eligible candidates matching requirements may apply directly."
        };
        mockData.jobs.push(newJob);
        
        mockData.activities.unshift({
            id: mockData.activities.length + 1,
            type: "job",
            text: `${comp} published new role: ${title} - CTC ${ctc}`,
            time: "Just now",
            icon: "briefcase"
        });

        app.showToast(`Job listing published for ${comp}!`, "success");
        app.navigate("dashboard");
    } 
    
    else if (actionType === 'schedule-interview') {
        const stud = document.getElementById("modal-int-student").value;
        const comp = document.getElementById("modal-int-comp").value;
        const time = new Date(document.getElementById("modal-int-time").value).toLocaleString();

        mockData.activities.unshift({
            id: mockData.activities.length + 1,
            type: "interview",
            text: `Interview scheduled: ${stud} with ${comp} on ${time}`,
            time: "Just now",
            icon: "calendar"
        });

        app.showToast(`Interview scheduled for ${stud} with ${comp}`, "success");
        app.navigate("dashboard");
    }

    else if (actionType === 'create-training') {
        const title = document.getElementById("modal-train-title").value;
        const trainer = document.getElementById("modal-train-trainer").value;
        const dur = document.getElementById("modal-train-dur").value;
        const date = document.getElementById("modal-train-date").value;
        const newId = `TRN${String(mockData.training.length + 1).padStart(3, '0')}`;

        mockData.training.push({
            id: newId,
            title,
            trainer,
            date,
            duration: dur,
            status: "Upcoming",
            description: "Program launched by administrator dashboard cell. Sessions are free for registered campus candidates."
        });

        mockData.activities.unshift({
            id: mockData.activities.length + 1,
            type: "training",
            text: `New Training program published: ${title}`,
            time: "Just now",
            icon: "book-open"
        });

        app.showToast(`Training Program "${title}" created!`, "success");
        app.navigate("dashboard");
    }

    else if (actionType === 'publish-results') {
        const job = document.getElementById("modal-res-job").value;
        const stud = document.getElementById("modal-res-stud").value.split("(")[0].trim();
        const lpa = document.getElementById("modal-res-lpa").value;

        // Change student status to placed
        const stuObj = mockData.students.find(s => s.name.toLowerCase() === stud.toLowerCase());
        if (stuObj) {
            stuObj.status = "Placed";
        }

        mockData.activities.unshift({
            id: mockData.activities.length + 1,
            type: "placement",
            text: `${stud} selected at ${job.split("-")[0].trim()} - CTC ${lpa}`,
            time: "Just now",
            icon: "award"
        });

        app.showToast(`Offer published! ${stud} placed at ${job.split("-")[0].trim()}`, "success");
        app.navigate("dashboard");
    }
}

// Global exports
window.openAdminModal = openAdminModal;
window.closeAdminModal = closeAdminModal;
window.handleAdminModalSubmit = handleAdminModalSubmit;
window.deleteStudent = deleteStudent;
window.downloadStudentResume = downloadStudentResume;
window.filterStudentsTable = filterStudentsTable;
window.deleteCompany = deleteCompany;
window.filterCompaniesTable = filterCompaniesTable;
