// assets/js/manage_admins.js
class AdminManagement {
    constructor() {
        this.currentPage = 1;
        this.currentLimit = 25;
        this.searchTimeout = null;
        this.init();
    }
    
    init() {
        // Get DOM elements
        this.searchInput = document.getElementById('adminSearchInput');
        this.limitSelect = document.getElementById('adminLimitSelect');
        this.clearBtn = document.getElementById('clearAdminFilters');
        this.addAdminBtn = document.getElementById('addAdminBtn');
        this.prevBtn = document.getElementById('adminPrevBtn');
        this.nextBtn = document.getElementById('adminNextBtn');
        this.loadingSpinner = document.getElementById('adminLoadingSpinner');
        this.resultsBody = document.getElementById('adminResultsTableBody');
        this.paginationInfo = document.getElementById('adminPaginationInfo');
        
        // Modal elements
        this.addAdminModal = new bootstrap.Modal(document.getElementById('addAdminModal'));
        this.addAdminForm = document.getElementById('addAdminForm');
        this.saveAdminBtn = document.getElementById('saveAdminBtn');
        
        // Add event listeners
        this.addEventListeners();
        
        // Load initial data
        this.loadData();
    }
    
    addEventListeners() {
        // Search with delay
        this.searchInput.addEventListener('input', () => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.currentPage = 1;
                this.loadData();
            }, 500);
        });
        
        // Limit change
        this.limitSelect.addEventListener('change', () => {
            this.currentLimit = parseInt(this.limitSelect.value);
            this.currentPage = 1;
            this.loadData();
        });
        
        // Clear filters
        this.clearBtn.addEventListener('click', () => {
            this.clearFilters();
        });
        
        // Add admin button
        this.addAdminBtn.addEventListener('click', () => {
            this.openAddAdminModal();
        });
        
        // Save admin button
        this.saveAdminBtn.addEventListener('click', () => {
            this.saveAdmin();
        });
        
        // Pagination
        this.prevBtn.addEventListener('click', () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadData();
            }
        });
        
        this.nextBtn.addEventListener('click', () => {
            this.currentPage++;
            this.loadData();
        });
    }
    
    clearFilters() {
        this.searchInput.value = '';
        this.currentPage = 1;
        this.loadData();
    }
    
    openAddAdminModal() {
        this.addAdminForm.reset();
        this.addAdminModal.show();
    }
    
    async loadData() {
        try {
            this.showLoading(true);
            
            const params = new URLSearchParams({
                search: this.searchInput.value,
                page: this.currentPage,
                limit: this.currentLimit
            });
            
            const response = await fetch(`../../api/manage_admins.php?${params}`);
            const result = await response.json();
            
            if (result.success) {
                this.displayResults(result.data);
                this.updatePagination(result.pagination);
            } else {
                this.showError(result.error || 'Failed to load data');
            }
            
        } catch (error) {
            console.error('Error loading data:', error);
            this.showError('Network error. Please try again.');
        } finally {
            this.showLoading(false);
        }
    }
    
    displayResults(data) {
        if (data.length === 0) {
            this.resultsBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <i class="fas fa-users fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No admins found</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        const rows = data.map(admin => `
            <tr>
                <td><strong>${admin.email}</strong></td>
                <td><span class="badge bg-primary">Admin</span></td>
                <td>
                    <span class="badge badge-${admin.status}">${this.capitalizeFirst(admin.status)}</span>
                </td>
                <td>${this.formatDate(admin.created_at)}</td>
                <td class="action-buttons">
                    <div class="d-flex gap-1 align-items-center">
                        <select class="form-select form-select-sm status-dropdown" 
                                onchange="adminManagement.updateAdminStatus(${admin.id}, this.value, '${admin.email}')">
                            <option value="active" ${admin.status === 'active' ? 'selected' : ''}>Active</option>
                            <option value="pending" ${admin.status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="rejected" ${admin.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                        </select>
                        <button class="btn btn-sm btn-outline-primary sendNoticeBtn" title="Send Mail">
                            <i class="fas fa-envelope"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="" title="Edit Admin Info">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="" title="Delete Admin">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        
        this.resultsBody.innerHTML = rows;
    }
    
    async saveAdmin() {
        const email = document.getElementById('adminEmail').value.trim();
        const password = document.getElementById('adminPassword').value;
        const status = document.getElementById('adminStatus').value;
        
        if (!email || !password) {
            this.showAlert('Email and password are required', 'danger');
            return;
        }
        
        try {
            const response = await fetch('../../api/manage_admins.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: email,
                    password: password,
                    status: status
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert(result.message, 'success');
                this.addAdminModal.hide();
                this.loadData(); // Refresh the table
            } else {
                this.showAlert(result.error || 'Failed to add admin', 'danger');
            }
            
        } catch (error) {
            console.error('Error adding admin:', error);
            this.showAlert('Network error. Please try again.', 'danger');
        }
    }
    
    async updateAdminStatus(adminId, newStatus, adminEmail) {
        if (!confirm(`Change status of ${adminEmail} to ${newStatus}?`)) {
            this.loadData(); // Reset dropdown
            return;
        }
        
        try {
            const response = await fetch('../../api/manage_admins.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    admin_id: adminId,
                    status: newStatus
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert(result.message, 'success');
                this.loadData(); // Refresh the table
            } else {
                this.showAlert(result.error || 'Status update failed', 'danger');
                this.loadData(); // Reset dropdown
            }
            
        } catch (error) {
            console.error('Error updating status:', error);
            this.showAlert('Network error. Please try again.', 'danger');
            this.loadData(); // Reset dropdown
        }
    }
    
    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }
    
    updatePagination(pagination) {
        const start = ((pagination.current_page - 1) * pagination.limit) + 1;
        const end = Math.min(pagination.current_page * pagination.limit, pagination.total_records);
        
        this.paginationInfo.innerHTML = `
            Showing ${start}-${end} of ${pagination.total_records} admins
        `;
        
        this.prevBtn.disabled = !pagination.has_prev;
        this.nextBtn.disabled = !pagination.has_next;
    }
    
    showLoading(show) {
        this.loadingSpinner.style.display = show ? 'block' : 'none';
    }
    
    showError(message) {
        this.resultsBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                    <p class="text-danger">${message}</p>
                    <button class="btn btn-outline-primary btn-sm" onclick="adminManagement.loadData()">
                        <i class="fas fa-redo"></i> Try Again
                    </button>
                </td>
            </tr>
        `;
    }
    
    showAlert(message, type) {
        // Create alert element
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 4000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.adminManagement = new AdminManagement();
});