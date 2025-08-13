// assets/js/alumni_events.js
class AlumniEvents {
    constructor() {
        this.searchTimeout = null;
        this.events = [];
        this.init();
    }
    
    init() {
        // Get DOM elements
        this.searchInput = document.getElementById('eventSearchInput');
        this.statusFilter = document.getElementById('eventStatusFilter');
        this.loadingSpinner = document.getElementById('loadingSpinner');
        this.eventsContainer = document.getElementById('eventsContainer');
        this.noEventsMessage = document.getElementById('noEventsMessage');
        this.eventStats = document.getElementById('eventStats');
        
        // Modal elements
        this.eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
        this.eventModalElement = document.getElementById('eventModal');
        
        // Stats elements
        this.totalEventsEl = document.getElementById('totalEvents');
        this.upcomingEventsEl = document.getElementById('upcomingEvents');
        this.liveEventsEl = document.getElementById('liveEvents');
        this.pastEventsEl = document.getElementById('pastEvents');
        
        // Add event listeners
        this.addEventListeners();
        
        // Load initial data
        this.loadEvents();
    }
    
    addEventListeners() {
        // Search with delay
        this.searchInput.addEventListener('input', () => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.loadEvents();
            }, 500);
        });
        
        // Status filter change
        this.statusFilter.addEventListener('change', () => {
            this.loadEvents();
        });
    }
    
    async loadEvents() {
        try {
            this.showLoading(true);
            
            const params = new URLSearchParams({
                search: this.searchInput.value,
                status: this.statusFilter.value,
                limit: 50
            });
            
            const response = await fetch(`../../api/alumni_events.php?${params}`);
            const result = await response.json();
            
            if (result.success) {
                this.events = result.data;
                this.displayEvents(result.data);
                this.updateStats(result.counts);
            } else {
                this.showError(result.error || 'Failed to load events');
            }
            
        } catch (error) {
            console.error('Error loading events:', error);
            this.showError('Network error. Please try again.');
        } finally {
            this.showLoading(false);
        }
    }
    
    displayEvents(events) {
        if (events.length === 0) {
            this.eventsContainer.style.display = 'none';
            this.noEventsMessage.style.display = 'block';
            return;
        }
        
        this.noEventsMessage.style.display = 'none';
        this.eventsContainer.style.display = 'flex';
        
        const eventCards = events.map(event => this.createEventCard(event)).join('');
        this.eventsContainer.innerHTML = eventCards;
    }
    
    createEventCard(event) {
        const statusClass = this.getStatusClass(event.status);
        const statusBadge = this.getStatusBadge(event.status);
        const eventDate = this.formatEventDate(event.event_date);
        const createdDate = this.formatDate(event.created_at);
        
        return `
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card event-card ${event.status}" onclick="alumniEvents.showEventDetails(${event.id})">
                    <div class="card-body">
                        <div class="event-date ${event.status}">
                            <div class="fw-bold">${eventDate.day}</div>
                            <div class="small">${eventDate.monthYear}</div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="event-title flex-grow-1">${this.escapeHtml(event.title)}</h5>
                            <span class="badge status-badge badge-${event.status} ms-2">${statusBadge}</span>
                        </div>
                        
                        <div class="event-location mb-2">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            ${this.truncateText(this.escapeHtml(event.location), 30)}
                        </div>
                        
                        <div class="event-details-preview mb-3">
                            ${this.truncateText(this.stripHtml(event.details), 100)}
                        </div>
                        
                        <div class="event-organizer mt-auto">
                            <i class="fas fa-user me-1"></i>
                            <span>${event.created_by_name || 'Admin'}</span>
                            <div class="event-meta">
                                Created on ${createdDate}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    showEventDetails(eventId) {
        const event = this.events.find(e => e.id == eventId);
        if (!event) {
            this.showError('Event not found');
            return;
        }
        
        // Populate modal
        document.getElementById('eventModalTitle').textContent = event.title;
        
        // Status
        const statusBadge = document.getElementById('modalStatus');
        statusBadge.textContent = this.capitalizeFirst(event.status);
        statusBadge.className = `badge badge-${event.status}`;
        
        // Event date
        const eventDate = new Date(event.event_date);
        document.getElementById('modalEventDate').innerHTML = `
            <div>${eventDate.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            })}</div>
            <small class="text-muted">${this.getEventTimeRemaining(event.event_date, event.status)}</small>
        `;
        
        // Location
        document.getElementById('modalLocation').textContent = event.location;
        
        // Organizer
        document.getElementById('modalOrganizer').innerHTML = `
            <div>${event.created_by_name || 'Admin'}</div>
            <small class="text-muted">${event.created_by_email}</small>
        `;
        
        // Image
        const imageContainer = document.getElementById('modalImageContainer');
        const modalImage = document.getElementById('modalImage');
        if (event.image_url) {
            modalImage.src = event.image_url;
            modalImage.alt = `${event.title} Image`;
            imageContainer.style.display = 'block';
        } else {
            imageContainer.style.display = 'none';
        }
        
        // Details
        document.getElementById('modalDetails').innerHTML = this.formatEventDetails(event.details);
        
        // Registration link
        const registrationContainer = document.getElementById('modalRegistrationContainer');
        const registrationLink = document.getElementById('modalRegistrationLink');
        if (event.registration_link && event.status !== 'past') {
            registrationLink.href = event.registration_link;
            registrationContainer.style.display = 'block';
        } else {
            registrationContainer.style.display = 'none';
        }
        
        // Show modal
        this.eventModal.show();
    }
    
    updateStats(counts) {
        if (counts) {
            this.totalEventsEl.textContent = counts.total || 0;
            this.upcomingEventsEl.textContent = counts.upcoming || 0;
            this.liveEventsEl.textContent = counts.live || 0;
            this.pastEventsEl.textContent = counts.past || 0;
            this.eventStats.style.display = 'flex';
        }
    }
    
    showLoading(show) {
        if (show) {
            this.loadingSpinner.style.display = 'block';
            this.eventsContainer.style.display = 'none';
            this.noEventsMessage.style.display = 'none';
            this.eventStats.style.display = 'none';
        } else {
            this.loadingSpinner.style.display = 'none';
        }
    }
    
    showError(message) {
        // Create and show error alert
        this.showAlert(message, 'danger');
    }
    
    showAlert(message, type) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert-dismissible');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create new alert
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert at the top of the container
        const container = document.querySelector('.container-fluid');
        container.insertBefore(alert, container.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
    
    // Utility functions
    getStatusClass(status) {
        const classes = {
            'upcoming': 'upcoming',
            'live': 'live',
            'past': 'past'
        };
        return classes[status] || 'upcoming';
    }
    
    getStatusBadge(status) {
        const badges = {
            'upcoming': 'Upcoming',
            'live': 'Live Now',
            'past': 'Past'
        };
        return badges[status] || 'Unknown';
    }
    
    formatEventDate(dateString) {
        const date = new Date(dateString);
        const day = date.getDate();
        const month = date.toLocaleDateString('en-US', { month: 'short' });
        const year = date.getFullYear();
        const currentYear = new Date().getFullYear();
        
        return {
            day: day,
            monthYear: currentYear === year ? month : `${month} ${year}`
        };
    }
    
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }
    
    getEventTimeRemaining(eventDateString, status) {
        const eventDate = new Date(eventDateString);
        const now = new Date();
        const diffTime = eventDate.getTime() - now.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (status === 'live') {
            return 'Happening now!';
        } else if (status === 'past') {
            const daysPast = Math.abs(diffDays);
            if (daysPast === 0) return 'Was today';
            if (daysPast === 1) return 'Was yesterday';
            return `Was ${daysPast} days ago`;
        } else {
            if (diffDays === 0) return 'Today!';
            if (diffDays === 1) return 'Tomorrow';
            if (diffDays > 0 && diffDays <= 7) return `In ${diffDays} days`;
            return `In ${Math.floor(diffDays / 7)} weeks`;
        }
    }
    
    formatEventDetails(details) {
        // Convert newlines to paragraphs and preserve formatting
        return details
            .split('\n')
            .filter(line => line.trim())
            .map(line => `<p class="mb-2">${this.escapeHtml(line.trim())}</p>`)
            .join('');
    }
    
    truncateText(text, length) {
        if (!text) return '';
        return text.length > length ? text.substring(0, length) + '...' : text;
    }
    
    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    stripHtml(text) {
        if (!text) return '';
        return text.replace(/<[^>]*>/g, '');
    }
}

// Initialize the alumni events system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.alumniEvents = new AlumniEvents();
});