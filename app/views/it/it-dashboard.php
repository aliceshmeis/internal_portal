<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff' || strtoupper($_SESSION['department_name'] ?? '') !== 'IT') {
    header('Location: /internal_portal/public/views/auth/login.php');
    exit;
}
$itName        = htmlspecialchars($_SESSION['user_name'] ?? 'IT Staff');
$initials      = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', $itName))));
$currentUserId = (int) $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Dashboard – LIU Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/internal_portal/public/css/it-dashboard.css">
</head>
<body>

<!-- HEADER -->
<header class="top-header">
    <div class="header-left">
        <h1>IT Dashboard</h1>
        <p>Manage and resolve support tickets</p>
    </div>
    <div class="header-right">
        <button class="notif-btn" title="Notifications">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span class="notif-dot"></span>
        </button>
        <div class="it-name">
            <div class="avatar"><?= $initials ?></div>
            <?= $itName ?>
        </div>
        <a href="/internal_portal/public/views/auth/logout.php" class="logout-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Logout
        </a>
    </div>
</header>

<!-- MAIN -->
<main>

    <!-- STAT CARDS -->
    <div class="stats-row">
        <div class="stat-card open">
            <div>
                <div class="stat-num" id="stat-open">–</div>
                <div class="stat-label">Open</div>
            </div>
        </div>
        <div class="stat-card inprog">
            <div>
                <div class="stat-num" id="stat-inprogress">–</div>
                <div class="stat-label">In Progress</div>
            </div>
        </div>
        <div class="stat-card pending">
            <div>
                <div class="stat-num" id="stat-pending">–</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="stat-card resolved">
            <div>
                <div class="stat-num" id="stat-resolved">–</div>
                <div class="stat-label">Resolved</div>
            </div>
        </div>
    </div>

    <!-- TICKET TABLE -->
    <div class="section-card">
        <div class="section-header">
            <span class="section-title">All Tickets</span>
            <div class="filter-row">
                <input class="search-input" type="text" id="searchInput" placeholder="Search tickets…">
                <select class="filter-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="pending">Pending</option>
                    <option value="resolved">Resolved</option>
                </select>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="ticketBody">
                <tr><td colspan="7" class="loading-row">Loading tickets…</td></tr>
            </tbody>
        </table>
    </div>

</main>

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

<!-- TOAST -->
<div class="toast" id="toast"></div>

<!-- Pass current IT user ID securely to JS -->
<script>
    const CURRENT_USER_ID = <?= $currentUserId ?>;
</script>
<script src="/internal_portal/public/js/it-dashboard.js"></script>
</body>
</html>