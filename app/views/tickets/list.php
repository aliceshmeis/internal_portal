<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
$is_admin  = ($user_role === 'Admin');
$initials  = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', trim($user_name)), 0, 2))));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets – LIU Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/tickets.css">
    <link rel="stylesheet" href="/internal_portal/public/css/create-ticket-modal.css">
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
                <a href="../tickets/list.php" class="sidebar-nav-item active"><span class="sidebar-nav-icon icon-tickets"></span><span class="sidebar-nav-text">Tickets</span></a>
                <a href="../assets/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-assets"></span><span class="sidebar-nav-text">Assets</span></a>
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
                    <span class="breadcrumb-item active">Tickets</span>
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
            <div class="page-header">
                <div class="page-header-left">
                    <h1>All Tickets</h1>
                    <p>Manage and track support requests</p>
                </div>
                <?php if ($is_admin): ?>
                <button class="btn-create" onclick="openCreateTicketModal()">+ New Ticket</button>
                <?php endif; ?>
            </div>
            <div class="filters-bar">
                <div class="filters-row">
                    <div class="filter-group">
                        <label class="filter-label">Search</label>
                        <div class="search-input-wrapper">
                            <input type="text" class="search-input" id="search" placeholder="Search by title or ID…">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Campus</label>
                        <select class="filter-select" id="campus-filter"><option value="">All Campuses</option></select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select class="filter-select" id="status-filter">
                            <option value="">All Statuses</option>
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Pending">Pending</option>
                            <option value="Resolved">Resolved</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Priority</label>
                        <select class="filter-select" id="priority-filter">
                            <option value="">All Priorities</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>
                    <button class="btn-filter" onclick="applyFilters()">Apply</button>
                </div>
            </div>
            <div class="table-card">
                <div id="loading"><div class="loading-state"><div class="spinner"></div><p style="color:#94A3B8;font-size:13px;margin-top:12px;">Loading tickets…</p></div></div>
                <div id="error" style="display:none;padding:24px;text-align:center;color:#B91C1C;"></div>
                <div id="tickets-container" style="display:none;">
                    <div class="table-wrapper">
                        <table class="tickets-table">
                            <thead><tr><th>ID</th><th>Title</th><th>Campus</th><th>Department</th><th>Priority</th><th>Status</th><th>Assigned To</th><th>Created By</th><th>Created</th></tr></thead>
                            <tbody id="tickets-tbody"></tbody>
                        </table>
                    </div>
                    <div class="pagination-wrapper">
                        <div class="pagination-info" id="pagination-info"></div>
                        <div class="pagination-controls" id="pagination-controls"></div>
                    </div>
                </div>
                <div id="empty-state" style="display:none;"><div class="empty-state"><span class="empty-icon">🎫</span><h3 class="empty-title">No tickets found</h3><p class="empty-text">Try adjusting your filters</p></div></div>
            </div>
        </div>
    </main>
</div>

<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/tickets.js"></script>
<script src="/internal_portal/public/js/create-ticket-modal.js"></script>
<script>
    const avatarBtn  = document.getElementById('avatarBtn');
    const avatarMenu = document.getElementById('avatarMenu');
    avatarBtn.addEventListener('click', e => { e.stopPropagation(); avatarMenu.classList.toggle('open'); });
    document.addEventListener('click', () => avatarMenu.classList.remove('open'));
    avatarMenu.addEventListener('click', e => e.stopPropagation());
</script>
</body>
</html>