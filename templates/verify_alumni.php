<!-- templates/verify_alumni.php -->
<div class="container-fluid p-4">
    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-user-check"></i> Alumni Verification</h5>
        </div>
        <div class="card-body">
            <form id="verifyFilterForm">
                <div class="row">
                    <!-- Search Bar -->
                    <div class="col-md-5 mb-3">
                        <label for="verifySearchInput" class="form-label">Search by Name or PRN</label>
                        <input type="text" class="form-control" id="verifySearchInput" 
                               placeholder="Enter name or PRN number">
                    </div>
                    
                    <!-- Branch Filter -->
                    <div class="col-md-4 mb-3">
                        <label for="verifyBranchFilter" class="form-label">Branch</label>
                        <select class="form-select" id="verifyBranchFilter">
                            <option value="">All Branches</option>
                            <option value="Computer Science & Engineering">Computer Science & Engineering</option>
                            <option value="Information Technology">Information Technology</option>
                            <option value="Electronics & Communication">Electronics & Communication</option>
                            <option value="Mechanical Engineering">Mechanical Engineering</option>
                            <option value="Civil Engineering">Civil Engineering</option>
                            <option value="Electrical Engineering">Electrical Engineering</option>
                            <option value="Chemical Engineering">Chemical Engineering</option>
                            <option value="Biotechnology">Biotechnology</option>
                        </select>
                    </div>
                    
                    <!-- Clear Filters -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-outline-secondary w-100" id="clearVerifyFilters">
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
            <h6 class="mb-0">Pending Alumni Registrations</h6>
            <div class="d-flex align-items-center">
                <label for="verifyLimitSelect" class="form-label me-2 mb-0">Show:</label>
                <select class="form-select form-select-sm" id="verifyLimitSelect" style="width: auto;">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <!-- Loading Spinner -->
            <div id="verifyLoadingSpinner" class="text-center py-4" style="display: none;">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            
            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>PRN No</th>
                            <th>Name</th>
                            <th>Branch</th>
                            <th>Passout Year</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Registration Date</th>
                            <th width="120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="verifyResultsTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-user-clock fa-2x text-muted mb-2"></i>
                                <p class="text-muted">Use filters above to view pending alumni registrations</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="verifyPaginationInfo" class="text-muted">
                        <!-- Pagination info will be displayed here -->
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-outline-primary btn-sm me-2" id="verifyPrevBtn" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button class="btn btn-outline-primary btn-sm" id="verifyNextBtn" disabled>
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom styles for alumni verification */
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

.btn-verify-approve {
    background-color: #198754;
    border-color: #198754;
    color: white;
}

.btn-verify-approve:hover {
    background-color: #157347;
    border-color: #146c43;
    color: white;
}

.btn-verify-reject {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.btn-verify-reject:hover {
    background-color: #bb2d3b;
    border-color: #b02a37;
    color: white;
}

.action-buttons {
    white-space: nowrap;
}

.badge-pending {
    background-color: #ffc107;
    color: #000;
}

.registration-date {
    font-size: 0.8rem;
    color: #6c757d;
}

#verifySearchInput {
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#verifySearchInput:focus {
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
</style>