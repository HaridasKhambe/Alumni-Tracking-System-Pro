<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notice Button</title>
</head>

<!-- templates/alumni_filter.php -->
<div class="container-fluid p-4">
    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-search"></i> Advanced Alumni Search</h5>
        </div>
        <div class="card-body">
            <form id="filterForm">
                <div class="row">
                    <!-- Search Bar -->
                    <div class="col-md-4 mb-3">
                        <label for="searchInput" class="form-label">Search by Name or PRN</label>
                        <input type="text" class="form-control" id="searchInput" 
                               placeholder="Enter name or PRN number">
                    </div>
                    
                    <!-- Branch Filter -->
                    <div class="col-md-3 mb-3">
                        <label for="branchFilter" class="form-label">Branch</label>
                        <select class="form-select" id="branchFilter">
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
                    
                    <!-- Employment Status Filter -->
                    <div class="col-md-3 mb-3">
                        <label for="employmentFilter" class="form-label">Employment Status</label>
                        <select class="form-select" id="employmentFilter">
                            <option value="">All Status</option>
                            <option value="employed">Employed</option>
                            <option value="unemployed">Unemployed</option>
                            <option value="self-employed">Self-Employed</option>
                        </select>
                    </div>

                    <!-- Passout Year Filter -->
                    <div class="col-md-2 mb-3">
                        <label for="yearFilter" class="form-label">Passout Year</label>
                        <select class="form-select" id="yearFilter">
                            <option value="">All Years</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                            <option value="2021">2021</option>
                            <option value="2020">2020</option>
                            <option value="2019">2019</option>
                            <option value="2018">2018</option>
                        </select>
                    </div>

                    <!-- Company Name Filter -->
                    <div class="col-md-3 mb-3">
                        <label for="companyFilter" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="companyFilter" 
                            placeholder="Enter company name">
                    </div>
                    
                    <!-- Clear Filters -->
                    <div class="col-md-2 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters">
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
            <h6 class="mb-0">Search Results</h6>
            <div class="d-flex align-items-center">
                <label for="limitSelect" class="form-label me-2 mb-0">Show:</label>
                <select class="form-select form-select-sm" id="limitSelect" style="width: auto;">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="text-center py-4" style="display: none;">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            
            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>PRN No</th>
                            <th>Name</th>
                            <th>Branch</th>
                            <th>Passout Year</th>
                            <th>Phone</th>
                            <th>Employment</th>
                            <th>Company</th>
                            <th>Email</th>
                            <th class="action-buttons">Action</th>
                            
                        </tr>
                    </thead>
                    <tbody id="resultsTableBody">
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-search fa-2x text-muted mb-2"></i>
                                <p class="text-muted">Use filters above to search alumni</p>
                            </td>
                        </tr>
                    </tbody>
    
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="paginationInfo" class="text-muted">
                        <!-- Pagination info will be displayed here -->
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-outline-primary btn-sm me-2" id="prevBtn" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button class="btn btn-outline-primary btn-sm" id="nextBtn" disabled>
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send Notice Modal -->
<div class="modal fade" id="sendNoticeModal" tabindex="-1" aria-labelledby="sendNoticeLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="sendNoticeForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sendNoticeLabel">
          Send Notice to <span id="noticeReceiverName"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="noticeReceiverId" name="noticeReceiverId">

        <div class="mb-3">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Message</label>
          <textarea name="message" class="form-control" rows="3" required></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Type</label>
          <select name="type" class="form-select">
            <option value="general">General</option>
            <option value="invitation">Invitation</option>
            <option value="event">Event</option>
            <option value="reminder">Reminder</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Expiry Date</label>
          <input type="date" name="expiry_date" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-paper-plane"></i> Send
        </button>
      </div>
    </form>
  </div>
</div>


<style>
/* Custom styles for alumni filter */
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

.table thead th {
    background-color: var(--primary-color) !important;
    color: white !important;
}

/* Change row hover background */
.table tbody tr:hover {
    background-color: var(--secondary-color) !important;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
}

.table td {
    font-size: 0.875rem;
    vertical-align: middle;
}

.form-select-sm {
    min-width: 70px;
}

#searchInput {
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#searchInput:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}


</style>