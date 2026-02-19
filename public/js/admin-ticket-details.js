// Tickets List JavaScript

let allTickets = [];
let filteredTickets = [];
let currentPage = 1;
const itemsPerPage = 10;

// Load tickets on page load
document.addEventListener('DOMContentLoaded', () => {
    loadTickets();
});

// Load tickets from API
async function loadTickets() {
    showLoading();
    
    try {
        const response = await fetch('/internal_portal/api/v1/tickets/list.php');
        const data = await response.json();
        
        if (data.success) {
            allTickets = data.data || [];
            filteredTickets = allTickets;
            renderTickets();
        } else {
            showError(data.message || 'Failed to load tickets');
        }
    } catch (error) {
        console.error('Error loading tickets:', error);
        showError('Failed to load tickets. Please try again.');
    }
}

// Render tickets table
function renderTickets() {
    const tbody = document.getElementById('tickets-tbody');
    const container = document.getElementById('tickets-container');
    const emptyState = document.getElementById('empty-state');
    const loading = document.getElementById('loading');
    const errorDiv = document.getElementById('error');
    
    // Hide loading and error
    loading.style.display = 'none';
    errorDiv.style.display = 'none';
    
    if (filteredTickets.length === 0) {
        container.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    container.style.display = 'block';
    emptyState.style.display = 'none';
    
    // Pagination
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedTickets = filteredTickets.slice(startIndex, endIndex);
    
    // Render table rows
    tbody.innerHTML = paginatedTickets.map(ticket => `
        <tr>
            <td><span class="ticket-id">${ticket.ticket_number || '#T-' + ticket.id}</span></td>
            <td><strong>${escapeHtml(ticket.title)}</strong></td>
            <td><span class="priority-badge priority-${ticket.priority.toLowerCase()}">${ticket.priority}</span></td>
            <td><span class="status-badge status-${ticket.status.toLowerCase().replace(/ /g, '-')}">${ticket.status}</span></td>
            <td>${ticket.creator_name || 'Unknown'}</td>
            <td>${formatDate(ticket.created_at)}</td>
            <td style="text-align: center;">
                <button class="btn-action" onclick="viewTicket(${ticket.id})">View</button>
            </td>
        </tr>
    `).join('');
    
    renderPagination();
}

// View ticket details
function viewTicket(ticketId) {
    // Redirect to ticket details page
   window.location.href = `/internal_portal/app/views/tickets/detail.php?id=${ticketId}`;
}

// Apply filters
function applyFilters() {
    const searchTerm = document.getElementById('search').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value;
    const priorityFilter = document.getElementById('priority-filter').value;
    
    filteredTickets = allTickets.filter(ticket => {
        const matchesSearch = !searchTerm || 
            ticket.title.toLowerCase().includes(searchTerm) ||
            ticket.ticket_number.toLowerCase().includes(searchTerm) ||
            ticket.id.toString().includes(searchTerm);
        
        const matchesStatus = !statusFilter || ticket.status === statusFilter;
        const matchesPriority = !priorityFilter || ticket.priority === priorityFilter;
        
        return matchesSearch && matchesStatus && matchesPriority;
    });
    
    currentPage = 1; // Reset to first page
    renderTickets();
}

// Render pagination
function renderPagination() {
    const totalPages = Math.ceil(filteredTickets.length / itemsPerPage);
    const info = document.getElementById('pagination-info');
    const controls = document.getElementById('pagination-controls');
    
    // Info
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, filteredTickets.length);
    info.textContent = `Showing ${startItem}-${endItem} of ${filteredTickets.length} tickets`;
    
    // Controls
    if (totalPages <= 1) {
        controls.innerHTML = '';
        return;
    }
    
    let html = `
        <button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>
    `;
    
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += `<button onclick="changePage(${i})" class="${i === currentPage ? 'active' : ''}">${i}</button>`;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            html += `<span>...</span>`;
        }
    }
    
    html += `
        <button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>
    `;
    
    controls.innerHTML = html;
}

// Change page
function changePage(page) {
    const totalPages = Math.ceil(filteredTickets.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderTickets();
}

// Open create modal
function openCreateModal() {
    alert('Create ticket modal coming soon!');
    // TODO: Implement create ticket modal
}

// Show loading state
function showLoading() {
    document.getElementById('loading').style.display = 'block';
    document.getElementById('error').style.display = 'none';
    document.getElementById('tickets-container').style.display = 'none';
    document.getElementById('empty-state').style.display = 'none';
}

// Show error state
function showError(message) {
    const errorDiv = document.getElementById('error');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    document.getElementById('loading').style.display = 'none';
    document.getElementById('tickets-container').style.display = 'none';
    document.getElementById('empty-state').style.display = 'none';
}

// Helper: Escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Helper: Format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    // Less than 1 minute
    if (diff < 60000) {
        return 'Just now';
    }
    // Less than 1 hour
    if (diff < 3600000) {
        const mins = Math.floor(diff / 60000);
        return `${mins} minute${mins > 1 ? 's' : ''} ago`;
    }
    // Less than 24 hours
    if (diff < 86400000) {
        const hours = Math.floor(diff / 3600000);
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    }
    // Less than 7 days
    if (diff < 604800000) {
        const days = Math.floor(diff / 86400000);
        return `${days} day${days > 1 ? 's' : ''} ago`;
    }
    // Format as "Today", "Yesterday", or date
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const ticketDate = new Date(date);
    ticketDate.setHours(0, 0, 0, 0);
    const daysDiff = Math.floor((today - ticketDate) / 86400000);
    
    if (daysDiff === 0) return 'Today';
    if (daysDiff === 1) return 'Yesterday';
    if (daysDiff < 7) return `${daysDiff} days ago`;
    
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

// Real-time search
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            applyFilters();
        });
    }
});