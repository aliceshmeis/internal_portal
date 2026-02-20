<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /internal_portal/app/views/auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'Admin') {
    header('Location: /internal_portal/app/views/dashboard/dashboard.php');
    exit;
}

$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/tickets.css">
    <link rel="stylesheet" href="/internal_portal/public/css/users.css">
    <link rel="stylesheet" href="/internal_portal/public/css/add-user-modal.css">
</head>


<body>
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <div class="page-wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="/internal_portal/public/images/liulogo.png" alt="LIU" style="height:36px;object-fit:contain;">                <div class="sidebar-title">Internal Portal</div>
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
                    <a href="../stock/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-stock"></span>
                        <span class="sidebar-nav-text">Stock</span>
                    </a>
                    <a href="../purchase-orders/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-po"></span>
                        <span class="sidebar-nav-text">Purchase Orders</span>
                    </a>
                </div>
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Settings</div>
                    <a href="list.php" class="sidebar-nav-item active">
                        <span class="sidebar-nav-icon icon-users"></span>
                        <span class="sidebar-nav-text">Users</span>
                    </a>
                    <a href="../reports/index.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-reports"></span>
                        <span class="sidebar-nav-text">Reports</span>
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
                        <a href="../dashboard/dashboard.php" class="breadcrumb-item">Home</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Users</span>
                    </div>
                </div>
                <div class="topbar-search">
                    <input type="text" placeholder="Search tickets, assets, users...">
                </div>
                <div class="topbar-right">
                    <button class="btn btn-primary" onclick="openAddUserModal()">+ Add User</button>
                    <button class="topbar-icon-btn" title="Notifications">
                        🔔
                        <span class="badge">3</span>
                    </button>
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
                <div class="page-header">
                    <h1 style="font-size:24px; font-weight:600; margin-bottom:8px; color:var(--color-text-primary);">User Management</h1>
                    <p style="color:var(--color-text-secondary); margin-bottom:32px; font-size:14px;">Manage staff access and permissions</p>
                </div>

                <div class="filters-bar">
                    <div class="filters-row">
                        <div class="filter-group">
                            <label class="filter-label">Search Users</label>
                            <div class="search-input-wrapper">
                                <input type="text" class="search-input" id="search" placeholder="Search by name or email...">
                            </div>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Role</label>
                            <select class="filter-select" id="role-filter">
                                <option value="">All Roles</option>
                                <option value="Admin">Admin</option>
                                <option value="Staff">Staff</option>
                                <option value="Asset Manager">Asset Manager</option>
                                <option value="Viewer">Viewer</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Status</label>
                            <select class="filter-select" id="status-filter">
                                <option value="">All Statuses</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <button class="btn-filter" onclick="applyFilters()">Apply</button>
                    </div>
                </div>

                <div class="table-card">
                    <div id="loading">
                        <div class="loading-state">
                            <div class="spinner"></div>
                            <p>Loading users...</p>
                        </div>
                    </div>
                    <div id="error" style="display:none; padding:24px; text-align:center; color:var(--color-danger);"></div>
                    <div id="users-container" style="display:none;">
                        <div class="table-wrapper">
                            <table class="users-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Campus</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                        <th style="text-align:center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="users-tbody"></tbody>
                            </table>
                        </div>
                        <div class="pagination-wrapper">
                            <div class="pagination-info" id="pagination-info"></div>
                            <div class="pagination-controls" id="pagination-controls"></div>
                        </div>
                    </div>
                    <div id="empty-state" style="display:none;">
                        <div class="empty-state">
                            <div class="empty-icon">👥</div>
                            <h3 class="empty-title">No users found</h3>
                            <p class="empty-text">Try adjusting your filters or add a new user.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/internal_portal/public/js/mobile-menu.js"></script>
    <script src="/internal_portal/public/js/users.js"></script>
    <script src="/internal_portal/public/js/add-user-modal.js"></script>

    <!-- ── EDIT USER MODAL ─────────────────────── -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit User</h3>
            <button class="modal-close" onclick="closeEditModal()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editUserId">

            <div class="form-group">
                <label>Full Name <span class="required">*</span></label>
                <input type="text" id="editName" class="form-control" placeholder="Full name">
            </div>
            <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <input type="email" id="editEmail" class="form-control" placeholder="Email">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Role <span class="required">*</span></label>
                    <select id="editRole" class="form-control">
                        <option value="Admin">Admin</option>
                        <option value="Staff">Staff</option>
                        <option value="Asset Manager">Asset Manager</option>
                        <option value="Viewer">Viewer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="editStatus" class="form-control">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Campus</label>
                <select id="editCampus" class="form-control" onchange="loadEditDepartments(this.value)">
                    <option value="">Select campus...</option>
                </select>
            </div>
            <div class="form-group">
                <label>Department</label>
                <select id="editDepartment" class="form-control">
                    <option value="">Select campus first...</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeEditModal()">Cancel</button>
            <button class="btn-primary" id="saveEditBtn" onclick="saveEditUser()">Save Changes</button>
        </div>
    </div>
</div>

<!-- ── DELETE CONFIRM MODAL ───────────────── -->
<div class="modal-overlay" id="deleteUserModal">
    <div class="modal-box modal-box-sm">
        <div class="modal-header modal-header-danger">
            <h3>Delete User</h3>
            <button class="modal-close" onclick="closeDeleteModal()">✕</button>
        </div>
        <div class="modal-body modal-body-center">
            <div class="delete-icon">🗑️</div>
            <p>Are you sure you want to delete</p>
            <strong id="deleteUserName"></strong>
            <p class="delete-email" id="deleteUserEmail"></p>
            <p class="delete-warning">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
        </div>
    </div>
</div>
</body>
</html>