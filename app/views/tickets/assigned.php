<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] === 'Admin') { header('Location: /internal_portal/app/views/dashboard/dashboard.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigned To Me - Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/staff-dashboard.css">
    <link rel="stylesheet" href="/internal_portal/public/css/it-dashboard.css">
</head>
<body>
<div class="mobile-overlay" id="mobileOverlay"></div>
<div class="page-wrapper">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="/internal_portal/public/images/liulogo.png" alt="LIU" style="height:36px;object-fit:contain;flex-shrink:0;">
            <img src="/internal_portal/public/images/Logo-Text.png" alt="LIU" style="height:22px;object-fit:contain;flex-shrink:0;">
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-section-title">Main</div>
                <a href="../dashboard/staff-dashboard.php" class="sidebar-nav-item">
                    <span class="sidebar-nav-icon icon-dashboard"></span>
                    <span class="sidebar-nav-text">Dashboard</span>
                </a>
                <a href="my-tickets.php" class="sidebar-nav-item">
                    <span class="sidebar-nav-icon icon-tickets"></span>
                    <span class="sidebar-nav-text">My Requests</span>
                </a>
                <a href="assigned.php" class="sidebar-nav-item active">
                    <span class="sidebar-nav-icon icon-tickets"></span>
                    <span class="sidebar-nav-text">Assigned To Me</span>
                </a>
                <a href="../assets/my-assets.php" class="sidebar-nav-item">
                    <span class="sidebar-nav-icon icon-assets"></span>
                    <span class="sidebar-nav-text">My Assets</span>
                </a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <a href="/internal_portal/app/views/auth/logout.php" class="sidebar-nav-item">
                <span class="sidebar-nav-icon icon-logout"></span>
                <span class="sidebar-nav-text">Logout</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger-menu" id="hamburgerMenu">☰</button>
                <div class="breadcrumb">
                    <a href="../dashboard/staff-dashboard.php" class="breadcrumb-item">Home</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active">Assigned To Me</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="header-user">
                    <div class="header-user-avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                    <div class="header-user-info">
                        <div class="header-user-name"><?php echo htmlspecialchars($user_name); ?></div>
                        <div class="header-user-role"><?php echo htmlspecialchars($user_role); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <div class="page-header-row">
                <div>
                    <h1 class="page-title">Assigned To Me</h1>
                    <p class="page-subtitle">Tickets assigned to you to handle</p>
                </div>
            </div>

            <div class="filters-row">
                <input type="text" id="searchInput" placeholder="Search tickets..." class="filter-input">
                <select id="statusFilter" class="filter-select">
                    <option value="">All Statuses</option>
                    <option value="Open">Open</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Pending">Pending</option>
                    <option value="Resolved">Resolved</option>
                    <option value="Closed">Closed</option>
                </select>
                <select id="priorityFilter" class="filter-select">
                    <option value="">All Priorities</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Critical">Critical</option>
                </select>
            </div>

            <div class="section-card">
                <div id="ticketsLoading" class="loading-small">Loading tickets...</div>
                <div id="ticketsEmpty" style="display:none;">
                    <div class="empty-clean">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:40px;height:40px;margin-bottom:12px;opacity:.3;">
                            <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
                        </svg>
                        <p>No tickets assigned to you yet.</p>
                    </div>
                </div>
                <div id="ticketsTable" style="display:none;">
                    <table class="tickets-table-clean">
                        <thead>
                            <tr>
                                <th style="padding:12px 20px;">ID</th>
                                <th style="padding:12px 20px;">Title</th>
                                <th style="padding:12px 20px;">Requester</th>
                                <th style="padding:12px 20px;">Status</th>
                                <th style="padding:12px 20px;">Priority</th>
                                <th style="padding:12px 20px;">Updated</th>
                            </tr>
                        </thead>
                        <tbody id="ticketsTbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- TICKET VIEW PANEL -->
<div class="modal-overlay" id="modalOverlay">
    <div class="ticket-panel" id="ticketPanel">
        <div class="panel-header">
            <h2>Ticket Details</h2>
            <button class="close-btn" id="closePanel">✕</button>
        </div>
        <div class="panel-body" id="panelBody"></div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script>
const API_BASE        = '/internal_portal/api/v1';
const CURRENT_USER_ID = <?php echo (int)$_SESSION['user_id']; ?>;
let allTickets        = [];

// ─── INIT ─────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadTickets();
    document.getElementById('searchInput').addEventListener('input', renderTable);
    document.getElementById('statusFilter').addEventListener('change', renderTable);
    document.getElementById('priorityFilter').addEventListener('change', renderTable);
    document.getElementById('closePanel').addEventListener('click', closePanel);
    document.getElementById('modalOverlay').addEventListener('click', (e) => {
        if (e.target === document.getElementById('modalOverlay')) closePanel();
    });

    const hamburger = document.getElementById('hamburgerMenu');
    const sidebar   = document.getElementById('sidebar');
    hamburger.addEventListener('click', () => sidebar.classList.toggle('sidebar-collapsed'));
    document.getElementById('mobileOverlay').addEventListener('click', () => sidebar.classList.remove('sidebar-collapsed'));
});

// ─── LOAD TICKETS ─────────────────────────────────────────────────────────
async function loadTickets() {
    try {
        const res    = await fetch(`${API_BASE}/tickets/assigned.php`, { credentials: 'include' });
        const result = await res.json();
        allTickets   = result.data || [];
        document.getElementById('ticketsLoading').style.display = 'none';
        renderTable();
    } catch (e) {
        document.getElementById('ticketsLoading').textContent = 'Failed to load tickets.';
    }
}

// ─── RENDER TABLE ─────────────────────────────────────────────────────────
function renderTable() {
    const search   = document.getElementById('searchInput').value.toLowerCase();
    const status   = document.getElementById('statusFilter').value;
    const priority = document.getElementById('priorityFilter').value;
    const filtered = allTickets.filter(t =>
        (!search   || t.title.toLowerCase().includes(search)) &&
        (!status   || t.status   === status) &&
        (!priority || t.priority === priority)
    );

    if (!filtered.length) {
        document.getElementById('ticketsTable').style.display = 'none';
        document.getElementById('ticketsEmpty').style.display = 'block';
        return;
    }
    document.getElementById('ticketsEmpty').style.display = 'none';
    document.getElementById('ticketsTable').style.display  = 'block';

    document.getElementById('ticketsTbody').innerHTML = filtered.map(t => `
        <tr onclick="openPanel(${t.id})" style="cursor:pointer;">
            <td style="padding:13px 20px;"><span class="ticket-id-clean">#T-${String(t.id).padStart(4,'0')}</span></td>
            <td style="padding:13px 20px;"><span class="ticket-title-clean">${escHtml(t.title)}</span></td>
            <td style="padding:13px 20px;"><span style="font-size:13px;color:var(--color-text-secondary);">${escHtml(t.creator_name || '—')}</span></td>
            <td style="padding:13px 20px;">${getStatusBadge(t.status)}</td>
            <td style="padding:13px 20px;">${getPriorityBadge(t.priority)}</td>
            <td style="padding:13px 20px;"><span class="ticket-updated">${formatDate(t.updated_at)}</span></td>
        </tr>
    `).join('');
}

// ─── OPEN PANEL ───────────────────────────────────────────────────────────
async function openPanel(ticketId) {
    document.getElementById('panelBody').innerHTML =
        `<p style="color:var(--muted);font-size:13px;padding:20px;">Loading…</p>`;
    document.getElementById('modalOverlay').classList.add('active');

    try {
        const res  = await fetch(`${API_BASE}/tickets/show.php?id=${ticketId}`, { credentials: 'include' });
        const data = await res.json();
        if (data.success) {
            const ticket   = data.data;
            const cRes     = await fetch(`${API_BASE}/tickets/comments/list.php?ticket_id=${ticketId}`, { credentials: 'include' });
            const cData    = await cRes.json();
            const comments = cData.success ? (cData.data || []) : [];
            renderPanel(ticket, comments);
        } else {
            document.getElementById('panelBody').innerHTML =
                `<p style="color:#ef4444;padding:20px;">${data.message}</p>`;
        }
    } catch (err) {
        document.getElementById('panelBody').innerHTML =
            `<p style="color:#ef4444;padding:20px;">Failed to load ticket.</p>`;
    }
}

// ─── RENDER PANEL ─────────────────────────────────────────────────────────
function renderPanel(t, comments) {
    const cmtHTML = comments.length
        ? comments.map(c => `
            <div class="comment-item">
                <div class="comment-meta">${escHtml(c.user_name || 'User')} · ${formatDateTime(c.created_at)}</div>
                <div class="comment-text">${escHtml(c.comment)}</div>
            </div>`).join('')
        : `<p style="font-size:13px;color:var(--muted);">No comments yet.</p>`;

    const assignedToMe  = t.assigned_to && parseInt(t.assigned_to) === CURRENT_USER_ID;

    const statusSection = assignedToMe
        ? `<div class="status-section">
                <div class="section-label">Update Status</div>
                <select id="statusSelect">
                    <option value="Open"        ${t.status === 'Open'        ? 'selected' : ''}>Open</option>
                    <option value="In Progress" ${t.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                    <option value="Pending"     ${t.status === 'Pending'     ? 'selected' : ''}>Pending</option>
                    <option value="Resolved"    ${t.status === 'Resolved'    ? 'selected' : ''}>Resolved</option>
                </select>
                <button class="update-btn" onclick="updateStatus(${t.id})">Update Status</button>
           </div>`
        : `<div class="status-section status-locked">
                <div class="lock-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <p class="lock-message">
                    Status changes are locked.<br>
                    <span>This ticket has not been assigned to you by an admin.</span>
                </p>
           </div>`;

    document.getElementById('panelBody').innerHTML = `
        <div class="info-block">
            <div class="info-block-title">Ticket Information</div>
            <div class="info-grid">
                <div class="info-item"><label>Ticket #</label><span style="font-family:'DM Mono',monospace;font-size:13px;">${escHtml(t.ticket_number)}</span></div>
                <div class="info-item"><label>Status</label><span>${statusBadge(t.status)}</span></div>
                <div class="info-item"><label>Staff Name</label><span>${escHtml(t.creator_name || '—')}</span></div>
                <div class="info-item"><label>Campus</label><span>${escHtml(t.campus_name || '—')}</span></div>
                <div class="info-item"><label>Category</label><span>${escHtml(t.category || '—')}</span></div>
                <div class="info-item"><label>Priority</label><span>${priorityHtml(t.priority)}</span></div>
                <div class="info-item"><label>Date Created</label><span>${formatDate(t.created_at)}</span></div>
                <div class="info-item"><label>Assigned To</label><span>${escHtml(t.assigned_name || 'Unassigned')}</span></div>
            </div>
        </div>

        <div>
            <div class="section-label">Problem Description</div>
            <div class="desc-box">${escHtml(t.description)}</div>
        </div>

        ${statusSection}

        <div>
            <div class="section-label">Internal Comments</div>
            <div class="comment-list" id="commentList-${t.id}">${cmtHTML}</div>
            <textarea id="commentInput-${t.id}" placeholder="Add a note or solution explanation…"></textarea>
            <button class="comment-btn" onclick="addComment(${t.id})">Add Comment</button>
        </div>
    `;
}

function closePanel() {
    document.getElementById('modalOverlay').classList.remove('active');
}

// ─── UPDATE STATUS ────────────────────────────────────────────────────────
async function updateStatus(ticketId) {
    const newStatus = document.getElementById('statusSelect').value;
    try {
        const res  = await fetch(`${API_BASE}/tickets/update-status.php`, {
            method:      'POST',
            headers:     { 'Content-Type': 'application/json' },
            credentials: 'include',
            body:        JSON.stringify({ id: ticketId, status: newStatus })
        });
        const data = await res.json();
        if (data.success) {
            showToast(`Status updated to "${newStatus}"`);
            loadTickets();
        } else {
            showToast(data.message || 'Failed to update status.', true);
        }
    } catch (err) {
        showToast('Network error.', true);
    }
}

// ─── ADD COMMENT ──────────────────────────────────────────────────────────
async function addComment(ticketId) {
    const input   = document.getElementById(`commentInput-${ticketId}`);
    const comment = input.value.trim();
    if (!comment) return;
    try {
        const res  = await fetch(`${API_BASE}/tickets/comments/create.php`, {
            method:      'POST',
            headers:     { 'Content-Type': 'application/json' },
            credentials: 'include',
            body:        JSON.stringify({ ticket_id: ticketId, comment })
        });
        const data = await res.json();
        if (data.success) {
            input.value = '';
            const list      = document.getElementById(`commentList-${ticketId}`);
            const noComment = list.querySelector('p');
            if (noComment) noComment.remove();
            const div     = document.createElement('div');
            div.className = 'comment-item';
            div.innerHTML = `
                <div class="comment-meta">You · Just now</div>
                <div class="comment-text">${escHtml(comment)}</div>
            `;
            list.appendChild(div);
            showToast('Comment added');
        } else {
            showToast(data.message || 'Failed to add comment.', true);
        }
    } catch (err) {
        showToast('Network error.', true);
    }
}

// ─── HELPERS ──────────────────────────────────────────────────────────────
function statusBadge(s) {
    const m = { 'Open':'badge-open','In Progress':'badge-inprog','Pending':'badge-pending','Resolved':'badge-resolved','Closed':'badge-resolved' };
    return `<span class="badge ${m[s]||'badge-open'}">${s}</span>`;
}
function priorityHtml(p) {
    return `<span class="priority-${(p||'').toLowerCase()}">${p||'—'}</span>`;
}
function getStatusBadge(s) {
    const m = {'Open':'badge-open','In Progress':'badge-in-progress','Pending':'badge-pending','Resolved':'badge-resolved','Closed':'badge-closed'};
    return `<span class="badge-clean ${m[s]||'badge-open'}">${s}</span>`;
}
function getPriorityBadge(p) {
    const m = {'Low':'#6b7280','Medium':'#3b82f6','High':'#f97316','Critical':'#ef4444'};
    return `<span style="font-size:11px;font-weight:600;color:${m[p]||'#6b7280'};">${p||'—'}</span>`;
}
function formatDate(d) {
    if (!d) return '—';
    const date = new Date(d), now = new Date();
    const days = Math.floor((now - date) / 86400000);
    if (days === 0) return 'Today'; if (days === 1) return 'Yesterday';
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString('en-US', {month:'short', day:'numeric'});
}
function formatDateTime(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleString('en-GB', { day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit' });
}
function escHtml(str) {
    if (!str) return '—';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function showToast(msg, isError = false) {
    const toast        = document.getElementById('toast');
    toast.textContent  = msg;
    toast.style.background = isError ? '#ef4444' : 'var(--navy)';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2500);
}
</script>
</body>
</html>