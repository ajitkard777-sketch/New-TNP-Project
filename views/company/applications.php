<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-users text-primary me-2"></i>Job Applications</h1>
        <p class="subtitle mb-0"><?= htmlspecialchars($job['title']) ?> &mdash; <?= count($applications) ?> Total Applicants</p>
    </div>
    <a href="<?= url('/company/jobs') ?>" class="btn btn-light border btn-sm fw-semibold">
        <i class="fas fa-arrow-left me-1"></i> Back to Jobs
    </a>
</div>

<!-- Status Filter Tabs -->
<?php
$statusCounts = ['all' => count($applications)];
foreach ($applications as $a) { 
    $statusCounts[$a['status']] = ($statusCounts[$a['status']] ?? 0) + 1; 
}
?>
<ul class="nav nav-tabs mb-4" id="statusTabs">
    <li class="nav-item">
        <button type="button" class="nav-link active fw-semibold" onclick="filterApplications('all', this)">
            All (<?= $statusCounts['all'] ?>)
        </button>
    </li>
    <?php foreach (['applied','shortlisted','interview','selected','rejected'] as $s): ?>
    <?php if (($statusCounts[$s] ?? 0) > 0): ?>
    <li class="nav-item">
        <button type="button" class="nav-link fw-semibold" onclick="filterApplications('<?= $s ?>', this)">
            <?= ucfirst($s) ?> (<?= $statusCounts[$s] ?>)
        </button>
    </li>
    <?php endif; ?>
    <?php endforeach; ?>
</ul>

<?php if (empty($applications)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center p-5">
        <i class="fas fa-inbox text-muted mb-3" style="font-size:3.5rem; opacity:0.4;"></i>
        <h5 class="fw-bold text-dark mb-1">No Applications Yet</h5>
        <p class="text-muted small mb-0">No students have applied for this job position yet.</p>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0" id="applicationsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Applicant Student</th>
                        <th>Email &amp; Contact</th>
                        <th>Branch</th>
                        <th>CGPA</th>
                        <th>Resume</th>
                        <th>Status</th>
                        <th>Applied Date</th>
                        <th class="text-end pe-3">Actions &amp; Chatbox</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $a): ?>
                    <?php 
                        $studentUserId = $a['student_user_id'] ?? $a['user_id'] ?? 0;
                        $fullName = htmlspecialchars($a['first_name'] . ' ' . $a['last_name']);
                        $avatarUrl = $a['profile_photo'] ? uploadUrl('profile_photos/' . $a['profile_photo']) : asset('images/default-avatar.png');
                    ?>
                    <tr data-status="<?= $a['status'] ?>">
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= $avatarUrl ?>" 
                                     alt="" class="rounded-circle border" 
                                     style="width:38px;height:38px;object-fit:cover;" 
                                     onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                                <div>
                                    <div class="fw-bold text-dark mb-0"><?= $fullName ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($a['phone'] ?? 'No phone') ?></small>
                                </div>
                            </div>
                        </td>
                        <td><small class="text-secondary"><?= htmlspecialchars($a['email']) ?></small></td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($a['branch']) ?></span></td>
                        <td><span class="fw-bold text-primary"><?= $a['cgpa'] ?? 'N/A' ?></span></td>
                        <td>
                            <?php if ($a['resume_path']): ?>
                            <a href="<?= url('/company/download-resume/' . $a['student_id']) ?>" class="btn btn-sm btn-outline-primary py-1 px-2">
                                <i class="fas fa-file-download me-1"></i>Resume
                            </a>
                            <?php else: ?>
                            <span class="text-muted small">No resume</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= getStatusBadgeClass($a['status']) ?>"><?= ucfirst($a['status']) ?></span>
                        </td>
                        <td><small class="text-muted"><?= timeAgo($a['applied_at']) ?></small></td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex align-items-center gap-1">
                                <!-- Chatbox Modal Launcher Button -->
                                <button type="button" 
                                        onclick="openDirectChatModal(<?= $studentUserId ?>, '<?= addslashes($fullName) ?>', '<?= addslashes($avatarUrl) ?>')" 
                                        class="btn btn-sm btn-primary py-1 px-2 fw-semibold shadow-sm">
                                    <i class="fas fa-comments me-1"></i> Chatbox
                                </button>

                                <!-- Status Dropdown -->
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-light border dropdown-toggle py-1 px-2" data-bs-toggle="dropdown">
                                        Action
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <?php foreach (['shortlisted'=>'Shortlist','interview'=>'Schedule Interview','selected'=>'Select Candidate','rejected'=>'Reject'] as $sk=>$sv): ?>
                                        <?php if ($a['status'] !== $sk): ?>
                                        <li>
                                            <a class="dropdown-item small" href="#" onclick="updateStatus(<?= $a['id'] ?>,'<?= $sk ?>')">
                                                <i class="fas fa-<?= $sk === 'selected' ? 'check-circle text-success' : ($sk === 'rejected' ? 'times-circle text-danger' : ($sk === 'shortlisted' ? 'star text-warning' : 'calendar text-info')) ?> me-2"></i><?= $sv ?>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item small text-primary" href="<?= url('/company/messages?partner=' . $studentUserId) ?>">
                                                <i class="fas fa-external-link-alt me-2"></i>Open Full Messages
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item small" href="#" data-bs-toggle="modal" data-bs-target="#scheduleModal" onclick="setInterviewApp(<?= $a['id'] ?>)">
                                                <i class="fas fa-calendar-plus text-info me-2"></i>Schedule Interview
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Schedule Interview Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-calendar-plus me-2"></i>Schedule Interview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleForm" method="POST">
                <?= CsrfMiddleware::tokenField() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-semibold small">Round *</label><input type="text" class="form-control" name="round" value="Round 1" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold small">Mode</label><select class="form-select" name="mode"><option value="offline">Offline</option><option value="online">Online</option></select></div>
                        <div class="col-md-6"><label class="form-label fw-semibold small">Date *</label><input type="date" class="form-control" name="interview_date" min="<?= date('Y-m-d') ?>" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold small">Time *</label><input type="time" class="form-control" name="interview_time" required></div>
                        <div class="col-12"><label class="form-label fw-semibold small">Venue</label><input type="text" class="form-control" name="venue" placeholder="e.g. Auditorium Room 102"></div>
                        <div class="col-12"><label class="form-label fw-semibold small">Meeting Link</label><input type="url" class="form-control" name="meeting_link" placeholder="https://zoom.us/..."></div>
                        <div class="col-12"><label class="form-label fw-semibold small">Instructions</label><textarea class="form-control" name="instructions" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i> Schedule Interview</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- LIVE APPLICANT CHATBOX MODAL -->
<div class="modal fade" id="jobChatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:14px; overflow:hidden;">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white py-3 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <img id="chatModalAvatar" src="" alt="Avatar" class="rounded-circle border border-2 border-white" style="width:42px;height:42px;object-fit:cover;">
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="chatModalName">Applicant Name</h6>
                        <small class="text-white-50" style="font-size:0.75rem;"><i class="fas fa-circle text-success me-1" style="font-size:0.5rem;"></i> Active Chat Session</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a id="chatModalFullLink" href="#" target="_blank" class="btn btn-sm btn-light py-1 px-2 text-primary fw-semibold" style="font-size:0.75rem;">
                        <i class="fas fa-external-link-alt me-1"></i> Full Chat Page
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <!-- Chat Messages Container -->
            <div class="modal-body p-4 bg-light" id="chatModalMessagesContainer" style="height:380px; overflow-y:auto; display:flex; flex-direction:column; gap:12px;">
                <div class="text-center text-muted my-auto" id="chatModalLoading">
                    <i class="fas fa-spinner fa-spin me-1"></i> Loading conversation history...
                </div>
            </div>

            <!-- Message Input Footer -->
            <div class="modal-footer bg-white border-top p-3">
                <form id="chatModalForm" class="w-100 d-flex gap-2 align-items-center" onsubmit="sendDirectChatMessage(event)">
                    <input type="text" id="chatModalInput" class="form-control" placeholder="Type your message to applicant..." autocomplete="off" required>
                    <button type="submit" id="chatModalSendBtn" class="btn btn-primary fw-semibold px-3 flex-shrink-0">
                        <i class="fas fa-paper-plane me-1"></i> Send
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let currentChatPartnerId = 0;
let chatPollInterval = null;

function openDirectChatModal(partnerUserId, partnerName, partnerAvatar) {
    currentChatPartnerId = partnerUserId;
    $('#chatModalName').text(partnerName);
    $('#chatModalAvatar').attr('src', partnerAvatar);
    $('#chatModalFullLink').attr('href', TPMS.baseUrl + '/company/messages?partner=' + partnerUserId);
    
    $('#chatModalMessagesContainer').html('<div class="text-center text-muted my-auto"><i class="fas fa-spinner fa-spin me-1"></i> Loading conversation...</div>');
    $('#jobChatModal').modal('show');
    
    fetchChatHistory();

    if (chatPollInterval) clearInterval(chatPollInterval);
    chatPollInterval = setInterval(fetchChatHistory, 3000);
}

$('#jobChatModal').on('hidden.bs.modal', function () {
    if (chatPollInterval) {
        clearInterval(chatPollInterval);
        chatPollInterval = null;
    }
});

function fetchChatHistory() {
    if (!currentChatPartnerId) return;

    $.ajax({
        url: TPMS.baseUrl + '/messages/history',
        type: 'GET',
        data: { partner_id: currentChatPartnerId },
        dataType: 'json',
        success: function(res) {
            if (res && res.success) {
                renderChatMessages(res.messages || []);
            }
        }
    });
}

function renderChatMessages(messages) {
    const container = $('#chatModalMessagesContainer');
    if (!messages || messages.length === 0) {
        container.html('<div class="text-center text-muted my-auto"><i class="fas fa-comments mb-2" style="font-size:2rem; opacity:0.4;"></i><br><small>No previous messages. Type a message below to start chatting with applicant.</small></div>');
        return;
    }

    let html = '';
    messages.forEach(function(m) {
        const isSelf = m.is_self;
        html += `
            <div class="d-flex flex-column ${isSelf ? 'align-items-end' : 'align-items-start'} mb-2">
                <div class="p-3 rounded-3 shadow-sm max-w-75 ${isSelf ? 'bg-primary text-white' : 'bg-white text-dark border'}" style="max-width:75%; font-size:0.88rem; line-height:1.4;">
                    ${escapeHtml(m.message_text)}
                </div>
                <small class="text-muted mt-1" style="font-size:0.7rem;">${m.time_ago || ''}</small>
            </div>
        `;
    });

    const isAtBottom = container[0].scrollHeight - container.scrollTop() - container.outerHeight() < 100;
    container.html(html);

    if (isAtBottom || container.children('.text-center').length > 0) {
        container.scrollTop(container[0].scrollHeight);
    }
}

function sendDirectChatMessage(e) {
    e.preventDefault();
    const input = $('#chatModalInput');
    const text = input.val().trim();
    if (!text || !currentChatPartnerId) return;

    const btn = $('#chatModalSendBtn');
    btn.prop('disabled', true);

    $.ajax({
        url: TPMS.baseUrl + '/messages/send',
        type: 'POST',
        data: {
            receiver_id: currentChatPartnerId,
            message_text: text,
            csrf_token: TPMS.csrfToken
        },
        dataType: 'json',
        success: function(res) {
            btn.prop('disabled', false);
            if (res && res.success) {
                input.val('');
                fetchChatHistory();
            } else {
                if (typeof TPMS.showToast === 'function') {
                    TPMS.showToast(res.message || 'Failed to send message', 'error');
                }
            }
        },
        error: function(xhr) {
            btn.prop('disabled', false);
            let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error sending message';
            if (typeof TPMS.showToast === 'function') {
                TPMS.showToast(msg, 'error');
            }
        }
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function updateStatus(appId, status) {
    if (!confirm('Update application status to ' + status + '?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = TPMS.baseUrl + '/company/update-application/' + appId;
    form.innerHTML = '<input name="csrf_token" value="' + TPMS.csrfToken + '"><input name="status" value="' + status + '">';
    document.body.appendChild(form);
    form.submit();
}

function setInterviewApp(appId) {
    document.getElementById('scheduleForm').action = TPMS.baseUrl + '/company/schedule-interview/' + appId;
}

function filterApplications(filter, btnElem) {
    document.querySelectorAll('#statusTabs .nav-link').forEach(t => t.classList.remove('active'));
    if (btnElem) {
        btnElem.classList.add('active');
    }
    
    document.querySelectorAll('#applicationsTable tbody tr').forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        if (filter === 'all' || rowStatus === filter) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
