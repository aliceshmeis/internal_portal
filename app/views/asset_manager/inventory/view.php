<?php
session_start();
if (!isset($_SESSION['user_id']))           { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] !== 'Asset Manager')  { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
$asset_id  = intval($_GET['id'] ?? 0);
if (!$asset_id) { header('Location: index.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Details – Asset Manager</title>
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
                    <span class="breadcrumb-item active">Asset Details</span>
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
                <h1>Asset Details</h1>
                <p>Full asset information, assignment and history</p>
            </div>

            <div class="detail-grid">
                <!-- LEFT -->
                <div>
                    <div class="am-card" style="margin-bottom:20px;">
                        <div class="am-card-header">
                            <div class="am-card-title">Asset Information</div>
                            <div id="assetStatusBadge"></div>
                        </div>
                        <div style="padding:20px;" id="assetDetails">
                            <div class="loading-state">Loading...</div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Actions -->
                <div>
                    <div class="am-card">
                        <div class="am-card-header"><div class="am-card-title">Actions</div></div>
                        <div style="padding:16px;display:flex;flex-direction:column;gap:10px;" id="assetActions">
                            <div class="loading-state">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/asset-manager.js"></script>
<script>
const ASSET_ID = <?= $asset_id ?>;

async function loadAssetDetail() {
    const res    = await fetch(`/internal_portal/api/v1/assets/show.php?id=${ASSET_ID}`, { credentials: 'include' });
    const result = await res.json();
    const a      = result.data;
    if (!a) return;

    document.getElementById('assetStatusBadge').innerHTML = assetStatusBadge(a.status);
    document.getElementById('assetDetails').innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div class="detail-field"><label>Asset Tag</label><span>${amEscape(a.asset_tag)}</span></div>
            <div class="detail-field"><label>Category</label><span>${amEscape(a.category)}</span></div>
            <div class="detail-field"><label>Name / Model</label><span>${amEscape(a.name)}</span></div>
            <div class="detail-field"><label>Serial Number</label><span>${amEscape(a.serial_number)}</span></div>
            <div class="detail-field"><label>Purchase Date</label><span>${amDate(a.purchase_date)}</span></div>
            <div class="detail-field"><label>Purchase Cost</label><span>${amCurrency(a.purchase_cost)}</span></div>
            <div class="detail-field"><label>Warranty Expiry</label><span>${amDate(a.warranty_expiry)}</span></div>
            <div class="detail-field"><label>Campus</label><span>${amEscape(a.campus_name)}</span></div>
            <div class="detail-field"><label>Assigned To</label><span>${amEscape(a.assigned_user_name)}</span></div>
            <div class="detail-field"><label>Location</label><span>${[a.building, a.floor, a.room].filter(Boolean).join(', ') || '—'}</span></div>
            <div class="detail-field full" style="grid-column:1/-1"><label>Description</label><span>${amEscape(a.description)}</span></div>
        </div>`;

    // Actions
    let actions = `<a href="index.php" class="btn-secondary" style="width:100%;justify-content:center;">← Back to Inventory</a>`;
    if (a.status === 'Available') {
        actions = `<a href="assign.php?id=${ASSET_ID}" class="btn-primary" style="width:100%;justify-content:center;">Assign to Employee</a>` + actions;
    } else if (a.assigned_to) {
        actions = `<button class="btn-secondary" style="width:100%;justify-content:center;" onclick="returnAsset(${ASSET_ID})">↩ Return Asset</button>` + actions;
    }
    document.getElementById('assetActions').innerHTML = actions;
}

loadAssetDetail();
</script>
</body>
</html>