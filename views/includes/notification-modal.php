<?php
/**
 * TPMS - Full Notification View Modal
 */
?>
<div class="modal fade" id="notificationDetailModal" tabindex="-1" aria-labelledby="notifModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 pb-0" style="background:var(--bg-card);">
                <div class="d-flex align-items-center gap-3">
                    <div id="notifModalIconContainer" class="p-3 rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i id="notifModalIcon" class="fas fa-bell fs-5"></i>
                    </div>
                    <div>
                        <span id="notifModalCategory" class="badge bg-primary text-uppercase" style="font-size:0.65rem;letter-spacing:0.5px;">NOTIFICATION</span>
                        <div id="notifModalTime" class="text-muted small mt-1"><i class="far fa-clock me-1"></i>Just now</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4 px-4">
                <h5 id="notifModalTitle" class="fw-bold mb-3" style="color:var(--text-primary);word-break:break-word;">Notification Details</h5>
                <div id="notifModalMessage" class="text-secondary" style="font-size:0.95rem;line-height:1.6;white-space:pre-wrap;word-break:break-word;"></div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Close</button>
                <a id="notifModalActionBtn" href="#" class="btn btn-primary px-4 d-none">
                    <i class="fas fa-external-link-alt me-2"></i><span id="notifModalActionText">Go to Details</span>
                </a>
            </div>
        </div>
    </div>
</div>
