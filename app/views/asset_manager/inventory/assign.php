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
    <title>Assign Asset – Asset Manager</title>
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
                    <span class="breadcrumb-item active">Assign Asset</span>
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
                <h1>Assign Asset</h1>
                <p>Assign this asset to an employee</p>
            </div>

            <div class="am-card" style="max-width:520px;">
                <div class="am-card-header">
                    <div class="am-card-title" id="assetLabel">Loading asset...</div>
                </div>
                <div class="am-form">
                    <div class="form-grid single">
                        <input type="hidden" id="assignAssetId" value="<?= $asset_id ?>">
                        <div class="form-group">
                            <label>Assign To (Employee) <span style="color:#ef4444">*</span></label>
                            <select id="assignUserId">
                                <option value="">Loading employees...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Notes (optional)</label>
                            <textarea id="assignNotes" placeholder="Reason for assignment, condition notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn-primary" onclick="submitAssignForm()">Confirm Assignment</button>
                    <a href="view.php?id=<?= $asset_id ?>" class="btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/asset-manager.js"></script>
<script>
// Load asset label + users
(async () => {
    const res    = await fetch(`/internal_portal/api/v1/assets/show.php?id=<?= $asset_id ?>`, { credentials: 'include' });
    const result = await res.json();
    const a      = result.data;
    if (a) document.getElementById('assetLabel').textContent = `${a.asset_tag} — ${a.name}`;
})();
loadUserOptions('assignUserId');
</script>
</body>
</html>