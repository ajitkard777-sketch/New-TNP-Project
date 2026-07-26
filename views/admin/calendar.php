<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h1 class="page-title"><i class="fas fa-calendar-alt text-primary me-2"></i>Placement Calendar Management</h1>
        <p class="subtitle">Schedule and manage recruitment drives, mock tests, workshops, and deadlines for students.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEventModal">
        <i class="fas fa-calendar-plus me-1"></i> Add Calendar Event
    </button>
</div>

<!-- Event Types Legend & Add Modal -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex flex-wrap gap-2">
                <span class="badge text-white p-2" style="background:#2563eb !important;"><i class="fas fa-calendar-check me-1"></i> Interview (Blue)</span>
                <span class="badge text-white p-2" style="background:#059669 !important;"><i class="fas fa-chalkboard-teacher me-1"></i> Training (Green)</span>
                <span class="badge text-white p-2" style="background:#d97706 !important;"><i class="fas fa-briefcase me-1"></i> Placement Drive (Orange)</span>
                <span class="badge text-white p-2" style="background:#7c3aed !important;"><i class="fas fa-clock me-1"></i> Deadline (Purple)</span>
            </div>
            <div>
                <a href="<?= url('/student/calendar') ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-eye me-1"></i> View Student Calendar
                </a>
            </div>
        </div>
    </div>
</div>

<!-- CREATE CALENDAR EVENT MODAL -->
<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form action="<?= url('/admin/create-calendar-event') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= CsrfMiddleware::getToken() ?>">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-calendar-plus me-2"></i>Schedule Placement Calendar Event</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Event Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. TCS CodeVita Drive 2026" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Event Category <span class="text-danger">*</span></label>
                            <select name="event_type" id="eventTypeSelect" class="form-select" onchange="updateEventColor(this.value)" required>
                                <option value="drive">Placement Drive (Green)</option>
                                <option value="interview">Interview Schedule (Blue)</option>
                                <option value="mock_test">Mock Test (Orange)</option>
                                <option value="workshop">Workshop / Seminar (Purple)</option>
                                <option value="deadline">Registration Deadline (Red)</option>
                                <option value="training">Training Session (Teal)</option>
                                <option value="activity">General Activity (Indigo)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Event Date <span class="text-danger">*</span></label>
                            <input type="date" name="event_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Start Time</label>
                            <input type="time" name="start_time" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">End Time</label>
                            <input type="time" name="end_time" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Venue / Online Link</label>
                            <input type="text" name="venue" class="form-control" placeholder="e.g. Main Auditorium / Zoom Link">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Organizer</label>
                            <input type="text" name="organizer" class="form-control" placeholder="e.g. T&P Cell / Company HR">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Related Company (Optional)</label>
                            <select name="company_id" class="form-select">
                                <option value="">None / General</option>
                                <?php foreach ($companies as $comp): ?>
                                <option value="<?= $comp['id'] ?>"><?= htmlspecialchars($comp['company_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Registration Link (Optional)</label>
                            <input type="url" name="registration_link" class="form-control" placeholder="https://...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Event Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Provide instructions, eligibility criteria, or program details..."></textarea>
                        </div>
                        <input type="hidden" name="color" id="eventColorInput" value="#22c55e">
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i> Create Event &amp; Notify Students</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateEventColor(type) {
    var colors = {
        'interview': '#2563eb',
        'drive': '#d97706',
        'mock_test': '#f97316',
        'workshop': '#059669',
        'deadline': '#7c3aed',
        'training': '#059669',
        'activity': '#6366f1'
    };
    $('#eventColorInput').val(colors[type] || '#2563eb');
}
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
