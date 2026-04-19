<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] === 'Admin') { header('Location: /internal_portal/app/views/dashboard/dashboard.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
$is_head   = isset($_SESSION['is_head']) && $_SESSION['is_head'] == 1;
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
    <link rel="stylesheet" href="/internal_portal/public/css/subtask.css">
    <style>
        /* ── Head Action Buttons ── */
        .head-actions {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .head-actions-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--color-text-secondary);
            margin-bottom: 4px;
        }
        .btn-submit-admin {
            width: 100%;
            padding: 11px;
            background: #1d4ed8;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-submit-admin:hover { background: #1e40af; }
        .btn-submit-admin:disabled { background: #93c5fd; cursor: not-allowed; }
        .btn-head-return {
            width: 100%;
            padding: 11px;
            background: #fff;
            color: #dc2626;
            border: 1.5px solid #fca5a5;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-head-return:hover { background: #fef2f2; }

        /* ── Return reason box ── */
        .return-reason-box {
            display: none;
            margin-top: 10px;
        }
        .return-reason-box textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 13.5px;
            resize: vertical;
            min-height: 90px;
            box-sizing: border-box;
            margin-bottom: 8px;
        }
        .btn-confirm-return {
            width: 100%;
            padding: 10px;
            background: #dc2626;
            color: #fff;
            border: none;
            border-radius: 7px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-confirm-return:hover { background: #b91c1c; }

        /* ── Already submitted badge ── */
        .submitted-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 600;
            width: 100%;
            justify-content: center;
        }
    </style>
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
                <a href="../dashboard/staff-dashboard.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-dashboard"></span><span class="sidebar-nav-text">Dashboard</span></a>
                <a href="my-tickets.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-tickets"></span><span class="sidebar-nav-text">My Requests</span></a>
                <a href="assigned.php" class="sidebar-nav-item active"><span class="sidebar-nav-icon icon-tickets"></span><span class="sidebar-nav-text">Assigned To Me</span></a>
                <a href="../assets/my-assets.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-assets"></span><span class="sidebar-nav-text">My Assets</span></a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <a href="/internal_portal/app/views/auth/logout.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-logout"></span><span class="sidebar-nav-text">Logout</span></a>
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
                    <div class="header-user-avatar"><?php echo strtoupper(substr($user_name,0,1)); ?></div>
                    <div class="header-user-info">
                        <div class="header-user-name"><?php echo htmlspecialchars($user_name); ?></div>
                        <div class="header-user-role"><?php echo htmlspecialchars($user_role); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-content">
            <div class="page-header-row"><div><h1 class="page-title">Assigned To Me</h1><p class="page-subtitle">Your assigned tickets and subtasks</p></div></div>

            <!-- SECTION 1: Tickets -->
            <div class="section-card" style="margin-bottom:24px;">
                <div class="assigned-section-title-row">
                    <span class="assigned-section-title">My Assigned Tickets</span>
                    <span class="assigned-section-badge" id="tickets-badge">0</span>
                </div>
                <div class="filters-row" style="margin-bottom:16px;">
                    <input type="text" id="searchInput" placeholder="Search tickets..." class="filter-input">
                    <select id="statusFilter" class="filter-select"><option value="">All Statuses</option><option value="Open">Open</option><option value="In Progress">In Progress</option><option value="Pending">Pending</option><option value="Resolved">Resolved</option><option value="Closed">Closed</option></select>
                    <select id="priorityFilter" class="filter-select"><option value="">All Priorities</option><option value="Low">Low</option><option value="Medium">Medium</option><option value="High">High</option><option value="Critical">Critical</option></select>
                </div>
                <div id="ticketsLoading" class="loading-small">Loading tickets...</div>
                <div id="ticketsEmpty" style="display:none;"><div class="empty-clean"><p>No tickets assigned to you yet.</p></div></div>
                <div id="ticketsTable" style="display:none;">
                    <table class="tickets-table-clean">
                        <thead><tr><th style="padding:12px 20px;">ID</th><th style="padding:12px 20px;">Title</th><th style="padding:12px 20px;">Requester</th><th style="padding:12px 20px;">Status</th><th style="padding:12px 20px;">Priority</th><th style="padding:12px 20px;">Updated</th></tr></thead>
                        <tbody id="ticketsTbody"></tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 2: Subtasks -->
            <div class="section-card">
                <div class="assigned-section-title-row">
                    <span class="assigned-section-title">My Subtasks</span>
                    <span class="assigned-section-badge orange" id="subtasks-badge">0</span>
                </div>
                <div id="subtasksLoading" class="loading-small">Loading subtasks...</div>
                <div id="subtasksEmpty" style="display:none;"><div class="empty-clean"><p>No subtasks assigned to you yet.</p></div></div>
                <div id="subtasksTable" style="display:none;">
                    <table style="width:100%;border-collapse:collapse;">
                        <thead style="background:var(--color-bg-secondary);border-bottom:1px solid var(--color-border-light);">
                            <tr><th class="subtask-table-th">Subtask</th><th class="subtask-table-th">Parent Ticket</th><th class="subtask-table-th">My Status</th><th class="subtask-table-th">Due Date</th><th class="subtask-table-th">Update</th></tr>
                        </thead>
                        <tbody id="subtasksTbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Side Panel -->
<div class="modal-overlay" id="modalOverlay">
    <div class="ticket-panel" id="ticketPanel">
        <div class="panel-header"><h2>Ticket Details</h2><button class="close-btn" id="closePanel">&#x2715;</button></div>
        <div class="panel-body" id="panelBody"></div>
    </div>
</div>
<div class="toast" id="toast"></div>

<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script>
const API_BASE       = '/internal_portal/api/v1';
const CURRENT_USER_ID = <?php echo (int)$_SESSION['user_id']; ?>;
const IS_HEAD        = <?php echo $is_head ? 'true' : 'false'; ?>;

let allTickets = [], allSubtasks = [];

document.addEventListener('DOMContentLoaded', () => {
    loadTickets();
    loadSubtasks();
    document.getElementById('searchInput').addEventListener('input', renderTicketsTable);
    document.getElementById('statusFilter').addEventListener('change', renderTicketsTable);
    document.getElementById('priorityFilter').addEventListener('change', renderTicketsTable);
    document.getElementById('closePanel').addEventListener('click', closePanel);
    document.getElementById('modalOverlay').addEventListener('click', e => {
        if (e.target === document.getElementById('modalOverlay')) closePanel();
    });
});

// ─── LOAD TICKETS ─────────────────────────────────────────────────────────────

async function loadTickets() {
    try {
        const r = await (await fetch(`${API_BASE}/tickets/assigned.php`, { credentials: 'include' })).json();
        allTickets = r.data || [];
        document.getElementById('ticketsLoading').style.display = 'none';
        document.getElementById('tickets-badge').textContent    = allTickets.length;
        renderTicketsTable();
    } catch (e) { document.getElementById('ticketsLoading').textContent = 'Failed.'; }
}

function renderTicketsTable() {
    const s  = document.getElementById('searchInput').value.toLowerCase();
    const st = document.getElementById('statusFilter').value;
    const p  = document.getElementById('priorityFilter').value;
    const f  = allTickets.filter(t =>
        (!s  || t.title.toLowerCase().includes(s)) &&
        (!st || t.status   === st) &&
        (!p  || t.priority === p)
    );
    if (!f.length) {
        document.getElementById('ticketsTable').style.display = 'none';
        document.getElementById('ticketsEmpty').style.display = 'block';
        return;
    }
    document.getElementById('ticketsEmpty').style.display = 'none';
    document.getElementById('ticketsTable').style.display  = 'block';
    document.getElementById('ticketsTbody').innerHTML = f.map(t => `
        <tr onclick="openPanel(${t.id})" style="cursor:pointer;border-bottom:1px solid var(--color-border-light);"
            onmouseover="this.style.background='#f8fbff'" onmouseout="this.style.background=''">
            <td style="padding:13px 20px;font-family:monospace;font-size:12px;color:var(--color-text-secondary);">#T-${String(t.id).padStart(4,'0')}</td>
            <td style="padding:13px 20px;font-weight:600;font-size:13.5px;">${escHtml(t.title)}</td>
            <td style="padding:13px 20px;font-size:13px;color:var(--color-text-secondary);">${escHtml(t.creator_name || '')}</td>
            <td style="padding:13px 20px;">${getStatusBadge(t.status)}</td>
            <td style="padding:13px 20px;">${getPriorityBadge(t.priority)}</td>
            <td style="padding:13px 20px;font-size:12.5px;color:var(--color-text-secondary);">${formatDate(t.updated_at)}</td>
        </tr>`).join('');
}

// ─── LOAD SUBTASKS ────────────────────────────────────────────────────────────

async function loadSubtasks() {
    try {
        const r = await (await fetch(`${API_BASE}/subtasks/my-subtasks.php`, { credentials: 'include' })).json();
        allSubtasks = r.data || [];
        document.getElementById('subtasksLoading').style.display = 'none';
        document.getElementById('subtasks-badge').textContent    = allSubtasks.length;
        renderSubtasksTable();
    } catch (e) { document.getElementById('subtasksLoading').textContent = 'Failed.'; }
}

function renderSubtasksTable() {
    if (!allSubtasks.length) {
        document.getElementById('subtasksTable').style.display = 'none';
        document.getElementById('subtasksEmpty').style.display = 'block';
        return;
    }
    document.getElementById('subtasksEmpty').style.display = 'none';
    document.getElementById('subtasksTable').style.display  = 'block';
    document.getElementById('subtasksTbody').innerHTML = allSubtasks.map(s => {
        const ov = s.due_date && new Date(s.due_date) < new Date() && s.user_status !== 'Done';
        const dc = s.user_status === 'Done' ? 'dot-done' : s.user_status === 'In Progress' ? 'dot-in-progress' : 'dot-assigned';
        return `
        <tr style="border-bottom:1px solid var(--color-border-light);"
            onmouseover="this.style.background='#f8fbff'" onmouseout="this.style.background=''">
            <td class="subtask-table-td"><div style="font-weight:600;font-size:13.5px;">${escHtml(s.title)}</div></td>
            <td class="subtask-table-td">
                <div style="font-family:monospace;font-size:12px;color:var(--color-primary);">${escHtml(s.ticket_number)}</div>
                <div style="font-size:12px;color:var(--color-text-secondary);">${escHtml(s.ticket_title)}</div>
            </td>
            <td class="subtask-table-td"><span class="my-status-pill"><span class="status-dot ${dc}"></span>${escHtml(s.user_status)}</span></td>
            <td class="subtask-table-td"><span class="${ov ? 'due-label overdue' : 'due-label'}">${s.due_date ? new Date(s.due_date).toLocaleDateString('en-US', {month:'short',day:'numeric',year:'numeric'}) : '—'}</span></td>
            <td class="subtask-table-td">
                <div style="display:flex;align-items:center;gap:8px;">
                    <select class="subtask-status-select" id="subtask-status-${s.id}">
                        <option value="Assigned"    ${s.user_status === 'Assigned'    ? 'selected' : ''}>Assigned</option>
                        <option value="In Progress" ${s.user_status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                        <option value="Done"        ${s.user_status === 'Done'        ? 'selected' : ''}>Done</option>
                    </select>
                    <button class="btn-update-status" onclick="updateSubtaskStatus(${s.id})">Save</button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

async function updateSubtaskStatus(id) {
    const us = document.getElementById(`subtask-status-${id}`).value;
    try {
        const r = await (await fetch(`${API_BASE}/subtasks/update-status.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
            body: JSON.stringify({ subtask_id: id, user_status: us })
        })).json();
        if (r.success) { showToast('Status updated'); loadSubtasks(); }
        else showToast(r.message || 'Failed.', true);
    } catch (e) { showToast('Network error.', true); }
}

// ─── PANEL ────────────────────────────────────────────────────────────────────

async function openPanel(id) {
    document.getElementById('panelBody').innerHTML = '<p style="padding:20px;">Loading...</p>';
    document.getElementById('modalOverlay').classList.add('active');
    try {
        const r  = await (await fetch(`${API_BASE}/tickets/show.php?id=${id}`, { credentials: 'include' })).json();
        const cr = await (await fetch(`${API_BASE}/tickets/comments/list.php?ticket_id=${id}`, { credentials: 'include' })).json();
        if (r.success) renderPanel(r.data, cr.success ? (cr.data || []) : []);
    } catch (e) {
        document.getElementById('panelBody').innerHTML = '<p style="padding:20px;color:red;">Failed.</p>';
    }
}

function renderPanel(t, c) {
    const isAssignedToMe = t.assigned_to && parseInt(t.assigned_to) === CURRENT_USER_ID;

    // Comments HTML
    const ch = c.length ? c.map(x => {
        const isReturn   = x.comment.startsWith('[Return Reason]') || x.comment.startsWith('[Head Return]');
        const isResubmit = x.comment.startsWith('[Resubmitted]');
        const isSubmit   = x.comment.startsWith('[Submitted to Admin]');
        const style = isReturn   ? 'background:#fef2f2;border-left:3px solid #dc2626;padding:8px 10px;border-radius:4px;'
                    : isResubmit ? 'background:#f0fdf4;border-left:3px solid #16a34a;padding:8px 10px;border-radius:4px;'
                    : isSubmit   ? 'background:#eff6ff;border-left:3px solid #3b82f6;padding:8px 10px;border-radius:4px;'
                    : '';
        return `<div class="comment-item" style="${style}">
            <div class="comment-meta">${escHtml(x.user_name)} · ${formatDate(x.created_at)}</div>
            <div class="comment-text">${escHtml(x.comment)}</div>
        </div>`;
    }).join('') : '<p style="font-size:13px;color:gray;">No comments.</p>';

    // Status update section (for assigned users)
    const statusSection = isAssignedToMe ? `
        <div class="status-section">
            <div class="section-label">Update Status</div>
            <select id="statusSelect">
                <option value="Open"        ${t.status === 'Open'        ? 'selected' : ''}>Open</option>
                <option value="In Progress" ${t.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                <option value="Pending"     ${t.status === 'Pending'     ? 'selected' : ''}>Pending</option>
                <option value="Resolved"    ${t.status === 'Resolved'    ? 'selected' : ''}>Resolved</option>
            </select>
            <button class="update-btn" onclick="updateTicketStatus(${t.id})">Update</button>
        </div>` : '';

    // Head actions section
    const canReturn  = !['Closed', 'Returned'].includes(t.status);
    const headSection = (IS_HEAD && isAssignedToMe) ? `
        <div class="head-actions">
            <div class="head-actions-title">🏷️ Head Actions</div>

            ${t.submitted_to_admin == 1
                ? `<div class="submitted-badge">✅ Submitted to Admin</div>`
                : `<button class="btn-submit-admin" id="btn-submit-admin-${t.id}" onclick="headSubmitToAdmin(${t.id})">
                        → Submit to Admin
                   </button>`
            }

            ${canReturn ? `
            <button class="btn-head-return" onclick="toggleReturnBox(${t.id})">↵ Return to User</button>
            <div class="return-reason-box" id="return-box-${t.id}">
                <textarea id="return-reason-${t.id}" placeholder="Explain what needs to be fixed or clarified..."></textarea>
                <button class="btn-confirm-return" onclick="headReturnTicket(${t.id})">Confirm Return</button>
            </div>` : ''}
        </div>` : '';

    document.getElementById('panelBody').innerHTML = `
        <div class="info-block">
            <div class="info-block-title">Ticket Info</div>
            <div class="info-grid">
                <div class="info-item"><label>Ticket #</label><span style="font-family:monospace;">${escHtml(t.ticket_number)}</span></div>
                <div class="info-item"><label>Status</label>${statusBadge(t.status)}</div>
                <div class="info-item"><label>Campus</label><span>${escHtml(t.campus_name || '—')}</span></div>
                <div class="info-item"><label>Priority</label>${priorityHtml(t.priority)}</div>
            </div>
        </div>
        <div>
            <div class="section-label">Description</div>
            <div class="desc-box">${escHtml(t.description)}</div>
        </div>
        ${statusSection}
        ${headSection}
        <div>
            <div class="section-label">Comments</div>
            <div class="comment-list" id="commentList-${t.id}">${ch}</div>
            <textarea id="commentInput-${t.id}" placeholder="Add a note..."></textarea>
            <button class="comment-btn" onclick="addComment(${t.id})">Add Comment</button>
        </div>`;
}

function closePanel() {
    document.getElementById('modalOverlay').classList.remove('active');
}

// ─── HEAD ACTIONS ─────────────────────────────────────────────────────────────

function toggleReturnBox(id) {
    const box = document.getElementById(`return-box-${id}`);
    box.style.display = box.style.display === 'block' ? 'none' : 'block';
}

async function headReturnTicket(id) {
    const reason = document.getElementById(`return-reason-${id}`).value.trim();
    if (!reason) { showToast('Please enter a return reason', true); return; }

    const btn = document.querySelector(`#return-box-${id} .btn-confirm-return`);
    btn.disabled = true; btn.textContent = 'Returning...';

    try {
        const r = await (await fetch(`${API_BASE}/tickets/head-return.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
            body: JSON.stringify({ id, reason })
        })).json();
        if (r.success) {
            showToast('Ticket returned to user');
            closePanel();
            loadTickets();
        } else showToast(r.message || 'Failed', true);
    } catch (e) { showToast('Network error', true); }
    finally { btn.disabled = false; btn.textContent = 'Confirm Return'; }
}

async function headSubmitToAdmin(id) {
    if (!confirm('Submit this ticket to admin? The admin will be able to review and assign it.')) return;

    const btn = document.getElementById(`btn-submit-admin-${id}`);
    if (btn) { btn.disabled = true; btn.textContent = 'Submitting...'; }

    try {
        const r = await (await fetch(`${API_BASE}/tickets/submit-to-admin.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
            body: JSON.stringify({ id })
        })).json();
        if (r.success) {
            showToast('Ticket submitted to admin successfully');
            closePanel();
            loadTickets();
        } else showToast(r.message || 'Failed', true);
    } catch (e) { showToast('Network error', true); }
    finally { if (btn) { btn.disabled = false; btn.textContent = '→ Submit to Admin'; } }
}

// ─── TICKET ACTIONS ───────────────────────────────────────────────────────────

async function updateTicketStatus(id) {
    const s = document.getElementById('statusSelect').value;
    try {
        const r = await (await fetch(`${API_BASE}/tickets/update-status.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
            body: JSON.stringify({ id, status: s })
        })).json();
        if (r.success) { showToast('Status updated'); loadTickets(); }
        else showToast(r.message || 'Failed.', true);
    } catch (e) { showToast('Network error.', true); }
}

async function addComment(id) {
    const inp = document.getElementById(`commentInput-${id}`);
    const c   = inp.value.trim();
    if (!c) return;
    try {
        const r = await (await fetch(`${API_BASE}/tickets/comments/create.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
            body: JSON.stringify({ ticket_id: id, comment: c })
        })).json();
        if (r.success) {
            inp.value = '';
            const l = document.getElementById(`commentList-${id}`);
            const d = document.createElement('div');
            d.className = 'comment-item';
            d.innerHTML = `<div class="comment-meta">You · Just now</div><div class="comment-text">${escHtml(c)}</div>`;
            l.appendChild(d);
            showToast('Comment added');
        } else showToast(r.message || 'Failed.', true);
    } catch (e) { showToast('Network error.', true); }
}

// ─── UTILITIES ────────────────────────────────────────────────────────────────

function statusBadge(s) {
    const m = { 'Open':'badge-open','In Progress':'badge-inprog','Pending':'badge-pending','Resolved':'badge-resolved','Closed':'badge-resolved','Returned':'badge-returned' };
    return `<span class="badge ${m[s] || 'badge-open'}">${s}</span>`;
}
function priorityHtml(p) { return `<span class="priority-${(p||'').toLowerCase()}">${p||'—'}</span>`; }
function getStatusBadge(s) {
    const m = { 'Open':'badge-open','In Progress':'badge-in-progress','Pending':'badge-pending','Resolved':'badge-resolved','Closed':'badge-closed','Returned':'badge-returned' };
    return `<span class="badge-clean ${m[s] || 'badge-open'}">${s}</span>`;
}
function getPriorityBadge(p) {
    const m = { 'Low':'#6b7280','Medium':'#3b82f6','High':'#f97316','Critical':'#ef4444' };
    return `<span style="font-size:11px;font-weight:600;color:${m[p]||'#6b7280'};">${p||'—'}</span>`;
}
function formatDate(d) {
    if (!d) return '—';
    const date = new Date(d), now = new Date(), days = Math.floor((now - date) / 86400000);
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7)  return `${days}d ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}
function escHtml(s) {
    if (!s) return '—';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function showToast(msg, isError = false) {
    const t = document.getElementById('toast');
    t.textContent      = msg;
    t.style.background = isError ? '#ef4444' : 'var(--color-primary)';
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
}
</script>
</body>
</html>