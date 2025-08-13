// assets/js/events.js
class EventManagement {
    constructor() {
        this.currentPage = 1;
        this.currentLimit = 25;
        this.searchTimeout = null;
        this.editingEventId = null;
        this.init();
    }
    
    init() {
        // Get DOM elements
        this.searchInput = document.getElementById('eventsSearchInput');
        this.limitSelect = document.getElementById('eventsLimitSelect');
        this.clearBtn = document.getElementById('clearEventsFilters');
        this.addEventBtn = document.getElementById('addEventBtn');
        this.prevBtn = document.getElementById('eventsPrevBtn');
        this.nextBtn = document.getElementById('eventsNextBtn');
        this.loadingSpinner = document.getElementById('eventsLoadingSpinner');
        this.resultsBody = document.getElementById('eventsResultsTableBody');
        this.paginationInfo = document.getElementById('eventsPaginationInfo');
        
        // Modal elements
        this.eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
        this.eventModalElement = document.getElementById('eventModal');
        this.eventForm = document.getElementById('eventForm');
        this.saveEventBtn = document.getElementById('saveEventBtn');
        this.eventModalLabel = document.getElementById('eventModalLabel');
        
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
        
        // Add event button
        this.addEventBtn.addEventListener('click', () => {
            this.openAddEventModal();
        });
        
        // Save event button
        this.saveEventBtn.addEventListener('click', () => {
            this.saveEvent();
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
        
        // Modal reset on close
        this.eventModalElement.addEventListener('hidden.bs.modal', () => {
            this.resetForm();
        });
    }
    
    clearFilters() {
        this.searchInput.value = '';
        this.currentPage = 1;
        this.loadData();
    }
    
    async loadData() {
        try {
            this.showLoading(true);
            
            const params = new URLSearchParams({
                search: this.searchInput.value,
                page: this.currentPage,
                limit: this.currentLimit
            });
            
            const response = await fetch(`../../api/manage_events.php?${params}`);
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
                    <td colspan="7" class="text-center py-4">
                        <i class="fas fa-calendar fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No events found</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        const rows = data.map(event => `
            <tr>
                <td>
                    <div class="event-title">${this.truncateText(event.title, 30)}</div>
                    ${event.details ? `<small class="text-muted event-details" title="${this.escapeHtml(event.details)}">${this.truncateText(event.details, 50)}</small>` : ''}
                </td>
                <td>
                    <div><strong>${this.formatEventDate(event.event_date)}</strong></div>
                    <small class="text-muted">${this.formatDate(event.event_date)}</small>
                </td>
                <td>${this.truncateText(event.location, 20)}</td>
                <td>
                    <span class="badge badge-${event.status}">${this.capitalizeFirst(event.status)}</span>
                </td>
                <td>
                    <div>${event.created_by_name || 'Admin'}</div>
                    <small class="event-creator">${event.created_by_email}</small>
                </td>
                <td>
                    <div>${this.formatDate(event.created_at)}</div>
                    <small class="event-creator">${this.formatTime(event.created_at)}</small>
                </td>
                <td class="action-buttons">
                    <button class="btn btn-edit btn-sm me-1" 
                            onclick="eventManagement.editEvent(${event.id})" 
                            title="Edit Event">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-delete btn-sm" 
                            onclick="eventManagement.deleteEvent(${event.id}, '${this.escapeQuotes(event.title)}')" 
                            title="Delete Event">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
        this.resultsBody.innerHTML = rows;
    }
    
    openAddEventModal() {
        this.editingEventId = null;
        this.eventModalLabel.textContent = 'Add New Event';
        this.resetForm();
        this.eventModal.show();
    }
    
    async editEvent(eventId) {
        try {
            // Find event data from current results
            const eventData = await this.getEventById(eventId);
            if (!eventData) {
                this.showError('Event not found');
                return;
            }
            
            this.editingEventId = eventId;
            this.eventModalLabel.textContent = 'Edit Event';
            
            // Populate form
            document.getElementById('eventId').value = eventData.id;
            document.getElementById('eventTitle').value = eventData.title;
            document.getElementById('eventDetails').value = eventData.details;
            document.getElementById('eventDate').value = eventData.event_date;
            document.getElementById('eventLocation').value = eventData.location;
            document.getElementById('eventImageUrl').value = eventData.image_url || '';
            document.getElementById('eventRegistrationLink').value = eventData.registration_link || '';
            
            this.eventModal.show();
            
        } catch (error) {
            console.error('Error editing event:', error);
            this.showError('Failed to load event data');
        }
    }
    
    async getEventById(eventId) {
        // Simple approach: find from current loaded data
        // In a more complex app, you might make a separate API call
        const tableRows = this.resultsBody.querySelectorAll('tr');
        for (let row of tableRows) {
            const editBtn = row.querySelector(`[onclick*="${eventId}"]`);
            if (editBtn) {
                // For now, we'll need to reload data or store it differently
                // Making a simple API call to get individual event
                try {
                    const response = await fetch(`../../api/manage_events.php?id=${eventId}`);
                    const result = await response.json();
                    if (result.success && result.data.length > 0) {
                        return result.data[0];
                    }
                } catch (error) {
                    console.error('Error fetching event:', error);
                }
            }
        }
        
        // Fallback: reload current page data and find the event
        const params = new URLSearchParams({
            search: this.searchInput.value,
            page: this.currentPage,
            limit: this.currentLimit
        });
        
        const response = await fetch(`../api/events.php?${params}`);
        const result = await response.json();
        
        if (result.success) {
            return result.data.find(event => event.id == eventId);
        }
        
        return null;
    }
    
    async saveEvent() {
        const formData = new FormData(this.eventForm);
        const eventData = Object.fromEntries(formData.entries());
        
        // Validate required fields
        const requiredFields = ['title', 'details', 'event_date', 'location'];
        for (let field of requiredFields) {
            if (!eventData[field] || eventData[field].trim() === '') {
                this.showError(`Please fill in the ${field.replace('_', ' ')} field`);
                return;
            }
        }
        
        try {
            this.saveEventBtn.disabled = true;
            this.saveEventBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            const url = '../../api/manage_events.php';
            const method = this.editingEventId ? 'PUT' : 'POST';
            
            if (this.editingEventId) {
                eventData.id = this.editingEventId;
            }
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(eventData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(result.message || (this.editingEventId ? 'Event updated successfully' : 'Event created successfully'));
                this.eventModal.hide();
                this.loadData(); // Reload the data
            } else {
                this.showError(result.error || 'Failed to save event');
            }
            
        } catch (error) {
            console.error('Error saving event:', error);
            this.showError('Network error. Please try again.');
        } finally {
            this.saveEventBtn.disabled = false;
            this.saveEventBtn.innerHTML = '<i class="fas fa-save"></i> Save Event';
        }
    }
    
    async deleteEvent(eventId, eventTitle) {
        if (!confirm(`Are you sure you want to delete the event "${eventTitle}"? This action cannot be undone.`)) {
            return;
        }
        
        try {
            const response = await fetch('../../api/manage_events.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: eventId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(result.message || 'Event deleted successfully');
                this.loadData(); // Reload the data
            } else {
                this.showError(result.error || 'Failed to delete event');
            }
            
        } catch (error) {
            console.error('Error deleting event:', error);
            this.showError('Network error. Please try again.');
        }
    }
    
    resetForm() {
        this.eventForm.reset();
        this.editingEventId = null;
        document.getElementById('eventId').value = '';
        
        // Remove any validation classes
        this.eventForm.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
    }
    
    updatePagination(pagination) {
        // Update pagination info
        const start = pagination.total_records === 0 ? 0 : ((pagination.current_page - 1) * pagination.limit) + 1;
        const end = Math.min(pagination.current_page * pagination.limit, pagination.total_records);
        
        this.paginationInfo.textContent = `Showing ${start} to ${end} of ${pagination.total_records} events`;
        
        // Update button states
        this.prevBtn.disabled = !pagination.has_prev;
        this.nextBtn.disabled = !pagination.has_next;
    }
    
    showLoading(show) {
        this.loadingSpinner.style.display = show ? 'block' : 'none';
    }
    
    showError(message) {
        // Create and show error alert
        this.showAlert(message, 'danger');
    }
    
    showSuccess(message) {
        // Create and show success alert
        this.showAlert(message, 'success');
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
    
    // Utility functions
    truncateText(text, length) {
        if (!text) return '';
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
    
    formatEventDate(dateString) {
        const date = new Date(dateString);
        const today = new Date();
        const diffTime = date.getTime() - today.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) {
            return 'Today';
        } else if (diffDays === 1) {
            return 'Tomorrow';
        } else if (diffDays === -1) {
            return 'Yesterday';
        } else if (diffDays > 0 && diffDays <= 7) {
            return `In ${diffDays} days`;
        } else if (diffDays < 0 && diffDays >= -7) {
            return `${Math.abs(diffDays)} days ago`;
        } else {
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric'
            });
        }
    }
    
    formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    escapeQuotes(text) {
        return text.replace(/'/g, "\\'").replace(/"/g, '\\"');
    }
}

// Initialize the event management system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.eventManagement = new EventManagement();
});