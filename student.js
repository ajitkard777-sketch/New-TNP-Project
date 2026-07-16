// Student Dashboard Module
function renderStudentDashboard(container) {
    const student = mockData.currentStudent;
    
    // Find applications relating to this student
    const studentApps = mockData.applications.filter(app => app.studentId === student.id);
    
    // Calculate metrics
    const availableJobsCount = mockData.jobs.length;
    const appliedJobsCount = student.appliedJobs.length;
    const trainingCount = student.registeredTraining.length;
    const higherStudiesCount = student.universityApplications.length;

    // 1. Core Student Layout HTML
    container.innerHTML = `
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Welcome back, ${student.name}!</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Track application updates, discover job matches and access study programs.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold px-2.5 py-1 bg-blue-500/10 text-blue-500 rounded-lg">ID: ${student.id}</span>
                <span class="text-xs font-semibold px-2.5 py-1 ${student.placementStatus === 'Placed' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500'} rounded-lg">Status: ${student.placementStatus}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Side: Profile, Academic, Resume, Skills -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Profile Completion Card -->
                <div class="dashboard-card p-5 flex items-center gap-5">
                    <!-- Progress Circle -->
                    <div class="relative w-20 h-20 shrink-0">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="40" cy="40" r="34" stroke="currentColor" class="text-slate-100 dark:text-slate-800" stroke-width="6" fill="transparent" />
                            <circle cx="40" cy="40" r="34" stroke="currentColor" class="text-blue-500" stroke-width="6" fill="transparent" 
                                    stroke-dasharray="213.6" stroke-dashoffset="${213.6 - (213.6 * student.profileCompletion) / 100}" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center text-sm font-bold text-slate-700 dark:text-white">
                            ${student.profileCompletion}%
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Profile Strength</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Complete academic info and upload your latest resume to reach 100%.</p>
                    </div>
                </div>

                <!-- Academic Info -->
                <div class="dashboard-card p-5 space-y-4">
                    <h3 class="text-sm font-bold text-slate-850 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2">Academic Profile</h3>
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Branch</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-350 text-right">${student.branch}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">CGPA Score</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400">${student.cgpa.toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Active Backlogs</span>
                            <span class="font-semibold ${student.backlogs > 0 ? 'text-rose-500' : 'text-slate-700 dark:text-slate-350'}">${student.backlogs}</span>
                        </div>
                    </div>
                </div>

                <!-- Resume Upload Card -->
                <div class="dashboard-card p-5 space-y-3.5">
                    <h3 class="text-sm font-bold text-slate-850 dark:text-slate-200">Resume File (PDF)</h3>
                    <div id="resume-container">
                        ${student.resumeUploaded 
                            ? `
                            <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl text-xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <i data-lucide="file-text" class="w-5 h-5 text-red-500 shrink-0"></i>
                                    <span class="font-medium text-slate-700 dark:text-slate-300 truncate">${student.resumeName}</span>
                                </div>
                                <button onclick="triggerResumeReupload()" class="p-1 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                </button>
                            </div>
                            ` 
                            : `
                            <div onclick="document.getElementById('resume-file-input').click()" class="border-2 border-dashed border-slate-300 dark:border-slate-800 hover:border-blue-500 dark:hover:border-purple-500 rounded-2xl p-5 text-center cursor-pointer hover:bg-blue-50/10 transition group">
                                <i data-lucide="upload-cloud" class="w-8 h-8 text-slate-450 dark:text-slate-600 group-hover:text-blue-500 dark:group-hover:text-purple-500 mx-auto mb-2 transition"></i>
                                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">Drag & Drop CV or Click to Upload</p>
                                <p class="text-[10px] text-slate-400 mt-1">Accepts PDF files up to 5MB</p>
                                <input type="file" id="resume-file-input" class="hidden" accept=".pdf" onchange="simulateResumeUpload(event)" />
                            </div>
                            `
                        }
                    </div>
                </div>

                <!-- Skills list -->
                <div class="dashboard-card p-5 space-y-3">
                    <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-2">
                        <h3 class="text-sm font-bold text-slate-850 dark:text-slate-200">Verified Skill Tags</h3>
                        <button onclick="addSkillPrompt()" class="text-xs text-blue-500 hover:underline flex items-center gap-0.5"><i data-lucide="plus" class="w-3.5 h-3.5"></i> Add</button>
                    </div>
                    <div class="flex flex-wrap gap-1.5 pt-1.5" id="student-skills-tags">
                        ${student.skills.map(s => `<span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800/80 rounded-lg text-xs font-medium text-slate-600 dark:text-slate-450">${s}</span>`).join("")}
                    </div>
                </div>
            </div>

            <!-- Right Side: Navigation Stats, Application Tracker, Recommendations -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Nav Stats Cards Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div onclick="app.navigate('jobs')" class="dashboard-card p-4 text-center cursor-pointer hover:bg-slate-50/50 dark:hover:bg-slate-800/10">
                        <h4 class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">${availableJobsCount}</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Available Jobs</p>
                    </div>
                    <div onclick="renderStudentApplicationsView()" class="dashboard-card p-4 text-center cursor-pointer hover:bg-slate-50/50 dark:hover:bg-slate-800/10">
                        <h4 class="text-2xl font-extrabold text-purple-600 dark:text-purple-400">${appliedJobsCount}</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Applied Jobs</p>
                    </div>
                    <div onclick="app.navigate('training')" class="dashboard-card p-4 text-center cursor-pointer hover:bg-slate-50/50 dark:hover:bg-slate-800/10">
                        <h4 class="text-2xl font-extrabold text-cyan-600 dark:text-cyan-400">${trainingCount}</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Trainings Registered</p>
                    </div>
                    <div onclick="app.navigate('higher-studies')" class="dashboard-card p-4 text-center cursor-pointer hover:bg-slate-50/50 dark:hover:bg-slate-800/10">
                        <h4 class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">${higherStudiesCount}</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Uni Programs</p>
                    </div>
                </div>

                <!-- 2. Application Tracker (Active Applications Timeline) -->
                <div class="dashboard-card p-5 space-y-4">
                    <h3 class="text-sm font-bold text-slate-850 dark:text-slate-200">Campus Application Tracker</h3>
                    <div id="student-tracker-wrapper" class="space-y-6">
                        ${studentApps.length === 0 
                            ? `<p class="text-xs text-slate-400 py-4 text-center">You haven't applied to any active campus openings yet.</p>`
                            : renderApplicationsTrackerTimeline(studentApps[0])
                        }
                    </div>
                </div>

                <!-- 3. Jobs Recommendations Grid -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-850 dark:text-slate-200">Recommended Jobs For You</h3>
                        <a href="#" data-view="jobs" class="text-xs font-semibold text-blue-500 hover:underline">View All Job Board</a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        ${mockData.jobs.slice(0, 2).map(job => {
                            const isApplied = student.appliedJobs.includes(job.id);
                            return `
                                <div class="dashboard-card p-5 flex flex-col justify-between h-full gap-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 flex items-center justify-center p-1.5 shadow-sm">
                                                <img src="${job.companyLogo}" class="w-full h-full object-contain" />
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-bold text-slate-800 dark:text-white truncate">${job.title}</h4>
                                                <span class="text-[10px] text-slate-400">${job.companyName} &bull; ${job.location}</span>
                                            </div>
                                        </div>
                                        <span class="text-xs font-bold text-emerald-500 bg-emerald-500/10 px-2 py-0.5 rounded-full">${job.package}</span>
                                    </div>
                                    
                                    <div class="space-y-1.5 text-[11px] text-slate-500">
                                        <div class="flex gap-2">
                                            <span class="font-semibold text-slate-400">Eligibility:</span>
                                            <span class="font-medium text-slate-600 dark:text-slate-400">${job.eligibility}</span>
                                        </div>
                                        <div class="flex gap-2">
                                            <span class="font-semibold text-slate-400">Deadline:</span>
                                            <span class="font-medium text-rose-500">${job.deadline}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800/80 pt-3 mt-1">
                                        <button onclick="openJobDetails('${job.id}')" class="text-xs text-blue-500 hover:underline font-semibold">More details</button>
                                        <button onclick="handleStudentApply('${job.id}', this)" class="px-4 py-1.5 rounded-lg text-xs font-bold text-white gradient-bg hover:opacity-95 transition-all shadow-md shadow-blue-500/10 ${isApplied ? 'opacity-50 pointer-events-none' : ''}">
                                            ${isApplied ? 'Applied' : 'Apply Now'}
                                        </button>
                                    </div>
                                </div>
                            `;
                        }).join("")}
                    </div>
                </div>

                <!-- 4. Lower Dual split: Active Training Slots + Higher Studies summary -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Training slot card -->
                    <div class="dashboard-card p-5 space-y-4">
                        <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-2">
                            <h3 class="text-sm font-bold text-slate-850 dark:text-slate-200">Registered training</h3>
                            <a href="#" data-view="training" class="text-xs font-semibold text-blue-500 hover:underline">Explore</a>
                        </div>
                        <div class="space-y-3">
                            ${mockData.training.slice(0, 2).map(trn => {
                                const isReg = student.registeredTraining.includes(trn.id);
                                return `
                                    <div class="flex items-center justify-between text-xs gap-3">
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-700 dark:text-slate-300 truncate">${trn.title}</p>
                                            <span class="text-[10px] text-slate-400">${trn.trainer.split("(")[0]}</span>
                                        </div>
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded-lg ${isReg ? 'bg-emerald-500/10 text-emerald-500' : 'bg-slate-100 text-slate-400'}">${isReg ? 'Registered' : 'Available'}</span>
                                    </div>
                                `;
                            }).join("")}
                        </div>
                    </div>

                    <!-- Higher studies slot card -->
                    <div class="dashboard-card p-5 space-y-4">
                        <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-2">
                            <h3 class="text-sm font-bold text-slate-850 dark:text-slate-200">Higher Studies Programs</h3>
                            <a href="#" data-view="higher-studies" class="text-xs font-semibold text-blue-500 hover:underline">Browse</a>
                        </div>
                        <div class="space-y-3">
                            ${mockData.universities.slice(0, 2).map(uni => {
                                const isApplied = student.universityApplications.includes(uni.id);
                                return `
                                    <div class="flex items-center justify-between text-xs gap-3">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <img src="${uni.logo}" class="w-6 h-6 object-contain" />
                                            <div class="min-w-0">
                                                <p class="font-bold text-slate-700 dark:text-slate-300 truncate">${uni.name}</p>
                                                <span class="text-[9px] text-slate-400">${uni.country}</span>
                                            </div>
                                        </div>
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded-lg ${isApplied ? 'bg-purple-500/10 text-purple-500' : 'bg-slate-100 text-slate-400'}">${isApplied ? 'Applied' : 'Details'}</span>
                                    </div>
                                `;
                            }).join("")}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    if (window.lucide) window.lucide.createIcons();
}

// Render Timeline Stepper visual for a specific application
function renderApplicationsTrackerTimeline(app) {
    const stages = ["Applied", "Under Review", "Shortlisted", "Interview", "Selected"];
    const currentStageIndex = stages.indexOf(app.status);

    return `
        <div class="flex items-center justify-between gap-3 mb-2">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-800 dark:text-white">${app.companyName}</span>
                <span class="text-[10px] text-slate-400">&bull; ${app.jobTitle}</span>
            </div>
            <span class="text-xs text-blue-500 font-bold px-2 py-0.5 bg-blue-500/10 rounded-full">${app.status}</span>
        </div>

        <!-- Stepper Line -->
        <div class="flex items-center justify-between relative mt-6 px-1">
            <div class="absolute left-4 right-4 h-0.5 bg-slate-200 dark:bg-slate-800 top-1/2 -translate-y-1/2 -z-10"></div>
            
            ${stages.map((stage, idx) => {
                const isDone = idx <= currentStageIndex;
                const isCurrent = idx === currentStageIndex;
                
                let dotColor = "bg-slate-200 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-800";
                if (isCurrent) {
                    dotColor = "bg-blue-600 text-white border-blue-600 ring-4 ring-blue-500/10";
                } else if (isDone) {
                    dotColor = "bg-emerald-500 text-white border-emerald-500";
                }

                return `
                    <div class="flex flex-col items-center gap-1.5 relative z-10">
                        <div class="w-7 h-7 rounded-full border-2 text-[10px] font-bold flex items-center justify-center transition-all ${dotColor}">
                            ${isDone && !isCurrent ? '<i data-lucide="check" class="w-3.5 h-3.5"></i>' : idx + 1}
                        </div>
                        <span class="text-[9px] font-bold text-center ${isCurrent ? 'text-blue-600 dark:text-blue-400' : isDone ? 'text-emerald-500' : 'text-slate-400'}">${stage}</span>
                    </div>
                `;
            }).join("")}
        </div>
    `;
}

// Renders the list of applications submitted by student
function renderStudentApplicationsView() {
    const container = document.getElementById("main-content");
    if (!container) return;

    const student = mockData.currentStudent;
    const studentApps = mockData.applications.filter(app => app.studentId === student.id);

    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <a href="#" data-view="dashboard" class="text-xs font-semibold text-blue-500 hover:underline flex items-center gap-1 mb-1"><i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to Dashboard</a>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Your Job Applications</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Review status updates and schedules of applied company drives.</p>
            </div>
        </div>

        <div class="space-y-6">
            ${studentApps.map(app => `
                <div class="dashboard-card p-5 space-y-5">
                    ${renderApplicationsTrackerTimeline(app)}
                </div>
            `).join("")}
        </div>
    `;

    if (window.lucide) window.lucide.createIcons();
}

// Resume Upload Simulation
function triggerResumeReupload() {
    mockData.currentStudent.resumeUploaded = false;
    mockData.currentStudent.resumeName = "";
    mockData.currentStudent.profileCompletion = 70;
    renderStudentDashboard(document.getElementById("main-content"));
}

function simulateResumeUpload(e) {
    const file = e.target.files[0];
    if (!file) return;

    const container = document.getElementById("resume-container");
    if (!container) return;

    // Show Progress Bar
    container.innerHTML = `
        <div class="space-y-2 py-2">
            <div class="flex justify-between text-xs font-medium">
                <span class="text-slate-500">Uploading ${file.name}...</span>
                <span id="upload-progress-pct" class="text-blue-500 font-semibold">0%</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                <div id="upload-progress-bar" class="bg-gradient-to-r from-blue-500 to-purple-500 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>
    `;

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
                mockData.currentStudent.resumeUploaded = true;
                mockData.currentStudent.resumeName = file.name;
                mockData.currentStudent.profileCompletion = 85;
                app.showToast("Resume uploaded successfully!", "success");
                renderStudentDashboard(document.getElementById("main-content"));
            }, 300);
        }
    }, 150);
}

// Student Action Handlers
function handleStudentApply(jobId, buttonNode) {
    const student = mockData.currentStudent;
    const job = mockData.jobs.find(j => j.id === jobId);
    
    if (!job) return;
    if (student.appliedJobs.includes(jobId)) return;

    // Save mock state
    student.appliedJobs.push(jobId);
    
    // Add to applications pool
    const newAppId = `APP${String(mockData.applications.length + 1).padStart(3, '0')}`;
    const newApp = {
        id: newAppId,
        studentId: student.id,
        studentName: student.name,
        studentCGPA: student.cgpa,
        studentBranch: student.branch,
        studentResume: student.resumeName,
        jobId: jobId,
        jobTitle: job.title,
        companyName: job.companyName,
        appliedDate: new Date().toISOString().split('T')[0],
        status: "Applied",
        timeline: [
            { stage: "Applied", date: new Date().toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'}), done: true },
            { stage: "Under Review", date: "Pending", done: false },
            { stage: "Shortlisted", date: "Pending", done: false },
            { stage: "Interview", date: "TBD", done: false },
            { stage: "Selected", date: "TBD", done: false }
        ]
    };
    mockData.applications.unshift(newApp);

    // Dynamic UI styling changes
    buttonNode.textContent = "Applied";
    buttonNode.classList.add("opacity-50", "pointer-events-none");
    
    app.showToast(`Applied successfully for ${job.title} role!`, "success");
    
    // Re-render dashboard dashboard after delay
    setTimeout(() => {
        if (mockData.session.role === 'student') {
            renderStudentDashboard(document.getElementById("main-content"));
        }
    }, 600);
}

function addSkillPrompt() {
    const skill = prompt("Enter a skill to add to your profile (e.g. Docker, TypeScript):");
    if (!skill || skill.trim() === "") return;

    const student = mockData.currentStudent;
    if (student.skills.includes(skill.trim())) {
        app.showToast("Skill already exists on profile!", "warning");
        return;
    }

    student.skills.push(skill.trim());
    app.showToast(`Skill "${skill.trim()}" added to academic profile`, "success");
    renderStudentDashboard(document.getElementById("main-content"));
}

// Global exports
window.triggerResumeReupload = triggerResumeReupload;
window.simulateResumeUpload = simulateResumeUpload;
window.handleStudentApply = handleStudentApply;
window.addSkillPrompt = addSkillPrompt;
window.renderStudentApplicationsView = renderStudentApplicationsView;
