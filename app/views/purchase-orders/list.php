<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /internal_portal/app/views/auth/login.php');
    exit;
}

$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
$is_admin = ($user_role === 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Orders - Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/tickets.css">
    <link rel="stylesheet" href="/internal_portal/public/css/purchase-orders.css">
</head>
<body>
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <div class="page-wrapper">
        <!-- Professional Sidebar with Clean Icons -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="/internal_portal/public/images/liulogo.png" alt="LIU" style="height:36px;object-fit:contain;">                <div class="sidebar-title">Internal Portal</div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Main</div>
                    <a href="../dashboard/dashboard.php" class="sidebar-nav-item">
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
                    <a href="list.php" class="sidebar-nav-item active">
                        <span class="sidebar-nav-icon icon-po"></span>
                        <span class="sidebar-nav-text">Purchase Orders</span>
                    </a>
                </div>
                
                <?php if ($is_admin): ?>
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
                <?php endif; ?>
            </nav>
            
            <div class="sidebar-footer">
                <a href="/internal_portal/app/views/auth/logout.php" class="sidebar-nav-item">
                    <span class="sidebar-nav-icon icon-logout"></span>
                    <span class="sidebar-nav-text">Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <div class="topbar-left">
                    <button class="hamburger-menu" id="hamburgerMenu">☰</button>
                    <div class="breadcrumb">
                        <a href="../dashboard/dashboard.php" class="breadcrumb-item">Home</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Purchase Orders</span>
                    </div>
                </div>
                
                <div class="topbar-search">
                    <input type="text" placeholder="Search tickets, assets, users...">
                </div>
                
                <div class="topbar-right">
                    <button class="btn btn-primary" onclick="openCreatePOModal()">+ Create PO</button>
                    <button class="topbar-icon-btn" title="Notifications">
                        🔔
                        <span class="badge">3</span>
                    </button>
                    <div class="header-user">
                        <div class="header-user-avatar">
                            <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                        </div>
                        <div class="header-user-info">
                            <div class="header-user-name"><?php echo htmlspecialchars($user_name); ?></div>
                            <div class="header-user-role"><?php echo htmlspecialchars($user_role); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <div class="page-content">
                <div class="page-header">
                    <h1 class="page-title">Purchase Orders</h1>
                    <p class="page-subtitle">Manage procurement and vendor orders</p>
                </div>

                <!-- Stats Summary -->
                <div class="stats-summary" id="stats-summary">
                    <!-- Populated by JavaScript -->
                </div>

                <!-- Filters -->
                <div class="filters-bar">
                    <div class="filters-row">
                        <div class="filter-group">
                            <label class="filter-label">Search POs</label>
                            <div class="search-input-wrapper">
                                <input type="text" class="search-input" id="search" placeholder="Search by PO ID or vendor...">
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Status</label>
                            <select class="filter-select" id="status-filter">
                                <option value="">All Statuses</option>
                                <option value="Draft">Draft</option>
                                <option value="Pending">Pending Approval</option>
                                <option value="Approved">Approved</option>
                                <option value="Ordered">Ordered</option>
                                <option value="Received">Received</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Date Range</label>
                            <select class="filter-select" id="date-filter">
                                <option value="">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="quarter">This Quarter</option>
                            </select>
                        </div>
                        
                        <button class="btn-filter" onclick="applyFilters()">Apply</button>
                    </div>
                </div>

                <!-- PO Table -->
                <div class="table-card">
                    <div id="loading">
                        <div class="loading-state">
                            <div class="spinner"></div>
                            <p>Loading purchase orders...</p>
                        </div>
                    </div>
                    
                    <div id="error" style="display: none; padding: 24px; text-align: center; color: var(--color-danger);"></div>
                    
                    <div id="po-container" style="display: none;">
                        <div class="table-wrapper">
                            <table class="po-table">
                                <thead>
                                    <tr>
                                        <th>PO ID</th>
                                        <th>Vendor</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Date</th>
                                        <th style="text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="po-tbody"></tbody>
                            </table>
                        </div>
                        
                        <div class="pagination-wrapper">
                            <div class="pagination-info" id="pagination-info"></div>
                            <div class="pagination-controls" id="pagination-controls"></div>
                        </div>
                    </div>
                    
                    <div id="empty-state" style="display: none;">
                        <div class="empty-state">
                            <div class="empty-icon">📦</div>
                            <h3 class="empty-title">No purchase orders found</h3>
                            <p class="empty-text">Try adjusting your filters or create a new PO to get started.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/internal_portal/public/js/mobile-menu.js"></script>
    <script src="/internal_portal/public/js/purchase-orders.js"></script>
</body>
</html>