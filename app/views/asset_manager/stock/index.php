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
    <title>Stock – Asset Manager</title>
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
                    <span class="breadcrumb-item active">Stock</span>
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
                <h1>Stock Management</h1>
                <p>Manage stock items, quantities and minimum thresholds</p>
            </div>

            <div class="am-card">
                <div class="controls-row">
                    <input type="text" id="stockSearch" placeholder="Search by name or SKU...">
                    <select id="stockCatFilter">
                        <option value="">All Categories</option>
                        <option>Laptop</option>
                        <option>Printer</option>
                        <option>Network Equipment</option>
                        <option>Furniture</option>
                        <option>Other</option>
                    </select>
                    <a href="create.php" class="btn-primary">+ Add Stock Item</a>
                </div>
                <table class="am-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Current Qty</th>
                            <th>Min Threshold</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="stockTbody">
                        <tr><td colspan="7"><div class="loading-state">Loading stock items...</div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Adjust Qty Modal -->
<div class="modal-overlay" id="adjustModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Adjust Quantity</div>
            <button class="modal-close" onclick="closeModal('adjustModal')">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="adjustStockId">
            <p style="font-size:13.5px;margin-bottom:16px;">
                Item: <strong id="adjustItemName"></strong> &nbsp;|&nbsp; Current Qty: <strong id="adjustCurrentQty"></strong>
            </p>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <div class="form-group">
                    <label>Action</label>
                    <select id="adjustType">
                        <option value="increase">Increase</option>
                        <option value="decrease">Decrease</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" id="adjustQty" min="1" placeholder="Enter quantity">
                </div>
                <div class="form-group">
                    <label>Notes (optional)</label>
                    <input type="text" id="adjustNotes" placeholder="Reason for adjustment">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('adjustModal')">Cancel</button>
            <button class="btn-primary" onclick="submitAdjust()">Save</button>
        </div>
    </div>
</div>

<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/asset-manager.js"></script>
</body>
</html>