
// assets/js/alumni_filter.js
class AlumniFilter {
    constructor() {
        this.currentPage = 1;
        this.currentLimit = 25;
        this.searchTimeout = null;
        this.init();
    }
    
    init() {
        // Get DOM elements
        this.searchInput = document.getElementById('searchInput');
        this.branchFilter = document.getElementById('branchFilter');
        this.employmentFilter = document.getElementById('employmentFilter');
        this.limitSelect = document.getElementById('limitSelect');

        this.yearFilter = document.getElementById('yearFilter');
        this.companyFilter = document.getElementById('companyFilter');

        this.clearBtn = document.getElementById('clearFilters');
        this.prevBtn = document.getElementById('prevBtn');
        this.nextBtn = document.getElementById('nextBtn');
        this.loadingSpinner = document.getElementById('loadingSpinner');
        this.resultsBody = document.getElementById('resultsTableBody');
        this.paginationInfo = document.getElementById('paginationInfo');
        
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
            }, 500); // 500ms delay
        });
        
        // Filter changes
        this.branchFilter.addEventListener('change', () => {
            this.currentPage = 1;
            this.loadData();
        });
        
        this.employmentFilter.addEventListener('change', () => {
            this.currentPage = 1;
            this.loadData();
        });
        
        // Limit change
        this.limitSelect.addEventListener('change', () => {
            this.currentLimit = parseInt(this.limitSelect.value);
            this.currentPage = 1;
            this.loadData();
        });

        this.yearFilter.addEventListener('change', () => {
            this.currentPage = 1;
            this.loadData();
        });

        this.companyFilter.addEventListener('input', () => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.currentPage = 1;
                this.loadData();
            }, 500);
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
        this.employmentFilter.value = '';
        this.yearFilter.value = '';
        this.companyFilter.value = '';
        this.currentPage = 1;
        this.loadData();
    }
    
    async loadData() {
        try {
            this.showLoading(true);
            
            // Build query parameters
            const params = new URLSearchParams({
                search: this.searchInput.value,
                branch: this.branchFilter.value,
                employment_status: this.employmentFilter.value,
                passout_year: this.yearFilter.value,
                company_name: this.companyFilter.value,
                page: this.currentPage,
                limit: this.currentLimit
            });
            
            // Make API call
            const response = await fetch(`../../api/alumni_search.php?${params}`);
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
                    <td colspan="9" class="text-center py-4">
                        <i class="fas fa-users fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No alumni found matching your criteria</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        const rows = data.map(alumni => `
            <tr>
                <td><strong>${alumni.prn_no}</strong></td>
                <td>${alumni.first_name} ${alumni.last_name}</td>
                <td><span class="badge bg-primary">${alumni.branch}</span></td>
                <td>${alumni.passout_year}</td>
                <td>${alumni.phone}</td>
                <td>
                    <span class="badge ${this.getEmploymentBadgeClass(alumni.employment_status)}">
                        ${this.formatEmploymentStatus(alumni.employment_status)}
                    </span>
                </td>
                <td>${alumni.company_name || '<em class="text-muted">Not specified</em>'}</td>
                <td>${alumni.email}</td>
                <td class="action-buttons">
                <button class="btn btn-sm btn-outline-primary sendNoticeBtn" 
                    data-id="${alumni.user_id}" 
                    data-name="${alumni.first_name} ${alumni.last_name}">
                    <i class="fas fa-envelope"></i>Notice
                </button>
                <button class="btn btn-sm btn-outline-success me-1" onclick="" title="Edit User Info">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="" title="Delete User">
                    <i class="fas fa-trash"></i>
                </button>
                </td>
            </tr>
        `).join('');

    
        this.resultsBody.innerHTML = rows;

         // Attach event listeners for send buttons
            document.querySelectorAll('.sendNoticeBtn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const receiverId = e.currentTarget.dataset.id;
                    const receiverName = e.currentTarget.dataset.name;
                    this.openSendNoticeModal(receiverId, receiverName);
                });
            });
    }

    openSendNoticeModal(receiverId, receiverName) {
        document.getElementById('noticeReceiverId').value = receiverId;
        document.getElementById('noticeReceiverName').innerText = receiverName;
        new bootstrap.Modal(document.getElementById('sendNoticeModal')).show();
    }
    
    getEmploymentBadgeClass(status) {
        switch (status) {
            case 'employed': return 'bg-success';
            case 'self-employed': return 'bg-info';
            case 'unemployed': return 'bg-warning';
            default: return 'bg-secondary';
        }
    }
    
    formatEmploymentStatus(status) {
        switch (status) {
            case 'employed': return 'Employed';
            case 'self-employed': return 'Self-Employed';
            case 'unemployed': return 'Unemployed';
            default: return 'Unknown';
        }
    }
    
    updatePagination(pagination) {
        // Update pagination info
        const start = ((pagination.current_page - 1) * pagination.limit) + 1;
        const end = Math.min(pagination.current_page * pagination.limit, pagination.total_records);
        
        this.paginationInfo.innerHTML = `
            Showing ${start}-${end} of ${pagination.total_records} results
        `;
        
        // Update pagination buttons
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
                    <button class="btn btn-outline-primary btn-sm" onclick="alumniFilter.loadData()">
                        <i class="fas fa-redo"></i> Try Again
                    </button>
                </td>
            </tr>
        `;
    }
}

document.getElementById('sendNoticeForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch('../../api/send_notice.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            alert('✅ Notice sent successfully!');
            bootstrap.Modal.getInstance(document.getElementById('sendNoticeModal')).hide();
        } else {
            alert('❌ Error: ' + (result.error || 'Failed to send notice'));
        }
    } catch (err) {
        console.error(err);
        alert('❌ Network error.');
    }
});



// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.alumniFilter = new AlumniFilter();
});

// Optional: Auto-refresh data every 30 seconds (uncomment if needed)
// setInterval(() => {
//     if (window.alumniFilter) {
//         window.alumniFilter.loadData();
//     }
// }, 30000);