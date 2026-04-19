<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] !== 'Admin') { header('Location: /internal_portal/app/views/dashboard/dashboard.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
$quo_id    = intval($_GET['id'] ?? 0);
if (!$quo_id) { header('Location: list.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Detail — Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/quotations.css">
</head>
<body>
<!-- Hidden data element for JS to read the ID -->
<div id="quotation-id-data" data-id="<?php echo $quo_id; ?>" style="display:none;"></div>

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
                    <a href="list.php" class="breadcrumb-item">Quotations</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active" id="quo-number-title">Loading...</span>
                </div>
            </div>
            <div class="topbar-right">
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

            <div id="loading" class="quo-loading">Loading quotation...</div>
            <div id="error"   style="display:none;padding:24px;text-align:center;color:#dc2626;"></div>

            <div id="quo-detail" style="display:none;">

                <!-- Page header row -->
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                    <h1 class="page-title" style="margin:0;" id="quo-number-header"></h1>
                    <span id="quo-status-badge"></span>
                </div>

                <!-- Expiry warning -->
                <div id="expiry-warning" style="display:none;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#c2410c;font-size:13.5px;font-weight:600;">
                    ⚠️ This quotation has expired and cannot be approved.
                </div>

                <div class="quo-detail-grid">
                    <!-- LEFT: Main info + requests -->
                    <div style="display:flex;flex-direction:column;gap:16px;">

                        <!-- Quotation Info -->
                        <div class="quo-detail-card">
                            <div class="quo-detail-card-header">
                                <span class="quo-detail-card-title">Quotation Information</span>
                            </div>
                            <div class="quo-detail-card-body">
                                <div class="quo-meta-row"><span class="quo-meta-key">Supplier</span><span class="quo-meta-value" id="quo-supplier-name"></span></div>
                                <div class="quo-meta-row"><span class="quo-meta-key">Email</span><span class="quo-meta-value" id="quo-supplier-email"></span></div>
                                <div class="quo-meta-row"><span class="quo-meta-key">Phone</span><span class="quo-meta-value" id="quo-supplier-phone"></span></div>
                                <div class="quo-meta-row"><span class="quo-meta-key">Campus</span><span class="quo-meta-value" id="quo-campus"></span></div>
                                <div class="quo-meta-row"><span class="quo-meta-key">Total Amount</span><span class="quo-meta-value" id="quo-total" style="font-size:18px;color:var(--color-primary);"></span></div>
                                <div class="quo-meta-row"><span class="quo-meta-key">Quotation Date</span><span class="quo-meta-value" id="quo-date"></span></div>
                                <div class="quo-meta-row"><span class="quo-meta-key">Valid Until</span><span class="quo-meta-value" id="quo-valid"></span></div>
                                <div class="quo-meta-row"><span class="quo-meta-key">Created By</span><span class="quo-meta-value" id="quo-created-by"></span></div>
                                <div class="quo-meta-row"><span class="quo-meta-key">Created At</span><span class="quo-meta-value" id="quo-created-at"></span></div>
                                <div class="quo-meta-row" id="quo-approved-row" style="display:none;"><span class="quo-meta-key">Approved By</span><span class="quo-meta-value" id="quo-approved-by"></span></div>
                                <div class="quo-meta-row"><span class="quo-meta-key">Notes</span><span class="quo-meta-value" id="quo-notes" style="font-weight:400;"></span></div>
                            </div>
                        </div>

                        <!-- Rejection reason -->
                        <div id="quo-rejection-row" style="display:none;">
                            <div class="rejection-box">
                                <strong>Rejection Reason:</strong><br>
                                <span id="quo-rejection-reason"></span>
                            </div>
                        </div>

                        <!-- Quotation Requests -->
                        <div class="quo-detail-card">
                            <div class="quo-detail-card-header">
                                <span class="quo-detail-card-title">Quotation Requests Sent</span>
                                <button class="quo-action-btn primary" onclick="openSendModal()">✉ Send Request to Supplier</button>
                            </div>
                            <div class="quo-detail-card-body" style="padding:0;">
                                <div class="table-wrapper">
                                    <table class="quo-requests-table">
                                        <thead>
                                            <tr>
                                                <th>Supplier</th>
                                                <th>Email</th>
                                                <th>Sent At</th>
                                                <th>Due By</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="req-tbody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: File + Actions -->
                    <div style="display:flex;flex-direction:column;gap:16px;">

                        <!-- Quotation File -->
                        <div class="quo-detail-card">
                            <div class="quo-detail-card-header">
                                <span class="quo-detail-card-title">Supplier Reply Document</span>
                            </div>
                            <div class="quo-detail-card-body">

                                <!-- Current file display -->
                                <div id="quo-file-box"></div>

                                <!-- Upload area — prominent, clearly labeled -->
                                <div style="margin-top:14px;">
                                    <div style="font-size:12px;font-weight:600;color:var(--color-text-secondary);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">
                                        Upload Supplier Reply (PDF)
                                    </div>
                                    <div class="quo-upload-area" id="upload-drop-area" onclick="document.getElementById('file-input').click()"
                                         ondragover="event.preventDefault();this.style.borderColor='var(--color-primary)'"
                                         ondragleave="this.style.borderColor=''"
                                         ondrop="handleDrop(event)">
                                        <div style="font-size:28px;">📄</div>
                                        <div class="quo-upload-text">Click to select PDF or drag &amp; drop here</div>
                                        <div style="font-size:11.5px;color:var(--color-text-secondary);margin-top:4px;">PDF only · Max 10MB</div>
                                        <div id="upload-filename" style="font-size:12px;color:var(--color-primary);margin-top:6px;font-weight:600;"></div>
                                    </div>
                                    <input type="file" id="file-input" class="quo-upload-input" accept=".pdf" onchange="onFileSelected()">
                                    <button id="btn-upload" onclick="uploadFile()"
                                            style="display:none;width:100%;margin-top:10px;padding:10px;background:var(--color-primary);color:#fff;border:none;border-radius:8px;font-size:13.5px;font-weight:700;cursor:pointer;">
                                        Upload PDF
                                    </button>
                                    <div id="upload-error" style="display:none;margin-top:8px;font-size:12.5px;color:#dc2626;"></div>
                                </div>

                            </div>
                        </div>

                        <!-- Approval Actions -->
                        <div class="quo-detail-card">
                            <div class="quo-detail-card-header">
                                <span class="quo-detail-card-title">Actions</span>
                            </div>
                            <div class="quo-detail-card-body">
                                <button class="btn-generate-po" id="btn-generate-po" onclick="generatePO()" style="display:none;">
                                    🗂 Generate Purchase Order
                                </button>
                            </div>
                            <div class="quo-action-bar">
                                <button class="btn-approve" id="btn-approve" onclick="approveQuotation()">✓ Approve</button>
                                <button class="btn-reject"  id="btn-reject"  onclick="rejectQuotation()">✗ Reject</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Send Request Modal -->
<div class="quo-modal-overlay" id="send-modal-overlay">
    <div class="quo-modal">
        <div class="quo-modal-header">
            <div class="quo-modal-title">Send Quotation Request</div>
            <button class="quo-modal-close" onclick="closeSendModal()">&#x2715;</button>
        </div>
        <div class="quo-modal-body">
            <div class="qm-form-group">
                <label class="qm-label">Select Suppliers <span>*</span></label>
                <div class="qm-supplier-list" id="qm-supplier-list">
                    <div style="padding:12px;font-size:13px;color:var(--color-text-secondary);">Loading...</div>
                </div>
            </div>
            <div class="qm-form-group">
                <label class="qm-label">Response Due Date</label>
                <input type="date" id="qm-due-date" class="qm-input">
            </div>
            <div class="qm-form-group">
                <label class="qm-label">Additional Notes / Instructions</label>
                <textarea id="qm-notes" class="qm-textarea" placeholder="Describe what items/services you need a quote for..."></textarea>
            </div>
        </div>
        <div class="quo-modal-footer">
            <button class="btn-qm-cancel" onclick="closeSendModal()">Cancel</button>
            <button class="btn-qm-submit" id="btn-send-submit" onclick="submitSendRequest()">Send Requests</button>
        </div>
    </div>
</div>

<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/quotation-detail.js"></script>
</body>
</html>