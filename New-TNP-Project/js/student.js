// Student Dashboard Module — TPMS v2.0 Premium
function renderStudentDashboard(container) {
    const student        = mockData.currentStudent;
    const studentApps    = mockData.applications.filter(a => a.studentId === student.id);
    const availableJobs  = mockData.jobs.length;
    const appliedCount   = student.appliedJobs.length;
    const trainingCount  = student.registeredTraining.length;
    const uniCount       = student.universityApplications.length;
    const statusBadge    = student.placementStatus === "Placed"
        ? "badge-success" : student.placementStatus === "Not Interested"
        ? "badge-muted" : "badge-warning";

    container.innerHTML = `
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <h1 class="text-2xl font-extrabold text-[var(--text-primary)] truncate">
                        Welcome back, ${student.name.split(" ")[0]}! 👋
                    </h1>
                </div>
                <p class="text-sm text-[var(--text-faint)]">Track applications, discover jobs, and access training programs.</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0 flex-wrap">
                <span class="badge badge-primary text-xs px-3 py-1">ID: ${student.id}</span>
                <span class="badge ${statusBadge} text-xs px-3 py-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse-slow"></span>
                    ${student.placementStatus}
                </span>
            </div>
        </div>

        <!-- Main Layout Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

            <!-- ──────────── LEFT COLUMN ──────────── -->
            <div class="lg:col-span-4 space-y-5">

                <!-- Profile Card -->
                <div class="dashboard-card p-5">
                    <div class="flex items-center gap-4 mb-5">
                        <div class="relative flex-shrink-0">
                            <img src="${student.avatar}" alt="${student.name}"
                                 class="w-16 h-16 rounded-2xl object-cover border-2 border-[var(--border-color)] shadow-[var(--shadow-sm)]" />
                            <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-[var(--success)] border-2 border-[var(--bg-card)] flex items-center justify-center">
                                <i data-lucide="check" class="w-2.5 h-2.5 text-white"></i>
                            </span>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-[var(--text-primary)] truncate">${student.name}</h3>
                            <p class="text-xs text-[var(--text-faint)] truncate mt-0.5">${student.branch}</p>
                            <p class="text-xs text-[var(--text-faint)] truncate">${student.email}</p>
                        </div>
                    </div>

                    <!-- Profile Completion Ring -->
                    <div class="flex items-center gap-4 p-3 bg-[var(--bg-subtle)] rounded-xl border border-[var(--border-color)]">
                        <div class="relative w-14 h-14 flex-shrink-0">
                            <svg class="w-full h-full -rotate-90" viewBox="0 0 56 56">
                                <circle cx="28" cy="28" r="22" stroke="var(--border-color)" stroke-width="5" fill="none"/>
                                <circle cx="28" cy="28" r="22" stroke="url(#grad-ring)" stroke-width="5" fill="none"
                                        stroke-dasharray="138.2"
                                        stroke-dashoffset="${138.2 - (138.2 * student.profileCompletion / 100)}"
                                        stroke-linecap="round"/>
                                <defs>
                                    <linearGradient id="grad-ring" x1="0" y1="0" x2="1" y2="0">
                                        <stop offset="0%" stop-color="#4F46E5"/>
                                        <stop offset="100%" stop-color="#7C3AED"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xs font-extrabold text-[var(--primary)]">${student.profileCompletion}%</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-[var(--text-primary)]">Profile Strength</p>
                            <p class="text-[11px] text-[var(--text-faint)] mt-0.5 leading-relaxed">Complete your info & upload resume to reach 100%.</p>
                        </div>
                    </div>
                </div>

                <!-- Academic Info -->
                <div class="dashboard-card p-5">
                    <h3 class="font-bold text-sm text-[var(--text-primary)] mb-4 flex items-center gap-2">
                        <i data-lucide="book-open" class="w-4 h-4 text-[var(--primary)]"></i> Academic Profile
                    </h3>
                    <div class="space-y-3">
                        ${[
                            { label: "Branch",    value: student.branch,               color: "var(--text-primary)"    },
                            { label: "CGPA",      value: student.cgpa.toFixed(2),      color: "var(--primary)",   bold: true },
                            { label: "Backlogs",  value: student.backlogs,             color: student.backlogs > 0 ? "var(--danger)" : "var(--success)" }
                        ].map(f => `
                            <div class="flex items-center justify-between py-2 border-b border-[var(--border-color)] last:border-0">
                                <span class="text-xs text-[var(--text-faint)]">${f.label}</span>
                                <span class="text-xs font-${f.bold ? 'extrabold text-sm' : 'semibold'}" style="color:${f.color};">${f.value}</span>
                            </div>
                        `).join("")}
                    </div>
                </div>

                <!-- Resume Upload -->
                <div class="dashboard-card p-5 space-y-3">
                    <h3 class="font-bold text-sm text-[var(--text-primary)] flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-[var(--danger)]"></i> Resume
                    </h3>
                    <div id="resume-container">
                        ${student.resumeUploaded
                            ? `<div class="flex items-center justify-between p-3 bg-[var(--bg-subtle)] border border-[var(--border-color)] rounded-xl">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-lg bg-[var(--danger-light)] flex items-center justify-center flex-shrink-0">
                                            <i data-lucide="file-text" class="w-4 h-4 text-[var(--danger)]"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-[var(--text-primary)] truncate">${student.resumeName}</p>
                                            <p class="text-[10px] text-[var(--text-faint)]">PDF · Uploaded</p>
                                        </div>
                                    </div>
                                    <button onclick="triggerResumeReupload()"
                                            class="btn btn-icon btn-ghost tooltip flex-shrink-0" data-tooltip="Replace">
                                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                    </button>
                               </div>`
                            : `<label onclick="document.getElementById('resume-file-input').click()"
                                      class="flex flex-col items-center justify-center gap-2.5 p-6 border-2 border-dashed border-[var(--border-color)] rounded-xl cursor-pointer hover:border-[var(--primary)] hover:bg-[var(--bg-hover)] transition-all group">
                                    <div class="w-10 h-10 rounded-xl bg-[var(--primary-light)] flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i data-lucide="upload-cloud" class="w-5 h-5 text-[var(--primary)]"></i>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs font-semibold text-[var(--text-primary)]">Drop PDF or click to upload</p>
                                        <p class="text-[11px] text-[var(--text-faint)] mt-0.5">Up to 5 MB · PDF only</p>
                                    </div>
                                    <input type="file" id="resume-file-input" class="hidden" accept=".pdf" onchange="simulateResumeUpload(event)" />
                               </label>`
                        }
                    </div>
                </div>

                <!-- Skills -->
                <div class="dashboard-card p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-sm text-[var(--text-primary)] flex items-center gap-2">
                            <i data-lucide="zap" class="w-4 h-4 text-[var(--warning)]"></i> Skills
                        </h3>
                        <button onclick="addSkillPrompt()" class="btn btn-ghost btn-sm text-[var(--primary)] text-xs">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-1.5" id="student-skills-tags">
                        ${student.skills.map(s => `
                            <span class="badge badge-primary text-[11px]">${s}</span>
                        `).join("")}
                    </div>
                </div>
            </div>

            <!-- ──────────── RIGHT COLUMN ──────────── -->
            <div class="lg:col-span-8 space-y-5">

                <!-- Quick Stat Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 stagger-children">
                    ${[
                        { view: "jobs",           val: availableJobs, label: "Jobs Available",    color: "var(--primary)",   bg: "var(--primary-light)",      icon: "briefcase"       },
                        { fn: "renderStudentApplicationsView()", val: appliedCount, label: "Applied Jobs",     color: "var(--secondary)", bg: "rgba(124,58,237,0.10)",    icon: "send"            },
                        { view: "training",       val: trainingCount, label: "Trainings",          color: "var(--accent)",    bg: "rgba(6,182,212,0.10)",     icon: "graduation-cap"  },
                        { view: "higher-studies", val: uniCount,      label: "Uni Programs",       color: "var(--success)",   bg: "var(--success-light)",     icon: "globe"           }
                    ].map(s => `
                        <div onclick="${s.view ? `app.navigate('${s.view}')` : s.fn}"
                             class="dashboard-card p-4 text-center cursor-pointer hover:shadow-[var(--shadow-md)] hover:-translate-y-1 transition-all duration-300">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-2.5" style="background:${s.bg};">
                                <i data-lucide="${s.icon}" class="w-4.5 h-4.5" style="color:${s.color};"></i>
                            </div>
                            <h4 class="text-2xl font-extrabold tracking-tight" style="color:${s.color};">${s.val}</h4>
                            <p class="text-[10px] font-semibold text-[var(--text-faint)] uppercase tracking-wider mt-1">${s.label}</p>
                        </div>
                    `).join("")}
                </div>

                <!-- Application Tracker -->
                <div class="dashboard-card p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-sm text-[var(--text-primary)]">Application Pipeline Tracker</h3>
                            <p class="text-xs text-[var(--text-faint)] mt-0.5">Latest status of your job applications</p>
                        </div>
                        <button onclick="renderStudentApplicationsView()"
                                class="btn btn-ghost btn-sm text-[var(--primary)] text-xs">
                            View All <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <div id="student-tracker-wrapper">
                        ${studentApps.length === 0
                            ? `<div class="empty-state">
                                   <div class="empty-state-icon"><i data-lucide="briefcase" class="w-6 h-6"></i></div>
                                   <p class="font-semibold text-sm text-[var(--text-secondary)]">No applications yet</p>
                                   <p class="text-xs text-[var(--text-faint)]">Browse available jobs and start applying!</p>
                                   <button onclick="app.navigate('jobs')" class="btn btn-primary btn-sm mt-2 shadow-[var(--shadow-brand)]">
                                       <i data-lucide="search" class="w-3.5 h-3.5"></i> Browse Jobs
                                   </button>
                               </div>`
                            : renderApplicationsTrackerTimeline(studentApps[0])
                        }
                    </div>
                </div>

                <!-- Recommended Jobs -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-sm text-[var(--text-primary)]">Recommended Jobs For You</h3>
                        <a href="#" data-view="jobs" class="btn btn-ghost btn-sm text-[var(--primary)] text-xs">
                            View All <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        ${mockData.jobs.slice(0, 2).map(job => {
                            const isApplied = student.appliedJobs.includes(job.id);
                            return `
                                <div class="job-card">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-xl bg-[var(--bg-subtle)] border border-[var(--border-color)] flex items-center justify-center p-1.5 flex-shrink-0">
                                                <img src="${job.companyLogo}" class="w-full h-full object-contain" alt="${job.companyName}" />
                                            </div>
                                            <div class="min-w-0">
                                                <h4 class="text-xs font-bold text-[var(--text-primary)] truncate">${job.title}</h4>
                                                <p class="text-[11px] text-[var(--text-faint)]">${job.companyName} · ${job.location}</p>
                                            </div>
                                        </div>
                                        <span class="badge badge-success text-[11px] flex-shrink-0">${job.package}</span>
                                    </div>

                                    <div class="space-y-1.5">
                                        <div class="flex items-start gap-1.5 text-[11px]">
                                            <span class="text-[var(--text-faint)] font-medium flex-shrink-0">Eligibility:</span>
                                            <span class="text-[var(--text-secondary)]">${job.eligibility}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[11px]">
                                            <i data-lucide="calendar" class="w-3 h-3 text-[var(--danger)] flex-shrink-0"></i>
                                            <span class="text-[var(--danger)] font-medium">Deadline: ${job.deadline}</span>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-1">
                                        ${job.skills.slice(0,3).map(s => `<span class="badge badge-muted text-[10px]">${s}</span>`).join("")}
                                    </div>

                                    <div class="flex items-center justify-between pt-2 border-t border-[var(--border-color)]">
                                        <button onclick="openJobDetails('${job.id}')"
                                                class="btn btn-ghost btn-sm text-[var(--primary)] text-xs">
                                            Details
                                        </button>
                                        <button onclick="handleStudentApply('${job.id}', this)"
                                                class="btn ${isApplied ? 'btn-secondary opacity-60 cursor-not-allowed pointer-events-none' : 'btn-primary shadow-[var(--shadow-brand)]'} btn-sm">
                                            ${isApplied
                                                ? '<i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Applied'
                                                : '<i data-lucide="send" class="w-3.5 h-3.5"></i> Apply Now'}
                                        </button>
                                    </div>
                                </div>
                            `;
                        }).join("")}
                    </div>
                </div>

                <!-- Training + Higher Studies Quick Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Training -->
                    <div class="dashboard-card p-5 space-y-3">
                        <div class="flex items-center justify-between border-b border-[var(--border-color)] pb-3">
                            <h3 class="font-bold text-sm text-[var(--text-primary)] flex items-center gap-2">
                                <i data-lucide="graduation-cap" class="w-4 h-4 text-[var(--accent)]"></i> Training
                            </h3>
                            <a href="#" data-view="training" class="btn btn-ghost btn-sm text-[var(--primary)] text-xs">
                                Explore <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        </div>
                        <div class="space-y-3">
                            ${mockData.training.slice(0,2).map(trn => {
                                const isReg = student.registeredTraining.includes(trn.id);
                                return `
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:${trn.status === 'Ongoing' ? 'var(--success-light)' : trn.status === 'Completed' ? 'var(--border-color)' : 'var(--warning-light)'};">
                                            <i data-lucide="${trn.status === 'Completed' ? 'check-circle' : trn.status === 'Ongoing' ? 'play-circle' : 'clock'}" class="w-4 h-4" style="color:${trn.status === 'Ongoing' ? 'var(--success)' : trn.status === 'Completed' ? 'var(--text-faint)' : 'var(--warning)'};"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-[var(--text-primary)] truncate">${trn.title}</p>
                                            <p class="text-[10px] text-[var(--text-faint)]">${trn.duration}</p>
                                        </div>
                                        <span class="badge ${isReg ? 'badge-success' : 'badge-muted'} text-[10px] flex-shrink-0">
                                            ${isReg ? 'Enrolled' : 'Open'}
                                        </span>
                                    </div>`;
                            }).join("")}
                        </div>
                    </div>

                    <!-- Higher Studies -->
                    <div class="dashboard-card p-5 space-y-3">
                        <div class="flex items-center justify-between border-b border-[var(--border-color)] pb-3">
                            <h3 class="font-bold text-sm text-[var(--text-primary)] flex items-center gap-2">
                                <i data-lucide="globe" class="w-4 h-4 text-[var(--secondary)]"></i> Higher Studies
                            </h3>
                            <a href="#" data-view="higher-studies" class="btn btn-ghost btn-sm text-[var(--primary)] text-xs">
                                Browse <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        </div>
                        <div class="space-y-3">
                            ${mockData.universities.slice(0,2).map(uni => {
                                const isApplied = student.universityApplications.includes(uni.id);
                                return `
                                    <div class="flex items-center gap-3">
                                        <img src="${uni.logo}" alt="${uni.name}" class="w-8 h-8 object-contain flex-shrink-0" />
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-[var(--text-primary)] truncate">${uni.name}</p>
                                            <p class="text-[10px] text-[var(--text-faint)]">${uni.country} · ${uni.ranking}</p>
                                        </div>
                                        <span class="badge ${isApplied ? 'badge-purple' : 'badge-muted'} text-[10px] flex-shrink-0">
                                            ${isApplied ? 'Applied' : 'Details'}
                                        </span>
                                    </div>`;
                            }).join("")}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    if (window.lucide) window.lucide.createIcons();
}

/* ──────────────────────────────────────────────────
   APPLICATION TRACKER TIMELINE
   ────────────────────────────────────────────────── */
function renderApplicationsTrackerTimeline(appObj) {
    const stages    = ["Applied","Under Review","Shortlisted","Interview","Selected"];
    const currentIdx= stages.indexOf(appObj.status);

    return `
        <div class="flex items-center justify-between gap-3 mb-5">
            <div class="min-w-0">
                <p class="font-bold text-sm text-[var(--text-primary)]">${appObj.companyName}</p>
                <p class="text-xs text-[var(--text-faint)]">${appObj.jobTitle}</p>
            </div>
            <span class="badge badge-primary flex-shrink-0">${appObj.status}</span>
        </div>

        <!-- Stepper -->
        <div class="relative flex items-start justify-between px-1">
            <!-- Track line -->
            <div class="stepper-track"></div>

            ${stages.map((stage, idx) => {
                const isDone    = idx < currentIdx;
                const isCurrent = idx === currentIdx;
                return `
                    <div class="stepper-step">
                        <div class="stepper-dot ${isCurrent ? 'current' : isDone ? 'done' : ''}">
                            ${isDone ? `<i data-lucide="check" class="w-3.5 h-3.5"></i>` : idx + 1}
                        </div>
                        <span class="text-[9px] font-bold text-center max-w-[50px] leading-tight
                                     ${isCurrent ? 'text-[var(--primary)]' : isDone ? 'text-[var(--success)]' : 'text-[var(--text-faint)]'}">
                            ${stage}
                        </span>
                    </div>`;
            }).join("")}
        </div>

        <!-- Timeline Dates -->
        <div class="mt-4 space-y-1.5 pt-3 border-t border-[var(--border-color)]">
            ${appObj.timeline.filter(t => t.done).map(t => `
                <div class="flex items-center gap-2 text-[11px]">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-[var(--success)] flex-shrink-0"></i>
                    <span class="font-medium text-[var(--text-secondary)]">${t.stage}</span>
                    <span class="text-[var(--text-faint)]">·</span>
                    <span class="text-[var(--text-faint)]">${t.date}</span>
                </div>
            `).join("")}
        </div>
    `;
}

/* ──────────────────────────────────────────────────
   ALL APPLICATIONS VIEW
   ────────────────────────────────────────────────── */
function renderStudentApplicationsView() {
    const container = document.getElementById("main-content");
    if (!container) return;

    const student    = mockData.currentStudent;
    const studentApps= mockData.applications.filter(a => a.studentId === student.id);

    container.innerHTML = `
        <div class="flex items-center gap-3 mb-2">
            <button onclick="app.navigate('dashboard')" class="btn btn-ghost btn-sm text-[var(--primary)]">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Dashboard
            </button>
        </div>
        <h1 class="text-2xl font-extrabold text-[var(--text-primary)] mb-1">My Job Applications</h1>
        <p class="text-sm text-[var(--text-faint)] mb-5">Review status updates and interview schedules for all applied drives.</p>

        ${studentApps.length === 0
            ? `<div class="dashboard-card">
                   <div class="empty-state">
                       <div class="empty-state-icon"><i data-lucide="briefcase" class="w-6 h-6"></i></div>
                       <p class="font-semibold text-sm text-[var(--text-secondary)]">No applications yet</p>
                       <p class="text-xs text-[var(--text-faint)]">Start applying to available campus job openings!</p>
                       <button onclick="app.navigate('jobs')" class="btn btn-primary btn-sm mt-2 shadow-[var(--shadow-brand)]">Browse Jobs</button>
                   </div>
               </div>`
            : `<div class="space-y-4">
                   ${studentApps.map(a => `
                       <div class="dashboard-card p-5 space-y-4">
                           ${renderApplicationsTrackerTimeline(a)}
                       </div>`).join("")}
               </div>`
        }
    `;

    if (window.lucide) window.lucide.createIcons();
}

/* ──────────────────────────────────────────────────
   RESUME HANDLERS
   ────────────────────────────────────────────────── */
function triggerResumeReupload() {
    mockData.currentStudent.resumeUploaded   = false;
    mockData.currentStudent.resumeName       = "";
    mockData.currentStudent.profileCompletion= 70;
    renderStudentDashboard(document.getElementById("main-content"));
}

function simulateResumeUpload(e) {
    const file = e.target.files[0];
    if (!file) return;

    const resumeContainer = document.getElementById("resume-container");
    if (!resumeContainer) return;

    resumeContainer.innerHTML = `
        <div class="space-y-2.5 py-2">
            <div class="flex justify-between text-xs font-medium">
                <span class="text-[var(--text-secondary)] truncate max-w-[180px]">${file.name}</span>
                <span id="upload-progress-pct" class="text-[var(--primary)] font-bold flex-shrink-0">0%</span>
            </div>
            <div class="progress-bar">
                <div id="upload-progress-bar" class="progress-fill" style="width:0%; transition: width 0.15s ease;"></div>
            </div>
            <p class="text-[11px] text-[var(--text-faint)]">Uploading securely…</p>
        </div>`;

    let progress = 0;
    const interval = setInterval(() => {
        progress += 20;
        const bar = document.getElementById("upload-progress-bar");
        const pct = document.getElementById("upload-progress-pct");
        if (bar) bar.style.width = `${progress}%`;
        if (pct) pct.textContent = `${progress}%`;

        if (progress >= 100) {
            clearInterval(interval);
            setTimeout(() => {
                mockData.currentStudent.resumeUploaded    = true;
                mockData.currentStudent.resumeName        = file.name;
                mockData.currentStudent.profileCompletion = 85;
                app.showToast("Resume uploaded successfully!", "success");
                renderStudentDashboard(document.getElementById("main-content"));
            }, 400);
        }
    }, 150);
}

/* ──────────────────────────────────────────────────
   STUDENT ACTION HANDLERS
   ────────────────────────────────────────────────── */
function handleStudentApply(jobId, btn) {
    const student = mockData.currentStudent;
    const job     = mockData.jobs.find(j => j.id === jobId);
    if (!job || student.appliedJobs.includes(jobId)) return;

    student.appliedJobs.push(jobId);

    const newId = `APP${String(mockData.applications.length + 1).padStart(3, "0")}`;
    mockData.applications.unshift({
        id: newId, studentId: student.id, studentName: student.name,
        studentCGPA: student.cgpa, studentBranch: student.branch,
        studentResume: student.resumeName, jobId, jobTitle: job.title,
        companyName: job.companyName, appliedDate: new Date().toISOString().split("T")[0],
        status: "Applied",
        timeline: [
            { stage: "Applied",      date: new Date().toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" }), done: true },
            { stage: "Under Review", date: "Pending", done: false },
            { stage: "Shortlisted",  date: "Pending", done: false },
            { stage: "Interview",    date: "TBD",     done: false },
            { stage: "Selected",     date: "TBD",     done: false }
        ]
    });

    btn.innerHTML = `<i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Applied`;
    btn.classList.add("opacity-60", "pointer-events-none", "cursor-not-allowed");
    btn.classList.remove("btn-primary");
    btn.classList.add("btn-secondary");
    if (window.lucide) window.lucide.createIcons();

    app.showToast(`Applied for ${job.title} at ${job.companyName}!`, "success");

    setTimeout(() => {
        if (mockData.session.role === "student") {
            renderStudentDashboard(document.getElementById("main-content"));
        }
    }, 700);
}

function addSkillPrompt() {
    const skill = prompt("Add a skill (e.g. Docker, TypeScript, AWS):");
    if (!skill?.trim()) return;
    const student = mockData.currentStudent;
    if (student.skills.includes(skill.trim())) {
        app.showToast("That skill is already on your profile!", "warning");
        return;
    }
    student.skills.push(skill.trim());
    app.showToast(`Skill "${skill.trim()}" added!`, "success");
    renderStudentDashboard(document.getElementById("main-content"));
}

// Global exports
window.triggerResumeReupload     = triggerResumeReupload;
window.simulateResumeUpload      = simulateResumeUpload;
window.handleStudentApply        = handleStudentApply;
window.addSkillPrompt            = addSkillPrompt;
window.renderStudentApplicationsView = renderStudentApplicationsView;
