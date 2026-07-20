/**
 * ============================================================
 *  TPMS — js/api.js
 *  API Service Layer — replaces mockData.js
 *  Provides the same `window.mockData` object that all
 *  rendering modules depend on, populated from real PHP APIs.
 * ============================================================
 */

// ── Global App Data (mirrors old mockData structure) ──────────
const mockData = {
    currentStudent: {
        id: null, name: '', email: '', avatar: '', branch: '',
        cgpa: 0, backlogs: 0, skills: [], resumeUploaded: false,
        resumeName: '', placementStatus: 'In Progress',
        appliedJobs: [], bookmarkedJobs: [],
        registeredTraining: [], universityApplications: [],
        profileCompletion: 0, theme: 'light'
    },
    session:       { role: 'guest', userId: null, companyName: '', companyId: null, comp_uid: null },
    jobs:          [],
    companies:     [],
    students:      [],
    applications:  [],
    training:      [],
    universities:  [],
    activities:    [],
    analytics:     {
        placementTrend: { years: [], rates: [] },
        departmentPlacements: { branches: [], placedCount: [], totalCount: [], placementPct: [] },
        companyHiring: { names: [], counts: [] },
        monthlyRegistrations: { months: [], studentRegistrations: [], companyRegistrations: [] },
    },
};
window.mockData = mockData;

// ── API Base URL ──────────────────────────────────────────────
const API_BASE = 'api/';

// ── CSRF Token ────────────────────────────────────────────────
let _csrfToken = '';

// ── Core HTTP Client ──────────────────────────────────────────
const ApiService = {

    /**
     * Internal fetch wrapper. Returns parsed JSON or throws.
     */
    async _request(endpoint, options = {}) {
        const url = API_BASE + endpoint;
        const headers = { 'Accept': 'application/json', ...(options.headers || {}) };

        // Include CSRF for non-GET
        if (options.method && options.method !== 'GET' && _csrfToken) {
            headers['X-CSRF-Token'] = _csrfToken;
        }

        try {
            const res  = await fetch(url, { ...options, headers });
            const data = await res.json();
            return data;
        } catch (err) {
            console.error(`[TPMS API] ${url} failed:`, err);
            return { success: false, message: 'Network error. Please check your connection.' };
        }
    },

    get(endpoint, params = {}) {
        const qs = new URLSearchParams(params).toString();
        return this._request(endpoint + (qs ? '?' + qs : ''), { method: 'GET' });
    },

    post(endpoint, data = {}) {
        const body = new FormData();
        Object.entries(data).forEach(([k, v]) => body.append(k, v));
        if (_csrfToken) body.append('csrf_token', _csrfToken);
        return this._request(endpoint, { method: 'POST', body });
    },

    postJSON(endpoint, data = {}) {
        return this._request(endpoint, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': _csrfToken },
            body:    JSON.stringify({ ...data, csrf_token: _csrfToken }),
        });
    },

    // ── Auth ──────────────────────────────────────────────────

    async checkSession() {
        const res = await this.get('auth.php', { action: 'session' });
        if (res.success && res.csrf_token) _csrfToken = res.csrf_token;
        return res;
    },

    async login(email, password, role) {
        const res = await this.post('auth.php?action=login', { email, password, role });
        if (res.success && res.csrf_token) _csrfToken = res.csrf_token;
        return res;
    },

    async logout() {
        return this.post('auth.php?action=logout', {});
    },

    async registerStudent(data) {
        return this.post('auth.php?action=register', { type: 'student', ...data });
    },

    async registerCompany(data) {
        return this.post('auth.php?action=register', { type: 'company', ...data });
    },

    async forgotPassword(email) {
        return this.post('auth.php?action=forgot_password', { email });
    },

    async updateProfile(name, email) {
        return this.post('auth.php?action=update_profile', { name, email });
    },

    async changePassword(current_password, new_password, confirm_password) {
        return this.post('auth.php?action=change_password', { current_password, new_password, confirm_password });
    },

    // ── Dashboard Data (one-shot init) ────────────────────────

    async loadInitData() {
        return this.get('dashboard.php', { action: 'init_data' });
    },

    // ── Jobs ──────────────────────────────────────────────────

    async createJob(data) {
        return this.post('jobs.php?action=create', data);
    },

    async deleteJob(id) {
        return this.post('jobs.php?action=delete', { id });
    },

    // ── Applications ──────────────────────────────────────────

    async applyJob(job_uid) {
        return this.post('applications.php?action=apply', { job_uid });
    },

    async updateAppStatus(app_uid, status) {
        return this.post('applications.php?action=update_status', { app_uid, status });
    },

    async bookmarkJob(job_uid) {
        return this.post('applications.php?action=bookmark', { job_uid });
    },

    async unbookmarkJob(job_uid) {
        return this.post('applications.php?action=unbookmark', { job_uid });
    },

    // ── Training ──────────────────────────────────────────────

    async createTraining(data) {
        return this.post('training.php?action=create', data);
    },

    async enrollTraining(trn_uid) {
        return this.post('training.php?action=enroll', { trn_uid });
    },

    async unenrollTraining(trn_uid) {
        return this.post('training.php?action=unenroll', { trn_uid });
    },

    // ── Universities ──────────────────────────────────────────

    async applyUniversity(uni_uid) {
        return this.post('universities.php?action=apply', { uni_uid });
    },

    // ── Companies ─────────────────────────────────────────────

    async createCompany(data) {
        return this.post('companies.php?action=create', data);
    },

    async deleteCompany(id) {
        return this.post('companies.php?action=delete', { id });
    },

    // ── Students ──────────────────────────────────────────────

    async deleteStudent(id) {
        return this.post('students.php?action=delete', { id });
    },

    // ── Upload ────────────────────────────────────────────────

    async uploadResume(file) {
        const body = new FormData();
        body.append('resume', file);
        body.append('csrf_token', _csrfToken);
        return this._request('upload.php', { method: 'POST', body });
    },
};

window.ApiService = ApiService;

// ── Populate mockData from API init response ──────────────────

/**
 * After login, call this to fill mockData from the server.
 * All existing render functions continue working unchanged.
 */
async function populateMockData(sessionUser) {
    // Set session
    mockData.session.role       = sessionUser.role;
    mockData.session.userId     = sessionUser.uid;
    mockData.session.companyName= sessionUser.company_name || '';
    mockData.session.companyId  = sessionUser.company_id   || null;
    mockData.session.comp_uid   = sessionUser.comp_uid     || null;

    // Set current student profile
    if (sessionUser.role === 'student') {
        Object.assign(mockData.currentStudent, {
            id:                   sessionUser.student_uid || sessionUser.uid,
            name:                 sessionUser.name,
            email:                sessionUser.email,
            avatar:               sessionUser.avatar || '',
            branch:               sessionUser.branch || '',
            cgpa:                 parseFloat(sessionUser.cgpa) || 0,
            backlogs:             parseInt(sessionUser.backlogs) || 0,
            skills:               sessionUser.skills || [],
            resumeUploaded:       !!sessionUser.resume_name,
            resumeName:           sessionUser.resume_name || '',
            placementStatus:      sessionUser.placement_status || 'In Progress',
            profileCompletion:    parseInt(sessionUser.profile_completion) || 0,
        });
    }

    // Load all role-specific data in one call
    const data = await ApiService.loadInitData();
    if (!data.success) {
        console.error('[TPMS] Failed to load init data:', data.message);
        return;
    }

    if (data.jobs)          mockData.jobs          = data.jobs;
    if (data.training)      mockData.training      = data.training;
    if (data.universities)  mockData.universities  = data.universities;
    if (data.students)      mockData.students      = data.students;
    if (data.companies)     mockData.companies     = data.companies;
    if (data.applications)  mockData.applications  = data.applications;
    if (data.activities)    mockData.activities    = data.activities;
    if (data.analytics)     mockData.analytics     = data.analytics;

    // Restore student-specific state arrays
    if (sessionUser.role === 'student') {
        mockData.currentStudent.appliedJobs         = data.appliedJobUids      || [];
        mockData.currentStudent.bookmarkedJobs      = data.bookmarkedJobUids   || [];
        mockData.currentStudent.registeredTraining  = data.registeredTrnUids   || [];
        mockData.currentStudent.universityApplications = data.appliedUniUids   || [];
    }
}

window.populateMockData = populateMockData;
