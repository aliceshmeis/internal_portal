<?php
session_start();
if (!isset($_SESSION['user_id']))            { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] !== 'Asset Manager')   { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Asset Manager</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/asset-manager.css">
</head>
<body>
<div class="mobile-overlay" id="mobileOverlay"></div>
<div class="page-wrapper">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger-menu" id="hamburgerMenu">☰</button>
                <div class="breadcrumb">
                    <span class="breadcrumb-item active">Dashboard</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="header-user">
                    <div class="header-user-avatar"><?= strtoupper(substr($user_name, 0, 1)) ?></div>
                    <div class="header-user-info">
                        <div class="header-user-name"><?= htmlspecialchars($user_name) ?></div>
                        <div class="header-user-role"><?= htmlspecialchars($user_role) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <div class="page-hero">
                <h1>Asset Manager Dashboard</h1>
                <p>Stock, inventory and purchase orders overview</p>
            </div>

            <!-- Widgets -->
            <div class="widgets-row">
                <div class="widget-card red">
                    <div class="widget-value" id="widgetLowStock">—</div>
                    <div class="widget-label">Low Stock Items</div>
                </div>
                <div class="widget-card blue">
                    <div class="widget-value" id="widgetTotalStock">—</div>
                    <div class="widget-label">Total Stock Items</div>
                </div>
                <div class="widget-card green">
                    <div class="widget-value" id="widgetAssigned">—</div>
                    <div class="widget-label">Assets Assigned</div>
                </div>
                <div class="widget-card orange">
                    <div class="widget-value" id="widgetOpenPOs">—</div>
                    <div class="widget-label">Open POs</div>
                </div>
            </div>

            <!-- Two column -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div class="am-card">
                    <div class="am-card-header">
                        <div class="am-card-title">⚠ Low Stock Alerts</div>
                        <a href="/internal_portal/app/views/asset_manager/stock/index.php" class="btn-secondary btn-sm">View All</a>
                    </div>
                    <div id="lowStockAlerts"><div class="loading-state">Loading...</div></div>
                </div>
                <div class="am-card">
                    <div class="am-card-header">
                        <div class="am-card-title">🕐 Pending PO Approvals</div>
                        <a href="/internal_portal/app/views/asset_manager/po/index.php" class="btn-secondary btn-sm">View All</a>
                    </div>
                    <div id="pendingPOs"><div class="loading-state">Loading...</div></div>
                </div>
            </div>

            <!-- Quick actions -->
            <div style="display:flex;gap:12px;margin-top:20px;flex-wrap:wrap;">
                <a href="/internal_portal/app/views/asset_manager/stock/create.php"     class="btn-primary">+ Add Stock Item</a>
                <a href="/internal_portal/app/views/asset_manager/inventory/create.php" class="btn-primary">+ Add Asset</a>
                <a href="/internal_portal/app/views/asset_manager/po/create.php"        class="btn-primary">+ Create PO</a>
            </div>
        </div>
    </main>
</div>
<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/asset-manager.js"></script>
</body>
</html>