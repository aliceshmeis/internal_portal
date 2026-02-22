<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] === 'Admin') { header('Location: /internal_portal/app/views/dashboard/dashboard.php'); exit; }
$user_name  = $_SESSION['name'];
$user_role  = $_SESSION['role'];
$first_name = explode(' ', $user_name)[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/staff-dashboard.css">
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
                <a href="staff-dashboard.php" class="sidebar-nav-item active">
                    <span class="sidebar-nav-icon icon-dashboard"></span>
                    <span class="sidebar-nav-text">Dashboard</span>
                </a>
                <a href="../tickets/my-tickets.php" class="sidebar-nav-item">
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
                    <span class="breadcrumb-item">Home</span>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active">Dashboard</span>
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

            <div class="action-header">
                <h1>What's the issue today, <?php echo htmlspecialchars($first_name); ?>?</h1>
                <p>Select a category to quickly create a ticket</p>
            </div>

            <div class="counters-row">
                <div class="counter-card" onclick="goToTickets('Open')" title="View Open Tickets">
                    <div class="counter-dot blue"></div>
                    <div class="counter-info">
                        <div class="counter-value" id="counterOpen">—</div>
                        <div class="counter-label">Open</div>
                    </div>
                    <span class="counter-arrow">→</span>
                </div>
                <div class="counter-card" onclick="goToTickets('In Progress')" title="View In Progress Tickets">
                    <div class="counter-dot orange"></div>
                    <div class="counter-info">
                        <div class="counter-value" id="counterInProgress">—</div>
                        <div class="counter-label">In Progress</div>
                    </div>
                    <span class="counter-arrow">→</span>
                </div>
                <div class="counter-card" onclick="goToTickets('Pending')" title="View Pending Tickets">
                    <div class="counter-dot purple"></div>
                    <div class="counter-info">
                        <div class="counter-value" id="counterPending">—</div>
                        <div class="counter-label">Pending</div>
                    </div>
                    <span class="counter-arrow">→</span>
                </div>
            </div>

            <div class="action-grid">
                <div class="action-card-large" onclick="createTicket('IT & Software')">
                    <div class="card-icon"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"></rect><path d="M8 21h8M12 17v4"></path></svg></div>
                    <div class="card-title">IT & Software</div>
                    <div class="card-description">Software issues, access problems</div>
                </div>
                <div class="action-card-large" onclick="createTicket('Printer Issue')">
                    <div class="card-icon"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg></div>
                    <div class="card-title">Printer Issue</div>
                    <div class="card-description">Printing problems, paper jams</div>
                </div>
                <div class="action-card-large" onclick="createTicket('Network Problem')">
                    <div class="card-icon"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="2"></circle><path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"></path></svg></div>
                    <div class="card-title">Network Problem</div>
                    <div class="card-description">WiFi, internet connectivity</div>
                </div>
                <div class="action-card-large" onclick="createTicket('Hardware Issue')">
                    <div class="card-icon"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg></div>
                    <div class="card-title">Hardware Issue</div>
                    <div class="card-description">Equipment malfunction, damage</div>
                </div>
                <div class="action-card-large" onclick="createTicket('Access Request')">
                    <div class="card-icon"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></div>
                    <div class="card-title">Access Request</div>
                    <div class="card-description">System access, permissions</div>
                </div>
                <div class="action-card-large" onclick="createTicket('Item Request')">
                    <div class="card-icon"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg></div>
                    <div class="card-title">Item Request</div>
                    <div class="card-description">Request supplies, equipment</div>
                </div>
            </div>

            <div class="action-banner" id="actionBanner" style="display:none;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span id="bannerText"></span>
                <a href="../tickets/my-tickets.php?status=Pending">View Tickets →</a>
            </div>

        </div>
    </main>
</div>
<!-- mobile-menu.js handles ALL sidebar toggling — no extra script needed -->
<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/staff-dashboard.js"></script>
</body>
</html>