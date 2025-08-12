// assets/js/notice_manager.js
class NoticeManager {
    constructor() {
        this.currentPage = 1;
        this.currentLimit = 25;
        this.searchTimeout = null;
        this.init();
    }
    
    init() {
        // Get DOM elements
        this.searchInput = document.getElementById('noticeSearchInput');
        this.statusFilter = document.getElementById('noticeStatusFilter');
        this.limitSelect = document.getElementById('noticeLimitSelect');
        this.clearBtn = document.getElementById('clearNoticeFilters');
        this.prevBtn = document.getElementById('noticePrevBtn');
        this.nextBtn = document.getElementById('noticeNextBtn');
        this.loadingSpinner = document.getElementById('noticeLoadingSpinner');
        this.resultsBody = document.getElementById('noticeResultsTableBody');
        this.paginationInfo = document.getElementById('noticePaginationInfo');
        
        // Modal elements
        this.editModal = new bootstrap.Modal(document.getElementById('editStatusModal'));
        this.saveStatusBtn = document.getElementById('saveStatusBtn');
        
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
        
        // Status filter change
        this.statusFilter.addEventListener('change', () => {
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
        
        // Modal save button
        this.saveStatusBtn.addEventListener('click', () => {
            this.updateNoticeStatus();
        });
    }
    
    clearFilters() {
        this.searchInput.value = '';
        this.statusFilter.value = '';
        this.currentPage = 1;
        this.loadData();
    }
    
    async loadData() {
        try {
            this.showLoading(true);
            
            const params = new URLSearchParams({
                search: this.searchInput.value,
                status: this.statusFilter.value,
                page: this.currentPage,
                limit: this.currentLimit
            });
            
            const response = await fetch(`../../api/notice_manager.php?${params}`);
            const result = await response.json();
            
            if (result.success) {
                this.displayResults(result.data);
                this.updatePagination(result.pagination);
            } else {
                this.showError(result.error || 'Failed to load notices');
            }
            
        } catch (error) {
            console.error('Error loading notices:', error);
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
                        <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No notices found matching your criteria</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        const rows = data.map(notice => `
            <tr>
                <td><strong>#${notice.id}</strong></td>
                <td>
                    <div><strong>${notice.first_name} ${notice.last_name}</strong></div>
                    <small class="text-muted">${notice.prn_no}</small>
                </td>
                <td>
                    <div class="text-truncate" style="max-width: 150px;" title="${notice.title}">
                        ${notice.title}
                    </div>
                </td>
                <td>
                    <div class="text-truncate" style="max-width: 150px;" title="">
                        ${notice.message}
                    </div>
                </td>

                <td><span class="badge bg-primary">${this.formatType(notice.type)}</span></td>
                <td><span class="badge badge-${notice.status}">${this.formatStatus(notice.status)}</span></td>
                <td>${this.formatDate(notice.created_at)}</td>
                <td>${notice.expiry_date ? this.formatDate(notice.expiry_date) : '<em class="text-muted">No expiry</em>'}</td>
                <td class="action-buttons">
                    <button class="btn btn-outline-primary btn-sm me-1" onclick="noticeManager.editStatus(${notice.id}, '${notice.title}', '${notice.first_name} ${notice.last_name}', '${notice.status}', '${notice.message}','${notice.type}', '${notice.expiry_date || ''}')" title="Edit Status">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="noticeManager.deleteNotice(${notice.id})" title="Delete Notice">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
        this.resultsBody.innerHTML = rows;
    }
    
    formatType(type) {
        return type.charAt(0).toUpperCase() + type.slice(1);
    }
    
    formatStatus(status) {
        return status.charAt(0).toUpperCase() + status.slice(1);
    }
    
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }
    
    editStatus(noticeId, title, receiverName, currentStatus, message, type, expiryDate) {
        document.getElementById('modalNoticeId').value = noticeId;
        document.getElementById('modalNoticeTitle').value = title;
        document.getElementById('modalReceiverName').textContent = receiverName;
        document.getElementById('modalMessage').value = message || '';
        document.getElementById('modalType').value = type;
        document.getElementById('modalStatusSelect').value = currentStatus;
        document.getElementById('modalExpiryDate').value = expiryDate || '';
        
        this.editModal.show();
    }
    
    async updateNoticeStatus() {
        const noticeId = document.getElementById('modalNoticeId').value;
        const title = document.getElementById('modalNoticeTitle').value.trim();
        const message = document.getElementById('modalMessage').value.trim();
        const type = document.getElementById('modalType').value;
        const status = document.getElementById('modalStatusSelect').value;
        const expiryDate = document.getElementById('modalExpiryDate').value;
        
    // Validation
    if (!title) {
        this.showAlert('Title is required', 'danger');
        return;
    }
    if (!message) {
        this.showAlert('Message is required', 'danger');
        return;
    }
    
    try {
        const response = await fetch('../../api/notice_manager.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: noticeId,
                title: title,
                message: message,
                type: type,
                status: status,
                expiry_date: expiryDate || null
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            this.editModal.hide();
            this.showAlert('Notice updated successfully', 'success');
            this.loadData();
        } else {
            this.showAlert(result.error || 'Failed to update notice', 'danger');
        }
        
    } catch (error) {
        console.error('Error updating notice:', error);
        this.showAlert(error,'Network error. Please try again.', 'danger');
    }
}
    
    async deleteNotice(noticeId) {
        if (!confirm('Are you sure you want to delete this notice? This action cannot be undone.')) {
            return;
        }
        
        try {
            const response = await fetch('../../api/notice_manager.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include',
                body: JSON.stringify({
                    id: noticeId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert('Notice deleted successfully', 'success');
                this.loadData(); // Refresh the table
            } else {
                this.showAlert(result.error || 'Failed to delete notice', 'danger');
            }
            
        } catch (error) {
            console.error('Error deleting notice:', error);
            this.showAlert('Network error. Please try again.', 'danger');
        }
    }
    
    updatePagination(pagination) {
        const start = ((pagination.current_page - 1) * pagination.limit) + 1;
        const end = Math.min(pagination.current_page * pagination.limit, pagination.total_records);
        
        this.paginationInfo.innerHTML = `
            Showing ${start}-${end} of ${pagination.total_records} notices
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
                    <button class="btn btn-outline-primary btn-sm" onclick="noticeManager.loadData()">
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
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 3000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.noticeManager = new NoticeManager();
});