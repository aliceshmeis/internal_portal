// ticket-detail.js — with subtasks, department-based user loading

let currentTicket = null;
let allUsers      = [];

const STATUS_ORDER = ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'];

document.addEventListener('DOMContentLoaded', () => {
    loadTicket();
    if (IS_ADMIN) loadUsers();
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.td-btn-more') && !e.target.closest('.td-more-menu'))
            document.getElementById('more-menu')?.classList.remove('open');
    });
});

// ─── LOAD TICKET ──────────────────────────────────────────────────────────────

async function loadTicket() {
    try {
        const res    = await fetch(`${API_BASE}/tickets/show.php?id=${TICKET_ID}`, { credentials: 'include' });
        const result = await res.json();
        if (result.success && result.data) {
            currentTicket = result.data;
            renderTicket(currentTicket);
            loadComments();
            loadSubtasks();
        } else {
            showError(result.message || 'Ticket not found');
        }
    } catch (e) { showError('Failed to load ticket.'); }
}

// ─── RENDER TICKET ────────────────────────────────────────────────────────────

function renderTicket(ticket) {
    document.getElementById('loading').style.display        = 'none';
    document.getElementById('ticket-content').style.display = 'block';

    document.getElementById('breadcrumb-id').textContent    = ticket.ticket_number || `#T-${ticket.id}`;
    document.getElementById('td-title').textContent         = ticket.title;
    document.getElementById('td-ticket-number').textContent = ticket.ticket_number || `#T-${ticket.id}`;
    document.getElementById('td-campus-meta').textContent   = ticket.campus_name  || '—';
    document.getElementById('td-created-meta').textContent  = formatDate(ticket.created_at);

    const statusEl = document.getElementById('td-status');
    statusEl.textContent = ticket.status;
    statusEl.className   = `status-badge status-${ticket.status.toLowerCase().replace(/ /g, '-')}`;

    const priorityEl = document.getElementById('td-priority');
    priorityEl.textContent = ticket.priority;
    priorityEl.className   = `priority-badge priority-${ticket.priority.toLowerCase()}`;

    if (ticket.category) {
        const catBadge = document.getElementById('td-category-badge');
        catBadge.textContent   = ticket.category;
        catBadge.style.display = 'inline-flex';
    }

    renderStatusFlow(ticket.status);

    const descParts = (ticket.description || '').split('\n\n--- Details ---\n');
    document.getElementById('td-description').textContent = descParts[0].trim();
    renderExtraDetails(ticket);

    document.getElementById('td-creator').textContent  = ticket.creator_name  || '—';
    document.getElementById('td-assigned').textContent = ticket.assigned_name || 'Unassigned';
    document.getElementById('td-campus').textContent   = ticket.campus_name   || '—';
    document.getElementById('td-created').textContent  = formatDate(ticket.created_at);
    document.getElementById('td-updated').textContent  = formatDate(ticket.updated_at);

    showMetaRow('td-category-row', 'td-category', ticket.category);
    showMetaRow('td-location-row', 'td-location', buildLocation(ticket.building, ticket.floor, ticket.room));
    showMetaRow('td-ssid-row',     'td-ssid',     ticket.ssid);

    if (ticket.resolved_at) {
        document.getElementById('td-resolved-row').style.display = 'flex';
        document.getElementById('td-resolved').textContent       = formatDate(ticket.resolved_at);
    }

    renderAttachments(ticket.attachments || []);

    if (IS_ADMIN) {
        const statusSelect = document.getElementById('status-select');
        if (statusSelect) statusSelect.value = ticket.status;
        const btnResolve = document.getElementById('btn-resolve');
        if (ticket.status === 'Closed' || ticket.status === 'Resolved')
            btnResolve?.setAttribute('disabled', true);
    }
}

function renderStatusFlow(currentStatus) {
    const steps    = document.querySelectorAll('.td-flow-step');
    const lines    = document.querySelectorAll('.td-flow-line');
    const currentI = STATUS_ORDER.indexOf(currentStatus);
    steps.forEach((step, i) => {
        step.classList.remove('completed', 'active');
        if (i < currentI)        step.classList.add('completed');
        else if (i === currentI) step.classList.add('active');
    });
    lines.forEach((line, i) => {
        line.style.background = i < currentI ? 'var(--color-primary)' : '#e5e7eb';
    });
}

function renderExtraDetails(ticket) {
    const items = [];
    if (ticket.building || ticket.floor || ticket.room)
        items.push({ key: 'Location', val: buildLocation(ticket.building, ticket.floor, ticket.room) });
    if (ticket.ssid) items.push({ key: 'WiFi SSID', val: ticket.ssid });
    const section = document.getElementById('td-extra-section');
    const list    = document.getElementById('td-extra-list');
    if (!items.length) { section.style.display = 'none'; return; }
    section.style.display = 'block';
    list.innerHTML = items.map(item => `
        <div class="td-extra-item">
            <span class="td-extra-key">${escapeHtml(item.key)}:</span>
            <span class="td-extra-val">${escapeHtml(item.val)}</span>
        </div>`).join('');
}

function renderAttachments(attachments) {
    const section   = document.getElementById('td-attachments-section');
    const container = document.getElementById('td-attachments');
    if (!attachments || !attachments.length) { section.style.display = 'none'; return; }
    section.style.display = 'block';
    container.innerHTML = attachments.map(a => `
        <a href="${escapeHtml(a.file_path)}" target="_blank" class="td-attachment-item" download>
            <span class="td-attachment-icon">${getFileIcon(a.file_type)}</span>
            <div class="td-attachment-info">
                <div class="td-attachment-name">${escapeHtml(a.file_name)}</div>
                <div class="td-attachment-meta">${formatFileSize(a.file_size)} · ${formatDate(a.uploaded_at)}</div>
            </div>
            <span class="td-attachment-download">↓</span>
        </a>`).join('');
}

// ─── SUBTASKS ─────────────────────────────────────────────────────────────────

async function loadSubtasks() {
    try {
        const res    = await fetch(`${API_BASE}/subtasks/list.php?ticket_id=${TICKET_ID}`, { credentials: 'include' });
        const result = await res.json();
        if (result.success) renderSubtasks(result.data || []);
    } catch (e) { console.error('Failed to load subtasks', e); }
}

function renderSubtasks(subtasks) {
    const list       = document.getElementById('subtask-list');
    const countBadge = document.getElementById('subtask-count');
    const active     = subtasks.filter(s => s.status !== 'Done').length;
    countBadge.textContent = active > 0 ? active : subtasks.length;

    if (!subtasks.length) {
        list.innerHTML = '<div class="subtask-empty">No subtasks yet.</div>';
        return;
    }
    list.innerHTML = subtasks.map(s => renderSubtaskItem(s)).join('');
}

function renderSubtaskItem(s) {
    const statusClass = s.status === 'Done'        ? 'status-done'
                      : s.status === 'In Progress' ? 'status-in-progress'
                      : 'status-open';

    const assigneesHtml = (s.assignments || []).map(a => {
        const dotClass = a.user_status === 'Done'        ? 'dot-done'
                       : a.user_status === 'In Progress' ? 'dot-in-progress'
                       : 'dot-assigned';
        return `<span class="subtask-assignee-chip">
                    <span class="assignee-dot ${dotClass}"></span>
                    ${escapeHtml(a.user_name)}
                    <span style="font-size:10px;color:var(--color-text-secondary);">(${a.user_status})</span>
                </span>`;
    }).join('');

    const dueHtml = s.due_date ? `
        <span class="subtask-due ${isDueOverdue(s.due_date) && s.status !== 'Done' ? 'overdue' : ''}">
            Due: ${formatDueDate(s.due_date)}
        </span>` : '';

    const myAssignment = (s.assignments || []).find(a => parseInt(a.user_id) === CURRENT_USER_ID);
    const myStatusHtml = myAssignment ? `
        <div class="my-status-row">
            <span class="my-status-label">My Status:</span>
            <select class="my-status-select" id="my-status-${s.id}">
                <option value="Assigned"    ${myAssignment.user_status === 'Assigned'    ? 'selected' : ''}>Assigned</option>
                <option value="In Progress" ${myAssignment.user_status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                <option value="Done"        ${myAssignment.user_status === 'Done'        ? 'selected' : ''}>Done</option>
            </select>
            <button class="btn-my-status" onclick="updateMySubtaskStatus(${s.id})">Save</button>
        </div>` : '';

    const commentsHtml = renderSubtaskCommentsList(s.comments || []);

    return `
        <div class="subtask-item" id="subtask-item-${s.id}">
            <div class="subtask-item-header">
                <div class="subtask-title">${escapeHtml(s.title)}</div>
                <span class="subtask-status ${statusClass}">${s.status}</span>
            </div>
            <div class="subtask-meta">
                ${s.priority ? `<span>Priority: <strong>${escapeHtml(s.priority)}</strong></span>` : ''}
                ${dueHtml}
            </div>
            ${s.description ? `<div style="font-size:13px;color:var(--color-text-secondary);margin-bottom:8px;">${escapeHtml(s.description)}</div>` : ''}
            <div class="subtask-assignees">${assigneesHtml || '<span style="font-size:12px;color:var(--color-text-secondary);">No users assigned</span>'}</div>
            ${myStatusHtml}
            <button class="btn-subtask-expand" onclick="toggleSubtaskComments(${s.id})">
                💬 Comments (${(s.comments || []).length})
            </button>
            <div class="subtask-comments-area" id="subtask-comments-${s.id}">
                <div id="subtask-comment-list-${s.id}">${commentsHtml}</div>
                <div class="subtask-comment-form">
                    <textarea class="subtask-comment-input" id="subtask-comment-input-${s.id}" placeholder="Add a comment on this subtask..." rows="2"></textarea>
                    <button class="btn-subtask-comment" onclick="addSubtaskComment(${s.id})">Post</button>
                </div>
            </div>
        </div>`;
}

function renderSubtaskCommentsList(comments) {
    if (!comments.length)
        return `<div style="font-size:12.5px;color:var(--color-text-secondary);padding:6px 0;">No comments yet.</div>`;
    return comments.map(c => `
        <div class="subtask-comment-item">
            <div class="subtask-comment-meta">${escapeHtml(c.user_name)} · ${formatDate(c.created_at)}</div>
            <div class="subtask-comment-text">${escapeHtml(c.comment)}</div>
        </div>`).join('');
}

function toggleSubtaskComments(subtask_id) {
    document.getElementById(`subtask-comments-${subtask_id}`).classList.toggle('open');
}

async function updateMySubtaskStatus(subtask_id) {
    const user_status = document.getElementById(`my-status-${subtask_id}`).value;
    try {
        const res  = await fetch(`${API_BASE}/subtasks/update-status.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
            body: JSON.stringify({ subtask_id, user_status })
        });
        const data = await res.json();
        if (data.success) { showToast('Status updated', 'success'); loadSubtasks(); }
        else showToast(data.message || 'Failed', 'error');
    } catch (e) { showToast('Network error', 'error'); }
}

async function addSubtaskComment(subtask_id) {
    const input   = document.getElementById(`subtask-comment-input-${subtask_id}`);
    const comment = input.value.trim();
    if (!comment) return;
    try {
        const res  = await fetch(`${API_BASE}/subtasks/comments/create.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
            body: JSON.stringify({ subtask_id, comment })
        });
        const data = await res.json();
        if (data.success) {
            input.value = '';
            const list  = document.getElementById(`subtask-comment-list-${subtask_id}`);
            const empty = list.querySelector('div[style]');
            if (empty) empty.remove();
            const div = document.createElement('div');
            div.className = 'subtask-comment-item';
            div.innerHTML = `
                <div class="subtask-comment-meta">You · Just now</div>
                <div class="subtask-comment-text">${escapeHtml(comment)}</div>`;
            list.appendChild(div);
            showToast('Comment added', 'success');
        } else showToast(data.message || 'Failed', 'error');
    } catch (e) { showToast('Network error', 'error'); }
}

// ─── ADD SUBTASK MODAL ────────────────────────────────────────────────────────

function openSubtaskModal() {
    document.getElementById('subtaskModalOverlay').classList.add('open');
    // Reset to initial state
    setActiveType(null);
    document.getElementById('sm-dept-group').style.display  = 'none';
    document.getElementById('sm-users-group').style.display = 'none';
    document.getElementById('sm-department').innerHTML      = '<option value="">Select department...</option>';
    document.getElementById('sm-user-list').innerHTML       = `<div class="sm-user-placeholder">Select a department first.</div>`;
}

function closeSubtaskModal() {
    document.getElementById('subtaskModalOverlay').classList.remove('open');
    document.getElementById('sm-title').value       = '';
    document.getElementById('sm-description').value = '';
    document.getElementById('sm-priority').value    = '';
    document.getElementById('sm-due-date').value    = '';
    setActiveType(null);
}

function setActiveType(type) {
    document.querySelectorAll('.sm-type-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.type === type);
    });
}

async function selectDeptType(type) {
    setActiveType(type);
    document.getElementById('sm-dept-group').style.display  = 'block';
    document.getElementById('sm-users-group').style.display = 'none';
    document.getElementById('sm-user-list').innerHTML       = `<div class="sm-user-placeholder">Select a department first.</div>`;

    const deptSelect = document.getElementById('sm-department');
    deptSelect.innerHTML = '<option value="">Loading...</option>';

    try {
        const res  = await fetch(`${API_BASE}/subtasks/departments.php?type=${type}`, { credentials: 'include' });
        const data = await res.json();
        if (data.success && data.data.length) {
            deptSelect.innerHTML = '<option value="">Select department...</option>';
            data.data.forEach(d => {
                deptSelect.innerHTML += `<option value="${d.id}">${escapeHtml(d.name)}</option>`;
            });
        } else {
            deptSelect.innerHTML = '<option value="">No departments found</option>';
        }
    } catch (e) {
        deptSelect.innerHTML = '<option value="">Failed to load</option>';
    }
}

async function onDepartmentChange() {
    const dept_id = document.getElementById('sm-department').value;
    const userList = document.getElementById('sm-user-list');

    if (!dept_id) {
        document.getElementById('sm-users-group').style.display = 'none';
        userList.innerHTML = `<div class="sm-user-placeholder">Select a department first.</div>`;
        return;
    }

    document.getElementById('sm-users-group').style.display = 'block';
    userList.innerHTML = `<div class="sm-user-placeholder">Loading users...</div>`;

    try {
        const res  = await fetch(`${API_BASE}/subtasks/users-by-department.php?department_id=${dept_id}`, { credentials: 'include' });
        const data = await res.json();
        if (data.success && data.data.length) {
            userList.innerHTML = data.data.map(u => `
                <label class="sm-user-item">
                    <input type="checkbox" value="${u.id}" name="sm-users">
                    <span class="sm-user-name">${escapeHtml(u.name)}</span>
                    <span class="sm-user-role">${escapeHtml(u.role || '')}</span>
                </label>`).join('');
        } else {
            userList.innerHTML = `<div class="sm-user-placeholder">No users found in this department.</div>`;
        }
    } catch (e) {
        userList.innerHTML = `<div class="sm-user-placeholder">Failed to load users.</div>`;
    }
}

async function submitSubtask() {
    const title = document.getElementById('sm-title').value.trim();
    if (!title) { showToast('Title is required', 'error'); return; }

    const user_ids = Array.from(document.querySelectorAll('input[name="sm-users"]:checked')).map(cb => parseInt(cb.value));

    const btn = document.getElementById('btn-sm-submit');
    btn.disabled = true; btn.textContent = 'Creating...';

    try {
        const res  = await fetch(`${API_BASE}/subtasks/create.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
            body: JSON.stringify({
                ticket_id:   TICKET_ID,
                title,
                description: document.getElementById('sm-description').value.trim() || null,
                priority:    document.getElementById('sm-priority').value            || null,
                due_date:    document.getElementById('sm-due-date').value            || null,
                user_ids
            })
        });
        const data = await res.json();
        if (data.success) { showToast('Subtask created', 'success'); closeSubtaskModal(); loadSubtasks(); }
        else showToast(data.message || 'Failed', 'error');
    } catch (e) { showToast('Network error', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Create Subtask'; }
}

// ─── TICKET COMMENTS ──────────────────────────────────────────────────────────

async function loadComments() {
    try {
        const res    = await fetch(`${API_BASE}/tickets/comments/list.php?ticket_id=${TICKET_ID}`, { credentials: 'include' });
        const result = await res.json();
        if (result.success) renderTimeline(result.data || []);
    } catch (e) { console.error('Failed to load comments', e); }
}

function renderTimeline(comments) {
    const container = document.getElementById('td-timeline');
    if (!comments.length) {
        container.innerHTML = '<div class="td-no-comments">No activity yet. Be the first to comment.</div>';
        return;
    }
    container.innerHTML = comments.map(c => `
        <div class="td-timeline-item">
            <div class="td-timeline-avatar">${getInitials(c.user_name || 'U')}</div>
            <div class="td-timeline-body">
                <div class="td-timeline-bubble">
                    <div class="td-timeline-header">
                        <span class="td-timeline-author">${escapeHtml(c.user_name || 'Unknown')}</span>
                        <span class="td-timeline-time">${formatDate(c.created_at)}</span>
                    </div>
                    <div class="td-timeline-text">${escapeHtml(c.comment)}</div>
                </div>
            </div>
        </div>`).join('');
}

async function addComment() {
    const input   = document.getElementById('comment-input');
    const comment = input.value.trim();
    if (!comment) { showToast('Please enter a comment', 'error'); return; }
    const btn = document.querySelector('.td-btn-comment');
    btn.disabled = true; btn.textContent = 'Posting...';
    try {
        const res  = await fetch(`${API_BASE}/tickets/comments/create.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
            body: JSON.stringify({ ticket_id: TICKET_ID, comment })
        });
        const data = await res.json();
        if (data.success) { input.value = ''; loadComments(); showToast('Comment posted', 'success'); }
        else showToast(data.message || 'Failed', 'error');
    } catch (e) { showToast('Network error', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Post Comment'; }
}

// ─── TICKET ACTIONS ───────────────────────────────────────────────────────────

async function resolveTicket() {
    if (!confirm('Mark this ticket as resolved?')) return;
    const btn = document.getElementById('btn-resolve');
    btn.disabled = true; btn.textContent = 'Resolving...';
    try {
        const res  = await fetch(`${API_BASE}/tickets/resolve.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
            body: JSON.stringify({ id: TICKET_ID })
        });
        const data = await res.json();
        if (data.success) { showToast('Ticket resolved', 'success'); loadTicket(); }
        else { showToast(data.message || 'Failed', 'error'); btn.disabled = false; btn.textContent = '✓ Mark Resolved'; }
    } catch (e) { showToast('Network error', 'error'); btn.disabled = false; btn.textContent = '✓ Mark Resolved'; }
}

async function closeTicket() {
    document.getElementById('more-menu').classList.remove('open');
    if (!confirm('Close this ticket? This cannot be undone.')) return;
    try {
        const res  = await fetch(`${API_BASE}/tickets/close.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
            body: JSON.stringify({ id: TICKET_ID })
        });
        const data = await res.json();
        if (data.success) { showToast('Ticket closed', 'success'); loadTicket(); }
        else showToast(data.message || 'Failed', 'error');
    } catch (e) { showToast('Network error', 'error'); }
}

async function reopenTicket() {
    document.getElementById('more-menu').classList.remove('open');
    try {
        const res  = await fetch(`${API_BASE}/tickets/update.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
            body: JSON.stringify({ id: TICKET_ID, status: 'Open' })
        });
        const data = await res.json();
        if (data.success) { showToast('Ticket reopened', 'success'); loadTicket(); }
        else showToast(data.message || 'Failed', 'error');
    } catch (e) { showToast('Network error', 'error'); }
}

async function updateStatus() {
    const status = document.getElementById('status-select').value;
    if (!status) return;
    try {
        const res  = await fetch(`${API_BASE}/tickets/update.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
            body: JSON.stringify({ id: TICKET_ID, status })
        });
        const data = await res.json();
        if (data.success) { showToast('Status updated', 'success'); loadTicket(); }
        else showToast(data.message || 'Failed', 'error');
    } catch (e) { showToast('Network error', 'error'); }
}

async function loadUsers() {
    try {
        const res  = await fetch(`${API_BASE}/users/it-list.php`, { credentials: 'include' });
        const data = await res.json();
        const sel  = document.getElementById('assign-select');
        if (data.success && data.data && data.data.length > 0) {
            allUsers = data.data;
            sel.innerHTML = '<option value="">Unassigned</option>';
            data.data.forEach(u => {
                sel.innerHTML += `<option value="${u.id}">${escapeHtml(u.name)} (${u.role})</option>`;
            });
            if (currentTicket?.assigned_to) sel.value = currentTicket.assigned_to;
        } else {
            sel.innerHTML += '<option disabled>No staff in this campus</option>';
        }
    } catch (e) { console.error('Failed to load users', e); }
}

async function assignTicket() {
    const assigned_to = document.getElementById('assign-select').value || null;
    try {
        const res  = await fetch(`${API_BASE}/tickets/assign.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
            body: JSON.stringify({ ticket_id: TICKET_ID, assigned_to })
        });
        const data = await res.json();
        if (data.success) { showToast('Assignment saved', 'success'); loadTicket(); }
        else showToast(data.message || 'Failed', 'error');
    } catch (e) { showToast('Network error', 'error'); }
}

function toggleMoreMenu() {
    document.getElementById('more-menu').classList.toggle('open');
}

// ─── UTILITIES ────────────────────────────────────────────────────────────────

function showMetaRow(rowId, valueId, value) {
    const row = document.getElementById(rowId);
    if (value) { row.style.display = 'flex'; document.getElementById(valueId).textContent = value; }
    else row.style.display = 'none';
}
function buildLocation(building, floor, room) {
    return [building||'', floor?`Floor ${floor}`:'', room?`Room ${room}`:''].filter(Boolean).join(', ');
}
function getInitials(name) {
    return name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
}
function getFileIcon(type) {
    if (!type) return '📎';
    if (type.startsWith('image/'))  return '🖼️';
    if (type === 'application/pdf') return '📄';
    if (type.includes('word'))      return '📝';
    return '📎';
}
function formatFileSize(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return bytes+' B';
    if (bytes < 1024*1024) return (bytes/1024).toFixed(1)+' KB';
    return (bytes/(1024*1024)).toFixed(1)+' MB';
}
function isDueOverdue(dateStr) { return dateStr && new Date(dateStr) < new Date(); }
function formatDueDate(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });
}
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
function formatDate(dateString) {
    if (!dateString) return '—';
    const date = new Date(dateString), now = new Date();
    const mins = Math.floor((now-date)/60000);
    const hrs  = Math.floor((now-date)/3600000);
    const days = Math.floor((now-date)/86400000);
    if (mins < 1)  return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    if (hrs  < 24) return `${hrs}h ago`;
    if (days < 7)  return `${days}d ago`;
    return date.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });
}
function showError(message) {
    document.getElementById('loading').style.display = 'none';
    const el = document.getElementById('error');
    el.style.display = 'block';
    el.textContent   = message;
}
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className   = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
}