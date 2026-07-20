// Jobs Board, Training, Higher Studies Modules — TPMS v2.0 Premium

/* ======================================================
   JOBS BOARD
   ====================================================== */
function renderJobsBoard(container) {
    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-[var(--text-primary)]">Job Openings</h1>
                <p class="text-sm text-[var(--text-faint)] mt-0.5">Browse and apply to active campus recruitment drives.</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="dashboard-card p-4">
            <div class="flex flex-col md:flex-row gap-3">
                <div class="search-wrapper flex-1">
                    <span class="search-icon"><i data-lucide="search" class="w-4 h-4"></i></span>
                    <input type="text" id="job-search" placeholder="Search by title, company, or skill…"
                           oninput="filterJobs()" class="search-input text-xs" />
                </div>
                <div class="flex gap-2 flex-wrap">
                    ${[
                        { id: "filter-all",       label: "All",      filter: "" },
                        { id: "filter-product",   label: "Product",  filter: "product" },
                        { id: "filter-service",   label: "Services", filter: "service" },
                        { id: "filter-core",      label: "Core",     filter: "core" }
                    ].map((f, i) => `
                        <button id="${f.id}" data-filter="${f.filter}"
                                onclick="filterJobs('${f.filter}')"
                                class="btn btn-sm ${i === 0 ? 'btn-primary shadow-[var(--shadow-brand)]' : 'btn-secondary'}">
                            ${f.label}
                        </button>
                    `).join("")}
                </div>
            </div>
        </div>

        <!-- Job Card Grid -->
        <div id="jobs-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 stagger-children"></div>
    `;

    renderJobsGrid(mockData.jobs);
    if (window.lucide) window.lucide.createIcons();
}

function renderJobsGrid(jobs) {
    const grid    = document.getElementById("jobs-grid");
    if (!grid) return;

    const student = mockData.currentStudent;

    if (jobs.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full">
                <div class="dashboard-card">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i data-lucide="briefcase" class="w-6 h-6"></i></div>
                        <p class="font-semibold text-sm text-[var(--text-secondary)]">No jobs found</p>
                        <p class="text-xs text-[var(--text-faint)]">Try adjusting your search terms or filters.</p>
                    </div>
                </div>
            </div>`;
        if (window.lucide) window.lucide.createIcons();
        return;
    }

    grid.innerHTML = jobs.map(job => {
        const isApplied = student.appliedJobs.includes(job.id);
        // Extract minimum CGPA from eligibility string, e.g. "B.Tech CSE/IT, CGPA >= 8.0, 0 Backlogs"
        const cgpaMatch = job.eligibility.match(/CGPA\s*[>=]+\s*([\d.]+)/i);
        const minCgpa   = cgpaMatch ? parseFloat(cgpaMatch[1]) : 0;
        const isEligible= student.cgpa >= minCgpa;
        const deadlineColor = new Date(job.deadline) <= new Date(Date.now() + 3 * 86400000)
            ? "var(--danger)" : "var(--text-faint)";

        return `
            <div class="job-card">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-[var(--bg-subtle)] border border-[var(--border-color)] flex items-center justify-center p-2 flex-shrink-0">
                            <img src="${job.companyLogo}" class="w-full h-full object-contain" alt="${job.companyName}" />
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-bold text-sm text-[var(--text-primary)] truncate">${job.title}</h4>
                            <p class="text-xs text-[var(--text-faint)] truncate">${job.companyName}</p>
                        </div>
                    </div>
                    <span class="badge badge-success flex-shrink-0 text-xs">${job.package}</span>
                </div>

                <p class="text-xs text-[var(--text-secondary)] leading-relaxed line-clamp-2">${job.description}</p>

                <div class="space-y-1.5">
                    <div class="flex items-center gap-2 text-[11px] text-[var(--text-faint)]">
                        <i data-lucide="map-pin" class="w-3 h-3 flex-shrink-0"></i>
                        <span>${job.location}</span>
                    </div>
                    <div class="flex items-center gap-2 text-[11px] text-[var(--text-faint)]">
                        <i data-lucide="book-open" class="w-3 h-3 flex-shrink-0"></i>
                        <span class="truncate">${job.eligibility}</span>
                    </div>
                    <div class="flex items-center gap-2 text-[11px]" style="color:${deadlineColor};">
                        <i data-lucide="clock" class="w-3 h-3 flex-shrink-0"></i>
                        <span>Deadline: ${job.deadline}</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-1.5">
                    ${job.skills.slice(0, 4).map(s => `<span class="badge badge-muted text-[10px]">${s}</span>`).join("")}
                    ${job.skills.length > 4 ? `<span class="badge badge-muted text-[10px]">+${job.skills.length - 4}</span>` : ""}
                </div>

                <div class="flex items-center gap-2 pt-2 border-t border-[var(--border-color)]">
                    <button onclick="openJobDetailsModal('${job.id}')"
                            class="btn btn-secondary btn-sm flex-1">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i> Details
                    </button>
                    ${mockData.session.role === 'student'
                        ? `<button onclick="handleStudentApply('${job.id}', this)"
                                   class="btn ${isApplied ? 'btn-secondary opacity-60 cursor-not-allowed pointer-events-none' : 'btn-primary shadow-[var(--shadow-brand)]'} btn-sm flex-1">
                               ${isApplied
                                   ? `<i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Applied`
                                   : `<i data-lucide="send" class="w-3.5 h-3.5"></i> Apply`}
                           </button>`
                        : `<span class="badge badge-success flex-shrink-0">Active</span>`
                    }
                </div>
            </div>`;
    }).join("");

    if (window.lucide) window.lucide.createIcons();
}

function filterJobs(category = "") {
    const query = document.getElementById("job-search")?.value.toLowerCase() || "";

    // Toggle active filter buttons
    document.querySelectorAll("[data-filter]").forEach(btn => {
        const isActive = btn.getAttribute("data-filter") === category;
        btn.className = `btn btn-sm ${isActive ? 'btn-primary shadow-[var(--shadow-brand)]' : 'btn-secondary'}`;
    });

    let filtered = mockData.jobs;

    if (query) {
        filtered = filtered.filter(j =>
            j.title.toLowerCase().includes(query) ||
            j.companyName.toLowerCase().includes(query) ||
            j.skills.some(s => s.toLowerCase().includes(query))
        );
    }

    if (category) {
        const categoryMap = {
            product: ["technology", "software", "product"],
            service: ["consulting", "services", "it services"],
            core:    ["hardware", "mechanical", "electrical", "manufacturing"]
        };
        const keywords = categoryMap[category] || [];
        if (keywords.length) {
            filtered = filtered.filter(j =>
                keywords.some(k => j.companyName.toLowerCase().includes(k) || j.title.toLowerCase().includes(k))
            );
        }
    }

    renderJobsGrid(filtered);
}

function openJobDetailsModal(jobId) {
    const job = mockData.jobs.find(j => j.id === jobId);
    if (!job) return;

    const overlay = document.getElementById("admin-modal-overlay");
    const body    = document.getElementById("admin-modal-body");
    const titleEl = document.getElementById("admin-modal-title");
    if (!overlay || !body || !titleEl) return;

    overlay.classList.remove("hidden");
    titleEl.textContent = "Job Details";

    const student   = mockData.currentStudent;
    const isApplied = student.appliedJobs.includes(job.id);
    const isStudent = mockData.session.role === "student";

    body.innerHTML = `
        <div class="space-y-4">
            <div class="flex items-center gap-4 p-4 bg-[var(--bg-subtle)] rounded-xl border border-[var(--border-color)]">
                <div class="w-14 h-14 rounded-2xl bg-white flex items-center justify-center p-2 flex-shrink-0 shadow-[var(--shadow-sm)] border border-[var(--border-color)]">
                    <img src="${job.companyLogo}" class="w-full h-full object-contain" alt="${job.companyName}" />
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-[var(--text-primary)]">${job.title}</h3>
                    <p class="text-sm text-[var(--text-secondary)]">${job.companyName} · ${job.location}</p>
                    <span class="badge badge-success text-xs mt-1">${job.package}</span>
                </div>
            </div>

            <p class="text-xs text-[var(--text-secondary)] leading-relaxed">${job.description}</p>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <p class="text-[var(--text-faint)] font-medium mb-1">Eligibility</p>
                    <p class="font-semibold text-[var(--text-primary)]">${job.eligibility}</p>
                </div>
                <div>
                    <p class="text-[var(--text-faint)] font-medium mb-1">Application Deadline</p>
                    <p class="font-semibold text-[var(--danger)]">${job.deadline}</p>
                </div>
            </div>

            <div>
                <p class="text-xs text-[var(--text-faint)] font-medium mb-2">Required Skills</p>
                <div class="flex flex-wrap gap-1.5">
                    ${job.skills.map(s => `<span class="badge badge-primary text-xs">${s}</span>`).join("")}
                </div>
            </div>

            <div class="flex gap-2 pt-2 border-t border-[var(--border-color)]">
                <button onclick="closeAdminModal()" class="btn btn-secondary flex-1">Close</button>
                ${isStudent
                    ? `<button onclick="handleStudentApply('${job.id}', this); closeAdminModal();"
                               class="btn ${isApplied ? 'btn-secondary opacity-60 cursor-not-allowed pointer-events-none' : 'btn-primary shadow-[var(--shadow-brand)]'} flex-1">
                           ${isApplied ? 'Already Applied' : '<i data-lucide="send" class="w-4 h-4"></i> Apply Now'}
                       </button>`
                    : ""
                }
            </div>
        </div>`;

    if (window.lucide) window.lucide.createIcons();
}

/* ======================================================
   TRAINING MODULE
   ====================================================== */
function renderTrainingModule(container) {
    const student = mockData.currentStudent;

    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-[var(--text-primary)]">Training Programs</h1>
                <p class="text-sm text-[var(--text-faint)] mt-0.5">Skill-building programs offered by the Training &amp; Placement Office.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 stagger-children">
            ${mockData.training.map(trn => {
                const isReg      = student.registeredTraining.includes(trn.id);
                const statusColor = {
                    "Completed": "badge-muted",
                    "Ongoing":   "badge-success",
                    "Upcoming":  "badge-warning"
                };
                const statusIcon = {
                    "Completed": "check-circle-2",
                    "Ongoing":   "play-circle",
                    "Upcoming":  "clock"
                };

                return `
                    <div class="job-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0"
                                 style="background:${trn.status === 'Ongoing' ? 'var(--success-light)' : trn.status === 'Completed' ? 'var(--border-color)' : 'var(--warning-light)'};">
                                <i data-lucide="${statusIcon[trn.status] || 'book-open'}" class="w-5 h-5"
                                   style="color:${trn.status === 'Ongoing' ? 'var(--success)' : trn.status === 'Completed' ? 'var(--text-faint)' : 'var(--warning)'};"></i>
                            </div>
                            <span class="badge ${statusColor[trn.status] || 'badge-muted'} flex-shrink-0">${trn.status}</span>
                        </div>

                        <div class="space-y-1">
                            <h4 class="font-bold text-sm text-[var(--text-primary)]">${trn.title}</h4>
                            <p class="text-xs text-[var(--text-secondary)] leading-relaxed line-clamp-2">${trn.description}</p>
                        </div>

                        <div class="space-y-1.5 text-[11px]">
                            <div class="flex items-center gap-2 text-[var(--text-faint)]">
                                <i data-lucide="user" class="w-3 h-3 flex-shrink-0"></i>
                                <span>${trn.trainer}</span>
                            </div>
                            <div class="flex items-center gap-2 text-[var(--text-faint)]">
                                <i data-lucide="clock" class="w-3 h-3 flex-shrink-0"></i>
                                <span>${trn.duration}</span>
                            </div>
                            <div class="flex items-center gap-2 text-[var(--text-faint)]">
                                <i data-lucide="calendar" class="w-3 h-3 flex-shrink-0"></i>
                                <span>${trn.date}</span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-[var(--border-color)]">
                            ${mockData.session.role === 'student'
                                ? `<button onclick="handleTrainingRegistration('${trn.id}', this)"
                                           class="btn w-full ${isReg ? 'btn-secondary opacity-70 cursor-not-allowed pointer-events-none' : trn.status === 'Completed' ? 'btn-secondary opacity-50 cursor-not-allowed pointer-events-none' : 'btn-primary shadow-[var(--shadow-brand)]'} btn-sm">
                                       ${isReg ? '<i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Enrolled'
                                               : trn.status === 'Completed' ? 'Concluded'
                                               : '<i data-lucide="calendar-plus" class="w-3.5 h-3.5"></i> Register Now'}
                                   </button>`
                                : `<div class="flex items-center gap-2">
                                       <span class="badge ${statusColor[trn.status] || 'badge-muted'} text-xs">Status: ${trn.status}</span>
                                   </div>`
                            }
                        </div>
                    </div>
                `;
            }).join("")}
        </div>
    `;

    if (window.lucide) window.lucide.createIcons();
}

async function handleTrainingRegistration(trnId, btn) {
    const student = mockData.currentStudent;
    if (student.registeredTraining.includes(trnId)) return;

    if (btn) { btn.disabled = true; btn.innerHTML = '<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i> Enrolling…'; if (window.lucide) window.lucide.createIcons(); }

    const res = await ApiService.enrollTraining(trnId);

    if (res.success) {
        student.registeredTraining.push(trnId);
        const trn = mockData.training.find(t => t.id === trnId);
        app.showToast(`Registered for "${trn?.title}"!`, "success");
        if (btn) {
            btn.innerHTML  = `<i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Enrolled`;
            btn.className  = btn.className.replace("btn-primary", "btn-secondary");
            btn.classList.add("opacity-70", "cursor-not-allowed", "pointer-events-none");
            btn.disabled = false;
            if (window.lucide) window.lucide.createIcons();
        }
    } else {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i data-lucide="calendar-plus" class="w-3.5 h-3.5"></i> Register Now'; if (window.lucide) window.lucide.createIcons(); }
        app.showToast(res.message || "Enrollment failed.", "danger");
    }
}

/* ======================================================
   HIGHER STUDIES MODULE
   ====================================================== */
function renderHigherStudiesModule(container) {
    const student = mockData.currentStudent;

    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-[var(--text-primary)]">Higher Studies Navigator</h1>
                <p class="text-sm text-[var(--text-faint)] mt-0.5">Explore top-ranked global universities, scholarships, and graduate programs.</p>
            </div>
        </div>

        <!-- Search Row -->
        <div class="dashboard-card p-4">
            <div class="search-wrapper">
                <span class="search-icon"><i data-lucide="search" class="w-4 h-4"></i></span>
                <input type="text" id="uni-search" placeholder="Search universities by name, country, or program…"
                       oninput="filterUniversities()" class="search-input text-xs" />
            </div>
        </div>

        <!-- University Cards Grid -->
        <div id="uni-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 stagger-children"></div>
    `;

    renderUniversitiesGrid(mockData.universities);
    if (window.lucide) window.lucide.createIcons();
}

function renderUniversitiesGrid(unis) {
    const grid    = document.getElementById("uni-grid");
    if (!grid) return;

    const student = mockData.currentStudent;

    if (unis.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full">
                <div class="dashboard-card">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i data-lucide="globe" class="w-6 h-6"></i></div>
                        <p class="font-semibold text-sm text-[var(--text-secondary)]">No universities found</p>
                        <p class="text-xs text-[var(--text-faint)]">Try adjusting your search terms.</p>
                    </div>
                </div>
            </div>`;
        if (window.lucide) window.lucide.createIcons();
        return;
    }

    grid.innerHTML = unis.map(uni => {
        const isApplied      = student.universityApplications.includes(uni.id);
        const isEligible     = student.cgpa >= (uni.minCGPA || 0);
        const deadlineColor  = new Date(uni.deadline) <= new Date(Date.now() + 7 * 86400000)
            ? "var(--danger)" : "var(--text-faint)";

        return `
            <div class="job-card">
                <div class="flex items-center gap-3">
                    <img src="${uni.logo}" alt="${uni.name}"
                         class="w-14 h-14 object-contain flex-shrink-0 bg-white rounded-xl border border-[var(--border-color)] p-2" />
                    <div class="min-w-0">
                        <h4 class="font-bold text-sm text-[var(--text-primary)] leading-tight truncate">${uni.name}</h4>
                        <p class="text-xs text-[var(--text-faint)] truncate">${uni.country}</p>
                        <span class="badge badge-primary text-[10px] mt-1">${uni.ranking}</span>
                    </div>
                </div>

                <div class="space-y-1.5 text-[11px]">
                    <div class="flex items-center justify-between">
                        <span class="text-[var(--text-faint)]">Program</span>
                        <span class="font-semibold text-[var(--text-primary)] text-right max-w-[60%] truncate">${uni.courses}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[var(--text-faint)]">Min CGPA</span>
                        <span class="font-bold ${isEligible ? 'text-[var(--success)]' : 'text-[var(--danger)]'}">${uni.minCGPA}+</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[var(--text-faint)]">Scholarship</span>
                        <span class="font-semibold text-[var(--warning)]">${uni.scholarship}</span>
                    </div>
                    <div class="flex items-center justify-between" style="color:${deadlineColor};">
                        <span class="text-[var(--text-faint)]">Deadline</span>
                        <span class="font-semibold">${uni.deadline}</span>
                    </div>
                </div>

                ${!isEligible
                    ? `<div class="flex items-center gap-1.5 p-2.5 rounded-xl bg-[var(--danger-light)] border border-[rgba(239,68,68,0.20)] text-[11px] font-medium text-[var(--danger)]">
                           <i data-lucide="alert-circle" class="w-3.5 h-3.5 flex-shrink-0"></i>
                           CGPA below minimum (${student.cgpa.toFixed(2)} &lt; ${uni.minCGPA})
                       </div>` : ""}

                <div class="pt-2 border-t border-[var(--border-color)]">
                    ${mockData.session.role === 'student'
                        ? `<button onclick="handleUniversityApply('${uni.id}', this)"
                                   class="btn w-full ${!isEligible ? 'btn-secondary opacity-60 cursor-not-allowed pointer-events-none'
                                                    : isApplied   ? 'btn-secondary opacity-70 cursor-not-allowed pointer-events-none'
                                                    : 'btn-primary shadow-[var(--shadow-brand)]'} btn-sm">
                               ${!isEligible ? '<i data-lucide="lock" class="w-3.5 h-3.5"></i> Not Eligible'
                                             : isApplied ? '<i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Application Submitted'
                                             : '<i data-lucide="send" class="w-3.5 h-3.5"></i> Apply Now'}
                           </button>`
                        : `<a href="${uni.website}" target="_blank" rel="noopener noreferrer"
                              class="btn btn-secondary btn-sm w-full">
                               <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Visit University
                           </a>`
                    }
                </div>
            </div>`;
    }).join("");

    if (window.lucide) window.lucide.createIcons();
}

function filterUniversities() {
    const q = document.getElementById("uni-search")?.value.toLowerCase() || "";
    const filtered = q
        ? mockData.universities.filter(u =>
            u.name.toLowerCase().includes(q) ||
            u.country.toLowerCase().includes(q) ||
            u.courses.toLowerCase().includes(q)   // fixed: was u.program (undefined)
          )
        : mockData.universities;
    renderUniversitiesGrid(filtered);
}

async function handleUniversityApply(uniId, btn) {
    const student = mockData.currentStudent;
    const uni     = mockData.universities.find(u => u.id === uniId);
    if (!uni || student.universityApplications.includes(uniId)) return;

    if (student.cgpa < (parseFloat(uni.minCGPA) || 0)) {
        app.showToast(`You don't meet the CGPA requirement (${uni.minCGPA}+) for ${uni.name}.`, "danger");
        return;
    }

    if (btn) { btn.disabled = true; btn.innerHTML = '<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i> Applying…'; if (window.lucide) window.lucide.createIcons(); }

    const res = await ApiService.applyUniversity(uniId);

    if (res.success) {
        student.universityApplications.push(uniId);
        app.showToast(`Application submitted for ${uni.name}!`, "success");
        if (btn) {
            btn.innerHTML = `<i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Application Submitted`;
            btn.className = btn.className.replace("btn-primary", "btn-secondary");
            btn.classList.add("opacity-70", "cursor-not-allowed", "pointer-events-none");
            btn.disabled = false;
            if (window.lucide) window.lucide.createIcons();
        }
    } else {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i data-lucide="send" class="w-3.5 h-3.5"></i> Apply Now'; if (window.lucide) window.lucide.createIcons(); }
        app.showToast(res.message || "Application failed.", "danger");
    }
}

// Global exports
window.filterJobs                  = filterJobs;
window.openJobDetailsModal         = openJobDetailsModal;
window.handleTrainingRegistration  = handleTrainingRegistration;
window.filterUniversities          = filterUniversities;
window.handleUniversityApply       = handleUniversityApply;
