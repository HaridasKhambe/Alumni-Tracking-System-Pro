<?php
session_start();

// making sure........ that user is set & role is correct
require_once '../../config/auth_helper.php';
requireRole('alumni');

// Page configuration
$page_title = "Alumni Dashboard - ATS";
$current_page = "events";

// Adding Reusable templates
include_once '../../templates/header.php'; //navbar global style
include_once '../../templates/navbar_alumni.php'; //navbar for alumini
?>

<!-- use .... templates/alumni_events.php -->
<script src="../../assets/js/alumni_events.js"></script>
<!-- -------------------------------------------------------------------------------------------------- -->
<div class="container-fluid p-4">
    <div class="row">
        <div class="col-md-10 col-lg-11 ms-sm-auto px-md-4">
            <!-- Header Section -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-calendar-alt me-2"></i>Events</h1>
            </div>

            <!-- Filter Section -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="eventSearchInput" 
                               placeholder="Search events by title...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="eventStatusFilter">
                        <option value="all">All Events</option>
                        <option value="upcoming">Upcoming Events</option>
                        <option value="live">Live Events</option>
                        <option value="past">Past Events</option>
                    </select>
                </div>
            </div>

            <!-- Event Stats -->
            <div class="row mb-4" id="eventStats" style="display: none;">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0" id="totalEvents">0</h5>
                                    <small>Total Events</small>
                                </div>
                                <i class="fas fa-calendar fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0" id="upcomingEvents">0</h5>
                                    <small>Upcoming</small>
                                </div>
                                <i class="fas fa-clock fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card text-white mylive">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0" id="liveEvents">0</h5>
                                    <small>Live</small>
                                </div>
                                <i class="fas fa-dot-circle fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0" id="pastEvents">0</h5>
                                    <small>Past</small>
                                </div>
                                <i class="fas fa-history fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading events...</p>
            </div>

            <!-- Events Container -->
            <div id="eventsContainer" class="row g-4" style="display: none;">
                <!-- Event cards will be loaded here -->
            </div>

            <!-- No Events Message -->
            <div id="noEventsMessage" class="text-center py-5" style="display: none;">
                <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Events Found</h4>
                <p class="text-muted">There are no events matching your criteria at the moment.</p>
            </div>
        </div>
    </div>
</div>

<!-- Event Detail Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted">Status:</small>
                        <span id="modalStatus" class="badge ms-1"></span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Event Date:</small>
                        <div id="modalEventDate" class="fw-bold"></div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted">Location:</small>
                        <div id="modalLocation"></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Organized by:</small>
                        <div id="modalOrganizer"></div>
                    </div>
                </div>
                <div class="row mb-3" id="modalImageContainer" style="display: none;">
                    <div class="col-12">
                        <img id="modalImage" src="" class="img-fluid rounded" alt="Event Image">
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <h6>Event Details:</h6>
                    <div id="modalDetails" class="p-3 bg-light rounded"></div>
                </div>
                <div id="modalRegistrationContainer" style="display: none;">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Registration Available:</strong>
                        <a id="modalRegistrationLink" href="#" target="_blank" class="alert-link">Click here to register</a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>

    :root {
            --primary-color: #667eea; 
            --secondary-color: #764ba2;
            --gradient-bg: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            --accent-color: #0d6efd;
            --hover-color: #7e68d9ff; /* light lavender hover */
        }

.mylive{
    background-color: var(--secondary-color);
}
.event-card {
    border-left: 4px solid var(--accent-color);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    height: 100%;
    min-height: 250px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.event-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.event-card.upcoming {
    border-left-color: var(--accent-color);
}

.event-card.live {
    border-left-color: var(--secondary-color);
}

.event-card.past {
    border-left-color: #6c757d;
    opacity: 0.8;
}

.event-card.past:hover {
    transform: translateY(-1px);
}

.event-date {
    background: var(--accent-color);
    color: white;
    border-radius: 8px;
    padding: 8px 12px;
    text-align: center;
    margin-bottom: 15px;
}

.event-date.live {
    background: var(--secondary-color);
}

.event-date.past {
    background: #6c757d;
}

.event-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.event-details-preview {
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.4;
}

.event-location {
    color: #495057;
    font-weight: 500;
}

.badge-upcoming {
    background-color: var(--accent-color);
}

.badge-live {
    background-color: var(--secondary-color);
}

.badge-past {
    background-color: #6c757d;
}

.event-organizer {
    font-size: 0.75rem;
    color: #6c757d;
    border-top: 1px solid #e9ecef;
    padding-top: 8px;
    margin-top: auto;
}

.card-body {
    display: flex;
    flex-direction: column;
}

</style>

<?php include_once '../../templates/footer.php'; ?>