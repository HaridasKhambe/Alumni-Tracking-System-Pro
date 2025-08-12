<!-- templates/notice_manager.php -->
<div class="container-fluid p-4">
    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-bell"></i> Notice Management</h5>
        </div>
        <div class="card-body">
            <form id="noticeFilterForm">
                <div class="row">
                    <!-- Search Bar -->
                    <div class="col-md-6 mb-3">
                        <label for="noticeSearchInput" class="form-label">Search by Receiver Name or PRN</label>
                        <input type="text" class="form-control" id="noticeSearchInput" 
                               placeholder="Enter receiver name or PRN number">
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="col-md-4 mb-3">
                        <label for="noticeStatusFilter" class="form-label">Status</label>
                        <select class="form-select" id="noticeStatusFilter">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="accepted">Accepted</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <!-- Clear Filters -->
                    <div class="col-md-2 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-outline-secondary w-100" id="clearNoticeFilters">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Sent Notices</h6>
            <div class="d-flex align-items-center">
                <label for="noticeLimitSelect" class="form-label me-2 mb-0">Show:</label>
                <select class="form-select form-select-sm" id="noticeLimitSelect" style="width: auto;">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <!-- Loading Spinner -->
            <div id="noticeLoadingSpinner" class="text-center py-4" style="display: none;">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            
            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Notice ID</th>
                            <th>Receiver</th>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="noticeResultsTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-bell fa-2x text-muted mb-2"></i>
                                <p class="text-muted">Use filters above to view your sent notices</p>
                            </td>
                        </tr>
                    </tbody>
                    <thead>
                        <tr>
                            <th>Notice ID</th>
                            <th>Receiver</th>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="noticePaginationInfo" class="text-muted">
                        <!-- Pagination info will be displayed here -->
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-outline-primary btn-sm me-2" id="noticePrevBtn" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button class="btn btn-outline-primary btn-sm" id="noticeNextBtn" disabled>
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Status Modal -->
<div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStatusModalLabel">Edit Notice Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                 <div class="mb-3">
                    <label for="modalReceiverName" class="form-label">Receiver:</label>
                    <p id="modalReceiverName" class="form-control-plaintext"></p>
                </div>
                
                <div class="mb-3">
                    <label for="modalNoticeTitle" class="form-label">Notice Title:</label>
                    <input type="text" class="form-control" id="modalNoticeTitle" maxlength="255">
                </div>
    
                <div class="mb-3">
                    <label for="modalMessage" class="form-label">Message:</label>
                    <textarea class="form-control" id="modalMessage" rows="4"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="modalType" class="form-label">Type:</label>
                        <select class="form-select" id="modalType">
                            <option value="invitation">Invitation</option>
                            <option value="event">Event</option>
                            <option value="reminder">Reminder</option>
                            <option value="general">General</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="modalStatusSelect" class="form-label">Status:</label>
                        <select class="form-select" id="modalStatusSelect">
                            <option value="pending">Pending</option>
                            <option value="accepted">Accepted</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="modalExpiryDate" class="form-label">Expiry Date:</label>
                    <input type="date" class="form-control" id="modalExpiryDate">
                </div>
                <input type="hidden" id="modalNoticeId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveStatusBtn">
                    <i class="fas fa-save"></i> Update Status
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom styles for notice manager */
.badge-pending { background-color: #ffc107; color: #000; }
.badge-accepted { background-color: #198754; }
.badge-rejected { background-color: #dc3545; }

:root {
            --primary-color: #667eea; 
            --secondary-color: #764ba2;
            --gradient-bg: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            --accent-color: #0d6efd;
            --hover-color: #7e68d9ff; /* light lavender hover */
        }

.table thead th {
    background-color: var(--primary-color) !important;
    color: white !important;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
}

.table td {
    font-size: 0.875rem;
    vertical-align: middle;
}

.btn-sm {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.action-buttons {
    white-space: nowrap;
}

.modal-content {
    border-radius: 0.5rem;
}

#noticeSearchInput {
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#noticeSearchInput:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>