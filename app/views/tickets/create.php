<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /internal_portal/app/views/auth/login.php');
    exit;
}

if ($_SESSION['role'] === 'Admin') {
    header('Location: /internal_portal/app/views/dashboard/dashboard.php');
    exit;
}

$user_name  = $_SESSION['name'];
$user_role  = $_SESSION['role'];
$first_name = explode(' ', $user_name)[0];

// Get category from URL
$category = $_GET['category'] ?? '';

// Category config
$categories = [
    'Printer Issue'    => ['icon' => '🖨️', 'subtitle' => 'Please provide details about the printing problem.'],
    'IT & Software'    => ['icon' => '💻', 'subtitle' => 'Describe the software or system issue you\'re experiencing.'],
    'Network Problem'  => ['icon' => '🌐', 'subtitle' => 'Tell us about your connectivity or network issue.'],
    'Hardware Issue'   => ['icon' => '🔧', 'subtitle' => 'Describe the hardware malfunction or damage.'],
    'Access Request'   => ['icon' => '🔑', 'subtitle' => 'Request access to a system or resource.'],
    'Item Request'     => ['icon' => '📦', 'subtitle' => 'Request supplies or equipment for your work.'],
];

$cat_info = $categories[$category] ?? ['icon' => '🎫', 'subtitle' => 'Describe your issue in detail.'];
$page_title = $category ? "Create Ticket – $category" : 'Create Ticket';
$title_suggestion = $category ? "$category – " : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/staff-dashboard.css">
    <link rel="stylesheet" href="/internal_portal/public/css/create-ticket-page.css">
</head>
<body>
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <div class="page-wrapper">
        <!-- Staff Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">LIU</div>
                <div class="sidebar-title">Internal Portal</div>
            </div>
            <nav class="sidebar-nav">
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Main</div>
                    <a href="staff-dashboard.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-dashboard"></span>
                        <span class="sidebar-nav-text">Dashboard</span>
                    </a>
                    <a href="../tickets/my-tickets.php" class="sidebar-nav-item active">
                        <span class="sidebar-nav-icon icon-tickets"></span>
                        <span class="sidebar-nav-text">My Tickets</span>
                    </a>
                </div>
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Assets</div>
                    <a href="../assets/my-assets.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-assets"></span>
                        <span class="sidebar-nav-text">My Assets</span>
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
            <!-- Topbar -->
            <div class="topbar">
                <div class="topbar-left">
                    <button class="hamburger-menu" id="hamburgerMenu">☰</button>
                    <div class="breadcrumb">
                        <a href="staff-dashboard.php" class="breadcrumb-item">Home</a>
                        <span class="breadcrumb-separator">/</span>
                        <a href="../tickets/my-tickets.php" class="breadcrumb-item">My Tickets</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Create Ticket</span>
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="header-user">
                        <div class="header-user-avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                        <div class="header-user-info">
                            <div class="header-user-name"><?php echo htmlspecialchars($user_name); ?></div>
                            <div class="header-user-role"><?php echo htmlspecialchars($user_role); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <div class="page-content">
                <div class="create-ticket-wrapper">

                    <!-- Page Header -->
                    <div class="ct-header">
                        <a href="staff-dashboard.php" class="ct-back">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 12H5M12 5l-7 7 7 7"/>
                            </svg>
                            Back
                        </a>
                        <div class="ct-title-row">
                            <span class="ct-category-icon"><?php echo $cat_info['icon']; ?></span>
                            <div>
                                <h1 class="ct-title"><?php echo htmlspecialchars($page_title); ?></h1>
                                <p class="ct-subtitle"><?php echo htmlspecialchars($cat_info['subtitle']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Error / Success -->
                    <div class="ct-error" id="ctError" style="display:none;"></div>
                    <div class="ct-success" id="ctSuccess" style="display:none;"></div>

                    <div class="ct-form-layout">

                        <!-- SECTION 1: Basic Information -->
                        <div class="ct-section">
                            <div class="ct-section-title">
                                <span class="ct-section-num">1</span>
                                Basic Information
                            </div>

                            <div class="ct-field">
                                <label class="ct-label">Ticket Title <span class="required">*</span></label>
                                <input type="text" class="ct-input" id="ticketTitle"
                                    value="<?php echo htmlspecialchars($title_suggestion); ?>"
                                    placeholder="Short description of the issue">
                                <span class="ct-hint">Be specific, e.g. "Printer Issue – 2nd Floor not printing"</span>
                            </div>

                            <div class="ct-field">
                                <label class="ct-label">Description <span class="required">*</span></label>
                                <textarea class="ct-textarea" id="ticketDescription" rows="4"
                                    placeholder="Describe the issue in detail. What happened? When did it start? What have you tried?"></textarea>
                            </div>

                            <div class="ct-field">
                                <label class="ct-label">Priority</label>
                                <div class="priority-group">
                                    <label class="priority-option">
                                        <input type="radio" name="priority" value="Low">
                                        <span class="priority-btn low">Low</span>
                                    </label>
                                    <label class="priority-option">
                                        <input type="radio" name="priority" value="Medium" checked>
                                        <span class="priority-btn medium">Medium</span>
                                    </label>
                                    <label class="priority-option">
                                        <input type="radio" name="priority" value="High">
                                        <span class="priority-btn high">High</span>
                                    </label>
                                    <label class="priority-option">
                                        <input type="radio" name="priority" value="Critical">
                                        <span class="priority-btn critical">Critical</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: Dynamic Fields per Category -->
                        <?php if ($category): ?>
                        <div class="ct-section">
                            <div class="ct-section-title">
                                <span class="ct-section-num">2</span>
                                <?php echo htmlspecialchars($category); ?> Details
                            </div>

                            <?php if ($category === 'Printer Issue'): ?>
                                <div class="ct-field">
                                    <label class="ct-label">Printer Name / Location</label>
                                    <select class="ct-select" id="printerName">
                                        <option value="">Select printer...</option>
                                    </select>
                                    <span class="ct-hint">Loading printers from assets...</span>
                                </div>
                                <div class="ct-field">
                                    <label class="ct-label">Issue Type</label>
                                    <select class="ct-select" id="printerIssueType">
                                        <option value="">Select issue type...</option>
                                        <option value="Paper Jam">Paper Jam</option>
                                        <option value="Not Printing">Not Printing</option>
                                        <option value="Poor Quality">Poor Print Quality</option>
                                        <option value="Offline">Printer Offline</option>
                                        <option value="No Ink/Toner">No Ink / Toner</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="ct-field">
                                    <label class="ct-checkbox-label">
                                        <input type="checkbox" id="printerUrgent">
                                        <span>This is urgent – the printer is needed immediately</span>
                                    </label>
                                </div>

                            <?php elseif ($category === 'IT & Software'): ?>
                                <div class="ct-field">
                                    <label class="ct-label">Software / System Name <span class="required">*</span></label>
                                    <input type="text" class="ct-input" id="softwareName" placeholder="e.g. Microsoft Office, ERP System, Email">
                                </div>
                                <div class="ct-field">
                                    <label class="ct-label">Error Message</label>
                                    <input type="text" class="ct-input" id="errorMessage" placeholder="Paste the exact error message if any">
                                </div>
                                <div class="ct-field">
                                    <label class="ct-label">What were you doing when it happened?</label>
                                    <input type="text" class="ct-input" id="whatHappened" placeholder="e.g. Trying to open a file, logging in...">
                                </div>

                            <?php elseif ($category === 'Network Problem'): ?>
                                <div class="ct-field">
                                    <label class="ct-label">Your Location / Room</label>
                                    <input type="text" class="ct-input" id="networkLocation" placeholder="e.g. Office 3B, 2nd Floor Meeting Room">
                                </div>
                                <div class="ct-field">
                                    <label class="ct-label">Connection Type</label>
                                    <div class="radio-group">
                                        <label class="radio-option">
                                            <input type="radio" name="connectionType" value="WiFi" checked>
                                            <span>📶 WiFi</span>
                                        </label>
                                        <label class="radio-option">
                                            <input type="radio" name="connectionType" value="Wired">
                                            <span>🔌 Wired (Ethernet)</span>
                                        </label>
                                        <label class="radio-option">
                                            <input type="radio" name="connectionType" value="Both">
                                            <span>Both</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="ct-field">
                                    <label class="ct-checkbox-label">
                                        <input type="checkbox" id="affectingOthers">
                                        <span>This issue is affecting other people too</span>
                                    </label>
                                </div>

                            <?php elseif ($category === 'Hardware Issue'): ?>
                                <div class="ct-field">
                                    <label class="ct-label">Device / Equipment</label>
                                    <select class="ct-select" id="hardwareAsset">
                                        <option value="">Select your device...</option>
                                    </select>
                                    <span class="ct-hint">Loading your assigned assets...</span>
                                </div>
                                <div class="ct-field">
                                    <label class="ct-label">Issue Type</label>
                                    <select class="ct-select" id="hardwareIssueType">
                                        <option value="">Select issue...</option>
                                        <option value="Not turning on">Not turning on</option>
                                        <option value="Screen damage">Screen damage</option>
                                        <option value="Keyboard / Mouse">Keyboard / Mouse issue</option>
                                        <option value="Battery issue">Battery issue</option>
                                        <option value="Physical damage">Physical damage</option>
                                        <option value="Overheating">Overheating</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                            <?php elseif ($category === 'Access Request'): ?>
                                <div class="ct-field">
                                    <label class="ct-label">System / Resource Name <span class="required">*</span></label>
                                    <input type="text" class="ct-input" id="systemName" placeholder="e.g. HR System, File Server, VPN">
                                </div>
                                <div class="ct-field">
                                    <label class="ct-label">Level of Access Needed</label>
                                    <select class="ct-select" id="accessLevel">
                                        <option value="">Select level...</option>
                                        <option value="Read only">Read Only</option>
                                        <option value="Read & Write">Read & Write</option>
                                        <option value="Full Access">Full Access</option>
                                        <option value="Admin">Admin Access</option>
                                    </select>
                                </div>
                                <div class="ct-field">
                                    <label class="ct-label">Reason for Access</label>
                                    <textarea class="ct-textarea" id="accessReason" rows="2" placeholder="Why do you need this access?"></textarea>
                                </div>
                                <div class="ct-field">
                                    <label class="ct-checkbox-label">
                                        <input type="checkbox" id="managerApproved">
                                        <span>Manager approval has been obtained</span>
                                    </label>
                                </div>

                            <?php elseif ($category === 'Item Request'): ?>
                                <div class="ct-field">
                                    <label class="ct-label">Item Name <span class="required">*</span></label>
                                    <input type="text" class="ct-input" id="itemName" placeholder="e.g. Notebook, USB Drive, HDMI Cable">
                                </div>
                                <div class="ct-field">
                                    <label class="ct-label">Quantity</label>
                                    <input type="number" class="ct-input" id="itemQuantity" placeholder="1" min="1" value="1" style="max-width:120px;">
                                </div>
                                <div class="ct-field">
                                    <label class="ct-label">Reason for Request</label>
                                    <textarea class="ct-textarea" id="itemReason" rows="2" placeholder="Why do you need this item?"></textarea>
                                </div>
                            <?php endif; ?>

                        </div>
                        <?php endif; ?>

                    </div>

                    <!-- Submit -->
                    <div class="ct-footer">
                        <a href="staff-dashboard.php" class="ct-btn-cancel">Cancel</a>
                        <button class="ct-btn-submit" id="submitBtn" onclick="submitTicket()">
                            <span id="submitText">Submit Ticket</span>
                            <span id="submitLoader" style="display:none;">Submitting...</span>
                        </button>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script src="/internal_portal/public/js/mobile-menu.js"></script>
    <script>
        const TICKET_CATEGORY = <?php echo json_encode($category); ?>;
    </script>
    <script src="/internal_portal/public/js/create-ticket-page.js"></script>
</body>
</html>