<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /internal_portal/app/views/auth/login.php');
    exit;
}

$user_name     = $_SESSION['name'];
$user_role     = $_SESSION['role'];
$is_admin      = ($user_role === 'Admin');
$user_initials = strtoupper(substr($user_name, 0, 1));

$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
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
    <title>Ticket Detail - Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/ticket-detail.css">
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

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger-menu" id="hamburgerMenu">☰</button>
                <div class="breadcrumb">
                    <a href="../dashboard/dashboard.php" class="breadcrumb-item">Home</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="list.php" class="breadcrumb-item">Tickets</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active" id="breadcrumb-id">Loading...</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="header-user">
                    <div class="header-user-avatar"><?php echo $user_initials; ?></div>
                    <div class="header-user-info">
                        <div class="header-user-name"><?php echo htmlspecialchars($user_name); ?></div>
                        <div class="header-user-role"><?php echo htmlspecialchars($user_role); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">

            <div id="loading" class="td-loading">
                <div class="spinner"></div>
                <p>Loading ticket...</p>
            </div>

            <div id="error" class="td-error" style="display:none;"></div>

            <div id="ticket-content" style="display:none;">

                <a href="list.php" class="td-back">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    Back to Tickets
                </a>

                <!-- HEADER CARD -->
                <div class="td-header-card">
                    <div class="td-header-top">
                        <div class="td-header-left">
                            <h1 id="td-title" class="td-title"></h1>
                            <div class="td-header-meta">
                                <span id="td-ticket-number"></span>
                                <span class="td-header-meta-dot">•</span>
                                <strong id="td-campus-meta"></strong>
                                <span class="td-header-meta-dot">•</span>
                                <span>Created <strong id="td-created-meta"></strong></span>
                            </div>
                            <div class="td-badges">
                                <span id="td-status"        class="status-badge"></span>
                                <span id="td-priority"      class="priority-badge"></span>
                                <span id="td-category-badge" class="category-badge" style="display:none;"></span>
                            </div>
                        </div>

                        <?php if ($is_admin): ?>
                        <div class="td-header-actions">
                            <button class="td-btn-resolve" id="btn-resolve" onclick="resolveTicket()">✓ Mark Resolved</button>
                            <div style="position:relative;">
                                <button class="td-btn-more" onclick="toggleMoreMenu()">More ▾</button>
                                <div class="td-more-menu" id="more-menu">
                                    <button class="td-more-item" onclick="reopenTicket()">↩ Reopen</button>
                                    <button class="td-more-item danger" onclick="closeTicket()">✕ Close Ticket</button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Status Flow Bar -->
                    <div class="td-status-flow">
                        <div class="td-flow-step" data-step="Open">
                            <div class="td-flow-label">
                                <div class="td-flow-dot">1</div>
                                <span class="td-flow-text">Open</span>
                            </div>
                        </div>
                        <div class="td-flow-line"></div>
                        <div class="td-flow-step" data-step="In Progress">
                            <div class="td-flow-label">
                                <div class="td-flow-dot">2</div>
                                <span class="td-flow-text">In Progress</span>
                            </div>
                        </div>
                        <div class="td-flow-line"></div>
                        <div class="td-flow-step" data-step="Pending">
                            <div class="td-flow-label">
                                <div class="td-flow-dot">3</div>
                                <span class="td-flow-text">Pending</span>
                            </div>
                        </div>
                        <div class="td-flow-line"></div>
                        <div class="td-flow-step" data-step="Resolved">
                            <div class="td-flow-label">
                                <div class="td-flow-dot">4</div>
                                <span class="td-flow-text">Resolved</span>
                            </div>
                        </div>
                        <div class="td-flow-line"></div>
                        <div class="td-flow-step" data-step="Closed">
                            <div class="td-flow-label">
                                <div class="td-flow-dot">✓</div>
                                <span class="td-flow-text">Closed</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BODY GRID -->
                <div class="td-grid">

                    <!-- LEFT -->
                    <div class="td-main">

                        <!-- Description -->
                        <div class="td-card">
                            <div class="td-card-title">Issue Description</div>
                            <div id="td-description" class="td-description"></div>
                            <div id="td-extra-section" class="td-extra-details" style="display:none;">
                                <div class="td-extra-title">Additional Details</div>
                                <div id="td-extra-list" class="td-extra-list"></div>
                            </div>
                        </div>

                        <!-- Attachments -->
                        <div class="td-card" id="td-attachments-section" style="display:none;">
                            <div class="td-card-title">Attachments</div>
                            <div id="td-attachments" class="td-attachments"></div>
                        </div>

                        <!-- Activity Timeline -->
                        <div class="td-card">
                            <div class="td-card-title">Activity</div>
                            <div id="td-timeline" class="td-timeline">
                                <div class="td-no-comments">No activity yet.</div>
                            </div>
                            <div class="td-comment-form">
                                <div class="td-comment-form-row">
                                    <div class="td-comment-avatar-sm"><?php echo $user_initials; ?></div>
                                    <div class="td-comment-form-inner">
                                        <textarea id="comment-input" class="td-comment-input" placeholder="Add a comment..." rows="3"></textarea>
                                        <div class="td-comment-form-footer">
                                            <button class="td-btn-comment" onclick="addComment()">Post Comment</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT -->
                    <div class="td-sidebar">
                        <div class="td-info-panel">
                            <div class="td-info-panel-header">Ticket Info</div>
                            <div class="td-info-panel-body">

                                <div class="td-meta-list">
                                    <div class="td-meta-item">
                                        <span class="td-meta-label">Created By</span>
                                        <span class="td-meta-value" id="td-creator">—</span>
                                    </div>
                                    <div class="td-meta-item">
                                        <span class="td-meta-label">Assigned To</span>
                                        <span class="td-meta-value" id="td-assigned">—</span>
                                    </div>
                                    <div class="td-meta-item">
                                        <span class="td-meta-label">Campus</span>
                                        <span class="td-meta-value" id="td-campus">—</span>
                                    </div>
                                    <div class="td-meta-item" id="td-category-row" style="display:none;">
                                        <span class="td-meta-label">Category</span>
                                        <span class="td-meta-value" id="td-category">—</span>
                                    </div>
                                    <div class="td-meta-item" id="td-location-row" style="display:none;">
                                        <span class="td-meta-label">Location</span>
                                        <span class="td-meta-value" id="td-location">—</span>
                                    </div>
                                    <div class="td-meta-item" id="td-ssid-row" style="display:none;">
                                        <span class="td-meta-label">WiFi SSID</span>
                                        <span class="td-meta-value" id="td-ssid">—</span>
                                    </div>

                                    <div class="td-info-divider"></div>

                                    <div class="td-meta-item">
                                        <span class="td-meta-label">Created</span>
                                        <span class="td-meta-value" id="td-created">—</span>
                                    </div>
                                    <div class="td-meta-item">
                                        <span class="td-meta-label">Updated</span>
                                        <span class="td-meta-value" id="td-updated">—</span>
                                    </div>
                                    <div class="td-meta-item" id="td-resolved-row" style="display:none;">
                                        <span class="td-meta-label">Resolved</span>
                                        <span class="td-meta-value" id="td-resolved">—</span>
                                    </div>
                                </div>

                                <?php if ($is_admin): ?>
                                <div class="td-info-divider"></div>

                                <div class="td-quick-action">
                                    <div class="td-quick-label">Assign To</div>
                                    <select id="assign-select" class="td-select">
                                        <option value="">Unassigned</option>
                                    </select>
                                    <button class="td-btn-save" onclick="assignTicket()">Save Assignment</button>
                                </div>

                                <div class="td-quick-action">
                                    <div class="td-quick-label">Status</div>
                                    <select id="status-select" class="td-select" onchange="updateStatus()">
                                        <option value="Open">Open</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Resolved">Resolved</option>
                                        <option value="Closed">Closed</option>
                                    </select>
                                </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
</div>

<script>
    const TICKET_ID   = <?php echo $ticket_id; ?>;
    const IS_ADMIN    = <?php echo $is_admin ? 'true' : 'false'; ?>;
    const API_BASE    = '/internal_portal/api/v1';
    const USER_INIT   = '<?php echo $user_initials; ?>';
</script>
<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/ticket-detail.js"></script>
</body>
</html>