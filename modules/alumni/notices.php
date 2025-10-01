<?php
session_start();

// make sure that user is set & role is correct
require_once '../../config/auth_helper.php';
requireRole('alumni');

// Page configuration
$page_title = "Alumni Dashboard - ATS";
$current_page = "notices";

// Include templates
include_once '../../templates/header.php'; //navbar global style
include_once '../../templates/navbar_alumni.php'; //navbar for alumini
?>

<!-- main page content -->
<div class="container-fluid p-4">
    <div class="row">
       
        <div class="col-md-10 col-lg-11 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-bell me-2"></i>My Notices</h1>
            </div>

            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading notices...</p>
            </div>

            <!-- Notices Container -->
            <div id="noticesContainer" class="row g-4" style="display: none;">
                
            </div>

            <!-- No Notices Message -->
            <div id="noNoticesMessage" class="text-center py-5" style="display: none;">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Notices Found</h4>
                <p class="text-muted">You don't have any notices at the moment.</p>
            </div>
        </div>
    </div>
</div>

<!-- Notice Detail Modal -->
<div class="modal fade" id="noticeModal" tabindex="-1" aria-labelledby="noticeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="noticeModalTitle">Notice Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted">Type:</small>
                        <span id="modalType" class="badge ms-1"></span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Status:</small>
                        <span id="modalStatus" class="badge ms-1"></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted">Received:</small>
                        <div id="modalReceived"></div>
                    </div>
                    <div class="col-md-6" id="modalExpiryContainer">
                        <small class="text-muted">Expiry:</small>
                        <div id="modalExpiry"></div>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <h6>Message:</h6>
                    <div id="modalMessage" class="p-3 bg-light rounded"></div>
                </div>
            </div>
            <div class="modal-footer" id="modalFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <div id="actionButtons" style="display: none;">
                    <button type="button" class="btn btn-success me-2" id="acceptBtn">
                        <i class="fas fa-check"></i> Accept
                    </button>
                    <button type="button" class="btn btn-danger" id="rejectBtn">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
class NoticesManager {
    constructor() {
        this.notices = [];
        this.currentNoticeId = null;
        this.init();
    }

    init() {
        this.loadNotices();
        this.setupEventListeners();
    }

    setupEventListeners() {
        document.getElementById('acceptBtn').addEventListener('click', () => {
            this.updateNoticeStatus('accepted');
        });

        document.getElementById('rejectBtn').addEventListener('click', () => {
            this.updateNoticeStatus('rejected');
        });
    }

    async loadNotices() {
        try {
            const response = await fetch('../../api/notices.php');
            const data = await response.json();

            if (data.success) {
                this.notices = data.notices;
                this.renderNotices();
            } else {
                this.showAlert('Failed to load notices', 'danger');
            }
        } catch (error) {
            this.showAlert('Error loading notices', 'danger');
        } finally {
            document.getElementById('loadingSpinner').style.display = 'none';
        }
    }

    renderNotices() {
        const container = document.getElementById('noticesContainer');
        const noNoticesMsg = document.getElementById('noNoticesMessage');

        if (this.notices.length === 0) {
            noNoticesMsg.style.display = 'block';
            return;
        }

        container.innerHTML = '';
        container.style.display = '';

        this.notices.forEach(notice => {
            const card = this.createNoticeCard(notice);
            container.appendChild(card);
        });
    }

    createNoticeCard(notice) {
        const col = document.createElement('div');
        col.className = 'col-12 col-sm-6 col-md-6 col-lg-4';

        const isExpired = notice.expiry_info && notice.expiry_info.expired;
        const cardClass = isExpired ? 'card notice-card expired-notice' : 'card notice-card';

        col.innerHTML = `
            <div class="${cardClass}" data-notice-id="${notice.id}" style="cursor: pointer; transition: transform 0.2s;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title fw-bold mb-1">${this.escapeHtml(notice.title)}</h6>
                        ${this.getTypebage(notice.type)}
                    </div>
                    
                    <small class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>
                        Received on: ${this.formatDate(notice.created_at)}
                    </small>
                    
                    <p class="card-text mt-2 mb-2">${this.escapeHtml(notice.preview)}</p>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        ${this.getStatusBadge(notice.status)}
                        ${notice.expiry_info ? `<small class="text-muted">${notice.expiry_info.text}</small>` : ''}
                    </div>
                </div>
            </div>
        `;

        // Add click event
        col.querySelector('.notice-card').addEventListener('click', () => {
            this.showNoticeModal(notice);
        });

        // Add hover effect
        const card = col.querySelector('.notice-card');
        card.addEventListener('mouseenter', () => {
            if (!isExpired) card.style.transform = 'translateY(-2px)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
        });

        return col;
    }

    showNoticeModal(notice) {
        this.currentNoticeId = notice.id;
        
        document.getElementById('noticeModalTitle').textContent = notice.title;
        document.getElementById('modalType').textContent = this.capitalizeFirst(notice.type);
        document.getElementById('modalType').className = `badge ms-1 ${this.getTypeBadgeClass(notice.type)}`;
        
        document.getElementById('modalStatus').textContent = this.capitalizeFirst(notice.status);
        document.getElementById('modalStatus').className = `badge ms-1 ${this.getStatusBadgeClass(notice.status)}`;
        
        document.getElementById('modalReceived').textContent = this.formatDate(notice.created_at);
        document.getElementById('modalMessage').textContent = notice.message;

        // Handle expiry
        const expiryContainer = document.getElementById('modalExpiryContainer');
        if (notice.expiry_info) {
            expiryContainer.style.display = 'block';
            document.getElementById('modalExpiry').textContent = notice.expiry_info.text;
        } else {
            expiryContainer.style.display = 'none';
        }

        // Show/hide action buttons
        const actionButtons = document.getElementById('actionButtons');
        const isExpired = notice.expiry_info && notice.expiry_info.expired;
        
        if (notice.status === 'pending' && !isExpired) {
            actionButtons.style.display = 'block';
        } else {
            actionButtons.style.display = 'none';
        }

        new bootstrap.Modal(document.getElementById('noticeModal')).show();
    }

    async updateNoticeStatus(status) {
        try {
            const response = await fetch('../../api/notices.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    notice_id: this.currentNoticeId,
                    status: status
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert(data.message, 'success');
                
                // Update notice in local array
                const notice = this.notices.find(n => n.id == this.currentNoticeId);
                if (notice) {
                    notice.status = status;
                }
                
                // Re-render notices
                this.renderNotices();
                
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('noticeModal')).hide();
            } else {
                this.showAlert(data.message, 'danger');
            }
        } catch (error) {
            this.showAlert('Error updating notice status', 'danger');
        }
    }

    getTypebage(type) {
        return `<span class="badge ${this.getTypeBadgeClass(type)}">${this.capitalizeFirst(type)}</span>`;
    }

    getTypeBadgeClass(type) {
        const classes = {
            'invitation': 'bg-info',
            'event': 'bg-primary',
            'reminder': 'bg-warning',
            'general': 'bg-secondary'
        };
        return classes[type] || 'bg-secondary';
    }

    getStatusBadge(status) {
        return `<span class="badge ${this.getStatusBadgeClass(status)}">${this.capitalizeFirst(status)}</span>`;
    }

    getStatusBadgeClass(status) {
        const classes = {
            'pending': 'bg-warning',
            'accepted': 'bg-success',
            'rejected': 'bg-danger'
        };
        return classes[status] || 'bg-secondary';
    }

    formatDate(dateString) {
        const options = { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        };
        return new Date(dateString).toLocaleDateString('en-US', options);
    }

    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
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

// Initializing.............. when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new NoticesManager();
});
</script>

<style>
.notice-card {
    border-left: 4px solid #007bff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    height: 100%; /* Add this line */
     min-height: 200px; /* Set minimum height */
}

.notice-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.expired-notice {
    opacity: 0.6;
    border-left-color: #6c757d;
}

.expired-notice:hover {
    transform: none !important;
}
</style>

<?php include_once '../../templates/footer.php'; ?>