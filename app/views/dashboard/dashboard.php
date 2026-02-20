<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /internal_portal/app/views/auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'Admin') {
    header('Location: /internal_portal/app/views/dashboard/staff-dashboard.php');
    exit;
}

$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Internal Portal</title>
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
                <img src="/internal_portal/public/images/liulogo.png" alt="LIU" style="height:36px;object-fit:contain;">
            </div>
            <nav class="sidebar-nav">
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Main</div>
                    <a href="dashboard.php" class="sidebar-nav-item active">
                        <span class="sidebar-nav-icon icon-dashboard"></span>
                        <span class="sidebar-nav-text">Dashboard</span>
                    </a>
                    <a href="../tickets/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-tickets"></span>
                        <span class="sidebar-nav-text">Tickets</span>
                    </a>
                    <a href="../assets/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-assets"></span>
                        <span class="sidebar-nav-text">Assets</span>
                    </a>
                </div>
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Inventory</div>
                    <a href="../stock/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-stock"></span>
                        <span class="sidebar-nav-text">Stock</span>
                    </a>
                    <a href="../purchase-orders/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-po"></span>
                        <span class="sidebar-nav-text">Purchase Orders</span>
                    </a>
                </div>
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Settings</div>
                    <a href="../users/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-users"></span>
                        <span class="sidebar-nav-text">Users</span>
                    </a>
                    <a href="../reports/index.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-reports"></span>
                        <span class="sidebar-nav-text">Reports</span>
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
                        <a href="dashboard.php" class="breadcrumb-item">Home</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Dashboard</span>
                    </div>
                </div>
                <div class="topbar-search">
                    <input type="text" placeholder="Search...">
                </div>
                <div class="topbar-right">
                    <button class="topbar-icon-btn" title="Notifications">
                        🔔
                        <span class="badge">3</span>
                    </button>
                    <button class="topbar-icon-btn" title="Settings">⚙</button>
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
                <h1 style="font-size:24px;font-weight:600;margin-bottom:8px;color:var(--color-text-primary);">
                    Welcome back, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>
                </h1>
                <p style="color:var(--color-text-secondary);margin-bottom:32px;font-size:14px;">
                    Here's what's happening with your portal today
                </p>

                <div id="loading" style="text-align:center;padding:60px;">
                    <div style="width:40px;height:40px;border:3px solid var(--color-border-light);border-top-color:var(--color-primary);border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 16px;"></div>
                    <p style="color:var(--color-text-secondary);font-size:14px;">Loading dashboard...</p>
                </div>

                <div id="dashboardContent" style="display:none;">

                    <!-- KPI Cards -->
                    <div class="kpi-grid" id="kpiCards"></div>

                    <!-- Charts Row -->
                    <div class="charts-grid">
                        <!-- Donut Chart -->
                        <div class="chart-card">
                            <div class="chart-header">
                                <div>
                                    <div class="chart-title">Tickets by Status</div>
                                    <div class="chart-subtitle">Current distribution</div>
                                </div>
                            </div>
                            <div class="chart-body donut-wrapper">
                                <div class="donut-container">
                                    <canvas id="donutChart"></canvas>
                                    <div class="donut-center" id="donutCenter">
                                        <div class="donut-total-label">Total</div>
                                        <div class="donut-total-value" id="donutTotal">0</div>
                                    </div>
                                </div>
                                <div class="donut-legend" id="donutLegend"></div>
                            </div>
                        </div>

                        <!-- Line Chart -->
                        <div class="chart-card">
                            <div class="chart-header">
                                <div>
                                    <div class="chart-title">Activity Trend</div>
                                    <div class="chart-subtitle">Last 7 days</div>
                                </div>
                            </div>
                            <div class="chart-body">
                                <canvas id="lineChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Tables Row -->
                    <div class="tables-grid">
                        <div class="table-card">
                            <div class="table-card-header">
                                <h3 class="table-card-title">Recent Tickets</h3>
                                <a href="../tickets/list.php" class="table-card-action">View All →</a>
                            </div>
                            <div class="table-wrapper">
                                <table class="table" style="margin:0;">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentTickets"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="table-card">
                            <div class="table-card-header">
                                <h3 class="table-card-title">Activity</h3>
                            </div>
                            <div class="activity-feed" id="activityFeed"></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/internal_portal/public/js/mobile-menu.js"></script>
    <script src="/internal_portal/public/js/dashboard.js"></script>

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</body>
</html>