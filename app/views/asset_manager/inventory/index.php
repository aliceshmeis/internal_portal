<?php
session_start();
if (!isset($_SESSION['user_id']))           { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] !== 'Asset Manager')  { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory – Asset Manager</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/asset-manager.css">
</head>
<body>
<div class="mobile-overlay" id="mobileOverlay"></div>
<div class="page-wrapper">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger-menu" id="hamburgerMenu">☰</button>
                <div class="breadcrumb">
                    <a href="../dashboard.php" class="breadcrumb-item">Dashboard</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active">Inventory</span>
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
                <h1>Inventory Management</h1>
                <p>Track assets, assignments and lifecycle</p>
            </div>

            <div class="am-card">
                <div class="controls-row">
                    <input type="text" id="invSearch" placeholder="Search by tag, serial, name or employee...">
                    <select id="invStatusFilter">
                        <option value="">All Statuses</option>
                        <option>Available</option>
                        <option>In Use</option>
                        <option>Maintenance</option>
                        <option>Retired</option>
                    </select>
                    <a href="create.php" class="btn-primary">+ Add Asset</a>
                </div>
                <table class="am-table">
                    <thead>
                        <tr>
                            <th>Asset Tag</th>
                            <th>Category</th>
                            <th>Name / Model</th>
                            <th>Serial Number</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Campus</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="inventoryTbody">
                        <tr><td colspan="8"><div class="loading-state">Loading assets...</div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/asset-manager.js"></script>
</body>
</html>