<?php
session_start();
if (!isset($_SESSION['user_id']))                                          { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if (!in_array($_SESSION['role'], ['Admin', 'Asset Manager']))              { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
$asset_id  = intval($_GET['id'] ?? 0);
if (!$asset_id)                                                            { header('Location: index.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Asset – Asset Manager</title>
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
                    <span class="breadcrumb-item active">Edit Asset</span>
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
                <h1>Edit Asset</h1>
                <p>Update physical details, location and status</p>
            </div>

            <div class="am-card" style="max-width:740px;">
                <div class="am-card-header">
                    <div class="am-card-title" id="assetTagLabel">Loading...</div>
                    <div id="assetStatusLabel"></div>
                </div>

                <form id="assetEditForm" onsubmit="event.preventDefault(); submitAssetEdit()">
                    <div class="am-form">
                        <div class="form-grid">
                            <input type="hidden" id="eId" value="<?= $asset_id ?>">

                            <!-- Identity -->
                            <div class="form-section-title">Asset Identity</div>
                            <div class="form-group">
                                <label>Asset Name / Model <span style="color:#ef4444">*</span></label>
                                <input type="text" id="eName" required>
                            </div>
                            <div class="form-group">
                                <label>Category <span style="color:#ef4444">*</span></label>
                                <select id="eCategory" required>
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
                                <input type="text" id="eSerial" placeholder="e.g. SN-2024-001">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select id="eStatus">
                                    <option value="Available">Available</option>
                                    <option value="Maintenance">Maintenance</option>
                                    <option value="Retired">Retired</option>
                                </select>
                            </div>

                            <!-- Purchase Info -->
                            <div class="form-section-title">Purchase Information</div>
                            <div class="form-group">
                                <label>Purchase Date</label>
                                <input type="date" id="ePurchaseDate">
                            </div>
                            <div class="form-group">
                                <label>Purchase Cost ($)</label>
                                <input type="number" id="ePurchaseCost" min="0" step="0.01" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label>Warranty Expiry</label>
                                <input type="date" id="eWarrantyExpiry">
                            </div>
                            <div class="form-group">
                                <label>Expected Return Date</label>
                                <input type="date" id="eExpectedReturn">
                            </div>

                            <!-- Location -->
                            <div class="form-section-title">Location</div>
                            <div class="form-group">
                                <label>Campus</label>
                                <select id="eCampus">
                                    <option value="">Loading...</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Building</label>
                                <input type="text" id="eBuilding" placeholder="e.g. Block A">
                            </div>
                            <div class="form-group">
                                <label>Floor</label>
                                <input type="text" id="eFloor" placeholder="e.g. 2">
                            </div>
                            <div class="form-group">
                                <label>Room</label>
                                <input type="text" id="eRoom" placeholder="e.g. 201">
                            </div>

                            <!-- Notes -->
                            <div class="form-section-title">Notes</div>
                            <div class="form-group full">
                                <label>Description / Notes</label>
                                <textarea id="eDescription" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Save Changes</button>
                        <a href="view.php?id=<?= $asset_id ?>" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/asset-manager.js"></script>
<script>
const EDIT_ASSET_ID = <?= $asset_id ?>;

async function loadAssetForEdit() {
    const res    = await fetch(`/internal_portal/api/v1/assets/show.php?id=${EDIT_ASSET_ID}`, { credentials: 'include' });
    const result = await res.json();
    const a      = result.data;
    if (!a) { amToast('Asset not found', 'error'); return; }

    document.getElementById('assetTagLabel').textContent  = a.asset_tag + ' — ' + a.name;
    document.getElementById('assetStatusLabel').innerHTML = assetStatusBadge(a.status);

    document.getElementById('eName').value            = a.name             || '';
    document.getElementById('eCategory').value        = a.category         || '';
    document.getElementById('eSerial').value          = a.serial_number    || '';
    document.getElementById('eStatus').value          = a.status           || 'Available';
    document.getElementById('ePurchaseDate').value    = a.purchase_date    ? a.purchase_date.substring(0,10) : '';
    document.getElementById('ePurchaseCost').value    = a.purchase_cost    || '';
    document.getElementById('eWarrantyExpiry').value  = a.warranty_expiry  ? a.warranty_expiry.substring(0,10) : '';
    document.getElementById('eExpectedReturn').value  = a.expected_return_date ? a.expected_return_date.substring(0,10) : '';
    document.getElementById('eBuilding').value        = a.building         || '';
    document.getElementById('eFloor').value           = a.floor            || '';
    document.getElementById('eRoom').value            = a.room             || '';
    document.getElementById('eDescription').value     = a.description      || '';

    // Load campuses then set value
    await loadCampusOptions('eCampus');
    document.getElementById('eCampus').value = a.campus_id || '';
}

async function submitAssetEdit() {
    const data = {
        id:                   EDIT_ASSET_ID,
        name:                 document.getElementById('eName').value.trim(),
        category:             document.getElementById('eCategory').value,
        serial_number:        document.getElementById('eSerial').value.trim()         || null,
        status:               document.getElementById('eStatus').value,
        purchase_date:        document.getElementById('ePurchaseDate').value          || null,
        purchase_cost:        document.getElementById('ePurchaseCost').value          || null,
        warranty_expiry:      document.getElementById('eWarrantyExpiry').value        || null,
        expected_return_date: document.getElementById('eExpectedReturn').value        || null,
        campus_id:            document.getElementById('eCampus').value                || null,
        building:             document.getElementById('eBuilding').value.trim()       || null,
        floor:                document.getElementById('eFloor').value.trim()          || null,
        room:                 document.getElementById('eRoom').value.trim()           || null,
        description:          document.getElementById('eDescription').value.trim()    || null,
    };

    if (!data.name)     { amToast('Asset name is required', 'error');     return; }
    if (!data.category) { amToast('Category is required', 'error');       return; }

    try {
        const res    = await fetch(`/internal_portal/api/v1/assets/update.php`, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            amToast('Asset updated successfully!');
            setTimeout(() => window.location.href = `view.php?id=${EDIT_ASSET_ID}`, 1200);
        } else {
            amToast(result.message || 'Error updating asset', 'error');
        }
    } catch(e) { amToast('Request failed', 'error'); }
}

loadAssetForEdit();
</script>
</body>
</html>