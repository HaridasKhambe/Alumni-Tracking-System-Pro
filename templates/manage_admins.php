<!-- templates/manage_admins.php -->
<div class="container-fluid p-4">
    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users-cog"></i> Admin Management</h5>
        </div>
        <div class="card-body">
            <form id="adminFilterForm">
                <div class="row">
                    <!-- Search Bar -->
                    <div class="col-md-6 mb-3">
                        <label for="adminSearchInput" class="form-label">Search by Email</label>
                        <input type="text" class="form-control" id="adminSearchInput" 
                               placeholder="Enter admin email">
                    </div>
                    
                    <!-- Add Admin Button -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-success w-100" id="addAdminBtn">
                            <i class="fas fa-plus"></i> Add New Admin
                        </button>
                    </div>
                    
                    <!-- Clear Filters -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-outline-secondary w-100" id="clearAdminFilters">
                            <i class="fas fa-times"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Admin List</h6>
            <div class="d-flex align-items-center">
                <label for="adminLimitSelect" class="form-label me-2 mb-0">Show:</label>
                <select class="form-select form-select-sm" id="adminLimitSelect" style="width: auto;">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <!-- Loading Spinner -->
            <div id="adminLoadingSpinner" class="text-center py-4" style="display: none;">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            
            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th width="200px">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adminResultsTableBody">
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                <p class="text-muted">Loading admin data...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="adminPaginationInfo" class="text-muted">
                        <!-- Pagination info will be displayed here -->
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-outline-primary btn-sm me-2" id="adminPrevBtn" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button class="btn btn-outline-primary btn-sm" id="adminNextBtn" disabled>
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addAdminForm">
                    <div class="mb-3">
                        <label for="adminEmail" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="adminEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="adminPassword" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="adminPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="adminRole" class="form-label">Role</label>
                        <select class="form-select" id="adminRole" disabled>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="adminStatus" class="form-label">Status *</label>
                        <select class="form-select" id="adminStatus" required>
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveAdminBtn">
                    <i class="fas fa-save"></i> Save Admin
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom styles for admin management */
:root {
            --primary-color: #667eea; 
            --secondary-color: #764ba2;
            --gradient-bg: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            --accent-color: #0d6efd;
            --hover-color: #7e68d9ff; /* light lavender hover */
        }

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
}

.table thead th{
     background-color: var(--primary-color) !important;
    color: white !important;
}

.table td {
    font-size: 0.875rem;
    vertical-align: middle;
}

.badge-active {
    background-color: #198754;
}

.badge-pending {
    background-color: #ffc107;
    color: #000;
}

.badge-rejected {
    background-color: #dc3545;
}

.status-dropdown {
     min-width: 100px;
    max-width: 100px;
}

.action-buttons {
    white-space: nowrap;
}

#adminSearchInput {
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#adminSearchInput:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
</style>