// Ticket Detail JavaScript - Redesigned

let currentTicket = null;

const STATUS_ORDER = ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'];

document.addEventListener('DOMContentLoaded', () => {
    loadTicket();
    if (IS_ADMIN) loadUsers();
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.td-btn-more') && !e.target.closest('.td-more-menu')) {
            document.getElementById('more-menu')?.classList.remove('open');
        }
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
        } else {
            showError(result.message || 'Ticket not found');
        }
    } catch (e) {
        showError('Failed to load ticket. Please check your connection.');
    }
}

// ─── RENDER TICKET ────────────────────────────────────────────────────────────

function renderTicket(ticket) {
    document.getElementById('loading').style.display        = 'none';
    document.getElementById('ticket-content').style.display = 'block';

    // Breadcrumb
    document.getElementById('breadcrumb-id').textContent = ticket.ticket_number || `#T-${ticket.id}`;

    // Header
    document.getElementById('td-title').textContent         = ticket.title;
    document.getElementById('td-ticket-number').textContent = ticket.ticket_number || `#T-${ticket.id}`;
    document.getElementById('td-campus-meta').textContent   = ticket.campus_name   || '—';
    document.getElementById('td-created-meta').textContent  = formatDate(ticket.created_at);

    // Badges
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

    // Status flow bar
    renderStatusFlow(ticket.status);

    // Description — strip the "--- Details ---" section appended by JS
    const descParts  = (ticket.description || '').split('\n\n--- Details ---\n');
    const cleanDesc  = descParts[0].trim();
    document.getElementById('td-description').textContent = cleanDesc;

    // Extra details from description
    renderExtraDetails(ticket);

    // Sidebar meta
    document.getElementById('td-creator').textContent  = ticket.creator_name  || '—';
    document.getElementById('td-assigned').textContent  = ticket.assigned_name || 'Unassigned';
    document.getElementById('td-campus').textContent    = ticket.campus_name   || '—';
    document.getElementById('td-created').textContent   = formatDate(ticket.created_at);
    document.getElementById('td-updated').textContent   = formatDate(ticket.updated_at);

    // Optional rows
    showMetaRow('td-category-row', 'td-category', ticket.category);
    showMetaRow('td-location-row', 'td-location', buildLocation(ticket.building, ticket.floor, ticket.room));
    showMetaRow('td-ssid-row',     'td-ssid',     ticket.ssid);

    if (ticket.resolved_at) {
        document.getElementById('td-resolved-row').style.display = 'flex';
        document.getElementById('td-resolved').textContent       = formatDate(ticket.resolved_at);
    }

    // Attachments
    renderAttachments(ticket.attachments || []);

    // Admin controls
    if (IS_ADMIN) {
        const statusSelect = document.getElementById('status-select');
        if (statusSelect) statusSelect.value = ticket.status;

        const btnResolve = document.getElementById('btn-resolve');
        if (ticket.status === 'Closed' || ticket.status === 'Resolved') {
            btnResolve?.setAttribute('disabled', true);
        }
    }
}

// ─── STATUS FLOW BAR ──────────────────────────────────────────────────────────

function renderStatusFlow(currentStatus) {
    const steps    = document.querySelectorAll('.td-flow-step');
    const lines    = document.querySelectorAll('.td-flow-line');
    const currentI = STATUS_ORDER.indexOf(currentStatus);

    steps.forEach((step, i) => {
        step.classList.remove('completed', 'active');
        if (i < currentI)      step.classList.add('completed');
        else if (i === currentI) step.classList.add('active');
    });

    lines.forEach((line, i) => {
        line.style.background = i < currentI ? 'var(--color-primary)' : '#e5e7eb';
    });
}

// ─── EXTRA DETAILS ────────────────────────────────────────────────────────────

function renderExtraDetails(ticket) {
    const items = [];

    if (ticket.building || ticket.floor || ticket.room) {
        items.push({ key: 'Location', val: buildLocation(ticket.building, ticket.floor, ticket.room) });
    }
    if (ticket.ssid) {
        items.push({ key: 'WiFi SSID', val: ticket.ssid });
    }

    const section = document.getElementById('td-extra-section');
    const list    = document.getElementById('td-extra-list');

    if (items.length === 0) { section.style.display = 'none'; return; }

    section.style.display = 'block';
    list.innerHTML = items.map(item => `
        <div class="td-extra-item">
            <span class="td-extra-key">${escapeHtml(item.key)}:</span>
            <span class="td-extra-val">${escapeHtml(item.val)}</span>
        </div>
    `).join('');
}

// ─── HELPERS ──────────────────────────────────────────────────────────────────

function showMetaRow(rowId, valueId, value) {
    const row = document.getElementById(rowId);
    if (value) {
        row.style.display = 'flex';
        document.getElementById(valueId).textContent = value;
    } else {
        row.style.display = 'none';
    }
}

function buildLocation(building, floor, room) {
    return [building || '', floor ? `Floor ${floor}` : '', room ? `Room ${room}` : ''].filter(Boolean).join(', ');
}

// ─── ATTACHMENTS ──────────────────────────────────────────────────────────────

function renderAttachments(attachments) {
    const section   = document.getElementById('td-attachments-section');
    const container = document.getElementById('td-attachments');

    if (!attachments || attachments.length === 0) { section.style.display = 'none'; return; }

    section.style.display = 'block';
    container.innerHTML = attachments.map(a => `
        <a href="${escapeHtml(a.file_path)}" target="_blank" class="td-attachment-item" download>
            <span class="td-attachment-icon">${getFileIcon(a.file_type)}</span>
            <div class="td-attachment-info">
                <div class="td-attachment-name">${escapeHtml(a.file_name)}</div>
                <div class="td-attachment-meta">${formatFileSize(a.file_size)} · ${formatDate(a.uploaded_at)}</div>
            </div>
            <span class="td-attachment-download">↓</span>
        </a>
    `).join('');
}

// ─── COMMENTS / TIMELINE ──────────────────────────────────────────────────────

async function loadComments() {
    try {
        const res    = await fetch(`${API_BASE}/tickets/comments/list.php?ticket_id=${TICKET_ID}`, { credentials: 'include' });
        const result = await res.json();
        if (result.success) renderTimeline(result.data || []);
    } catch (e) {
        console.error('Failed to load comments', e);
    }
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
        </div>
    `).join('');
}

// ─── ADD COMMENT ──────────────────────────────────────────────────────────────

async function addComment() {
    const input   = document.getElementById('comment-input');
    const comment = input.value.trim();
    if (!comment) { showToast('Please enter a comment', 'error'); return; }

    const btn = document.querySelector('.td-btn-comment');
    btn.disabled = true; btn.textContent = 'Posting...';

    try {
        const res  = await fetch(`${API_BASE}/tickets/comments/create.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ ticket_id: TICKET_ID, comment })
        });
        const data = await res.json();

        if (data.success) {
            input.value = '';
            loadComments();
            showToast('Comment posted', 'success');
        } else {
            showToast(data.message || 'Failed to post comment', 'error');
        }
    } catch (e) {
        showToast('Network error', 'error');
    } finally {
        btn.disabled = false; btn.textContent = 'Post Comment';
    }
}

// ─── RESOLVE ──────────────────────────────────────────────────────────────────

async function resolveTicket() {
    if (!confirm('Mark this ticket as resolved?')) return;

    const btn = document.getElementById('btn-resolve');
    btn.disabled = true; btn.textContent = 'Resolving...';

    try {
        const res  = await fetch(`${API_BASE}/tickets/resolve.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ id: TICKET_ID })
        });
        const data = await res.json();
        if (data.success) { showToast('Ticket resolved', 'success'); loadTicket(); }
        else { showToast(data.message || 'Failed', 'error'); btn.disabled = false; btn.textContent = '✓ Mark Resolved'; }
    } catch (e) {
        showToast('Network error', 'error');
        btn.disabled = false; btn.textContent = '✓ Mark Resolved';
    }
}

// ─── CLOSE ────────────────────────────────────────────────────────────────────

async function closeTicket() {
    document.getElementById('more-menu').classList.remove('open');
    if (!confirm('Close this ticket? This action cannot be undone.')) return;

    try {
        const res  = await fetch(`${API_BASE}/tickets/close.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ id: TICKET_ID })
        });
        const data = await res.json();
        if (data.success) { showToast('Ticket closed', 'success'); loadTicket(); }
        else showToast(data.message || 'Failed to close', 'error');
    } catch (e) {
        showToast('Network error', 'error');
    }
}

// ─── REOPEN ───────────────────────────────────────────────────────────────────

async function reopenTicket() {
    document.getElementById('more-menu').classList.remove('open');

    try {
        const res  = await fetch(`${API_BASE}/tickets/update.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ id: TICKET_ID, status: 'Open' })
        });
        const data = await res.json();
        if (data.success) { showToast('Ticket reopened', 'success'); loadTicket(); }
        else showToast(data.message || 'Failed', 'error');
    } catch (e) {
        showToast('Network error', 'error');
    }
}

// ─── UPDATE STATUS (auto-save on change) ──────────────────────────────────────

async function updateStatus() {
    const status = document.getElementById('status-select').value;
    if (!status) return;

    try {
        const res  = await fetch(`${API_BASE}/tickets/update.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ id: TICKET_ID, status })
        });
        const data = await res.json();
        if (data.success) { showToast('Status updated', 'success'); loadTicket(); }
        else showToast(data.message || 'Failed', 'error');
    } catch (e) {
        showToast('Network error', 'error');
    }
}

// ─── LOAD USERS ───────────────────────────────────────────────────────────────

async function loadUsers() {
    try {
        const res  = await fetch(`${API_BASE}/users/list.php`, { credentials: 'include' });
        const data = await res.json();
        const sel  = document.getElementById('assign-select');
        if (!sel) return;

        sel.innerHTML = '<option value="">Unassigned</option>';
        if (data.success && data.data) {
            data.data.forEach(u => {
                sel.innerHTML += `<option value="${u.id}">${escapeHtml(u.name)} (${u.role})</option>`;
            });
            if (currentTicket?.assigned_to) sel.value = currentTicket.assigned_to;
        }
    } catch (e) { console.error('Failed to load users', e); }
}

// ─── ASSIGN ───────────────────────────────────────────────────────────────────

async function assignTicket() {
    const assigned_to = document.getElementById('assign-select').value || null;

    try {
        const res  = await fetch(`${API_BASE}/tickets/assign.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ ticket_id: TICKET_ID, assigned_to })
        });
        const data = await res.json();
        if (data.success) { showToast('Assignment saved', 'success'); loadTicket(); }
        else showToast(data.message || 'Failed', 'error');
    } catch (e) {
        showToast('Network error', 'error');
    }
}

// ─── MORE MENU ────────────────────────────────────────────────────────────────

function toggleMoreMenu() {
    document.getElementById('more-menu').classList.toggle('open');
}

// ─── UTILITIES ────────────────────────────────────────────────────────────────

function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function getFileIcon(type) {
    if (!type) return '📎';
    if (type.startsWith('image/'))  return '🖼️';
    if (type === 'application/pdf') return '📄';
    if (type.includes('word'))      return '📝';
    return '📎';
}

function formatFileSize(bytes) {
    if (!bytes)            return '';
    if (bytes < 1024)      return bytes + ' B';
    if (bytes < 1024*1024) return (bytes/1024).toFixed(1) + ' KB';
    return (bytes/(1024*1024)).toFixed(1) + ' MB';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '—';
    const date = new Date(dateString);
    const now  = new Date();
    const diff = now - date;
    const mins = Math.floor(diff / 60000);
    const hrs  = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (mins < 1)  return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    if (hrs  < 24) return `${hrs}h ago`;
    if (days < 7)  return `${days}d ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
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