// Tickets List JavaScript
const API_BASE = '/internal_portal/api/v1';

let allTickets      = [];
let filteredTickets = [];
let allCampuses     = [];
let currentPage     = 1;
const itemsPerPage  = 10;

document.addEventListener('DOMContentLoaded', () => {
    loadTickets();
    loadCampusFilter();
    setupSearchDebounce();
    setupFilterListeners();
});

// ── Load Tickets ──────────────────────────────
async function loadTickets() {
    try {
        const response = await fetch(`${API_BASE}/tickets/list.php`, {
            method: 'GET', credentials: 'include'
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'Failed to load tickets');
        if (result.success && result.data) {
            allTickets      = result.data;
            filteredTickets = [...allTickets];
            displayTickets();
        } else throw new Error('Invalid response format');
    } catch (error) {
        console.error('Error:', error);
        showError(error.message);
    }
}

// ── Load Campus Filter Dropdown ───────────────
async function loadCampusFilter() {
    try {
        const res  = await fetch(`${API_BASE}/campuses/list.php`, { credentials: 'include' });
        const data = await res.json();
        const sel  = document.getElementById('campus-filter');
        if (!sel || !data.success) return;
        data.data.forEach(c => {
            sel.innerHTML += `<option value="${c.id}">${c.campus_name}</option>`;
        });
    } catch (e) { console.error('Failed to load campuses', e); }
}

// ── Display Tickets ───────────────────────────
function displayTickets() {
    const loading    = document.getElementById('loading');
    const container  = document.getElementById('tickets-container');
    const emptyState = document.getElementById('empty-state');
    const tbody      = document.getElementById('tickets-tbody');

    loading.style.display = 'none';

    if (filteredTickets.length === 0) {
        container.style.display  = 'none';
        emptyState.style.display = 'block';
        return;
    }

    container.style.display  = 'block';
    emptyState.style.display = 'none';

    const startIndex  = (currentPage - 1) * itemsPerPage;
    const pageTickets = filteredTickets.slice(startIndex, startIndex + itemsPerPage);

    tbody.innerHTML = pageTickets.map(ticket => {
        const creatorName     = ticket.creator_name  || 'Unknown';
        const assigneeName    = ticket.assignee_name || null;
        const creatorInitials = getInitials(creatorName);

        return `
        <tr onclick="viewTicket(${ticket.id})">
            <td><span class="ticket-id">#T-${String(ticket.id).padStart(4, '0')}</span></td>
            <td>
                <div class="ticket-title-cell">
                    <span class="ticket-title-link">${escapeHtml(ticket.title)}</span>
                    ${ticket.category ? `<span class="ticket-category">${escapeHtml(ticket.category)}</span>` : ''}
                </div>
            </td>
            <td>${ticket.campus_name ? `<span class="campus-badge">${escapeHtml(ticket.campus_name)}</span>` : '<span class="text-muted">—</span>'}</td>
            <td>${ticket.department_name ? `<span class="dept-label">${escapeHtml(ticket.department_name)}</span>` : '<span class="text-muted">—</span>'}</td>
            <td>${getPriorityBadge(ticket.priority)}</td>
            <td>${getStatusBadge(ticket.status)}</td>
            <td>
                ${assigneeName
                    ? `<div class="creator-cell">
                        <div class="creator-avatar">${getInitials(assigneeName)}</div>
                        <span class="creator-name">${escapeHtml(assigneeName)}</span>
                       </div>`
                    : '<span class="unassigned-text">Unassigned</span>'}
            </td>
            <td>
                <div class="creator-cell">
                    <div class="creator-avatar">${creatorInitials}</div>
                    <span class="creator-name">${escapeHtml(creatorName)}</span>
                </div>
            </td>
            <td><span class="ticket-date">${formatDate(ticket.created_at)}</span></td>
        </tr>`;
    }).join('');

    renderPagination();
}

// ── Helpers ───────────────────────────────────
function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

// ── NO random colors — avatars are always neutral gray via CSS ──
// getAvatarColor() removed intentionally

function getPriorityBadge(priority) {
    const map = {
        'Low':      { cls: 'priority-low',      label: 'Low' },
        'Medium':   { cls: 'priority-medium',    label: 'Medium' },
        'High':     { cls: 'priority-high',      label: 'High' },
        'Critical': { cls: 'priority-critical',  label: 'Critical' },
    };
    const p = map[priority] || map['Low'];
    return `<span class="priority-badge ${p.cls}">${p.label}</span>`;
}

function getStatusBadge(status) {
    const map = {
        'Open':        'status-open',
        'In Progress': 'status-in-progress',
        'Pending':     'status-pending',
        'Resolved':    'status-resolved',
        'Closed':      'status-closed',
    };
    const cls = map[status] || 'status-closed';
    return `<span class="status-badge ${cls}">${status}</span>`;
}

// ── Pagination ────────────────────────────────
function renderPagination() {
    const totalPages = Math.ceil(filteredTickets.length / itemsPerPage);
    const info       = document.getElementById('pagination-info');
    const controls   = document.getElementById('pagination-controls');

    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem   = Math.min(currentPage * itemsPerPage, filteredTickets.length);
    info.textContent = `Showing ${startItem}–${endItem} of ${filteredTickets.length}`;

    let html = `<button class="page-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>←</button>`;
    const maxButtons = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
    let endPage   = Math.min(totalPages, startPage + maxButtons - 1);
    if (endPage - startPage < maxButtons - 1) startPage = Math.max(1, endPage - maxButtons + 1);
    for (let i = startPage; i <= endPage; i++) {
        html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
    }
    html += `<button class="page-btn" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>→</button>`;
    controls.innerHTML = html;
}

function changePage(page) {
    const totalPages = Math.ceil(filteredTickets.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    displayTickets();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Filters ───────────────────────────────────
function applyFilters() {
    const searchTerm     = document.getElementById('search').value.toLowerCase();
    const statusFilter   = document.getElementById('status-filter').value;
    const priorityFilter = document.getElementById('priority-filter').value;
    const campusFilter   = document.getElementById('campus-filter').value;

    filteredTickets = allTickets.filter(ticket => {
        const matchesSearch   = !searchTerm     || ticket.title.toLowerCase().includes(searchTerm) || String(ticket.id).includes(searchTerm);
        const matchesStatus   = !statusFilter   || ticket.status   === statusFilter;
        const matchesPriority = !priorityFilter || ticket.priority === priorityFilter;
        const matchesCampus   = !campusFilter   || String(ticket.campus_id) === String(campusFilter);
        return matchesSearch && matchesStatus && matchesPriority && matchesCampus;
    });

    currentPage = 1;
    displayTickets();
}

let searchTimeout;
function setupSearchDebounce() {
    document.getElementById('search').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 300);
    });
}

function setupFilterListeners() {
    document.getElementById('status-filter').addEventListener('change', applyFilters);
    document.getElementById('priority-filter').addEventListener('change', applyFilters);
    document.getElementById('campus-filter').addEventListener('change', applyFilters);
}

function viewTicket(ticketId) {
    window.location.href = `/internal_portal/app/views/tickets/detail.php?id=${ticketId}`;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now  = new Date();
    const days = Math.floor((now - date) / 86400000);
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7)  return `${days} days ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    document.getElementById('loading').style.display = 'none';
    const error = document.getElementById('error');
    error.style.display = 'block';
    error.textContent   = `Error: ${message}`;
}