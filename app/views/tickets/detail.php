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
    <link rel="stylesheet" href="/internal_portal/public/css/subtask.css">
    <style>
        /* ── Returned Banner ── */
        .returned-banner {
            display: none;
            background: #fef2f2;
            border: 1.5px solid #fca5a5;
            border-left: 5px solid #dc2626;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 18px;
            color: #991b1b;
        }
        .returned-banner-title {
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .returned-banner-body {
            font-size: 13.5px;
            color: #7f1d1d;
        }

        /* ── Resubmit Form ── */
        .resubmit-form {
            display: none;
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 18px;
        }
        .resubmit-form-title {
            font-weight: 700;
            font-size: 15px;
            color: var(--color-text-primary);
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f3f4f6;
        }
        .rs-form-group { margin-bottom: 14px; }
        .rs-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--color-text-secondary);
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }
        .rs-input, .rs-select, .rs-textarea {
            width: 100%;
            padding: 8px 11px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13.5px;
            color: var(--color-text-primary);
            background: #fafafa;
            box-sizing: border-box;
        }
        .rs-textarea { resize: vertical; min-height: 90px; }
        .rs-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
        .btn-resubmit {
            background: var(--color-primary);
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 6px;
            width: 100%;
        }
        .btn-resubmit:hover { opacity: .9; }

        /* ── Timeline bubble variants ── */
        .bubble-return  { background: #fef2f2 !important; border-left: 3px solid #dc2626; }
        .bubble-resubmit { background: #f0fdf4 !important; border-left: 3px solid #16a34a; }

        /* ── Return modal ── */
        .return-modal-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .return-modal-overlay.open { display: flex; }
        .return-modal {
            background: #fff;
            border-radius: 12px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 60px rgba(0,0,0,.18);
            overflow: hidden;
        }
        .return-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            border-bottom: 1px solid #f3f4f6;
        }
        .return-modal-title { font-weight: 700; font-size: 16px; color: #dc2626; }
        .return-modal-close {
            background: none; border: none; font-size: 18px;
            cursor: pointer; color: var(--color-text-secondary);
        }
        .return-modal-body { padding: 20px 22px; }
        .return-modal-body textarea {
            width: 100%; padding: 10px 12px;
            border: 1px solid #d1d5db; border-radius: 7px;
            font-size: 13.5px; resize: vertical; min-height: 110px;
            box-sizing: border-box;
        }
        .return-modal-footer {
            display: flex; gap: 10px; justify-content: flex-end;
            padding: 14px 22px; border-top: 1px solid #f3f4f6;
        }
        .btn-return-cancel {
            padding: 9px 18px; border-radius: 7px; border: 1px solid #e5e7eb;
            background: #fff; cursor: pointer; font-size: 13.5px;
        }
        .btn-return-confirm {
            padding: 9px 18px; border-radius: 7px; border: none;
            background: #dc2626; color: #fff; cursor: pointer;
            font-size: 13.5px; font-weight: 600;
        }
        .btn-return-confirm:hover { background: #b91c1c; }
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

                <!-- ── RETURNED BANNER ── -->
                <div class="returned-banner" id="returned-banner">
                    <div class="returned-banner-title">⚠️ This ticket has been returned</div>
                    <div class="returned-banner-body">The admin has returned this ticket and is awaiting more information. Please review the return reason in the Activity section below, update the ticket, and resubmit.</div>
                </div>

                <!-- ── RESUBMIT FORM (requester only, shown when status = Returned) ── -->
                <?php if (!$is_admin): ?>
                <div class="resubmit-form" id="resubmit-form">
                    <div class="resubmit-form-title">✏️ Update & Resubmit Your Ticket</div>
                    <div class="rs-form-group">
                        <label class="rs-label">Title <span style="color:#dc2626;">*</span></label>
                        <input type="text" id="rs-title" class="rs-input" placeholder="Ticket title">
                    </div>
                    <div class="rs-form-group">
                        <label class="rs-label">Description <span style="color:#dc2626;">*</span></label>
                        <textarea id="rs-description" class="rs-textarea" placeholder="Describe the issue in detail..."></textarea>
                    </div>
                    <div class="rs-row">
                        <div class="rs-form-group">
                            <label class="rs-label">Priority</label>
                            <select id="rs-priority" class="rs-select">
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                        <div class="rs-form-group">
                            <label class="rs-label">Category</label>
                            <select id="rs-category" class="rs-select">
                                <option value="">Select...</option>
                                <option value="Hardware">Hardware</option>
                                <option value="Software">Software</option>
                                <option value="Network">Network</option>
                                <option value="Access">Access</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="rs-form-group">
                            <label class="rs-label">Building</label>
                            <input type="text" id="rs-building" class="rs-input" placeholder="Building">
                        </div>
                    </div>
                    <div class="rs-row">
                        <div class="rs-form-group">
                            <label class="rs-label">Floor</label>
                            <input type="text" id="rs-floor" class="rs-input" placeholder="Floor">
                        </div>
                        <div class="rs-form-group">
                            <label class="rs-label">Room</label>
                            <input type="text" id="rs-room" class="rs-input" placeholder="Room">
                        </div>
                        <div></div>
                    </div>
                    <button class="btn-resubmit" id="btn-resubmit" onclick="resubmitTicket()">↑ Resubmit Ticket</button>
                </div>
                <?php endif; ?>

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
                                <span id="td-status"         class="status-badge"></span>
                                <span id="td-priority"       class="priority-badge"></span>
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
                                    <button class="td-more-item" id="btn-return" onclick="openReturnModal()" style="color:#dc2626;">↵ Return to Requester</button>
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
                        <div class="td-flow-step" data-step="Returned">
                            <div class="td-flow-label">
                                <div class="td-flow-dot" style="background:#dc2626;">↵</div>
                                <span class="td-flow-text" style="color:#dc2626;">Returned</span>
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

                        <!-- SUBTASKS -->
                        <div class="td-card subtask-section" id="subtask-section">
                            <div class="subtask-section-header">
                                <div style="display:flex;align-items:center;">
                                    <div class="td-card-title" style="margin:0;">Subtasks</div>
                                    <span class="subtask-count-badge" id="subtask-count">0</span>
                                </div>
                                <?php if ($is_admin): ?>
                                <button class="btn-add-subtask" onclick="openSubtaskModal()">+ Add Subtask</button>
                                <?php endif; ?>
                            </div>
                            <div class="subtask-list" id="subtask-list">
                                <div class="subtask-empty">No subtasks yet.</div>
                            </div>
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

<!-- ── RETURN TICKET MODAL (Admin) ── -->
<?php if ($is_admin): ?>
<div class="return-modal-overlay" id="returnModalOverlay">
    <div class="return-modal">
        <div class="return-modal-header">
            <div class="return-modal-title">↵ Return Ticket to Requester</div>
            <button class="return-modal-close" onclick="closeReturnModal()">&#x2715;</button>
        </div>
        <div class="return-modal-body">
            <label style="display:block;font-size:13px;font-weight:600;margin-bottom:8px;color:var(--color-text-secondary);">
                Return Reason <span style="color:#dc2626;">*</span>
            </label>
            <textarea id="return-reason" placeholder="Explain what information or changes are needed from the requester..." rows="5"></textarea>
        </div>
        <div class="return-modal-footer">
            <button class="btn-return-cancel" onclick="closeReturnModal()">Cancel</button>
            <button class="btn-return-confirm" id="btn-return-submit" onclick="submitReturn()">Return Ticket</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── ADD SUBTASK MODAL ── -->
<?php if ($is_admin): ?>
<div class="subtask-modal-overlay" id="subtaskModalOverlay">
    <div class="subtask-modal">
        <div class="subtask-modal-header">
            <div class="subtask-modal-title">Add Subtask</div>
            <button class="subtask-modal-close" onclick="closeSubtaskModal()">&#x2715;</button>
        </div>
        <div class="subtask-modal-body">
            <div class="sm-form-group">
                <label class="sm-label">Title <span>*</span></label>
                <input type="text" id="sm-title" class="sm-input" placeholder="e.g. Prepare financial report">
            </div>
            <div class="sm-form-group">
                <label class="sm-label">Description</label>
                <textarea id="sm-description" class="sm-textarea" placeholder="Optional details..."></textarea>
            </div>
            <div class="sm-row">
                <div class="sm-form-group">
                    <label class="sm-label">Priority</label>
                    <select id="sm-priority" class="sm-select">
                        <option value="">Inherit from ticket</option>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
                <div class="sm-form-group">
                    <label class="sm-label">Due Date</label>
                    <input type="date" id="sm-due-date" class="sm-input">
                </div>
            </div>
            <div class="sm-form-group">
                <label class="sm-label">Department Type <span>*</span></label>
                <div class="sm-type-toggle">
                    <button type="button" class="sm-type-btn" data-type="academic"       onclick="selectDeptType('academic')">Academic</button>
                    <button type="button" class="sm-type-btn" data-type="administrative" onclick="selectDeptType('administrative')">Administrative</button>
                </div>
            </div>
            <div class="sm-form-group" id="sm-dept-group" style="display:none;">
                <label class="sm-label">Department <span>*</span></label>
                <select id="sm-department" class="sm-select" onchange="onDepartmentChange()">
                    <option value="">Select department...</option>
                </select>
            </div>
            <div class="sm-form-group" id="sm-users-group" style="display:none;">
                <label class="sm-label">Assign Users <span>*</span></label>
                <div class="sm-user-list" id="sm-user-list">
                    <div class="sm-user-placeholder">Select a department first.</div>
                </div>
            </div>
        </div>
        <div class="subtask-modal-footer">
            <button class="btn-sm-cancel" onclick="closeSubtaskModal()">Cancel</button>
            <button class="btn-sm-submit" id="btn-sm-submit" onclick="submitSubtask()">Create Subtask</button>
        </div>
    </div>
</div>

<!-- ── EDIT SUBTASK MODAL ── -->
<div class="subtask-modal-overlay" id="editSubtaskModalOverlay">
    <div class="subtask-modal">
        <div class="subtask-modal-header">
            <div class="subtask-modal-title">Edit Subtask</div>
            <button class="subtask-modal-close" onclick="closeEditSubtaskModal()">&#x2715;</button>
        </div>
        <div class="subtask-modal-body">
            <input type="hidden" id="edit-sm-id">
            <div class="sm-form-group">
                <label class="sm-label">Title <span>*</span></label>
                <input type="text" id="edit-sm-title" class="sm-input" placeholder="Subtask title">
            </div>
            <div class="sm-form-group">
                <label class="sm-label">Description</label>
                <textarea id="edit-sm-description" class="sm-textarea" placeholder="Optional details..."></textarea>
            </div>
            <div class="sm-row">
                <div class="sm-form-group">
                    <label class="sm-label">Priority</label>
                    <select id="edit-sm-priority" class="sm-select">
                        <option value="">Inherit from ticket</option>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
                <div class="sm-form-group">
                    <label class="sm-label">Due Date</label>
                    <input type="date" id="edit-sm-due-date" class="sm-input">
                </div>
            </div>
        </div>
        <div class="subtask-modal-footer">
            <button class="btn-sm-cancel" onclick="closeEditSubtaskModal()">Cancel</button>
            <button class="btn-sm-submit" id="btn-edit-sm-submit" onclick="submitEditSubtask()">Save Changes</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    const TICKET_ID       = <?php echo $ticket_id; ?>;
    const IS_ADMIN        = <?php echo $is_admin ? 'true' : 'false'; ?>;
    const API_BASE        = '/internal_portal/api/v1';
    const USER_INIT       = '<?php echo $user_initials; ?>';
    const CURRENT_USER_ID = <?php echo (int)$_SESSION['user_id']; ?>;
</script>
<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/ticket-detail.js"></script>
</body>
</html>