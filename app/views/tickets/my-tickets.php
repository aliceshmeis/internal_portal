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
    <title>My Tickets - Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/staff-dashboard.css">
    <style>
        .sidebar { transition: width 0.25s ease; }
        .sidebar-collapsed { width: 0 !important; overflow: hidden !important; padding: 0 !important; }
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
                    <span class="breadcrumb-item active">My Tickets</span>
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
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
                <div>
                    <h1 style="font-size:24px;font-weight:600;color:var(--color-text-primary);margin-bottom:4px;">My Tickets</h1>
                    <p style="font-size:14px;color:var(--color-text-secondary);">All tickets you have submitted</p>
                </div>
                <button class="btn-create-small" onclick="window.location.href='create.php'">+ New Ticket</button>
            </div>

            <!-- Filters -->
            <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
                <input type="text" id="searchInput" placeholder="Search tickets..." style="flex:1;min-width:200px;padding:9px 14px;border:1px solid var(--color-border-light);border-radius:8px;font-size:13px;background:var(--color-bg-primary);color:var(--color-text-primary);">
                <select id="statusFilter" style="padding:9px 14px;border:1px solid var(--color-border-light);border-radius:8px;font-size:13px;background:var(--color-bg-primary);color:var(--color-text-primary);">
                    <option value="">All Statuses</option>
                    <option value="Open">Open</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Pending">Pending</option>
                    <option value="Resolved">Resolved</option>
                    <option value="Closed">Closed</option>
                </select>
            </div>

            <!-- Table -->
            <div class="section-card">
                <div id="ticketsLoading" class="loading-small">Loading tickets...</div>
                <div id="ticketsEmpty" style="display:none;">
                    <div class="empty-clean">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <p>No tickets found</p>
                        <button class="btn-create-small" onclick="window.location.href='create.php'">Create Your First Ticket</button>
                    </div>
                </div>
                <div id="ticketsTable" style="display:none;">
                    <table class="tickets-table-clean" style="margin:0;">
                        <thead>
                            <tr>
                                <th style="padding:12px 20px;">ID</th>
                                <th style="padding:12px 20px;">Title</th>
                                <th style="padding:12px 20px;">Category</th>
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

<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script>
    const hamburger = document.getElementById('hamburgerMenu');
    const sidebar   = document.getElementById('sidebar');
    hamburger.addEventListener('click', () => sidebar.classList.toggle('sidebar-collapsed'));
    document.getElementById('mobileOverlay').addEventListener('click', () => sidebar.classList.remove('sidebar-collapsed'));
</script>
<script>
const API_BASE = '/internal_portal/api/v1';
let allTickets = [];

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('status')) {
        document.getElementById('statusFilter').value = params.get('status');
    }
    loadTickets();
    document.getElementById('searchInput').addEventListener('input', renderTable);
    document.getElementById('statusFilter').addEventListener('change', renderTable);
});

async function loadTickets() {
    try {
        const res    = await fetch(`${API_BASE}/tickets/my-tickets.php`, { credentials: 'include' });
        const result = await res.json();
        allTickets   = result.data || [];
        document.getElementById('ticketsLoading').style.display = 'none';
        renderTable();
    } catch (e) {
        document.getElementById('ticketsLoading').textContent = 'Failed to load tickets.';
    }
}

function renderTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;

    const filtered = allTickets.filter(t => {
        const matchSearch = !search || t.title.toLowerCase().includes(search);
        const matchStatus = !status || t.status === status;
        return matchSearch && matchStatus;
    });

    if (filtered.length === 0) {
        document.getElementById('ticketsTable').style.display = 'none';
        document.getElementById('ticketsEmpty').style.display = 'block';
        return;
    }

    document.getElementById('ticketsEmpty').style.display = 'none';
    document.getElementById('ticketsTable').style.display  = 'block';

    document.getElementById('ticketsTbody').innerHTML = filtered.map(t => `
        <tr onclick="viewTicket(${t.id})" style="cursor:pointer;">
            <td style="padding:13px 20px;"><span class="ticket-id-clean">#T-${String(t.id).padStart(4,'0')}</span></td>
            <td style="padding:13px 20px;"><span class="ticket-title-clean">${escapeHtml(t.title)}</span></td>
            <td style="padding:13px 20px;"><span style="font-size:13px;color:var(--color-text-secondary);">${escapeHtml(t.category || '—')}</span></td>
            <td style="padding:13px 20px;">${getStatusBadge(t.status)}</td>
            <td style="padding:13px 20px;">${getPriorityBadge(t.priority)}</td>
            <td style="padding:13px 20px;"><span class="ticket-updated">${formatDate(t.updated_at)}</span></td>
        </tr>
    `).join('');
}

function getStatusBadge(status) {
    const map = { 'Open':'badge-open','In Progress':'badge-in-progress','Pending':'badge-pending','Resolved':'badge-resolved','Closed':'badge-resolved' };
    return `<span class="badge-clean ${map[status]||'badge-open'}">${status}</span>`;
}

function getPriorityBadge(priority) {
    const map = { 'Low':'#6b7280','Medium':'#3b82f6','High':'#f97316','Critical':'#ef4444' };
    const color = map[priority] || '#6b7280';
    return `<span style="font-size:11px;font-weight:600;color:${color};">${priority || '—'}</span>`;
}

function formatDate(d) {
    const date = new Date(d), now = new Date();
    const days = Math.floor((now - date) / 86400000);
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7)  return `${days}d ago`;
    return date.toLocaleDateString('en-US', { month:'short', day:'numeric' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function viewTicket(id) {
    window.location.href = `view.php?id=${id}`;
}
</script>
</body>
</html>