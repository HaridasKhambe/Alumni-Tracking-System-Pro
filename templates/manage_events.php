<!-- templates/events.php -->
<div class="container-fluid p-4">
    <!-- Header Section with Add Event Button -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Event Management</h5>
        </div>
        <div class="card-body">
            <form id="eventsFilterForm">
                <div class="row">
                    <!-- Search Bar -->
                    <div class="col-md-6 mb-3">
                        <label for="eventsSearchInput" class="form-label">Search by Event Title</label>
                        <input type="text" class="form-control" id="eventsSearchInput" 
                               placeholder="Enter event title">
                    </div>

                     <!-- Add Admin Button -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-success w-100" id="addEventBtn">
                            <i class="fas fa-plus"></i> Add New Event
                        </button>
                    </div>
                    
                    <!-- Clear Filters -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-outline-secondary w-100" id="clearEventsFilters">
                            <i class="fas fa-times"></i> Clear Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">All Events</h6>
            <div class="d-flex align-items-center">
                <label for="eventsLimitSelect" class="form-label me-2 mb-0">Show:</label>
                <select class="form-select form-select-sm" id="eventsLimitSelect" style="width: auto;">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <!-- Loading Spinner -->
            <div id="eventsLoadingSpinner" class="text-center py-4" style="display: none;">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            
            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Title</th>
                            <th>Event Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th width="120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="eventsResultsTableBody">
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-calendar fa-2x text-muted mb-2"></i>
                                <p class="text-muted">Loading events...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="eventsPaginationInfo" class="text-muted">
                        <!-- Pagination info will be displayed here -->
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-outline-primary btn-sm me-2" id="eventsPrevBtn" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button class="btn btn-outline-primary btn-sm" id="eventsNextBtn" disabled>
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">Add New Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="eventForm">
                    <input type="hidden" id="eventId" name="eventId">
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="eventTitle" class="form-label">Event Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="eventTitle" name="title" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="eventDate" class="form-label">Event Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="eventDate" name="event_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="eventLocation" class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="eventLocation" name="location" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="eventDetails" class="form-label">Event Details <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="eventDetails" name="details" rows="4" required></textarea>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="eventImageUrl" class="form-label">Image URL</label>
                            <input type="url" class="form-control" id="eventImageUrl" name="image_url" placeholder="https://example.com/image.jpg">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="eventRegistrationLink" class="form-label">Registration Link</label>
                            <input type="url" class="form-control" id="eventRegistrationLink" name="registration_link" placeholder="https://example.com/register">
                        </div>
                    </div>
                    
                    <div class="text-muted small mb-3">
                        <i class="fas fa-info-circle"></i> Event status will be automatically determined based on the event date.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEventBtn">
                    <i class="fas fa-save"></i> Save Event
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom styles for event management */
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
}

.table td {
    font-size: 0.875rem;
    vertical-align: middle;
}

.btn-edit {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.btn-edit:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
    color: white;
}

.btn-delete {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.btn-delete:hover {
    background-color: #bb2d3b;
    border-color: #b02a37;
    color: white;
}

.action-buttons {
    white-space: nowrap;
}

.badge-upcoming {
    background-color: #0d6efd;
}

.badge-live {
    background-color: #198754;
    animation: pulse 2s infinite;
}

.badge-past {
    background-color: #6c757d;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.event-creator {
    font-size: 0.8rem;
    color: #6c757d;
}

.event-title {
    font-weight: 600;
    color: #2c3e50;
}

.event-details {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

#eventsSearchInput {
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#eventsSearchInput:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.table-responsive {
    border-radius: 0.375rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.modal-lg {
    max-width: 900px;
}

.text-danger {
    color: #dc3545 !important;
}

.required-field {
    position: relative;
}

.required-field::after {
    content: "*";
    color: #dc3545;
    margin-left: 3px;
}
</style>