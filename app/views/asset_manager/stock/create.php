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
    <title>Add Stock Item – Asset Manager</title>
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
                    <a href="index.php" class="breadcrumb-item">Stock</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active">Add Item</span>
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
                <h1>Add Stock Item</h1>
                <p>Add a new item to the stock catalog</p>
            </div>

            <div class="am-card" style="max-width:680px;">
                <form id="stockCreateForm" onsubmit="event.preventDefault(); submitStockForm('stockCreateForm', false)">
                    <div class="am-form">
                        <div class="form-grid">
                            <div class="form-section-title">Basic Information</div>
                            <div class="form-group">
                                <label>Item Name <span style="color:#ef4444">*</span></label>
                                <input type="text" name="item_name" required placeholder="e.g. HP LaserJet Toner">
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category">
                                    <option value="">Select category</option>
                                    <option>Laptop</option>
                                    <option>Printer</option>
                                    <option>Network Equipment</option>
                                    <option>Furniture</option>
                                    <option>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Unit</label>
                                <select name="unit">
                                    <option value="units">Units</option>
                                    <option value="pcs">Pieces (pcs)</option>
                                    <option value="box">Box</option>
                                    <option value="pack">Pack</option>
                                    <option value="roll">Roll</option>
                                    <option value="kg">Kg</option>
                                    <option value="litre">Litre</option>
                                    <option value="bottle">Bottle</option>
                                    <option value="set">Set</option>
                                    <option value="pair">Pair</option>
                                </select>
                            </div>

                            <div class="form-section-title">Stock Rules</div>
                            <div class="form-group">
                                <label>Current Quantity <span style="color:#ef4444">*</span></label>
                                <input type="number" name="quantity" min="0" required placeholder="0">
                            </div>
                            <div class="form-group">
                                <label>Minimum Threshold <span style="color:#ef4444">*</span></label>
                                <input type="number" name="minimum_threshold" min="0" required placeholder="10">
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Save Stock Item</button>
                        <a href="index.php" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/asset-manager.js"></script>
</body>
</html>