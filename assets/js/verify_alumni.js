// assets/js/verify_alumni.js
class AlumniVerification {
    constructor() {
        this.currentPage = 1;
        this.currentLimit = 25;
        this.searchTimeout = null;
        this.init();
    }
    
    init() {
        // Get DOM elements
        this.searchInput = document.getElementById('verifySearchInput');
        this.branchFilter = document.getElementById('verifyBranchFilter');
        this.limitSelect = document.getElementById('verifyLimitSelect');
        this.clearBtn = document.getElementById('clearVerifyFilters');
        this.prevBtn = document.getElementById('verifyPrevBtn');
        this.nextBtn = document.getElementById('verifyNextBtn');
        this.loadingSpinner = document.getElementById('verifyLoadingSpinner');
        this.resultsBody = document.getElementById('verifyResultsTableBody');
        this.paginationInfo = document.getElementById('verifyPaginationInfo');
        
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
        
        // Branch filter change
        this.branchFilter.addEventListener('change', () => {
            this.currentPage = 1;
            this.loadData();
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
        this.branchFilter.value = '';
        this.currentPage = 1;
        this.loadData();
    }
    
    async loadData() {
        try {
            this.showLoading(true);
            
            const params = new URLSearchParams({
                search: this.searchInput.value,
                branch: this.branchFilter.value,
                page: this.currentPage,
                limit: this.currentLimit
            });
            
            const response = await fetch(`../../api/verify_alumni.php?${params}`);
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
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-user-check fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No pending alumni registrations found</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        const rows = data.map(alumni => `
            <tr>
                <td><strong>${alumni.prn_no}</strong></td>
                <td>
                    <div><strong>${alumni.first_name} ${alumni.last_name}</strong></div>
                    ${alumni.company_name ? `<small class="text-muted">${alumni.company_name}</small>` : ''}
                </td>
                <td><span class="badge bg-primary">${this.truncateText(alumni.branch, 15)}</span></td>
                <td>${alumni.passout_year}</td>
                <td>${alumni.phone}</td>
                <td>${alumni.email}</td>
                <td>
                    <div>${this.formatDate(alumni.registration_date)}</div>
                    <small class="registration-date">${this.formatTime(alumni.registration_date)}</small>
                </td>
                <td class="action-buttons">
                    <button class="btn btn-verify-approve btn-sm me-1" 
                            onclick="alumniVerification.verifyAlumni(${alumni.user_id}, 'approve', '${alumni.first_name} ${alumni.last_name}')" 
                            title="Approve Alumni">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="btn btn-verify-reject btn-sm" 
                            onclick="alumniVerification.verifyAlumni(${alumni.user_id}, 'reject', '${alumni.first_name} ${alumni.last_name}')" 
                            title="Reject Alumni">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
        this.resultsBody.innerHTML = rows;
    }
    
    truncateText(text, length) {
        return text.length > length ? text.substring(0, length) + '...' : text;
    }
    
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }
    
    formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    async verifyAlumni(userId, action, alumniName) {
        // Confirmation dialog
        const actionText = action === 'approve' ? 'approve' : 'reject';
        const confirmMessage = `Are you sure you want to ${actionText} ${alumniName}?`;
        
        if (!confirm(confirmMessage)) {
            return;
        }
        
        try {
            const response = await fetch('../../api/verify_alumni.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    action: action
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert(result.message, 'success');
                this.loadData(); // Refresh the table
            } else {
                this.showAlert(result.error || 'Action failed', 'danger');
            }
            
        } catch (error) {
            console.error('Error verifying alumni:', error);
            this.showAlert('Network error. Please try again.', 'danger');
        }
    }
    
    updatePagination(pagination) {
        const start = ((pagination.current_page - 1) * pagination.limit) + 1;
        const end = Math.min(pagination.current_page * pagination.limit, pagination.total_records);
        
        this.paginationInfo.innerHTML = `
            Showing ${start}-${end} of ${pagination.total_records} pending registrations
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
                <td colspan="8" class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                    <p class="text-danger">${message}</p>
                    <button class="btn btn-outline-primary btn-sm" onclick="alumniVerification.loadData()">
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
    window.alumniVerification = new AlumniVerification();
});