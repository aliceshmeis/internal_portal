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
    <title>Add Asset – Asset Manager</title>
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
                    <a href="index.php" class="breadcrumb-item">Inventory</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active">Add Asset</span>
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
                <h1>Add New Asset</h1>
                <p>Register a new asset in the inventory</p>
            </div>

            <div class="am-card" style="max-width:740px;">
                <form id="assetForm" onsubmit="event.preventDefault(); submitAssetForm()">
                    <div class="am-form">
                        <div class="form-grid">
                            <div class="form-section-title">Asset Identity</div>
                            <div class="form-group">
                                <label>Asset Name / Model <span style="color:#ef4444">*</span></label>
                                <input type="text" name="name" required placeholder="e.g. Dell XPS 15">
                            </div>
                            <div class="form-group">
                                <label>Category <span style="color:#ef4444">*</span></label>
                                <select name="category" required>
                                    <option value="">Select category</option>
                                    <option>Laptop</option>
                                    <option>Printer</option>
                                    <option>Network Equipment</option>
                                    <option>Furniture</option>
                                    <option>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Serial Number</label>
                                <input type="text" name="serial_number" placeholder="e.g. SN-2024-001">
                            </div>
                            <input type="hidden" name="status" value="Available">
                            <div class="form-group">
                                <label>Purchase Date</label>
                                <input type="date" name="purchase_date">
                            </div>
                            <div class="form-group">
                                <label>Purchase Cost ($)</label>
                                <input type="number" name="purchase_cost" min="0" step="0.01" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label>Warranty Expiry</label>
                                <input type="date" name="warranty_expiry">
                            </div>
                            <div class="form-group">
                                <label>Campus</label>
                                <select name="campus_id" id="campusSelect">
                                    <option value="">Loading...</option>
                                </select>
                            </div>

                            <div class="form-section-title">Location</div>
                            <div class="form-group">
                                <label>Building</label>
                                <input type="text" name="building" placeholder="e.g. Block A">
                            </div>
                            <div class="form-group">
                                <label>Floor</label>
                                <input type="text" name="floor" placeholder="e.g. 2">
                            </div>
                            <div class="form-group full">
                                <label>Room</label>
                                <input type="text" name="room" placeholder="e.g. 201">
                            </div>
                            <div class="form-group full">
                                <label>Description / Notes</label>
                                <textarea name="description" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Save Asset</button>
                        <a href="index.php" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/asset-manager.js"></script>
<script>
loadCampusOptions('campusSelect');
</script>
</body>
</html>