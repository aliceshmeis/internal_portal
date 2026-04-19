<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] !== 'Admin') { header('Location: /internal_portal/app/views/dashboard/dashboard.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];

// Load suppliers for filter dropdown
require_once __DIR__ . '/../../../config/database.php';
try {
    $db       = (new Database())->getConnection();
    $stmt     = $db->query("SELECT id, name FROM suppliers WHERE is_active = 1 ORDER BY name ASC");
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $suppliers = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotations — Internal Portal</title>
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
        <a href="../stock/list.php"           class="sidebar-nav-item"><span class="sidebar-nav-icon icon-stock"></span><span class="sidebar-nav-text">Stock</span></a>
        <a href="../purchase-orders/list.php" class="sidebar-nav-item"><span class="sidebar-nav-icon icon-po"></span><span class="sidebar-nav-text">Purchase Orders</span></a>
    </div>
    <div class="sidebar-nav-section">
        <div class="sidebar-nav-section-title">Procurement</div>
        <a href="../suppliers/list.php"  class="sidebar-nav-item"><span class="sidebar-nav-icon icon-users"></span><span class="sidebar-nav-text">Suppliers</span></a>
        <a href="../quotations/list.php" class="sidebar-nav-item active"><span class="sidebar-nav-icon icon-po"></span><span class="sidebar-nav-text">Quotations</span></a>
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
                    <span class="breadcrumb-item active">Quotations</span>
                </div>
            </div>
            <div class="topbar-right">
                <button class="btn btn-primary" onclick="goCreate()">+ Add Quotation</button>
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
                <h1 class="page-title">Quotations</h1>
                <p class="page-subtitle">Review and approve supplier quotations</p>
            </div>

            <!-- Stats -->
            <div class="quo-stats" id="quo-stats"></div>

            <!-- Filters -->
            <div class="quo-filters">
                <div class="quo-filter-group">
                    <label class="quo-filter-label">Search</label>
                    <input type="text" class="quo-filter-input" id="quo-search" placeholder="Quotation # or supplier..." oninput="applyFilters()">
                </div>
                <div class="quo-filter-group">
                    <label class="quo-filter-label">Status</label>
                    <select class="quo-filter-select" id="quo-status" onchange="applyFilters()">
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Expired">Expired</option>
                    </select>
                </div>
                <div class="quo-filter-group">
                    <label class="quo-filter-label">Supplier</label>
                    <select class="quo-filter-select" id="quo-supplier" onchange="applyFilters()">
                        <option value="">All Suppliers</option>
                        <?php foreach ($suppliers as $s): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="quo-table-card">
                <div class="quo-table-header">
                    <div>
                        <span class="quo-table-title">All Quotations</span>
                        <span class="quo-table-count" id="quo-count">0</span>
                    </div>
                </div>

                <div id="loading" class="quo-loading">Loading quotations...</div>
                <div id="error"   style="display:none;padding:24px;text-align:center;color:#dc2626;"></div>

                <div id="quo-table-wrap" style="display:none;">
                    <div class="table-wrapper">
                        <table class="quo-table">
                            <thead>
                                <tr>
                                    <th>Quotation #</th>
                                    <th>Supplier</th>
                                    <th>Total</th>
                                    <th>Valid Until</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="quo-tbody"></tbody>
                        </table>
                    </div>
                </div>

                <div id="empty-state" style="display:none;">
                    <div class="quo-empty">
                        <div class="quo-empty-icon">📋</div>
                        <div class="quo-empty-title">No quotations found</div>
                        <div class="quo-empty-text">Add a quotation to begin the procurement process.</div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/quotations.js"></script>
</body>
</html>