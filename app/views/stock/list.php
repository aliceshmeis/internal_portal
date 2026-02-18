<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /internal_portal/app/views/auth/login.php');
    exit;
}

$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
$is_admin  = ($user_role === 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock - Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/tickets.css">
    <link rel="stylesheet" href="/internal_portal/public/css/stock.css">
</head>
<body>
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <div class="page-wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">LIU</div>
                <div class="sidebar-title">Internal Portal</div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Main</div>
                    <a href="../dashboard/dashboard.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-dashboard"></span>
                        <span class="sidebar-nav-text">Dashboard</span>
                    </a>
                    <a href="../tickets/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-tickets"></span>
                        <span class="sidebar-nav-text">Tickets</span>
                    </a>
                    <a href="../assets/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-assets"></span>
                        <span class="sidebar-nav-text">Assets</span>
                    </a>
                </div>
                
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Inventory</div>
                    <a href="list.php" class="sidebar-nav-item active">
                        <span class="sidebar-nav-icon icon-stock"></span>
                        <span class="sidebar-nav-text">Stock</span>
                    </a>
                    <a href="../purchase-orders/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-po"></span>
                        <span class="sidebar-nav-text">Purchase Orders</span>
                    </a>
                </div>
                
                <?php if ($is_admin): ?>
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Settings</div>
                    <a href="../users/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-users"></span>
                        <span class="sidebar-nav-text">Users</span>
                    </a>
                    <a href="../reports/index.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-reports"></span>
                        <span class="sidebar-nav-text">Reports</span>
                    </a>
                </div>
                <?php endif; ?>
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
                        <a href="../dashboard/dashboard.php" class="breadcrumb-item">Home</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Stock</span>
                    </div>
                </div>
                
                <div class="topbar-search">
                    <input type="text" placeholder="Search tickets, assets, users...">
                </div>
                
                <div class="topbar-right">
                    <?php if ($is_admin): ?>
                    <button class="btn btn-primary" onclick="openAddStockModal()">+ Add Item</button>
                    <?php endif; ?>
                    <button class="topbar-icon-btn" title="Notifications">
                        🔔
                        <span class="badge">3</span>
                    </button>
                    <div class="header-user">
                        <div class="header-user-avatar">
                            <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                        </div>
                        <div class="header-user-info">
                            <div class="header-user-name"><?php echo htmlspecialchars($user_name); ?></div>
                            <div class="header-user-role"><?php echo htmlspecialchars($user_role); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1 class="page-title">Stock Inventory</h1>
                    <p class="page-subtitle">Monitor and manage stock levels</p>
                </div>

                <div id="alert-banner" style="display: none;"></div>

                <div class="stats-row" id="stats-row"></div>

                <div class="filters-bar">
                    <div class="filters-row">
                        <div class="filter-group">
                            <label class="filter-label">Search Items</label>
                            <div class="search-input-wrapper">
                                <input type="text" class="search-input" id="search" placeholder="Search by name or SKU...">
                            </div>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Category</label>
                            <select class="filter-select" id="category-filter">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Low Stock</label>
                            <label class="filter-toggle" id="low-stock-toggle">
                                <input type="checkbox" id="low-stock-filter">
                                <span class="filter-toggle-label">Show Low Stock Only</span>
                            </label>
                        </div>
                        <button class="btn-filter" onclick="applyFilters()">Apply</button>
                    </div>
                </div>

                <div class="table-card">
                    <div id="loading">
                        <div class="loading-state">
                            <div class="spinner"></div>
                            <p>Loading stock items...</p>
                        </div>
                    </div>
                    <div id="error" style="display: none; padding: 24px; text-align: center; color: var(--color-danger);"></div>
                    <div id="stock-container" style="display: none;">
                        <div class="table-wrapper">
                            <table class="stock-table">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>SKU</th>
                                        <th>Quantity</th>
                                        <th>Min Threshold</th>
                                        <th>Status</th>
                                        <th>Last Updated</th>
                                        <th style="text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="stock-tbody"></tbody>
                            </table>
                        </div>
                        <div class="pagination-wrapper">
                            <div class="pagination-info" id="pagination-info"></div>
                            <div class="pagination-controls" id="pagination-controls"></div>
                        </div>
                    </div>
                    <div id="empty-state" style="display: none;">
                        <div class="empty-state">
                            <div class="empty-icon">📋</div>
                            <h3 class="empty-title">No stock items found</h3>
                            <p class="empty-text">Try adjusting your filters or add a new item to get started.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- ── ADD STOCK MODAL ─────────────────────────────── -->
    <div class="modal-overlay" id="addStockModal">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Add Stock Item</h3>
                <button class="modal-close" onclick="closeAddStockModal()">✕</button>
            </div>
            <div class="modal-body">
                <form id="addStockForm" onsubmit="return false;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Item Name <span class="required">*</span></label>
                            <input type="text" id="addName" class="form-control" placeholder="e.g. A4 Paper Ream">
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <input type="text" id="addCategory" class="form-control" placeholder="e.g. Stationery">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>SKU</label>
                            <input type="text" id="addSku" class="form-control" placeholder="e.g. STK-001">
                        </div>
                        <div class="form-group">
                            <label>Unit</label>
                            <input type="text" id="addUnit" class="form-control" placeholder="e.g. units, boxes, reams">
                        </div>
                    </div>
                    <div class="form-group">
    <label>Campus <span class="required">*</span></label>
    <select id="addCampus" class="form-control">
        <option value="">Select campus...</option>
    </select>
</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Quantity <span class="required">*</span></label>
                            <input type="number" id="addQuantity" class="form-control" placeholder="0" min="0">
                        </div>
                        <div class="form-group">
                            <label>Min Threshold</label>
                            <input type="number" id="addThreshold" class="form-control" placeholder="10" min="0" value="10">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Unit Cost ($)</label>
                        <input type="number" id="addUnitCost" class="form-control" placeholder="0.00" min="0" step="0.01">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeAddStockModal()">Cancel</button>
                <button class="btn-primary" id="addStockBtn" onclick="submitAddStock()">Add Item</button>
            </div>
        </div>
    </div>

    <!-- ── ADJUST STOCK MODAL ──────────────────────────── -->
    <div class="modal-overlay" id="adjustStockModal">
        <div class="modal-box modal-box-sm">
            <div class="modal-header">
                <h3>Adjust Stock</h3>
                <button class="modal-close" onclick="closeAdjustModal()">✕</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="adjustStockId">
                <div class="adjust-item-info">
                    <div class="adjust-item-name" id="adjustItemName"></div>
                    <div class="adjust-current-qty">Current: <strong id="adjustCurrentQty"></strong></div>
                </div>
                <div class="form-group">
                    <label>New Quantity <span class="required">*</span></label>
                    <input type="number" id="adjustQuantity" class="form-control" min="0">
                </div>
                <div class="form-group">
                    <label>Min Threshold</label>
                    <input type="number" id="adjustThreshold" class="form-control" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeAdjustModal()">Cancel</button>
                <button class="btn-primary" id="adjustStockBtn" onclick="submitAdjustStock()">Save Changes</button>
            </div>
        </div>
    </div>

    <script src="/internal_portal/public/js/mobile-menu.js"></script>
    <script src="/internal_portal/public/js/stock.js"></script>
</body>
</html>