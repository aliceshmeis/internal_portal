<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] !== 'Admin') { header('Location: /internal_portal/app/views/dashboard/staff-dashboard.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
$initials  = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', trim($user_name)), 0, 2))));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – LIU Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/dashboard.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
</head>
<body>
<div class="mobile-overlay" id="mobileOverlay"></div>
<div class="page-wrapper">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <button class="hamburger-menu" id="hamburgerMenu">&#9776;</button>
            <img src="/internal_portal/public/images/liulogo.png" alt="LIU" class="sidebar-logo-icon">
            <img src="/internal_portal/public/images/Logo-Text.png" alt="LIU" class="sidebar-logo-text">
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-section-title">Main</div>
                <a href="dashboard.php" class="sidebar-nav-item active"><span class="sidebar-nav-icon icon-dashboard"></span><span class="sidebar-nav-text">Dashboard</span></a>
                <a href="../tickets/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-tickets"></span><span class="sidebar-nav-text">Tickets</span></a>
                <a href="../assets/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-assets"></span><span class="sidebar-nav-text">Assets</span></a>
            </div>
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-section-title">Inventory</div>
                <a href="../stock/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-stock"></span><span class="sidebar-nav-text">Stock</span></a>
                <a href="../purchase-orders/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-po"></span><span class="sidebar-nav-text">Purchase Orders</span></a>
            </div>
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-section-title">Administration</div>
                <a href="../users/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-users"></span><span class="sidebar-nav-text">Users</span></a>
                <a href="../reports/index.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-reports"></span><span class="sidebar-nav-text">Reports</span></a>
            </div>
        </nav>
        <!-- User info at bottom like reference image -->
        <div class="sidebar-footer">
            <div class="sidebar-footer-avatar"><?= $initials ?></div>
            <div class="sidebar-footer-info">
                <div class="sidebar-footer-name"><?= htmlspecialchars($user_name) ?></div>
                <div class="sidebar-footer-role"><?= htmlspecialchars($user_role) ?></div>
            </div>
            <a href="/internal_portal/app/views/auth/logout.php" class="sidebar-footer-logout" title="Logout">⏻</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <div class="breadcrumb">
                    <a href="dashboard.php" class="breadcrumb-item">Home</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active">Dashboard</span>
                </div>
                <div class="topbar-search">
                    <input type="text" placeholder="Search for anything...">
                </div>
            </div>
            <div class="topbar-right">
                <button class="topbar-icon-btn" title="Notifications">🔔<span class="badge">3</span></button>
                <button class="topbar-icon-btn" title="Alerts">🔔</button>
                <button class="topbar-icon-btn" title="Messages">💬</button>
            </div>
        </div>

        <div class="page-content">
            <div style="margin-bottom:24px;">
                <h1 style="font-size:22px;font-weight:700;color:#1F2937;margin-bottom:4px;">Welcome back, <?= htmlspecialchars(explode(' ', $user_name)[0]) ?></h1>
                <p style="font-size:13px;color:#6B7280;">Here's what's happening with your portal today</p>
            </div>
            <div id="loading" style="text-align:center;padding:60px 0;">
                <div class="spinner"></div>
                <p style="color:#6B7280;font-size:13px;margin-top:12px;">Loading dashboard…</p>
            </div>
            <div id="dashboardContent" style="display:none;">
                <div class="kpi-grid" id="kpiCards"></div>
                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-header"><div><div class="chart-title">Tickets by Status</div><div class="chart-subtitle">Current distribution</div></div></div>
                        <div class="chart-body donut-wrapper">
                            <div class="donut-container"><canvas id="donutChart"></canvas><div class="donut-center"><div class="donut-total-label">Total</div><div class="donut-total-value" id="donutTotal">0</div></div></div>
                            <div class="donut-legend" id="donutLegend"></div>
                        </div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header"><div><div class="chart-title">Activity Trend</div><div class="chart-subtitle">Last 7 days</div></div></div>
                        <div class="chart-body"><canvas id="lineChart"></canvas></div>
                    </div>
                </div>
                <div class="tables-grid">
                    <div class="table-card">
                        <div class="table-card-header"><h3 class="table-card-title">Recent Tickets</h3><a href="../tickets/list.php" class="table-card-action">View All →</a></div>
                        <div class="table-wrapper"><table class="table" style="margin:0;"><thead><tr><th>ID</th><th>Title</th><th>Priority</th><th>Status</th><th>Created</th></tr></thead><tbody id="recentTickets"></tbody></table></div>
                    </div>
                    <div class="table-card">
                        <div class="table-card-header"><h3 class="table-card-title">Activity</h3></div>
                        <div class="activity-feed" id="activityFeed"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<style>.spinner{width:34px;height:34px;border:3px solid #E5E7EB;border-top-color:#1a2a4a;border-radius:50%;animation:spin 0.9s linear infinite;margin:0 auto;}@keyframes spin{to{transform:rotate(360deg);}}</style>
<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/dashboard.js"></script>
</body>
</html>