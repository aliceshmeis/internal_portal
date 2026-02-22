/**
 * IT Dashboard JavaScript
 * File: public/js/it-dashboard.js
 */

const API_BASE = '/internal_portal/api/v1';

// ─── INIT ────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadStats();
    loadTickets();

    document.getElementById('searchInput').addEventListener('input', () => loadTickets());
    document.getElementById('statusFilter').addEventListener('change', () => loadTickets());
    document.getElementById('closePanel').addEventListener('click', closePanel);
    document.getElementById('modalOverlay').addEventListener('click', (e) => {
        if (e.target === document.getElementById('modalOverlay')) closePanel();
    });
});

// ─── STATS ───────────────────────────────────────────────────────────────────
async function loadStats() {
    try {
        const res  = await fetch(`${API_BASE}/tickets/stats.php`);
        const data = await res.json();
        if (data.success) {
            document.getElementById('stat-open').textContent       = data.data.open        ?? 0;
            document.getElementById('stat-inprogress').textContent = data.data.in_progress ?? 0;
            document.getElementById('stat-pending').textContent    = data.data.pending      ?? 0;
            document.getElementById('stat-resolved').textContent   = data.data.resolved     ?? 0;
        }
    } catch (err) {
        console.error('Failed to load stats:', err);
    }
}

// ─── TICKETS TABLE ───────────────────────────────────────────────────────────
async function loadTickets() {
    const search = document.getElementById('searchInput').value.trim();
    const status = document.getElementById('statusFilter').value;

    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);

    setTableLoading(true);

    try {
        const res  = await fetch(`${API_BASE}/tickets/list.php?${params.toString()}`);
        const data = await res.json();
        if (data.success) {
            renderTable(data.data);
        } else {
            showTableError(data.message || 'Failed to load tickets.');
        }
    } catch (err) {
        showTableError('Network error. Please try again.');
        console.error(err);
    }
}

function renderTable(tickets) {
    const tbody = document.getElementById('ticketBody');
    if (!tickets || !tickets.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="loading-row">No tickets found.</td></tr>`;
        return;
    }
    tbody.innerHTML = tickets.map(t => `
        <tr>
            <td class="ticket-num">${escHtml(t.ticket_number)}</td>
            <td class="ticket-title">${escHtml(t.title)}</td>
            <td>${escHtml(t.category || '—')}</td>
            <td>${priorityHtml(t.priority)}</td>
            <td>${statusBadge(t.status)}</td>
            <td style="color:var(--muted);font-size:12.5px;">${formatDate(t.created_at)}</td>
            <td><button class="view-btn" onclick="openPanel(${t.id})">View</button></td>
        </tr>
    `).join('');
}

function setTableLoading(on) {
    if (on) document.getElementById('ticketBody').innerHTML =
        `<tr><td colspan="7" class="loading-row">Loading tickets…</td></tr>`;
}

function showTableError(msg) {
    document.getElementById('ticketBody').innerHTML =
        `<tr><td colspan="7" class="loading-row" style="color:#ef4444;">${msg}</td></tr>`;
}

// ─── VIEW PANEL ──────────────────────────────────────────────────────────────
async function openPanel(ticketId) {
    document.getElementById('panelBody').innerHTML =
        `<p style="color:var(--muted);font-size:13px;padding:20px;">Loading…</p>`;
    document.getElementById('modalOverlay').classList.add('active');

    try {
        const res  = await fetch(`${API_BASE}/tickets/show.php?id=${ticketId}`);
        const data = await res.json();
        if (data.success) {
            const ticket   = data.data;
            const cRes     = await fetch(`${API_BASE}/tickets/comments/list.php?ticket_id=${ticketId}`);
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
        console.error(err);
    }
}

function renderPanel(t, comments) {
    const cmtHTML = comments.length
        ? comments.map(c => `
            <div class="comment-item">
                <div class="comment-meta">${escHtml(c.user_name || c.author_name || 'User')} · ${formatDateTime(c.created_at)}</div>
                <div class="comment-text">${escHtml(c.comment)}</div>
            </div>`).join('')
        : `<p style="font-size:13px;color:var(--muted);">No comments yet.</p>`;

    // ── ASSIGNMENT CHECK ──────────────────────────────────────────────────────
    // CURRENT_USER_ID is injected by PHP in it-dashboard.php
    // t.assigned_to is the user ID that admin set on this ticket
    const assignedToMe = t.assigned_to && parseInt(t.assigned_to) === CURRENT_USER_ID;

    const statusSection = assignedToMe
        // ✅ Assigned to this IT user → show status controls
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
        // 🔒 Not assigned → show lock notice
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

        <!-- Ticket Info -->
        <div class="info-block">
            <div class="info-block-title">Ticket Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <label>Ticket #</label>
                    <span style="font-family:'DM Mono',monospace;font-size:13px;">${escHtml(t.ticket_number)}</span>
                </div>
                <div class="info-item">
                    <label>Status</label>
                    <span>${statusBadge(t.status)}</span>
                </div>
                <div class="info-item">
                    <label>Staff Name</label>
                    <span>${escHtml(t.creator_name || '—')}</span>
                </div>
                <div class="info-item">
                    <label>Campus</label>
                    <span>${escHtml(t.campus_name || '—')}</span>
                </div>
                <div class="info-item">
                    <label>Category</label>
                    <span>${escHtml(t.category || '—')}</span>
                </div>
                <div class="info-item">
                    <label>Priority</label>
                    <span>${priorityHtml(t.priority)}</span>
                </div>
                <div class="info-item">
                    <label>Date Created</label>
                    <span>${formatDate(t.created_at)}</span>
                </div>
                <div class="info-item">
                    <label>Assigned To</label>
                    <span>${escHtml(t.assignee_name || 'Unassigned')}</span>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div>
            <div class="section-label">Problem Description</div>
            <div class="desc-box">${escHtml(t.description)}</div>
        </div>

        <!-- Status section (locked or active) -->
        ${statusSection}

        <!-- Internal Comments -->
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

// ─── UPDATE STATUS ───────────────────────────────────────────────────────────
async function updateStatus(ticketId) {
    const newStatus = document.getElementById('statusSelect').value;
    try {
        const res  = await fetch(`${API_BASE}/tickets/update-status.php`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ id: ticketId, status: newStatus })
        });
        const data = await res.json();
        if (data.success) {
            showToast(`Status updated to "${newStatus}"`);
            loadTickets();
            loadStats();
        } else {
            showToast(data.message || 'Failed to update status.', true);
        }
    } catch (err) {
        showToast('Network error.', true);
        console.error(err);
    }
}

// ─── ADD COMMENT ─────────────────────────────────────────────────────────────
async function addComment(ticketId) {
    const input   = document.getElementById(`commentInput-${ticketId}`);
    const comment = input.value.trim();
    if (!comment) return;
    try {
        const res  = await fetch(`${API_BASE}/tickets/comments/create.php`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ ticket_id: ticketId, comment })
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
        console.error(err);
    }
}

// ─── HELPERS ─────────────────────────────────────────────────────────────────
function statusBadge(s) {
    const map = { 'Open': 'badge-open', 'In Progress': 'badge-inprog', 'Pending': 'badge-pending', 'Resolved': 'badge-resolved', 'Closed': 'badge-resolved' };
    return `<span class="badge ${map[s] || 'badge-open'}">${s}</span>`;
}

function priorityHtml(p) {
    return `<span class="priority-${(p || '').toLowerCase()}">${p || '—'}</span>`;
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatDateTime(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleString('en-GB', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(msg, isError = false) {
    const toast = document.getElementById('toast');
    toast.textContent      = msg;
    toast.style.background = isError ? '#ef4444' : 'var(--navy)';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2500);
}