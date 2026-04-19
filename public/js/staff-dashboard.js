const API_BASE = '/internal_portal/api/v1';

// ─── ICON MAP ─────────────────────────────────────────────────────────────────
const DEPT_ICONS = {
    'it':                `<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"></rect><path d="M8 21h8M12 17v4"></path></svg>`,
    'finance':           `<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>`,
    'hr':                `<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>`,
    'human resources':   `<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>`,
    'library':           `<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>`,
    'registrar':         `<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>`,
    'pharmacy':          `<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 3H5a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-3"></path><rect x="8" y="1" width="8" height="4" rx="1" ry="1"></rect></svg>`,
    'engineering':       `<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"></circle><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"></path></svg>`,
    'admissions':        `<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>`,
    'student affairs':   `<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>`,
    'school of business':`<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>`,
    'school of education':`<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>`,
    'arts':              `<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>`,
    'default':           `<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`
};

function getDeptIcon(name) {
    const key = (name || '').toLowerCase();
    for (const [k, v] of Object.entries(DEPT_ICONS)) {
        if (key.includes(k)) return v;
    }
    return DEPT_ICONS['default'];
}

// ─── INIT ─────────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    loadDepartments();
    loadDashboard();
});

// ─── LOAD DEPARTMENTS (dynamic cards) ─────────────────────────────────────────

async function loadDepartments() {
    const grid = document.getElementById('action-grid');
    try {
        const res  = await fetch(`${API_BASE}/departments/list.php`, { credentials: 'include' });
        const data = await res.json();

        if (data.success && data.data.length) {
            grid.innerHTML = data.data.map(d => `
                <div class="action-card-large" onclick="createTicket('${escapeAttr(d.name)}', ${d.id})">
                    <div class="card-icon">${getDeptIcon(d.name)}</div>
                    <div class="card-title">${escapeHtml(d.name)}</div>
                    <div class="card-description">${escapeHtml(d.description || '')}</div>
                </div>`).join('');
        } else {
            grid.innerHTML = '<div class="mini-table-empty">No departments found for your campus.</div>';
        }
    } catch (e) {
        console.error('Failed to load departments', e);
        grid.innerHTML = '<div class="mini-table-empty">Failed to load departments.</div>';
    }
}

// ─── LOAD DASHBOARD DATA ──────────────────────────────────────────────────────

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

// ─── COUNTERS ─────────────────────────────────────────────────────────────────

function renderCounters(myTickets, assignedTickets) {
    const activeMyTickets = myTickets.filter(t => t.status !== 'Closed' && t.status !== 'Resolved');
    const pending         = myTickets.filter(t => t.status === 'Pending');

    document.getElementById('counterMyRequests').textContent = activeMyTickets.length;
    const activeAssigned = assignedTickets.filter(t => t.status !== 'Resolved');
    document.getElementById('counterAssigned').textContent   = activeAssigned.length;
    document.getElementById('counterPending').textContent    = pending.length;
}

// ─── BANNER ───────────────────────────────────────────────────────────────────

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

// ─── TABLES ───────────────────────────────────────────────────────────────────

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

// ─── UTILITIES ────────────────────────────────────────────────────────────────

function getStatusBadge(status) {
    const map = {
        'Open':        'badge-open',
        'In Progress': 'badge-in-progress',
        'Pending':     'badge-pending',
        'Resolved':    'badge-resolved',
        'Closed':      'badge-closed',
        'Returned':    'badge-returned'
    };
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

// Safe for use inside onclick="" attributes
function escapeAttr(text) {
    if (!text) return '';
    return text.replace(/'/g, "\\'");
}

function createTicket(category, department_id) {
    let url = `../tickets/create.php?category=${encodeURIComponent(category)}`;
    if (department_id) url += `&department_id=${department_id}`;
    window.location.href = url;
}

function viewTicket(id) {
    window.location.href = `../tickets/detail.php?id=${id}`;
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