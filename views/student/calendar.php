<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<!-- FullCalendar Library -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<style>
#placementCalendar {
    max-width: 100%;
}
.fc-theme-standard td, .fc-theme-standard th {
    border-color: #e2e8f0 !important;
}
.fc-header-toolbar {
    margin-bottom: 0.75rem !important;
    padding: 0.25rem 0.5rem;
}
.fc-toolbar-title {
    font-size: 1.15rem !important;
    font-weight: 700 !important;
    color: #1e293b;
}
.fc-button {
    font-size: 0.8rem !important;
    padding: 0.25rem 0.6rem !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    box-shadow: none !important;
}
.fc-button-primary {
    background-color: #2563eb !important;
    border-color: #2563eb !important;
}
.fc-button-primary:hover {
    background-color: #1d4ed8 !important;
}
.fc-daygrid-day-number {
    font-size: 0.8rem !important;
    font-weight: 700;
    color: #475569;
    padding: 4px 6px !important;
}
.fc-col-header-cell-cushion {
    font-size: 0.8rem !important;
    font-weight: 700;
    text-transform: uppercase;
    color: #334155;
    letter-spacing: 0.5px;
    padding: 6px 0 !important;
}
.fc-daygrid-event {
    border-radius: 4px !important;
    padding: 3px 6px !important;
    font-size: 0.78rem !important;
    font-weight: 600 !important;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    border: none !important;
    margin-top: 2px !important;
    line-height: 1.35 !important;
    color: #ffffff !important;
}
.fc-daygrid-event-dot {
    display: none !important;
}
.fc-event-title {
    font-weight: 600 !important;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.fc-event-time {
    font-weight: 500 !important;
    margin-right: 3px;
    opacity: 0.95;
}
.fc-more-link {
    font-size: 0.75rem !important;
    font-weight: 700 !important;
    color: #2563eb !important;
}
</style>

<div class="content-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-calendar-alt text-primary me-2"></i>Placement Calendar</h1>
        <p class="subtitle mb-0">Stay updated on upcoming placement drives, mock tests, interview schedules, workshops, and deadlines.</p>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="badge text-white p-2" style="background:#2563eb !important;"><i class="fas fa-calendar-check me-1"></i> Interviews</span>
        <span class="badge text-white p-2" style="background:#059669 !important;"><i class="fas fa-chalkboard-teacher me-1"></i> Trainings</span>
        <span class="badge text-white p-2" style="background:#d97706 !important;"><i class="fas fa-briefcase me-1"></i> Placement Drives</span>
        <span class="badge text-white p-2" style="background:#7c3aed !important;"><i class="fas fa-clock me-1"></i> Deadlines</span>
    </div>
</div>

<!-- Calendar Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div id="placementCalendar"></div>
    </div>
</div>

<!-- EVENT DETAILS MODAL -->
<div class="modal fade" id="eventDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" id="eventModalHeader" style="background: #2563eb;">
                <h5 class="modal-title fw-bold" id="eventModalTitle">Event Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="badge px-3 py-2 text-capitalize mb-3" id="eventModalTypeBadge">Event</div>
                
                <p class="small text-secondary mb-3" id="eventModalDescription"></p>

                <div class="p-3 bg-light rounded-3">
                    <div class="row g-2 small">
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size:0.75rem;">DATE</span>
                            <strong class="text-dark" id="eventModalDate"></strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size:0.75rem;">TIME</span>
                            <strong class="text-dark" id="eventModalTime"></strong>
                        </div>
                        <div class="col-6 mt-2">
                            <span class="text-muted d-block" style="font-size:0.75rem;">VENUE / LOCATION</span>
                            <strong class="text-dark" id="eventModalVenue"></strong>
                        </div>
                        <div class="col-6 mt-2">
                            <span class="text-muted d-block" style="font-size:0.75rem;">ORGANIZER</span>
                            <strong class="text-dark" id="eventModalOrganizer"></strong>
                        </div>
                    </div>
                </div>

                <div id="eventModalCompanyRow" class="mt-3 d-none">
                    <small class="text-muted d-block" style="font-size:0.75rem;">RELATED COMPANY</small>
                    <strong class="text-dark fs-6" id="eventModalCompany"></strong>
                </div>

                <div id="eventModalRegRow" class="mt-3 d-none">
                    <a href="#" id="eventModalRegBtn" class="btn btn-primary btn-sm w-100"><i class="fas fa-external-link-alt me-1"></i> View Destination / Details</a>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('placementCalendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        height: 580,
        contentHeight: 520,
        aspectRatio: 1.85,
        dayMaxEvents: 3,
        eventDisplay: 'block',
        displayEventTime: true,
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short',
            omitZeroMinute: true
        },
        themeSystem: 'standard',
        events: function(fetchInfo, successCallback, failureCallback) {
            $.ajax({
                url: TPMS.baseUrl + '/student/calendar-events',
                type: 'GET',
                data: {
                    start: fetchInfo.startStr.substring(0, 10),
                    end: fetchInfo.endStr.substring(0, 10)
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var events = response.events.map(function(item) {
                            return {
                                id: item.id,
                                title: item.title,
                                start: item.date + (item.start_time !== 'All Day' ? 'T' + convertTo24Hour(item.start_time) : ''),
                                backgroundColor: item.color,
                                borderColor: item.color,
                                extendedProps: item
                            };
                        });
                        successCallback(events);
                    } else {
                        failureCallback();
                    }
                },
                error: function() {
                    failureCallback();
                }
            });
        },
        eventClick: function(info) {
            var props = info.event.extendedProps;
            
            $('#eventModalTitle').text(props.title);
            $('#eventModalHeader').css('background', props.color || '#2563eb');
            $('#eventModalTypeBadge').text(props.event_type.replace('_', ' ')).css('background', props.color || '#2563eb').addClass('text-white');
            $('#eventModalDescription').text(props.description || 'No additional details provided.');
            $('#eventModalDate').text(props.date);
            $('#eventModalTime').text(props.start_time || 'All Day');
            $('#eventModalVenue').text(props.venue || 'Campus / Online');
            $('#eventModalOrganizer').text(props.organizer || 'T&P Cell');

            if (props.company_name) {
                $('#eventModalCompany').text(props.company_name);
                $('#eventModalCompanyRow').removeClass('d-none');
            } else {
                $('#eventModalCompanyRow').addClass('d-none');
            }

            if (props.registration_link) {
                $('#eventModalRegBtn').attr('href', props.registration_link);
                $('#eventModalRegRow').removeClass('d-none');
            } else {
                $('#eventModalRegRow').addClass('d-none');
            }

            new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
        }
    });

    calendar.render();

    function convertTo24Hour(timeStr) {
        if (!timeStr || timeStr === 'All Day' || !timeStr.includes(':')) return '09:00:00';
        var parts = timeStr.match(/(\d+):(\d+)\s*(AM|PM)?/i);
        if (!parts) return '09:00:00';
        var hours = parseInt(parts[1]);
        var minutes = parseInt(parts[2]);
        var ampm = parts[3];
        if (ampm) {
            if (ampm.toUpperCase() === 'PM' && hours < 12) hours += 12;
            if (ampm.toUpperCase() === 'AM' && hours === 12) hours = 0;
        }
        return (hours < 10 ? '0' + hours : hours) + ':' + (minutes < 10 ? '0' + minutes : minutes) + ':00';
    }
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
