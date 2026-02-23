<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] === 'Admin') { header('Location: /internal_portal/app/views/dashboard/dashboard.php'); exit; }

$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$ticket_id) { header('Location: my-tickets.php'); exit; }

$user_name     = $_SESSION['name'];
$user_role     = $_SESSION['role'];
$user_initials = strtoupper(substr($user_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Detail - Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/staff-dashboard.css">
    <style>
        .sidebar { transition: width 0.25s ease; }
        .sidebar-collapsed { width: 0 !important; overflow: hidden !important; padding: 0 !important; }

        /* ── Pending Banner ── */
        .pending-banner {
            display: none;
            background: #fffbeb;
            border: 1.5px solid #fbbf24;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
            align-items: flex-start;
            gap: 14px;
        }
        .pending-banner.show { display: flex; }
        .pending-banner-icon { font-size: 22px; flex-shrink: 0; margin-top: 2px; }
        .pending-banner-body { flex: 1; }
        .pending-banner-title {
            font-size: 14px;
            font-weight: 700;
            color: #92400e;
            margin-bottom: 4px;
        }
        .pending-banner-msg {
            font-size: 13px;
            color: #78350f;
            line-height: 1.5;
        }
        .pending-banner-action {
            font-size: 12px;
            font-weight: 600;
            color: #d97706;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Page Layout ── */
        .tv-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            align-items: start;
        }
        @media (max-width: 768px) { .tv-grid { grid-template-columns: 1fr; } }

        /* ── Header Card ── */
        .tv-header-card {
            background: var(--color-bg-primary);
            border: 1px solid var(--color-border-light);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
        }
        .tv-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--color-text-primary);
            margin-bottom: 8px;
        }
        .tv-meta {
            font-size: 13px;
            color: var(--color-text-secondary);
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .tv-meta-dot { opacity: 0.4; }
        .tv-badges { display: flex; gap: 8px; flex-wrap: wrap; }

        /* ── Cards ── */
        .tv-card {
            background: var(--color-bg-primary);
            border: 1px solid var(--color-border-light);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
        }
        .tv-card-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 14px;
        }
        .tv-description {
            font-size: 14px;
            color: var(--color-text-primary);
            line-height: 1.7;
            white-space: pre-wrap;
        }

        /* ── Info Panel ── */
        .tv-info-panel {
            background: var(--color-bg-primary);
            border: 1px solid var(--color-border-light);
            border-radius: 12px;
            overflow: hidden;
        }
        .tv-info-header {
            padding: 14px 18px;
            border-bottom: 1px solid var(--color-border-light);
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .tv-info-body { padding: 16px 18px; }
        .tv-meta-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid var(--color-border-light);
            font-size: 13px;
        }
        .tv-meta-item:last-child { border-bottom: none; }
        .tv-meta-label { color: var(--color-text-secondary); }
        .tv-meta-value { font-weight: 500; color: var(--color-text-primary); text-align: right; max-width: 160px; }

        /* ── Timeline ── */
        .tv-timeline-item {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }
        .tv-timeline-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--color-primary);
            color: white;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .tv-timeline-bubble {
            flex: 1;
            background: var(--color-bg-secondary);
            border-radius: 10px;
            padding: 12px 14px;
        }
        .tv-timeline-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .tv-timeline-author { font-size: 13px; font-weight: 600; color: var(--color-text-primary); }
        .tv-timeline-time   { font-size: 11px; color: var(--color-text-secondary); }
        .tv-timeline-text   { font-size: 13px; color: var(--color-text-primary); line-height: 1.5; }
        .tv-it-bubble {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
        }
        .tv-it-label {
            font-size: 10px;
            font-weight: 700;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 4px;
        }

        /* ── Reply Form ── */
        .tv-reply-form {
            background: var(--color-bg-primary);
            border: 1px solid var(--color-border-light);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
        }
        .tv-reply-form.highlight {
            border-color: #fbbf24;
            background: #fffbeb;
        }
        .tv-reply-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--color-text-primary);
            margin-bottom: 12px;
        }
        .tv-reply-textarea {
            width: 100%;
            min-height: 100px;
            padding: 12px;
            border: 1px solid var(--color-border-light);
            border-radius: 8px;
            font-size: 13px;
            resize: vertical;
            background: var(--color-bg-primary);
            color: var(--color-text-primary);
            font-family: inherit;
            box-sizing: border-box;
        }
        .tv-reply-textarea:focus { outline: none; border-color: var(--color-primary); }
        .tv-reply-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }
        .tv-btn-reply {
            background: var(--color-primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 9px 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .tv-btn-reply:hover { opacity: 0.9; }
        .tv-btn-reply:disabled { opacity: 0.5; cursor: not-allowed; }

        /* ── Status badges ── */
        .badge-clean { display:inline-flex;align-items:center;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;letter-spacing:.02em; }
        .badge-open        { background:#eff6ff;color:#2563eb; }
        .badge-in-progress { background:#fff7ed;color:#c2410c; }
        .badge-pending     { background:#fffbeb;color:#d97706; }
        .badge-resolved    { background:#f0fdf4;color:#15803d; }
        .badge-closed      { background:#f3f4f6;color:#6b7280; }
        .priority-badge { font-size:11px;font-weight:600; }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: var(--color-text-secondary);
            text-decoration: none;
            margin-bottom: 16px;
        }
        .back-link:hover { color: var(--color-primary); }

        #tv-loading { text-align:center; padding:60px; color: var(--color-text-secondary); }
        #tv-error   { color: #ef4444; padding: 20px; }
        #tv-content { display: none; }

        .tv-no-comments { text-align:center; padding:20px; color: var(--color-text-secondary); font-size:13px; }
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
                <a href="../dashboard/staff-dashboard.php" class="sidebar-nav-item">
                    <span class="sidebar-nav-icon icon-dashboard"></span>
                    <span class="sidebar-nav-text">Dashboard</span>
                </a>
                <a href="my-tickets.php" class="sidebar-nav-item active">
                    <span class="sidebar-nav-icon icon-tickets"></span>
                    <span class="sidebar-nav-text">My Tickets</span>
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
                    <a href="my-tickets.php" class="breadcrumb-item">My Tickets</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active" id="breadcrumb-id">Loading...</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="header-user">
                    <div class="header-user-avatar"><?= $user_initials ?></div>
                    <div class="header-user-info">
                        <div class="header-user-name"><?= htmlspecialchars($user_name) ?></div>
                        <div class="header-user-role"><?= htmlspecialchars($user_role) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <div id="tv-loading">Loading ticket...</div>
            <div id="tv-error"  style="display:none;"></div>

            <div id="tv-content">
                <a href="my-tickets.php" class="back-link">← Back to My Tickets</a>

                <!-- Pending Banner -->
                <div class="pending-banner" id="pendingBanner">
                    <div class="pending-banner-icon">🟡</div>
                    <div class="pending-banner-body">
                        <div class="pending-banner-title">Waiting for your response</div>
                        <div class="pending-banner-msg" id="pendingMsg">IT has replied to your ticket and is waiting for more information from you.</div>
                        <div class="pending-banner-action">👇 Scroll down to reply</div>
                    </div>
                </div>

                <!-- Header -->
                <div class="tv-header-card">
                    <h1 class="tv-title" id="tv-title"></h1>
                    <div class="tv-meta">
                        <span id="tv-ticket-number"></span>
                        <span class="tv-meta-dot">•</span>
                        <span id="tv-campus"></span>
                        <span class="tv-meta-dot">•</span>
                        <span>Created <strong id="tv-created-meta"></strong></span>
                    </div>
                    <div class="tv-badges">
                        <span id="tv-status-badge"   class="badge-clean"></span>
                        <span id="tv-priority-badge" class="priority-badge"></span>
                    </div>
                </div>

                <div class="tv-grid">
                    <!-- LEFT -->
                    <div>
                        <!-- Description -->
                        <div class="tv-card">
                            <div class="tv-card-title">Issue Description</div>
                            <div class="tv-description" id="tv-description"></div>
                        </div>

                        <!-- Activity -->
                        <div class="tv-card">
                            <div class="tv-card-title">Activity</div>
                            <div id="tv-timeline">
                                <div class="tv-no-comments">No activity yet.</div>
                            </div>
                        </div>

                        <!-- Reply Form -->
                        <div class="tv-reply-form" id="replyForm">
                            <div class="tv-reply-title" id="replyTitle">💬 Add a Comment</div>
                            <textarea class="tv-reply-textarea" id="replyInput" placeholder="Type your reply here..."></textarea>
                            <div class="tv-reply-actions">
                                <button class="tv-btn-reply" id="replyBtn" onclick="submitReply()">Send Reply</button>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT -->
                    <div>
                        <div class="tv-info-panel">
                            <div class="tv-info-header">Ticket Info</div>
                            <div class="tv-info-body">
                                <div class="tv-meta-item">
                                    <span class="tv-meta-label">Status</span>
                                    <span class="tv-meta-value" id="tv-status-text">—</span>
                                </div>
                                <div class="tv-meta-item">
                                    <span class="tv-meta-label">Priority</span>
                                    <span class="tv-meta-value" id="tv-priority-text">—</span>
                                </div>
                                <div class="tv-meta-item">
                                    <span class="tv-meta-label">Assigned To</span>
                                    <span class="tv-meta-value" id="tv-assigned">—</span>
                                </div>
                                <div class="tv-meta-item">
                                    <span class="tv-meta-label">Category</span>
                                    <span class="tv-meta-value" id="tv-category">—</span>
                                </div>
                                <div class="tv-meta-item">
                                    <span class="tv-meta-label">Created</span>
                                    <span class="tv-meta-value" id="tv-created">—</span>
                                </div>
                                <div class="tv-meta-item">
                                    <span class="tv-meta-label">Updated</span>
                                    <span class="tv-meta-value" id="tv-updated">—</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script>
const TICKET_ID  = <?= $ticket_id ?>;
const USER_INIT  = '<?= $user_initials ?>';
const USER_NAME  = '<?= addslashes($user_name) ?>';
const API_BASE   = '/internal_portal/api/v1';

let currentTicket = null;

document.addEventListener('DOMContentLoaded', () => {
    loadTicket();
    const hamburger = document.getElementById('hamburgerMenu');
    const sidebar   = document.getElementById('sidebar');
    hamburger.addEventListener('click', () => sidebar.classList.toggle('sidebar-collapsed'));
    document.getElementById('mobileOverlay').addEventListener('click', () => sidebar.classList.remove('sidebar-collapsed'));
});

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
    } catch (e) { showError('Failed to load ticket.'); }
}

function renderTicket(t) {
    document.getElementById('tv-loading').style.display = 'none';
    document.getElementById('tv-content').style.display = 'block';

    document.getElementById('breadcrumb-id').textContent   = t.ticket_number || `#T-${t.id}`;
    document.getElementById('tv-title').textContent        = t.title;
    document.getElementById('tv-ticket-number').textContent = t.ticket_number || `#T-${t.id}`;
    document.getElementById('tv-campus').textContent       = t.campus_name   || '—';
    document.getElementById('tv-created-meta').textContent = formatDate(t.created_at);
    document.getElementById('tv-description').textContent  = t.description   || '—';
    document.getElementById('tv-status-text').textContent  = t.status;
    document.getElementById('tv-priority-text').textContent = t.priority;
    document.getElementById('tv-assigned').textContent     = t.assigned_name || 'Unassigned';
    document.getElementById('tv-category').textContent     = t.category      || '—';
    document.getElementById('tv-created').textContent      = formatDate(t.created_at);
    document.getElementById('tv-updated').textContent      = formatDate(t.updated_at);

    // Status badge
    const statusEl = document.getElementById('tv-status-badge');
    const statusClasses = { 'Open':'badge-open','In Progress':'badge-in-progress','Pending':'badge-pending','Resolved':'badge-resolved','Closed':'badge-closed' };
    statusEl.textContent = t.status;
    statusEl.className   = `badge-clean ${statusClasses[t.status] || 'badge-open'}`;

    // Priority
    const priorityColors = { 'Low':'#6b7280','Medium':'#3b82f6','High':'#f97316','Critical':'#ef4444' };
    const priorityEl = document.getElementById('tv-priority-badge');
    priorityEl.textContent = t.priority;
    priorityEl.style.color = priorityColors[t.priority] || '#6b7280';

    // Pending banner + reply form highlight
    if (t.status === 'Pending') {
        document.getElementById('pendingBanner').classList.add('show');
        document.getElementById('replyForm').classList.add('highlight');
        document.getElementById('replyTitle').textContent = '🟡 Reply to IT — Your response is needed';
        document.getElementById('replyInput').placeholder = 'Type your response to IT here...';
    }

    // Hide reply form if closed
    if (t.status === 'Closed' || t.status === 'Resolved') {
        document.getElementById('replyForm').style.display = 'none';
    }
}

async function loadComments() {
    try {
        const res    = await fetch(`${API_BASE}/tickets/comments/list.php?ticket_id=${TICKET_ID}`, { credentials: 'include' });
        const result = await res.json();
        if (result.success) renderTimeline(result.data || []);
    } catch (e) { console.error('Failed to load comments', e); }
}

function renderTimeline(comments) {
    const container = document.getElementById('tv-timeline');
    if (!comments.length) {
        container.innerHTML = '<div class="tv-no-comments">No activity yet.</div>';
        return;
    }

    container.innerHTML = comments.map(c => {
        const isIT    = c.user_role && c.user_role !== 'Staff';
        const initials = getInitials(c.user_name || 'U');
        const bubbleClass = isIT ? 'tv-it-bubble' : '';
        return `
        <div class="tv-timeline-item">
            <div class="tv-timeline-avatar" style="background:${isIT ? '#3b82f6' : '#6b7280'}">${initials}</div>
            <div class="tv-timeline-body" style="flex:1;">
                <div class="tv-timeline-bubble ${bubbleClass}">
                    ${isIT ? '<div class="tv-it-label">IT Support</div>' : ''}
                    <div class="tv-timeline-header">
                        <span class="tv-timeline-author">${escapeHtml(c.user_name || 'Unknown')}</span>
                        <span class="tv-timeline-time">${formatDate(c.created_at)}</span>
                    </div>
                    <div class="tv-timeline-text">${escapeHtml(c.comment)}</div>
                </div>
            </div>
        </div>`;
    }).join('');
}

async function submitReply() {
    const input   = document.getElementById('replyInput');
    const comment = input.value.trim();
    if (!comment) { showToast('Please enter a reply', 'error'); return; }

    const btn = document.getElementById('replyBtn');
    btn.disabled = true; btn.textContent = 'Sending...';

    try {
        // 1. Post comment
        const res  = await fetch(`${API_BASE}/tickets/comments/create.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ ticket_id: TICKET_ID, comment })
        });
        const data = await res.json();
        if (!data.success) { showToast(data.message || 'Failed', 'error'); return; }

        // 2. If ticket was Pending → auto change to In Progress
        if (currentTicket && currentTicket.status === 'Pending') {
            await fetch(`${API_BASE}/tickets/update.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: TICKET_ID, status: 'In Progress' })
            });
        }

        input.value = '';
        showToast('Reply sent!', 'success');
        loadTicket(); // reload to update status + comments
    } catch (e) { showToast('Network error', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Send Reply'; }
}

// ── Helpers ──
function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function formatDate(d) {
    if (!d) return '—';
    const date = new Date(d), now = new Date();
    const mins = Math.floor((now - date) / 60000);
    const hrs  = Math.floor(mins / 60);
    const days = Math.floor(hrs / 24);
    if (mins < 1)  return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    if (hrs  < 24) return `${hrs}h ago`;
    if (days < 7)  return `${days}d ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    document.getElementById('tv-loading').style.display = 'none';
    const el = document.getElementById('tv-error');
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
</script>
</body>
</html>