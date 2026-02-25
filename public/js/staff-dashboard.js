const API_BASE = '/internal_portal/api/v1';

document.addEventListener('DOMContentLoaded', () => {
    loadDashboard();
});

async function loadDashboard() {
    try {
        const res    = await fetch(`${API_BASE}/staff/dashboard.php`, { credentials: 'include' });
        const result = await res.json();

        if (result.success && result.data) {
            const myTickets       = result.data.my_tickets       || [];
            const assignedTickets = result.data.assigned_tickets || [];

            renderCounters(myTickets, assignedTickets);
            renderActionBanner(myTickets);
            renderAssignedTable(assignedTickets);
            renderRequestsTable(myTickets);
        }
    } catch (e) {
        console.error('Error loading dashboard:', e);
    }
}

function renderCounters(myTickets, assignedTickets) {
    const activeMyTickets = myTickets.filter(t => t.status !== 'Closed' && t.status !== 'Resolved');
    const pending         = myTickets.filter(t => t.status === 'Pending');

    document.getElementById('counterMyRequests').textContent = activeMyTickets.length;
    document.getElementById('counterAssigned').textContent   = assignedTickets.length;
    document.getElementById('counterPending').textContent    = pending.length;
}

function renderActionBanner(myTickets) {
    const pending = myTickets.filter(t => t.status === 'Pending').length;
    const banner  = document.getElementById('actionBanner');
    if (pending > 0) {
        banner.style.display = 'flex';
        document.getElementById('bannerText').textContent =
            `You have ${pending} ticket${pending > 1 ? 's' : ''} waiting for your reply.`;
    } else {
        banner.style.display = 'none';
    }
}

function renderAssignedTable(tickets) {
    document.getElementById('assignedLoading').style.display = 'none';

    if (!tickets.length) {
        document.getElementById('assignedEmpty').style.display = 'block';
        return;
    }

    document.getElementById('assignedTable').style.display = 'table';
    const top5 = tickets.slice(0, 5);

    document.getElementById('assignedTbody').innerHTML = top5.map(t => `
        <tr onclick="viewTicket(${t.id})">
            <td><span style="font-size:12px;font-weight:600;color:var(--color-text-secondary);">#T-${String(t.id).padStart(4,'0')}</span></td>
            <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(t.title)}</td>
            <td>${getStatusBadge(t.status)}</td>
            <td style="font-size:12px;color:var(--color-text-secondary);">${escapeHtml(t.creator_name || '—')}</td>
        </tr>
    `).join('');
}

function renderRequestsTable(tickets) {
    document.getElementById('requestsLoading').style.display = 'none';

    if (!tickets.length) {
        document.getElementById('requestsEmpty').style.display = 'block';
        return;
    }

    document.getElementById('requestsTable').style.display = 'table';
    const top5 = tickets.slice(0, 5);

    document.getElementById('requestsTbody').innerHTML = top5.map(t => `
        <tr onclick="viewTicket(${t.id})">
            <td><span style="font-size:12px;font-weight:600;color:var(--color-text-secondary);">#T-${String(t.id).padStart(4,'0')}</span></td>
            <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(t.title)}</td>
            <td>
                ${getStatusBadge(t.status)}
                ${t.status === 'Pending' ? '<div style="font-size:10px;color:#d97706;margin-top:2px;">⚡ Reply needed</div>' : ''}
            </td>
            <td style="font-size:12px;color:var(--color-text-secondary);">${formatDate(t.updated_at)}</td>
        </tr>
    `).join('');
}

function getStatusBadge(status) {
    const map = { 'Open':'badge-open','In Progress':'badge-in-progress','Pending':'badge-pending','Resolved':'badge-resolved','Closed':'badge-closed' };
    return `<span class="badge-clean ${map[status] || 'badge-open'}">${status}</span>`;
}

function formatDate(d) {
    if (!d) return '—';
    const date = new Date(d), now = new Date();
    const days = Math.floor((now - date) / 86400000);
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7)  return `${days}d ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function escapeHtml(text) {
    if (!text) return '—';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function createTicket(category) {
    window.location.href = `../tickets/create.php?category=${encodeURIComponent(category)}`;
}

function viewTicket(id) {
    window.location.href = `../tickets/view.php?id=${id}`;
}

function goToTickets(status) {
    const url = status
        ? `../tickets/my-tickets.php?status=${encodeURIComponent(status)}`
        : '../tickets/my-tickets.php';
    window.location.href = url;
}

function goToAssigned() {
    window.location.href = '../tickets/assigned.php';
}