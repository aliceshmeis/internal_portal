<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /internal_portal/app/views/auth/login.php');
    exit;
}

$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
$is_admin = ($user_role === 'Admin');

// Admin only
if (!$is_admin) {
    header('Location: /internal_portal/app/views/staff/dashboard.php');
    exit;
}

// Get ticket ID from URL
$ticket_id = $_GET['id'] ?? null;
if (!$ticket_id) {
    header('Location: /internal_portal/app/views/tickets/list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Details - Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/tickets.css">
    <link rel="stylesheet" href="/internal_portal/public/css/ticket-details.css">
</head>
<body>
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <div class="page-wrapper">
        <!-- Sidebar - Same as list.php -->
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
                    <a href="list.php" class="sidebar-nav-item active">
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

        <!-- Main Content -->
        <main class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <div class="topbar-left">
                    <button class="hamburger-menu" id="hamburgerMenu">☰</button>
                    <div class="breadcrumb">
                        <a href="../dashboard/dashboard.php" class="breadcrumb-item">Home</a>
                        <span class="breadcrumb-separator">/</span>
                        <a href="list.php" class="breadcrumb-item">Tickets</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Ticket Details</span>
                    </div>
                </div>
                
                <div class="topbar-search">
                    <input type="text" placeholder="Search tickets, assets, users...">
                </div>
                
                <div class="topbar-right">
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

            <!-- Page Content -->
            <div class="page-content">

                <!-- Back Button -->
                <div style="margin-bottom: 24px;">
                    <a href="list.php" class="btn-back">
                        ← Back to Tickets
                    </a>
                </div>

                <!-- Loading State -->
                <div id="loading">
                    <div class="loading-state">
                        <div class="spinner"></div>
                        <p>Loading ticket details...</p>
                    </div>
                </div>

                <!-- Error State -->
                <div id="error" style="display: none; padding: 24px; text-align: center; color: var(--color-danger);"></div>

                <!-- Ticket Details (loaded by JS) -->
                <div id="ticket-details" style="display: none;">

                    <!-- Ticket Header Card -->
                    <div class="table-card" style="padding: 32px; margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
                            <div>
                                <div id="ticket-number" style="font-size: 13px; color: var(--color-text-secondary); margin-bottom: 8px;"></div>
                                <h1 id="ticket-title" style="font-size: 24px; font-weight: 600; color: var(--color-text-primary); margin-bottom: 16px;"></h1>
                                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                                    <span id="ticket-status"></span>
                                    <span id="ticket-priority"></span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div id="action-buttons" style="display: flex; gap: 12px; flex-wrap: wrap;"></div>
                        </div>
                    </div>

                    <!-- Info Cards Row -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                        
                        <!-- Ticket Info -->
                        <div class="table-card" style="padding: 24px;">
                            <h2 style="font-size: 16px; font-weight: 600; margin-bottom: 20px; color: var(--color-text-primary);">Ticket Information</h2>
                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div>
                                    <div style="font-size: 12px; color: var(--color-text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Created By</div>
                                    <div id="ticket-creator" style="font-size: 15px; font-weight: 500; color: var(--color-text-primary);"></div>
                                </div>
                                <div>
                                    <div style="font-size: 12px; color: var(--color-text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Assigned To</div>
                                    <div id="ticket-assignee" style="font-size: 15px; font-weight: 500; color: var(--color-text-primary);"></div>
                                </div>
                                <div>
                                    <div style="font-size: 12px; color: var(--color-text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Campus</div>
                                    <div id="ticket-campus" style="font-size: 15px; font-weight: 500; color: var(--color-text-primary);"></div>
                                </div>
                                <div>
                                    <div style="font-size: 12px; color: var(--color-text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Created At</div>
                                    <div id="ticket-created" style="font-size: 15px; font-weight: 500; color: var(--color-text-primary);"></div>
                                </div>
                                <div>
                                    <div style="font-size: 12px; color: var(--color-text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Last Updated</div>
                                    <div id="ticket-updated" style="font-size: 15px; font-weight: 500; color: var(--color-text-primary);"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="table-card" style="padding: 24px;">
                            <h2 style="font-size: 16px; font-weight: 600; margin-bottom: 16px; color: var(--color-text-primary);">Description</h2>
                            <div id="ticket-description" style="font-size: 15px; color: var(--color-text-secondary); line-height: 1.6; white-space: pre-wrap;"></div>
                        </div>
                    </div>

                    <!-- Resolution Notes (shown only if Resolved/Closed) -->
                    <div id="resolution-section" style="display: none; margin-bottom: 24px;">
                        <div class="table-card" style="padding: 24px; border-left: 4px solid #38a169;">
                            <h2 style="font-size: 16px; font-weight: 600; margin-bottom: 12px; color: #38a169;">✅ Resolution Notes</h2>
                            <div id="resolution-notes" style="font-size: 15px; color: var(--color-text-secondary); line-height: 1.6;"></div>
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="table-card" style="padding: 24px;">
                        <h2 style="font-size: 16px; font-weight: 600; margin-bottom: 20px; color: var(--color-text-primary);">
                            Comments <span id="comments-count" style="font-size: 13px; color: var(--color-text-secondary); font-weight: 400;"></span>
                        </h2>
                        <div id="comments-list"></div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script src="/internal_portal/public/js/mobile-menu.js"></script>
    <script>
        // Pass PHP ticket ID to JavaScript
        const TICKET_ID = <?php echo intval($ticket_id); ?>;
    </script>
    <script src="/internal_portal/public/js/ticket-details.js"></script>
</body>
</html>