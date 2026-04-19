<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] !== 'Admin') { header('Location: /internal_portal/app/views/dashboard/dashboard.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers — Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/quotations.css">
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
        <a href="../tickets/list.php"        class="sidebar-nav-item"><span class="sidebar-nav-icon icon-tickets"></span><span class="sidebar-nav-text">Tickets</span></a>
        <a href="../assets/list.php"         class="sidebar-nav-item"><span class="sidebar-nav-icon icon-assets"></span><span class="sidebar-nav-text">Assets</span></a>
    </div>
    <div class="sidebar-nav-section">
        <div class="sidebar-nav-section-title">Inventory</div>
        <a href="../stock/list.php"          class="sidebar-nav-item"><span class="sidebar-nav-icon icon-stock"></span><span class="sidebar-nav-text">Stock</span></a>
        <a href="../purchase-orders/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-po"></span><span class="sidebar-nav-text">Purchase Orders</span></a>
    </div>
    <div class="sidebar-nav-section">
        <div class="sidebar-nav-section-title">Procurement</div>
        <a href="../suppliers/list.php"  class="sidebar-nav-item active"><span class="sidebar-nav-icon icon-users"></span><span class="sidebar-nav-text">Suppliers</span></a>
        <a href="../quotations/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-po"></span><span class="sidebar-nav-text">Quotations</span></a>
    </div>
    <div class="sidebar-nav-section">
        <div class="sidebar-nav-section-title">Administration</div>
        <a href="../users/list.php"    class="sidebar-nav-item"><span class="sidebar-nav-icon icon-users"></span><span class="sidebar-nav-text">Users</span></a>
        <a href="../reports/index.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-reports"></span><span class="sidebar-nav-text">Reports</span></a>
    </div>
</nav>
        <div class="sidebar-footer">
            <a href="/internal_portal/app/views/auth/logout.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-logout"></span><span class="sidebar-nav-text">Logout</span></a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger-menu" id="hamburgerMenu">☰</button>
                <div class="breadcrumb">
                    <a href="../dashboard/dashboard.php" class="breadcrumb-item">Home</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active">Suppliers</span>
                </div>
            </div>
            <div class="topbar-right">
                <button class="btn btn-primary" onclick="openAddModal()">+ Add Supplier</button>
                <div class="header-user">
                    <div class="header-user-avatar"><?php echo strtoupper(substr($user_name,0,1)); ?></div>
                    <div class="header-user-info">
                        <div class="header-user-name"><?php echo htmlspecialchars($user_name); ?></div>
                        <div class="header-user-role"><?php echo htmlspecialchars($user_role); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <div class="page-header">
                <h1 class="page-title">Suppliers</h1>
                <p class="page-subtitle">Manage your procurement suppliers</p>
            </div>

            <!-- Search -->
            <div class="quo-filters">
                <div class="quo-filter-group">
                    <label class="quo-filter-label">Search</label>
                    <input type="text" class="quo-filter-input" id="sup-search" placeholder="Name or email..." oninput="filterSuppliers()">
                </div>
            </div>

            <!-- Table Card -->
            <div class="quo-table-card">
                <div class="quo-table-header">
                    <div>
                        <span class="quo-table-title">All Suppliers</span>
                        <span class="quo-table-count" id="sup-count">0</span>
                    </div>
                </div>

                <div id="loading" style="padding:40px;text-align:center;color:var(--color-text-secondary);">Loading...</div>
                <div id="error"   style="display:none;padding:24px;text-align:center;color:#dc2626;"></div>

                <div id="sup-table-wrap" style="display:none;">
                    <div class="table-wrapper">
                        <table class="quo-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="supplier-tbody"></tbody>
                        </table>
                    </div>
                </div>

                <div id="empty-state" style="display:none;">
                    <div class="quo-empty">
                        <div class="quo-empty-icon">🏭</div>
                        <div class="quo-empty-title">No suppliers yet</div>
                        <div class="quo-empty-text">Add your first supplier to get started.</div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add / Edit Modal -->
<div class="quo-modal-overlay" id="sup-modal-overlay">
    <div class="quo-modal">
        <div class="quo-modal-header">
            <div class="quo-modal-title" id="modal-title">Add Supplier</div>
            <button class="quo-modal-close" onclick="closeSupModal()">&#x2715;</button>
        </div>
        <div class="quo-modal-body">
            <input type="hidden" id="sup-form-id">
            <div class="qm-form-group">
                <label class="qm-label">Name <span>*</span></label>
                <input type="text" id="sup-form-name" class="qm-input" placeholder="Supplier company name">
            </div>
            <div class="qm-form-group">
                <label class="qm-label">Email</label>
                <input type="email" id="sup-form-email" class="qm-input" placeholder="contact@supplier.com">
            </div>
            <div class="qm-form-group">
                <label class="qm-label">Phone</label>
                <input type="text" id="sup-form-phone" class="qm-input" placeholder="+961 ...">
            </div>
            <div class="qm-form-group">
                <label class="qm-label">Address</label>
                <textarea id="sup-form-addr" class="qm-textarea" placeholder="Full address..."></textarea>
            </div>
        </div>
        <div class="quo-modal-footer">
            <button class="btn-qm-cancel" onclick="closeSupModal()">Cancel</button>
            <button class="btn-qm-submit" id="btn-save-sup" onclick="saveSupplier()">Save</button>
        </div>
    </div>
</div>

<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/suppliers.js"></script>
</body>
</html>