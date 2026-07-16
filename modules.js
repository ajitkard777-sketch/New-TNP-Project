// Shared Modules View (Placement Jobs Board, Training Calendar, Higher Studies Directory)

// ==========================================
// 1. PLACEMENT JOBS BOARD MODULE
// ==========================================
function renderJobsBoard(container) {
    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Placement Opportunities</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Discover and apply to active corporate job postings and internship drives.</p>
            </div>
        </div>

        <div class="dashboard-card p-5 space-y-4">
            <!-- Search, Sort & Filters -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="relative md:col-span-6">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" id="jobs-search" placeholder="Search by job title, company name, skills..." oninput="filterJobsBoard()" class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 dark:text-slate-300" />
                </div>
                
                <div class="md:col-span-3">
                    <select id="jobs-sort" onchange="filterJobsBoard()" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 dark:text-slate-350">
                        <option value="">Sort by (CTC Package)</option>
                        <option value="high-low">CTC: High to Low</option>
                        <option value="low-high">CTC: Low to High</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <select id="jobs-filter-eligibility" onchange="filterJobsBoard()" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 dark:text-slate-350">
                        <option value="">All Eligibility Grades</option>
                        <option value="6.0">CGPA threshold <= 6.0</option>
                        <option value="7.5">CGPA threshold <= 7.5</option>
                        <option value="8.0">CGPA threshold <= 8.0</option>
                    </select>
                </div>
            </div>

            <!-- Jobs Grid List -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pt-2" id="jobs-cards-grid">
                <!-- Loaded dynamically -->
            </div>
        </div>
    `;

    populateJobsCards(mockData.jobs);
    if (window.lucide) window.lucide.createIcons();
}

function populateJobsCards(list) {
    const grid = document.getElementById("jobs-cards-grid");
    if (!grid) return;

    if (list.length === 0) {
        grid.innerHTML = `<div class="col-span-full py-12 text-center text-slate-400 text-sm">No matching active job postings found</div>`;
        return;
    }

    const student = mockData.currentStudent;

    grid.innerHTML = list.map(job => {
        const isApplied = student.appliedJobs.includes(job.id);
        const isBookmarked = student.bookmarkedJobs.includes(job.id);
        
        return `
            <div class="dashboard-card p-5 flex flex-col justify-between gap-4 h-full relative">
                <!-- Top details -->
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 flex items-center justify-center p-2 shadow-sm">
                            <img src="${job.companyLogo}" class="w-full h-full object-contain" />
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-slate-800 dark:text-white truncate">${job.title}</h4>
                            <p class="text-[10px] text-slate-400 truncate">${job.companyName} &bull; ${job.location}</p>
                        </div>
                    </div>

                    <button onclick="toggleJobBookmark('${job.id}', this)" class="p-1.5 rounded-lg border border-slate-100 dark:border-slate-800 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition ${isBookmarked ? 'text-amber-500' : ''}">
                        <i data-lucide="bookmark" class="w-4 h-4" fill="${isBookmarked ? 'currentColor' : 'none'}"></i>
                    </button>
                </div>

                <!-- Package & Eligibility Tags -->
                <div class="flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 text-[9px] font-extrabold bg-blue-500/10 text-blue-500 rounded-lg">CTC: ${job.package}</span>
                    <span class="px-2 py-0.5 text-[9px] font-semibold bg-purple-500/10 text-purple-500 rounded-lg">${job.location.split(",")[0]}</span>
                    <span class="px-2 py-0.5 text-[9px] font-semibold bg-emerald-500/10 text-emerald-500 rounded-lg">CGPA >= ${job.eligibility.match(/[0-9.]+/)?.[0] || '6.0'}</span>
                </div>

                <!-- Skills list -->
                <div class="space-y-1">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Required Skills</span>
                    <div class="flex flex-wrap gap-1 pt-0.5">
                        ${job.skills.map(s => `<span class="px-2 py-0.5 bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-[10px] text-slate-500 dark:text-slate-400 rounded-md font-medium">${s}</span>`).join("")}
                    </div>
                </div>

                <!-- Action Button Foot -->
                <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800/80 pt-3.5 mt-2">
                    <button onclick="openJobDetails('${job.id}')" class="text-xs text-blue-500 hover:underline font-bold">Read details</button>
                    
                    <button onclick="handleStudentApply('${job.id}', this)" class="px-4 py-1.5 rounded-lg text-xs font-bold text-white gradient-bg hover:opacity-95 transition-all shadow-md shadow-blue-500/15 ${isApplied ? 'opacity-50 pointer-events-none' : ''}">
                        ${isApplied ? 'Applied' : 'Apply Now'}
                    </button>
                </div>
            </div>
        `;
    }).join("");

    if (window.lucide) window.lucide.createIcons();
}

function filterJobsBoard() {
    const query = document.getElementById("jobs-search")?.value.toLowerCase() || "";
    const sort = document.getElementById("jobs-sort")?.value || "";
    const minCgpa = parseFloat(document.getElementById("jobs-filter-eligibility")?.value) || 0;

    let filtered = mockData.jobs.filter(job => {
        const matchesQuery = job.title.toLowerCase().includes(query) || 
                             job.companyName.toLowerCase().includes(query) || 
                             job.skills.some(s => s.toLowerCase().includes(query));
        
        // Parse CGPA criteria from text eligibility
        const parsedCgpa = parseFloat(job.eligibility.match(/[0-9.]+/)?.[0]) || 6.0;
        const matchesEligibility = !minCgpa || parsedCgpa <= minCgpa;

        return matchesQuery && matchesEligibility;
    });

    // Handle package sorting
    if (sort === "high-low") {
        filtered.sort((a, b) => parseFloat(b.package) - parseFloat(a.package));
    } else if (sort === "low-high") {
        filtered.sort((a, b) => parseFloat(a.package) - parseFloat(b.package));
    }

    populateJobsCards(filtered);
}

function toggleJobBookmark(jobId, element) {
    const student = mockData.currentStudent;
    const isBookmarked = student.bookmarkedJobs.includes(jobId);

    if (isBookmarked) {
        student.bookmarkedJobs = student.bookmarkedJobs.filter(id => id !== jobId);
        app.showToast("Bookmark removed", "info");
    } else {
        student.bookmarkedJobs.push(jobId);
        app.showToast("Job added to bookmarks", "success");
    }

    // Refresh view
    if (mockData.session.role === 'student') {
        if (document.getElementById("jobs-search")) {
            filterJobsBoard();
        } else {
            renderStudentDashboard(document.getElementById("main-content"));
        }
    }
}

// Open detailed Job information modal overlay
function openJobDetails(jobId) {
    const job = mockData.jobs.find(j => j.id === jobId);
    if (!job) return;

    openAdminModal("add-job"); // Trigger overlay initialization
    const modalTitle = document.getElementById("admin-modal-title");
    const modalBody = document.getElementById("admin-modal-body");

    if (modalTitle && modalBody) {
        modalTitle.textContent = `${job.companyName} | Placement Opening Details`;
        
        const isApplied = mockData.currentStudent.appliedJobs.includes(job.id);
        
        modalBody.innerHTML = `
            <div class="space-y-4 text-slate-700 dark:text-slate-350 text-xs">
                <div class="flex items-center gap-4 bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-4 rounded-2xl">
                    <img src="${job.companyLogo}" class="w-12 h-12 object-contain" />
                    <div>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">${job.title}</h4>
                        <p class="font-medium text-blue-500">${job.companyName} &bull; CTC ${job.package}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <h5 class="font-bold text-slate-800 dark:text-white uppercase tracking-wider text-[10px]">Job Description</h5>
                    <p class="leading-relaxed font-medium">${job.description}</p>
                </div>

                <div class="grid grid-cols-2 gap-4 border-t border-slate-100 dark:border-slate-800/80 pt-3">
                    <div>
                        <h5 class="font-bold text-slate-800 dark:text-white uppercase tracking-wider text-[10px] mb-1">Eligible Criteria</h5>
                        <p class="font-medium text-slate-600 dark:text-slate-400">${job.eligibility}</p>
                    </div>
                    <div>
                        <h5 class="font-bold text-slate-800 dark:text-white uppercase tracking-wider text-[10px] mb-1">Office Location</h5>
                        <p class="font-medium text-slate-600 dark:text-slate-400">${job.location}</p>
                    </div>
                </div>

                <div class="space-y-1.5 border-t border-slate-100 dark:border-slate-800/80 pt-3">
                    <h5 class="font-bold text-slate-800 dark:text-white uppercase tracking-wider text-[10px]">Core Skills Required</h5>
                    <div class="flex flex-wrap gap-1.5">
                        ${job.skills.map(s => `<span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 border border-slate-200/50 dark:border-slate-750 text-slate-600 dark:text-slate-400 rounded-lg font-semibold">${s}</span>`).join("")}
                    </div>
                </div>

                <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                    <button type="button" onclick="closeAdminModal()" class="px-4 py-2 border border-slate-250 dark:border-slate-850 rounded-xl font-semibold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-900">Close</button>
                    ${mockData.session.role === 'student'
                        ? `<button type="button" onclick="closeAdminModal(); handleStudentApply('${job.id}', document.createElement('button'))" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-xl shadow-md ${isApplied ? 'opacity-55 pointer-events-none' : ''}">
                            ${isApplied ? 'Applied' : 'Apply Now'}
                          </button>`
                        : ''
                    }
                </div>
            </div>
        `;
    }
}


// ==========================================
// 2. TRAINING MODULE
// ==========================================
function renderTrainingModule(container) {
    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Training & Skills Bootcamp</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Join intensive expert courses to polish aptitude, soft skills and coding directories.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Training cards list -->
            <div class="lg:col-span-8 space-y-4" id="training-modules-list">
                <!-- Loaded dynamically -->
            </div>

            <!-- Side Training Calendar / Attendance Tracker -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Interactive Calendar Widget -->
                <div class="dashboard-card p-5 space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Training Calendar</h3>
                        <span class="text-xs text-blue-500 font-semibold">July 2026</span>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="space-y-2.5">
                        <div class="calendar-grid text-center text-[10px] font-bold text-slate-400">
                            <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                        </div>
                        <div class="calendar-grid text-center text-xs font-semibold text-slate-600 dark:text-slate-350" id="calendar-days">
                            <!-- Populated with dots on training days -->
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center justify-center gap-4 text-[10px] text-slate-400 border-t border-slate-100 dark:border-slate-800/80 pt-3">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Ongoing</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Scheduled</span>
                    </div>
                </div>

                <!-- Progress status certificate mock -->
                <div class="dashboard-card p-5 space-y-3.5">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white">Certification status</h3>
                    <div class="flex items-center gap-3 p-3 bg-emerald-500/5 border border-emerald-500/10 rounded-xl text-xs">
                        <div class="w-9 h-9 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
                            <i data-lucide="award" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-850 dark:text-slate-300">Aptitude foundations</p>
                            <span class="text-[10px] text-emerald-500 font-medium flex items-center gap-0.5 mt-0.5"><i data-lucide="check" class="w-3 h-3"></i> Certificate Claimable</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    populateTrainingCards(mockData.training);
    populateCalendarDays();
    if (window.lucide) window.lucide.createIcons();
}

function populateTrainingCards(list) {
    const listContainer = document.getElementById("training-modules-list");
    if (!listContainer) return;

    const student = mockData.currentStudent;

    listContainer.innerHTML = list.map(trn => {
        const isReg = student.registeredTraining.includes(trn.id);
        const badges = {
            Ongoing: "bg-emerald-500/10 text-emerald-500",
            Upcoming: "bg-blue-500/10 text-blue-500",
            Completed: "bg-slate-100 text-slate-400"
        };

        return `
            <div class="dashboard-card p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1.5 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-sm font-bold text-slate-850 dark:text-white">${trn.title}</h4>
                        <span class="px-2 py-0.5 text-[9px] font-extrabold rounded-lg ${badges[trn.status] || 'bg-slate-100'}">${trn.status}</span>
                    </div>
                    <p class="text-xs text-slate-455 font-medium">${trn.description}</p>
                    <div class="flex items-center gap-4 text-[10px] text-slate-400 font-medium pt-1">
                        <span class="flex items-center gap-1"><i data-lucide="user" class="w-3.5 h-3.5"></i> ${trn.trainer}</span>
                        <span class="flex items-center gap-1"><i data-lucide="clock" class="w-3.5 h-3.5"></i> ${trn.duration}</span>
                        <span class="flex items-center gap-1"><i data-lucide="calendar" class="w-3.5 h-3.5"></i> ${trn.date}</span>
                    </div>
                </div>

                <div class="shrink-0 w-full sm:w-auto">
                    ${trn.status === 'Completed'
                        ? `<button class="w-full sm:w-auto px-4 py-2 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-400 flex items-center justify-center gap-1 bg-slate-50 dark:bg-slate-900 pointer-events-none">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> Completed
                          </button>`
                        : `<button onclick="handleRegisterTraining('${trn.id}', this)" class="w-full sm:w-auto px-4 py-2 rounded-xl text-xs font-bold text-white gradient-bg shadow-md shadow-blue-500/10 hover:opacity-95 transition ${isReg ? 'opacity-50 pointer-events-none' : ''}">
                            ${isReg ? 'Registered' : 'Register Now'}
                          </button>`
                    }
                </div>
            </div>
        `;
    }).join("");
}

function handleRegisterTraining(trnId, btnNode) {
    const student = mockData.currentStudent;
    if (student.registeredTraining.includes(trnId)) return;

    student.registeredTraining.push(trnId);
    
    btnNode.textContent = "Registered";
    btnNode.classList.add("opacity-50", "pointer-events-none");
    
    app.showToast("Registered successfully for training module!", "success");
    
    setTimeout(() => {
        if (mockData.session.role === 'student' && document.getElementById("training-modules-list")) {
            renderTrainingModule(document.getElementById("main-content"));
        }
    }, 600);
}

function populateCalendarDays() {
    const daysGrid = document.getElementById("calendar-days");
    if (!daysGrid) return;

    // Build a static mock grid representation for July 2026 (Wednesday start)
    let daysHTML = "";
    
    // Wed, July 1 is start day, so add 3 empty grids
    for (let i = 0; i < 3; i++) {
        daysHTML += `<span class="py-1.5 opacity-0"></span>`;
    }

    // Days 1 to 31
    const trainingDays = {
        10: "ongoing", 11: "ongoing", 15: "scheduled", 17: "scheduled", 24: "scheduled", 25: "scheduled"
    };

    for (let day = 1; day <= 31; day++) {
        const type = trainingDays[day];
        let style = "py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer relative";
        
        if (type === "ongoing") {
            style += " text-emerald-500 font-bold bg-emerald-500/5";
            daysHTML += `<span class="${style}" onclick="app.showToast('DSA Boot Camp ongoing session today', 'success')">${day}<span class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-emerald-500"></span></span>`;
        } else if (type === "scheduled") {
            style += " text-blue-500 font-bold bg-blue-500/5";
            daysHTML += `<span class="${style}" onclick="app.showToast('System Design Class Scheduled', 'info')">${day}<span class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-blue-500"></span></span>`;
        } else {
            daysHTML += `<span class="${style}">${day}</span>`;
        }
    }

    daysGrid.innerHTML = daysHTML;
}


// ==========================================
// 3. HIGHER STUDIES MODULE
// ==========================================
function renderHigherStudiesModule(container) {
    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Global University Pathways</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Discover postgraduate courses, fellowships, and partial/full funding packages.</p>
            </div>
        </div>

        <div class="dashboard-card p-5 space-y-4">
            <!-- Search & Country Filters -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="relative md:col-span-6">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" id="uni-search" placeholder="Search by university name, major course field..." oninput="filterHigherStudies()" class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 dark:text-slate-350" />
                </div>
                
                <div class="md:col-span-3">
                    <select id="uni-filter-country" onchange="filterHigherStudies()" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 dark:text-slate-350">
                        <option value="">All Countries</option>
                        <option value="USA">United States (USA)</option>
                        <option value="UK">United Kingdom (UK)</option>
                        <option value="Singapore">Singapore</option>
                        <option value="Australia">Australia</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <select id="uni-filter-scholarship" onchange="filterHigherStudies()" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 dark:text-slate-350">
                        <option value="">All Scholarship types</option>
                        <option value="Fully Funded">Fully Funded fellowships</option>
                        <option value="Partial">Partial/Tuition grants</option>
                    </select>
                </div>
            </div>

            <!-- Universities cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pt-2" id="uni-cards-grid">
                <!-- Loaded dynamically -->
            </div>
        </div>
    `;

    populateUniversityCards(mockData.universities);
    if (window.lucide) window.lucide.createIcons();
}

function populateUniversityCards(list) {
    const grid = document.getElementById("uni-cards-grid");
    if (!grid) return;

    if (list.length === 0) {
        grid.innerHTML = `<div class="col-span-full py-12 text-center text-slate-400 text-sm">No matching universities found</div>`;
        return;
    }

    const student = mockData.currentStudent;

    grid.innerHTML = list.map(uni => {
        const isApplied = student.universityApplications.includes(uni.id);
        const hasFullScholarship = uni.scholarship.toLowerCase().includes("fully funded") || uni.scholarship.toLowerCase().includes("fellowship");

        return `
            <div class="dashboard-card p-5 flex flex-col justify-between gap-4 h-full">
                <!-- Logo & header -->
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-xl bg-white border border-slate-100 flex items-center justify-center p-2 shrink-0 shadow-sm">
                        <img src="${uni.logo}" class="w-full h-full object-contain" />
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-xs font-bold text-slate-850 dark:text-white truncate">${uni.name}</h4>
                        <span class="text-[9px] text-slate-400 font-semibold flex items-center gap-1 mt-0.5"><i data-lucide="map-pin" class="w-3 h-3 text-slate-350"></i> ${uni.country}</span>
                    </div>
                </div>

                <!-- Stats summary -->
                <div class="grid grid-cols-2 gap-2 text-[10px] bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-2.5 rounded-xl">
                    <div>
                        <span class="text-slate-400 font-semibold block">Academic Ranking</span>
                        <span class="font-bold text-slate-700 dark:text-slate-350">${uni.ranking}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold block">Estimated Fees</span>
                        <span class="font-bold text-slate-700 dark:text-slate-350">${uni.fees}</span>
                    </div>
                </div>

                <!-- Scholarship Badge -->
                <div class="p-2 border border-dashed rounded-xl flex items-center gap-2 ${hasFullScholarship ? 'border-emerald-500/20 bg-emerald-500/5 text-emerald-500' : 'border-purple-500/20 bg-purple-500/5 text-purple-500'}">
                    <i data-lucide="${hasFullScholarship ? 'award' : 'gift'}" class="w-4 h-4 shrink-0"></i>
                    <span class="text-[9px] font-bold leading-tight">${uni.scholarship}</span>
                </div>

                <!-- Course major description -->
                <div class="space-y-1 text-[11px]">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Target Programs</span>
                    <p class="font-medium text-slate-600 dark:text-slate-400 line-clamp-1">${uni.courses}</p>
                </div>

                <!-- Application Stepper foot -->
                <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800/80 pt-3.5 mt-2">
                    <span class="text-[9px] text-rose-500 font-semibold">Deadline: ${uni.deadline}</span>
                    
                    <button onclick="handleUniversityApply('${uni.id}', this)" class="px-4 py-1.5 rounded-lg text-xs font-bold text-white gradient-bg hover:opacity-95 transition-all shadow-md shadow-blue-500/10 ${isApplied ? 'opacity-55 pointer-events-none' : ''}">
                        ${isApplied ? 'Applied' : 'Apply Gateway'}
                    </button>
                </div>
            </div>
        `;
    }).join("");

    if (window.lucide) window.lucide.createIcons();
}

function filterHigherStudies() {
    const query = document.getElementById("uni-search")?.value.toLowerCase() || "";
    const country = document.getElementById("uni-filter-country")?.value || "";
    const scholar = document.getElementById("uni-filter-scholarship")?.value || "";

    const filtered = mockData.universities.filter(uni => {
        const matchesQuery = uni.name.toLowerCase().includes(query) || uni.courses.toLowerCase().includes(query);
        const matchesCountry = !country || uni.country === country;
        
        const hasFull = uni.scholarship.toLowerCase().includes("fully funded") || uni.scholarship.toLowerCase().includes("fellowship");
        const hasPartial = uni.scholarship.toLowerCase().includes("partial") || uni.scholarship.toLowerCase().includes("tuition");
        
        const matchesScholar = !scholar || 
                               (scholar === "Fully Funded" && hasFull) || 
                               (scholar === "Partial" && hasPartial);

        return matchesQuery && matchesCountry && matchesScholar;
    });

    populateUniversityCards(filtered);
}

function handleUniversityApply(uniId, btnNode) {
    const student = mockData.currentStudent;
    if (student.universityApplications.includes(uniId)) return;

    student.universityApplications.push(uniId);
    
    btnNode.textContent = "Applied";
    btnNode.classList.add("opacity-50", "pointer-events-none");
    
    app.showToast("Higher studies application submitted successfully via portal Gateway!", "success");
    
    setTimeout(() => {
        if (mockData.session.role === 'student' && document.getElementById("uni-cards-grid")) {
            renderHigherStudiesModule(document.getElementById("main-content"));
        }
    }, 600);
}

// Global exports
window.toggleJobBookmark = toggleJobBookmark;
window.openJobDetails = openJobDetails;
window.filterJobsBoard = filterJobsBoard;
window.handleRegisterTraining = handleRegisterTraining;
window.filterHigherStudies = filterHigherStudies;
window.handleUniversityApply = handleUniversityApply;
