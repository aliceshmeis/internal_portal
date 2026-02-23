<?php
session_start();
if (!isset($_SESSION['user_id']))           { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if (!in_array($_SESSION['role'], ['Admin', 'Asset Manager']))  { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create PO – Asset Manager</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/asset-manager.css">
    <style>
        .po-item-card { border:1px solid #e5e7eb; border-radius:10px; padding:16px; margin-bottom:12px; }
        .po-item-card.stock-card { border-left:4px solid #16a34a; background:#f0fdf4; }
        .po-item-card.asset-card { border-left:4px solid #2563eb; background:#eff6ff; }
        .po-item-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
        .po-item-type-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.6px; }
        .stock-card .po-item-type-label { color:#16a34a; }
        .asset-card .po-item-type-label { color:#2563eb; }
        .po-item-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
        .po-item-grid .full { grid-column:1/-1; }
        .po-item-grid label { font-size:11.5px; font-weight:600; color:#374151; display:block; margin-bottom:3px; }
        .po-item-grid input, .po-item-grid select {
            width:100%; padding:7px 10px; border:1px solid #d1d5db;
            border-radius:6px; font-size:13px; font-family:inherit;
            background:#fff; outline:none; box-sizing:border-box;
        }
        .po-item-grid input:focus, .po-item-grid select:focus { border-color:#1a2a4a; }
        .btn-remove { background:none; border:1.5px solid #fecaca; color:#dc2626; border-radius:6px; padding:4px 10px; cursor:pointer; font-size:12px; font-weight:600; }
        .btn-remove:hover { background:#fef2f2; }
    </style>
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
                    <a href="index.php" class="breadcrumb-item">Purchase Orders</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active">Create PO</span>
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
                <h1>Create Purchase Order</h1>
                <p>Fill in supplier details then add stock or asset items</p>
            </div>

            <!-- PO Header -->
            <div class="am-card" style="margin-bottom:20px;">
                <div class="am-card-header"><div class="am-card-title">PO Details</div></div>
                <div class="am-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Supplier Name <span style="color:#ef4444">*</span></label>
                            <input type="text" id="poSupplier" placeholder="e.g. TechSource Lebanon">
                        </div>
                        <div class="form-group">
                            <label>Requested By</label>
                            <input type="text" value="<?= htmlspecialchars($user_name) ?>" readonly style="background:#f3f4f6;color:#6b7280;">
                        </div>
                        <div class="form-group">
                            <label>Campus</label>
                            <select id="poCampus"><option value="">Loading...</option></select>
                        </div>
                        <div class="form-group full">
                            <label>Notes</label>
                            <textarea id="poNotes" placeholder="Optional notes for this PO..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PO Items -->
            <div class="am-card">
                <div class="am-card-header">
                    <div class="am-card-title">PO Items</div>
                    <div style="display:flex;gap:8px;">
                        <button class="btn-secondary" onclick="addPOItem('stock')" style="border-color:#16a34a;color:#16a34a;">+ Stock Item</button>
                        <button class="btn-secondary" onclick="addPOItem('asset')" style="border-color:#2563eb;color:#2563eb;">+ Asset Item</button>
                    </div>
                </div>

                <div style="padding:16px;" id="poItemsContainer">
                    <div id="noItemsMsg" style="text-align:center;padding:32px;color:#9ca3af;font-size:13.5px;">
                        No items yet — click "Stock Item" or "Asset Item" to add
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;padding:0 20px 16px;gap:12px;align-items:center;">
                    <span style="font-size:13px;font-weight:700;color:#6b7280;text-transform:uppercase;">Grand Total:</span>
                    <span id="poGrandTotal" style="font-size:20px;font-weight:700;color:#1a2a4a;">$0.00</span>
                </div>

                <div class="form-actions">
                    <button class="btn-primary" onclick="submitPOForm('draft')">Save as Draft</button>
                    <button class="btn-primary" onclick="submitPOForm('submit')" style="background:#16a34a;">Submit for Approval</button>
                    <a href="index.php" class="btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/asset-manager.js"></script>
<script>
loadCampusOptions('poCampus');
loadStockOptions();
</script>
</body>
</html>