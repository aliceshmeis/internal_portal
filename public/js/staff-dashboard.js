// Staff Dashboard JavaScript
const API_BASE = '/internal_portal/api/v1';

document.addEventListener('DOMContentLoaded', () => {
    loadDashboard();
});

async function loadDashboard() {
    try {
        const response = await fetch(`${API_BASE}/staff/dashboard.php`, {
            credentials: 'include'
        });
        const result = await response.json();
        if (result.success && result.data) {
            const tickets = result.data.my_tickets || [];
            renderCounters(tickets);
            renderActionBanner(tickets);
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
}

function renderCounters(tickets) {
    document.getElementById('counterOpen').textContent       = tickets.filter(t => t.status === 'Open').length;
    document.getElementById('counterInProgress').textContent = tickets.filter(t => t.status === 'In Progress').length;
    document.getElementById('counterPending').textContent    = tickets.filter(t => t.status === 'Pending').length;
}

function renderActionBanner(tickets) {
    const pending = tickets.filter(t => t.status === 'Pending').length;
    const banner  = document.getElementById('actionBanner');
    if (pending > 0) {
        banner.style.display = 'flex';
        document.getElementById('bannerText').textContent =
            `You have ${pending} ticket${pending > 1 ? 's' : ''} pending your response.`;
    } else {
        banner.style.display = 'none';
    }
}

function getStatusBadge(status) {
    const classes = {
        'Open':        'badge-open',
        'In Progress': 'badge-in-progress',
        'Pending':     'badge-pending',
        'Resolved':    'badge-resolved'
    };
    return `<span class="badge-clean ${classes[status] || 'badge-open'}">${status}</span>`;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now  = new Date();
    const days = Math.floor((now - date) / (1000 * 60 * 60 * 24));
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7)  return `${days}d ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function createTicket(category = null) {
    const url = category
        ? `../tickets/create.php?category=${encodeURIComponent(category)}`
        : '../tickets/create.php';
    window.location.href = url;
}

function viewTicket(id) {
    window.location.href = `../tickets/view.php?id=${id}`;
}

function goToTickets(status = null) {
    const url = status
        ? `../tickets/my-tickets.php?status=${encodeURIComponent(status)}`
        : '../tickets/my-tickets.php';
    window.location.href = url;
}