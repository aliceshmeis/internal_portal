<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
$is_admin  = ($user_role === 'Admin');
$can_add   = ($user_role === 'Admin' || $user_role === 'Asset Manager');
$initials  = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', trim($user_name)), 0, 2))));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assets – LIU Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/tickets.css">
    <link rel="stylesheet" href="/internal_portal/public/css/assets.css">
    <link rel="stylesheet" href="/internal_portal/public/css/create-asset-modal.css">
    <link rel="stylesheet" href="/internal_portal/public/css/assign-asset-modal.css">
    <style>
        .avatar-dropdown { position: relative; }
        .avatar-btn {
            width: 36px; height: 36px; border-radius: 9px;
            background: #4D9FE8; color: white;
            font-size: 12px; font-weight: 700; letter-spacing: 0.5px;
            border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.18s; font-family: inherit;
        }
        .avatar-btn:hover { background: #3A8ED6; box-shadow: 0 4px 12px rgba(77,159,232,0.28); }
        .avatar-menu {
            display: none; position: absolute;
            top: calc(100% + 10px); right: 0;
            background: #FFFFFF; border: 1px solid #E2E8F0;
            border-radius: 12px; box-shadow: 0 8px 24px rgba(15,23,42,0.12);
            min-width: 200px; z-index: 999; overflow: hidden;
            animation: dropIn 0.18s ease;
        }
        @keyframes dropIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
        .avatar-menu.open { display: block; }
        .avatar-menu-header { padding: 14px 16px; border-bottom: 1px solid #E2E8F0; background: #EEF6FD; }
        .avatar-menu-name { font-size: 13.5px; font-weight: 700; color: #0F172A; margin-bottom: 2px; }
        .avatar-menu-role { font-size: 11px; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
        .avatar-menu-item {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 16px; font-size: 13.5px; color: #64748B;
            text-decoration: none; font-weight: 500; transition: all 0.12s;
            cursor: pointer; border: none; background: none;
            width: 100%; text-align: left; font-family: inherit;
        }
        .avatar-menu-item:hover { background: #EEF6FD; color: #1E293B; }
        .avatar-menu-item.danger { color: #DC2626; }
        .avatar-menu-item.danger:hover { background: #FEF2F2; color: #B91C1C; }
        .avatar-menu-divider { height: 1px; background: #E2E8F0; margin: 4px 0; }
        .avatar-menu-icon { font-size: 14px; width: 18px; text-align: center; flex-shrink: 0; }
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
                <a href="../assets/list.php" class="sidebar-nav-item active"><span class="sidebar-nav-icon icon-assets"></span><span class="sidebar-nav-text">Assets</span></a>
            </div>
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-section-title">Inventory</div>
                <a href="../stock/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-stock"></span><span class="sidebar-nav-text">Stock</span></a>
                <a href="../purchase-orders/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-po"></span><span class="sidebar-nav-text">Purchase Orders</span></a>
            </div>
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-section-title">Procurement</div>
                <a href="../suppliers/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-users"></span><span class="sidebar-nav-text">Suppliers</span></a>
                <a href="../quotations/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-po"></span><span class="sidebar-nav-text">Quotations</span></a>
            </div>
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-section-title">Administration</div>
                <a href="../users/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-users"></span><span class="sidebar-nav-text">Users</span></a>
                <a href="../reports/index.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-reports"></span><span class="sidebar-nav-text">Reports</span></a>
            </div>
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
                    <span class="breadcrumb-item active">Assets</span>
                </div>
                <div class="topbar-search">
                    <input type="text" placeholder="Search tickets, assets, users...">
                </div>
            </div>
            <div class="topbar-right">
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
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;">
                <div>
                    <h1 style="font-size:20px;font-weight:700;color:#0F172A;letter-spacing:-0.3px;margin-bottom:3px;">Asset Inventory</h1>
                    <p style="color:#94A3B8;font-size:13px;">Track and manage company assets</p>
                </div>
                <?php if ($can_add): ?>
                <button class="btn-filter" onclick="openCreateAssetModal()">+ Add Asset</button>
                <?php endif; ?>
            </div>
            <div class="stats-row" id="stats-row"></div>
            <div class="filters-bar">
                <div class="filters-row">
                    <div class="filter-group">
                        <label class="filter-label">Search Assets</label>
                        <div class="search-input-wrapper">
                            <input type="text" class="search-input" id="search" placeholder="Search by name, tag, serial...">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Category</label>
                        <select class="filter-select" id="category-filter">
                            <option value="">All Categories</option>
                            <option value="Laptop">Laptop</option>
                            <option value="Printer">Printer</option>
                            <option value="Network Equipment">Network Equipment</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select class="filter-select" id="status-filter">
                            <option value="">All Statuses</option>
                            <option value="Available">Available</option>
                            <option value="In Use">In Use</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Retired">Retired</option>
                        </select>
                    </div>
                    <button class="btn-filter" onclick="applyFilters()">Apply</button>
                </div>
            </div>
            <div class="table-card">
                <div id="loading"><div class="loading-state"><div class="spinner"></div><p style="color:#94A3B8;font-size:13px;margin-top:12px;">Loading assets…</p></div></div>
                <div id="error" style="display:none;padding:24px;text-align:center;color:#B91C1C;"></div>
                <div id="assets-container" style="display:none;">
                    <div class="table-wrapper">
                        <table class="assets-table">
                            <thead><tr><th>Asset Tag</th><th>Name</th><th>Category</th><th>Status</th><th>Assigned To</th><th>Campus</th><th style="text-align:center;">Actions</th></tr></thead>
                            <tbody id="assets-tbody"></tbody>
                        </table>
                    </div>
                    <div class="pagination-wrapper">
                        <div class="pagination-info" id="pagination-info"></div>
                        <div class="pagination-controls" id="pagination-controls"></div>
                    </div>
                </div>
                <div id="empty-state" style="display:none;"><div class="empty-state"><span class="empty-icon">💼</span><h3 class="empty-title">No assets found</h3></div></div>
            </div>
        </div>
    </main>
</div>

<!-- ASSIGN MODAL -->
<div id="assignModal">
    <div class="asset-modal-overlay" onclick="closeAssignModal()"></div>
    <div class="asset-modal-container">
        <div class="asset-modal-header">
            <div><h2 class="asset-modal-title">Assign Asset</h2><p class="asset-modal-subtitle" id="assignAssetName"></p></div>
            <button class="asset-modal-close" type="button" onclick="closeAssignModal()"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>
        </div>
        <div class="asset-modal-body">
            <div class="asset-modal-error" id="assignModalError" style="display:none;"></div>
            <div class="asset-form-group"><label class="asset-form-label">Assign To <span style="color:#ef4444;">*</span></label><select class="asset-form-select" id="assignUserId"><option value="">Loading staff...</option></select></div>
            <div class="asset-form-group"><label class="asset-form-label">Expected Return Date <span class="optional">(optional)</span></label><input type="date" class="asset-form-input" id="assignReturnDate"></div>
        </div>
        <div class="asset-modal-footer"><button class="btn-asset-cancel" type="button" onclick="closeAssignModal()">Cancel</button><button class="btn-asset-confirm" type="button" onclick="submitAssign()">Assign Asset</button></div>
    </div>
</div>

<!-- RETURN MODAL -->
<div id="returnModal">
    <div class="asset-modal-overlay" onclick="closeReturnModal()"></div>
    <div class="asset-modal-container">
        <div class="asset-modal-header">
            <div><h2 class="asset-modal-title">Return Asset</h2><p class="asset-modal-subtitle">Confirm asset return</p></div>
            <button class="asset-modal-close" type="button" onclick="closeReturnModal()"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>
        </div>
        <div class="asset-modal-body">
            <div class="asset-modal-error" id="returnModalError" style="display:none;"></div>
            <div class="return-confirm-box"><strong>Are you sure you want to return this asset?</strong><br>"<span id="returnAssetName"></span>" will be marked as Available.</div>
        </div>
        <div class="asset-modal-footer"><button class="btn-asset-cancel" type="button" onclick="closeReturnModal()">Cancel</button><button class="btn-asset-confirm danger" type="button" onclick="submitReturn()">Confirm Return</button></div>
    </div>
</div>

<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/assets.js"></script>
<script src="/internal_portal/public/js/create-asset-modal.js"></script>
<script>
    const avatarBtn  = document.getElementById('avatarBtn');
    const avatarMenu = document.getElementById('avatarMenu');
    avatarBtn.addEventListener('click', e => { e.stopPropagation(); avatarMenu.classList.toggle('open'); });
    document.addEventListener('click', () => avatarMenu.classList.remove('open'));
    avatarMenu.addEventListener('click', e => e.stopPropagation());
</script>
</body>
</html>