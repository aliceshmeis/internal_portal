<?php
// app/views/asset_manager/partials/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir  = basename(dirname($_SERVER['PHP_SELF']));

function am_nav($href, $icon, $label, $active) {
    $cls = $active ? 'sidebar-nav-item active' : 'sidebar-nav-item';
    echo "<a href=\"{$href}\" class=\"{$cls}\">
        <span class=\"sidebar-nav-icon {$icon}\"></span>
        <span class=\"sidebar-nav-text\">{$label}</span>
    </a>";
}
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="/internal_portal/public/images/liulogo.png"   alt="LIU" style="height:36px;object-fit:contain;flex-shrink:0;">
        <img src="/internal_portal/public/images/Logo-Text.png" alt="LIU" style="height:22px;object-fit:contain;flex-shrink:1;max-width:140px;">
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-nav-section">
            <div class="sidebar-nav-section-title">Asset Manager</div>
            <?php am_nav('/internal_portal/app/views/asset_manager/dashboard.php',       'icon-dashboard', 'Dashboard',       $current_page === 'dashboard.php' && $current_dir === 'asset_manager'); ?>
            <?php am_nav('/internal_portal/app/views/asset_manager/stock/index.php',     'icon-stock',     'Stock',           $current_dir  === 'stock'); ?>
            <?php am_nav('/internal_portal/app/views/asset_manager/inventory/index.php', 'icon-assets',    'Inventory',       $current_dir  === 'inventory'); ?>
            <?php am_nav('/internal_portal/app/views/asset_manager/po/index.php',        'icon-po',        'Purchase Orders', $current_dir  === 'po'); ?>
            <?php am_nav('/internal_portal/app/views/asset_manager/reports.php',         'icon-reports',   'Reports',         $current_page === 'reports.php'); ?>
        </div>
    </nav>
    <div class="sidebar-footer">
        <a href="/internal_portal/app/views/auth/logout.php" class="sidebar-nav-item">
            <span class="sidebar-nav-icon icon-logout"></span>
            <span class="sidebar-nav-text">Logout</span>
        </a>
    </div>
</aside>