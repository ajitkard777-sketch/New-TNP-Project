<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<style>
.question-nav-btn {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #475569;
    transition: all 0.2s;
}
.question-nav-btn.active {
    border-color: var(--tpms-primary, #2563eb);
    background: var(--tpms-primary, #2563eb);
    color: #fff;
    box-shadow: 0 4px 6px -1px rgba(37,99,235,0.25);
}
.question-nav-btn.answered {
    background: #dcfce7;
    border-color: #86efac;
    color: #166534;
}
.option-card {
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 18px;
    cursor: pointer;
    transition: all 0.2s;
    background: #ffffff;
}
.option-card:hover {
    border-color: var(--tpms-primary, #2563eb);
    background: #f0f9ff;
}
.option-card.selected {
    border-color: var(--tpms-primary, #2563eb);
    background: #eff6ff;
    box-shadow: 0 0 0 1px var(--tpms-primary, #2563eb);
}
</style>

<?php if (empty($questions)): ?>
<div class="card shadow-sm border-0 mt-4 text-center p-5">
    <div class="card-body">
        <i class="fas fa-exclamation-triangle text-warning mb-3" style="font-size:3.5rem;"></i>
        <h4 class="fw-bold">No Questions Found</h4>
        <p class="text-muted">This test module does not have any active questions assigned yet.</p>
        <a href="<?= url('/student/mock-tests') ?>" class="btn btn-primary btn-sm px-4 fw-semibold mt-2">
            <i class="fas fa-arrow-left me-1"></i> Return to Mock Tests
        </a>
    </div>
</div>
<?php else: ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($test['title']) ?></h2>
        <span class="badge bg-primary-soft text-primary fw-semibold me-2"><?= htmlspecialchars($test['category']) ?></span>
        <small class="text-muted"><i class="fas fa-clock me-1"></i><?= $test['duration_minutes'] ?> Minutes Limit</small>
    </div>
    
    <!-- Countdown Timer Card -->
    <div class="card border-0 shadow-sm bg-primary text-white px-4 py-2 text-center">
        <small class="text-white-50 text-uppercase fw-bold" style="font-size:0.7rem; letter-spacing:1px;">Time Remaining</small>
        <div id="timerDisplay" class="fw-bold fs-4" style="font-family:monospace;">00:00</div>
    </div>
</div>

<form id="mockTestForm" action="<?= url('/student/submit-mock-test/' . $test['id']) ?>" method="POST">
    <?= CsrfMiddleware::tokenField() ?>
    <input type="hidden" name="time_taken" id="timeTakenInput" value="0">

    <div class="row g-4">
        <!-- Question Content -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <span class="fw-bold text-primary" id="qNumHeader">Question 1 of <?= count($questions) ?></span>
                    <span class="badge bg-light text-secondary border">1 Mark</span>
                </div>
                <div class="card-body p-4">
                    
                    <?php foreach ($questions as $idx => $q): ?>
                    <div class="question-block" id="qBlock_<?= $idx ?>" style="<?= $idx === 0 ? '' : 'display:none;' ?>">
                        <h5 class="fw-bold text-dark mb-4" style="line-height:1.5;">
                            <span class="text-primary me-2">Q<?= $idx + 1 ?>.</span><?= htmlspecialchars($q['question_text']) ?>
                        </h5>

                        <div class="d-flex flex-column gap-3 mb-4">
                            <?php foreach (['a' => $q['option_a'], 'b' => $q['option_b'], 'c' => $q['option_c'], 'd' => $q['option_d']] as $optKey => $optVal): ?>
                            <label class="option-card d-flex align-items-center gap-3 mb-0" id="optLabel_<?= $q['id'] ?>_<?= $optKey ?>">
                                <input type="radio" 
                                       name="answers[<?= $q['id'] ?>]" 
                                       value="<?= $optKey ?>" 
                                       class="form-check-input flex-shrink-0"
                                       onchange="selectOption(<?= $idx ?>, <?= $q['id'] ?>, '<?= $optKey ?>')">
                                <span class="fw-semibold text-secondary me-1"><?= strtoupper($optKey) ?>.</span>
                                <span class="text-dark fw-medium"><?= htmlspecialchars($optVal) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-4">
                        <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" id="prevBtn" onclick="navigateQuestion(-1)" disabled>
                            <i class="fas fa-chevron-left me-1"></i> Previous
                        </button>
                        
                        <div>
                            <button type="button" class="btn btn-primary px-4 fw-semibold me-2" id="nextBtn" onclick="navigateQuestion(1)">
                                Next <i class="fas fa-chevron-right ms-1"></i>
                            </button>
                            <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm" id="submitBtn" onclick="return confirm('Are you sure you want to submit your test now?');">
                                <i class="fas fa-check-circle me-1"></i> Submit Test
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Question Navigator Grid -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-th me-2 text-primary"></i>Question Navigator</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap gap-2 mb-4" id="navGrid">
                        <?php foreach ($questions as $idx => $q): ?>
                        <div class="question-nav-btn <?= $idx === 0 ? 'active' : '' ?>" 
                             id="navBtn_<?= $idx ?>" 
                             onclick="goToQuestion(<?= $idx ?>)">
                            <?= $idx + 1 ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-top pt-3 small text-muted">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="question-nav-btn active" style="width:20px; height:20px; font-size:0.6rem;">1</span>
                            <span>Current Question</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="question-nav-btn answered" style="width:20px; height:20px; font-size:0.6rem;">1</span>
                            <span>Answered Question</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="question-nav-btn" style="width:20px; height:20px; font-size:0.6rem;">1</span>
                            <span>Unanswered Question</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>

<script>
var totalQuestions = <?= count($questions) ?>;
var currentIdx = 0;
var answeredMap = {};
var durationSeconds = <?= (int)$test['duration_minutes'] * 60 ?>;
var timeTakenSeconds = 0;
var timerInterval = null;

function startTimer() {
    var timerDisplay = document.getElementById('timerDisplay');
    timerInterval = setInterval(function() {
        durationSeconds--;
        timeTakenSeconds++;
        document.getElementById('timeTakenInput').value = timeTakenSeconds;

        var m = Math.floor(durationSeconds / 60);
        var s = durationSeconds % 60;
        timerDisplay.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;

        if (durationSeconds <= 0) {
            clearInterval(timerInterval);
            alert('Time is up! Submitting your test automatically.');
            document.getElementById('mockTestForm').submit();
        }
    }, 1000);
}

function selectOption(idx, qid, optKey) {
    answeredMap[idx] = true;
    document.getElementById('navBtn_' + idx).classList.add('answered');

    // Toggle card selection state
    ['a','b','c','d'].forEach(function(k) {
        var el = document.getElementById('optLabel_' + qid + '_' + k);
        if (el) el.classList.remove('selected');
    });
    var selectedEl = document.getElementById('optLabel_' + qid + '_' + optKey);
    if (selectedEl) selectedEl.classList.add('selected');
}

function goToQuestion(idx) {
    if (idx < 0 || idx >= totalQuestions) return;
    
    document.getElementById('qBlock_' + currentIdx).style.display = 'none';
    document.getElementById('navBtn_' + currentIdx).classList.remove('active');

    currentIdx = idx;

    document.getElementById('qBlock_' + currentIdx).style.display = 'block';
    document.getElementById('navBtn_' + currentIdx).classList.add('active');

    document.getElementById('qNumHeader').textContent = 'Question ' + (currentIdx + 1) + ' of ' + totalQuestions;
    document.getElementById('prevBtn').disabled = (currentIdx === 0);
    document.getElementById('nextBtn').disabled = (currentIdx === totalQuestions - 1);
}

function navigateQuestion(step) {
    goToQuestion(currentIdx + step);
}

document.addEventListener('DOMContentLoaded', function() {
    startTimer();
});
</script>

<?php endif; ?>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
