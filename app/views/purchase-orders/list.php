<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
$is_admin  = ($user_role === 'Admin');
$initials  = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', trim($user_name)), 0, 2))));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Orders – Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/tickets.css">
    <link rel="stylesheet" href="/internal_portal/public/css/purchase-orders.css">
    <style>
        .avatar-dropdown { position: relative; }
        .avatar-btn { width:36px;height:36px;border-radius:9px;background:#4D9FE8;color:white;font-size:12px;font-weight:700;letter-spacing:0.5px;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.18s;font-family:inherit; }
        .avatar-btn:hover { background:#3A8ED6;box-shadow:0 4px 12px rgba(77,159,232,0.28); }
        .avatar-menu { display:none;position:absolute;top:calc(100% + 10px);right:0;background:#FFFFFF;border:1px solid #E2E8F0;border-radius:12px;box-shadow:0 8px 24px rgba(15,23,42,0.12);min-width:200px;z-index:999;overflow:hidden;animation:dropIn 0.18s ease; }
        @keyframes dropIn { from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)} }
        .avatar-menu.open { display:block; }
        .avatar-menu-header { padding:14px 16px;border-bottom:1px solid #E2E8F0;background:#EEF6FD; }
        .avatar-menu-name { font-size:13.5px;font-weight:700;color:#0F172A;margin-bottom:2px; }
        .avatar-menu-role { font-size:11px;color:#94A3B8;text-transform:uppercase;letter-spacing:0.5px;font-weight:600; }
        .avatar-menu-item { display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:13.5px;color:#64748B;text-decoration:none;font-weight:500;transition:all 0.12s;cursor:pointer;border:none;background:none;width:100%;text-align:left;font-family:inherit; }
        .avatar-menu-item:hover { background:#EEF6FD;color:#1E293B; }
        .avatar-menu-item.danger { color:#DC2626; }
        .avatar-menu-item.danger:hover { background:#FEF2F2;color:#B91C1C; }
        .avatar-menu-divider { height:1px;background:#E2E8F0;margin:4px 0; }
        .avatar-menu-icon { font-size:14px;width:18px;text-align:center;flex-shrink:0; }
    </style>
</head>
<body>
<div class="mobile-overlay" id="mobileOverlay"></div>
<div class="page-wrapper">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="/internal_portal/public/images/liulogo.png" alt="LIU" style="height:36px;object-fit:contain;">
            <div class="sidebar-title">Internal Portal</div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-section-title">Main</div>
                <a href="../dashboard/dashboard.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-dashboard"></span><span class="sidebar-nav-text">Dashboard</span></a>
                <a href="../tickets/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-tickets"></span><span class="sidebar-nav-text">Tickets</span></a>
                <a href="../assets/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-assets"></span><span class="sidebar-nav-text">Assets</span></a>
            </div>
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-section-title">Inventory</div>
                <a href="../stock/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-stock"></span><span class="sidebar-nav-text">Stock</span></a>
                <a href="../purchase-orders/list.php" class="sidebar-nav-item active"><span class="sidebar-nav-icon icon-po"></span><span class="sidebar-nav-text">Purchase Orders</span></a>
            </div>
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-section-title">Procurement</div>
                <a href="../suppliers/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-users"></span><span class="sidebar-nav-text">Suppliers</span></a>
                <a href="../quotations/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-po"></span><span class="sidebar-nav-text">Quotations</span></a>
            </div>
            <?php if ($is_admin): ?>
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-section-title">Administration</div>
                <a href="../users/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-users"></span><span class="sidebar-nav-text">Users</span></a>
                <a href="../reports/index.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-reports"></span><span class="sidebar-nav-text">Reports</span></a>
            </div>
            <?php endif; ?>
        </nav>
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
                <button class="hamburger-menu" id="hamburgerMenu">☰</button>
                <div class="breadcrumb">
                    <a href="../dashboard/dashboard.php" class="breadcrumb-item">Home</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active">Purchase Orders</span>
                </div>
                <div class="topbar-search"><input type="text" placeholder="Search tickets, assets, users..."></div>
            </div>
            <div class="topbar-right">
                <?php if ($is_admin): ?>
                <button class="btn btn-primary" onclick="openCreatePOModal()">+ New PO</button>
                <?php endif; ?>
                <div class="header-user">
                    <div class="header-user-info">
                        <div class="header-user-name"><?= htmlspecialchars($user_name) ?></div>
                        <div class="header-user-role"><?= htmlspecialchars($user_role) ?></div>
                    </div>
                    <div class="avatar-dropdown" id="avatarDropdown">
                        <button class="avatar-btn" id="avatarBtn"><?= $initials ?></button>
                        <div class="avatar-menu" id="avatarMenu">
                            <div class="avatar-menu-header">
                                <div class="avatar-menu-name"><?= htmlspecialchars($user_name) ?></div>
                                <div class="avatar-menu-role"><?= htmlspecialchars($user_role) ?></div>
                            </div>
                            <a href="../profile/index.php" class="avatar-menu-item"><span class="avatar-menu-icon">👤</span> My Profile</a>
                            <a href="../settings/index.php" class="avatar-menu-item"><span class="avatar-menu-icon">⚙️</span> Settings</a>
                            <div class="avatar-menu-divider"></div>
                            <a href="/internal_portal/app/views/auth/logout.php" class="avatar-menu-item danger"><span class="avatar-menu-icon">⏻</span> Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <div class="page-header">
                <h1 class="page-title">Purchase Orders</h1>
                <p class="page-subtitle">Manage procurement and vendor orders</p>
            </div>

            <div class="stats-summary" id="stats-summary"></div>

            <div class="filters-bar">
                <div class="filters-row">
                    <div class="filter-group">
                        <label class="filter-label">Search POs</label>
                        <div class="search-input-wrapper"><input type="text" class="search-input" id="search" placeholder="Search by PO ID or vendor..."></div>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select class="filter-select" id="status-filter">
                            <option value="">All Statuses</option>
                            <option value="Draft">Draft</option>
                            <option value="Pending Approval">Pending Approval</option>
                            <option value="Approved">Approved</option>
                            <option value="Completed">Completed</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Cancelled">Cancelled</option>
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

            <div class="table-card">
                <div id="loading"><div class="loading-state"><div class="spinner"></div><p>Loading purchase orders...</p></div></div>
                <div id="error" style="display:none;padding:24px;text-align:center;color:var(--color-danger);"></div>
                <div id="po-container" style="display:none;">
                    <div class="table-wrapper">
                        <table class="po-table">
                            <thead><tr><th>PO ID</th><th>Vendor</th><th>Total</th><th>Status</th><th>Created By</th><th>Date</th><th style="text-align:center;">Actions</th></tr></thead>
                            <tbody id="po-tbody"></tbody>
                        </table>
                    </div>
                    <div class="pagination-wrapper">
                        <div class="pagination-info" id="pagination-info"></div>
                        <div class="pagination-controls" id="pagination-controls"></div>
                    </div>
                </div>
                <div id="empty-state" style="display:none;">
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
<script>
    const avatarBtn  = document.getElementById('avatarBtn');
    const avatarMenu = document.getElementById('avatarMenu');
    avatarBtn.addEventListener('click', e => { e.stopPropagation(); avatarMenu.classList.toggle('open'); });
    document.addEventListener('click', () => avatarMenu.classList.remove('open'));
    avatarMenu.addEventListener('click', e => e.stopPropagation());
</script>
</body>
</html>