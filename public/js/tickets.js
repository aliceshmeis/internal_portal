// Tickets List JavaScript
const API_BASE = '/internal_portal/api/v1';

let allTickets = [];
let filteredTickets = [];
let currentPage = 1;
const itemsPerPage = 10;

// Load tickets on page load
document.addEventListener('DOMContentLoaded', () => {
    loadTickets();
    setupSearchDebounce();
    setupFilterListeners();
});

// Load tickets from API
async function loadTickets() {
    try {
        const response = await fetch(`${API_BASE}/tickets/list.php`, {
            method: 'GET',
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.message || 'Failed to load tickets');
        }
        
        if (result.success && result.data) {
            allTickets = result.data;
            filteredTickets = [...allTickets];
            displayTickets();
        } else {
            throw new Error('Invalid response format');
        }
    } catch (error) {
        console.error('Error:', error);
        showError(error.message);
    }
}

// Display tickets in table
function displayTickets() {
    const loading = document.getElementById('loading');
    const container = document.getElementById('tickets-container');
    const emptyState = document.getElementById('empty-state');
    const tbody = document.getElementById('tickets-tbody');
    
    loading.style.display = 'none';
    
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
    const pageTickets = filteredTickets.slice(startIndex, endIndex);
    
    // Render tickets
    tbody.innerHTML = pageTickets.map(ticket => `
        <tr>
            <td><span class="ticket-id">#T-${String(ticket.id).padStart(4, '0')}</span></td>
            <td><span class="ticket-title">${escapeHtml(ticket.title)}</span></td>
            <td>${getPriorityBadge(ticket.priority)}</td>
            <td>${getStatusBadge(ticket.status)}</td>
            <td>${escapeHtml(ticket.created_by_name || 'Unknown')}</td>
            <td><span class="ticket-date">${formatDate(ticket.created_at)}</span></td>
            <td style="text-align: center;">
                <button class="action-btn" onclick="viewTicket(${ticket.id})">View</button>
            </td>
        </tr>
    `).join('');
    
    renderPagination();
}

// Priority Badge
function getPriorityBadge(priority) {
    const classes = {
        'Low': 'priority-low',
        'Medium': 'priority-medium',
        'High': 'priority-high',
        'Critical': 'priority-critical'
    };
    return `<span class="priority-badge ${classes[priority] || 'priority-low'}">${priority}</span>`;
}

// Status Badge
function getStatusBadge(status) {
    const classes = {
        'Open': 'status-open',
        'In Progress': 'status-in-progress',
        'Pending': 'status-pending',
        'Resolved': 'status-resolved',
        'Closed': 'status-closed'
    };
    const className = classes[status] || 'status-open';
    return `<span class="status-badge ${className}">${status}</span>`;
}

// Pagination
function renderPagination() {
    const totalPages = Math.ceil(filteredTickets.length / itemsPerPage);
    const info = document.getElementById('pagination-info');
    const controls = document.getElementById('pagination-controls');
    
    // Pagination info
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, filteredTickets.length);
    info.textContent = `Showing ${startItem}-${endItem} of ${filteredTickets.length}`;
    
    // Pagination buttons
    let buttonsHTML = '';
    
    // Previous button
    buttonsHTML += `<button class="page-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>←</button>`;
    
    // Page numbers
    const maxButtons = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
    let endPage = Math.min(totalPages, startPage + maxButtons - 1);
    
    if (endPage - startPage < maxButtons - 1) {
        startPage = Math.max(1, endPage - maxButtons + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        buttonsHTML += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
    }
    
    // Next button
    buttonsHTML += `<button class="page-btn" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>→</button>`;
    
    controls.innerHTML = buttonsHTML;
}

function changePage(page) {
    const totalPages = Math.ceil(filteredTickets.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    displayTickets();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Apply filters
function applyFilters() {
    const searchTerm = document.getElementById('search').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value;
    const priorityFilter = document.getElementById('priority-filter').value;
    
    filteredTickets = allTickets.filter(ticket => {
        const matchesSearch = !searchTerm || 
            ticket.title.toLowerCase().includes(searchTerm) ||
            String(ticket.id).includes(searchTerm);
        
        const matchesStatus = !statusFilter || ticket.status === statusFilter;
        const matchesPriority = !priorityFilter || ticket.priority === priorityFilter;
        
        return matchesSearch && matchesStatus && matchesPriority;
    });
    
    currentPage = 1;
    displayTickets();
}

// Search debounce
let searchTimeout;
function setupSearchDebounce() {
    document.getElementById('search').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });
}

// Filter listeners
function setupFilterListeners() {
    document.getElementById('status-filter').addEventListener('change', applyFilters);
    document.getElementById('priority-filter').addEventListener('change', applyFilters);
}

// View ticket
function viewTicket(id) {
    window.location.href = `view.php?id=${id}`;
}

// Create ticket modal (placeholder)
function openCreateModal() {
    alert('Create Ticket modal will be implemented next!');
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7) return `${days} days ago`;
    
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
    });
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Show error
function showError(message) {
    const loading = document.getElementById('loading');
    const error = document.getElementById('error');
    
    loading.style.display = 'none';
    error.style.display = 'block';
    error.textContent = `Error: ${message}`;
}