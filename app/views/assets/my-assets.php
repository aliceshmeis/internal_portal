<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] === 'Admin') { header('Location: /internal_portal/app/views/dashboard/dashboard.php'); exit; }
$user_name  = $_SESSION['name'];
$user_role  = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assets - Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/staff-dashboard.css">
    <style>
        .sidebar { transition: width 0.25s ease; }
        .sidebar-collapsed { width: 0 !important; overflow: hidden !important; padding: 0 !important; }
    </style>
</head>
<body>
<div class="mobile-overlay" id="mobileOverlay"></div>
<div class="page-wrapper">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="/internal_portal/public/images/liulogo.png" alt="LIU" style="height:36px;object-fit:contain;flex-shrink:0;">
            <img src="/internal_portal/public/images/Logo-Text.png" alt="LIU" style="height:22px;object-fit:contain;flex-shrink:0;">
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-section-title">Main</div>
                <a href="../dashboard/staff-dashboard.php" class="sidebar-nav-item">
                    <span class="sidebar-nav-icon icon-dashboard"></span>
                    <span class="sidebar-nav-text">Dashboard</span>
                </a>
                <a href="../tickets/my-tickets.php" class="sidebar-nav-item">
                    <span class="sidebar-nav-icon icon-tickets"></span>
                    <span class="sidebar-nav-text">My Tickets</span>
                </a>
                <a href="my-assets.php" class="sidebar-nav-item active">
                    <span class="sidebar-nav-icon icon-assets"></span>
                    <span class="sidebar-nav-text">My Assets</span>
                </a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <a href="/internal_portal/app/views/auth/logout.php" class="sidebar-nav-item">
                <span class="sidebar-nav-icon icon-logout"></span>
                <span class="sidebar-nav-text">Logout</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger-menu" id="hamburgerMenu">☰</button>
                <div class="breadcrumb">
                    <a href="../dashboard/staff-dashboard.php" class="breadcrumb-item">Home</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active">My Assets</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="header-user">
                    <div class="header-user-avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                    <div class="header-user-info">
                        <div class="header-user-name"><?php echo htmlspecialchars($user_name); ?></div>
                        <div class="header-user-role"><?php echo htmlspecialchars($user_role); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <h1 style="font-size:24px;font-weight:600;margin-bottom:6px;color:var(--color-text-primary);">My Assets</h1>
            <p style="color:var(--color-text-secondary);margin-bottom:28px;font-size:14px;">Assets currently assigned to you</p>

            <div class="section-card">
                <div id="assetsLoading" class="loading-small">Loading assets...</div>
                <div id="assetsEmpty" style="display:none;">
                    <div class="empty-clean">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                        <p>No assets assigned to you yet</p>
                    </div>
                </div>
                <div id="assetsTable" style="display:none;">
                    <div class="table-wrapper">
                        <table class="tickets-table-clean" style="margin:0;">
                            <thead>
                                <tr>
                                    <th style="padding:14px 20px;">Asset Tag</th>
                                    <th style="padding:14px 20px;">Name</th>
                                    <th style="padding:14px 20px;">Category</th>
                                    <th style="padding:14px 20px;">Status</th>
                                    <th style="padding:14px 20px;">Campus</th>
                                    <th style="padding:14px 20px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="assetsTbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script>
    const hamburger = document.getElementById('hamburgerMenu');
    const sidebar   = document.getElementById('sidebar');
    hamburger.addEventListener('click', () => sidebar.classList.toggle('sidebar-collapsed'));
    document.getElementById('mobileOverlay').addEventListener('click', () => sidebar.classList.remove('sidebar-collapsed'));
</script>
<script>
const API_BASE = '/internal_portal/api/v1';

document.addEventListener('DOMContentLoaded', loadMyAssets);

async function loadMyAssets() {
    try {
        const res    = await fetch(`${API_BASE}/staff/dashboard.php`, { credentials: 'include' });
        const result = await res.json();
        const assets = result.data?.my_assets || [];

        document.getElementById('assetsLoading').style.display = 'none';

        if (assets.length === 0) {
            document.getElementById('assetsEmpty').style.display = 'block';
            return;
        }

        document.getElementById('assetsTable').style.display = 'block';
        document.getElementById('assetsTbody').innerHTML = assets.map(asset => `
            <tr>
                <td style="padding:14px 20px;"><span class="asset-tag-clean">${escapeHtml(asset.asset_tag || 'N/A')}</span></td>
                <td style="padding:14px 20px;"><span class="asset-name-clean">${escapeHtml(asset.name)}</span></td>
                <td style="padding:14px 20px;"><span style="font-size:13px;color:var(--color-text-secondary);">${escapeHtml(asset.category || '—')}</span></td>
                <td style="padding:14px 20px;"><span class="asset-status-clean in-use">${escapeHtml(asset.status)}</span></td>
                <td style="padding:14px 20px;"><span style="font-size:13px;color:var(--color-text-secondary);">${escapeHtml(asset.campus_name || '—')}</span></td>
                <td style="padding:14px 20px;">
                    <button class="btn-report" onclick="reportIssue(${asset.id})">Report Issue</button>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        document.getElementById('assetsLoading').textContent = 'Failed to load assets.';
    }
}

function reportIssue(assetId) {
    window.location.href = `../tickets/create.php?asset_id=${assetId}`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
</body>
</html>