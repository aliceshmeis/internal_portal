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

$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$first_name = explode(' ', $user_name)[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/staff-dashboard.css">
</head>
<body>
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <div class="page-wrapper">
        <!-- Minimal Staff Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">LIU</div>
                <div class="sidebar-title">Internal Portal</div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Main</div>
                    <a href="staff-dashboard.php" class="sidebar-nav-item active">
                        <span class="sidebar-nav-icon icon-dashboard"></span>
                        <span class="sidebar-nav-text">Dashboard</span>
                    </a>
                    <a href="../tickets/my-tickets.php" class="sidebar-nav-item">
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
                    <a href="../stock/catalog.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon icon-stock"></span>
                        <span class="sidebar-nav-text">Stock Catalog</span>
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

        <!-- Main Content -->
        <main class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <div class="topbar-left">
                    <button class="hamburger-menu" id="hamburgerMenu">☰</button>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">Home</span>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Dashboard</span>
                    </div>
                </div>
                
                <div class="topbar-search">
                    <input type="text" placeholder="Search my tickets...">
                </div>
                
                <div class="topbar-right">
                    <button class="topbar-icon-btn" title="Notifications">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
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

            <!-- Page Content -->
            <div class="page-content">
                <!-- Action-Focused Header -->
                <div class="action-header">
                    <h1>What's the issue today?</h1>
                    <p>Select a category to quickly create a ticket</p>
                </div>

                <!-- BIG ACTION CARDS - Main Focus -->
                <div class="action-grid">
                    <div class="action-card-large" onclick="createTicket('IT & Software')">
                        <div class="card-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                                <path d="M8 21h8M12 17v4"></path>
                            </svg>
                        </div>
                        <div class="card-title">IT & Software</div>
                        <div class="card-description">Software issues, access problems</div>
                    </div>

                    <div class="action-card-large" onclick="createTicket('Printer Issue')">
                        <div class="card-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <rect x="6" y="14" width="12" height="8"></rect>
                            </svg>
                        </div>
                        <div class="card-title">Printer Issue</div>
                        <div class="card-description">Printing problems, paper jams</div>
                    </div>

                    <div class="action-card-large" onclick="createTicket('Network Problem')">
                        <div class="card-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="12" r="2"></circle>
                                <path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"></path>
                            </svg>
                        </div>
                        <div class="card-title">Network Problem</div>
                        <div class="card-description">WiFi, internet connectivity</div>
                    </div>

                    <div class="action-card-large" onclick="createTicket('Hardware Issue')">
                        <div class="card-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="2" y="7" width="20" height="14" rx="2"></rect>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                            </svg>
                        </div>
                        <div class="card-title">Hardware Issue</div>
                        <div class="card-description">Equipment malfunction, damage</div>
                    </div>

                    <div class="action-card-large" onclick="createTicket('Access Request')">
                        <div class="card-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <div class="card-title">Access Request</div>
                        <div class="card-description">System access, permissions</div>
                    </div>

                    <div class="action-card-large" onclick="createTicket('Item Request')">
                        <div class="card-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                        </div>
                        <div class="card-title">Item Request</div>
                        <div class="card-description">Request supplies, equipment</div>
                    </div>
                </div>

                <!-- My Open Tickets Section -->
                <div class="section-card">
                    <div class="section-header">
                        <h3>My Open Tickets</h3>
                        <a href="../tickets/my-tickets.php" class="section-link">View All →</a>
                    </div>
                    <div class="section-body" id="openTickets">
                        <div class="loading-small">Loading tickets...</div>
                    </div>
                </div>

                <!-- My Assets Section -->
                <div class="section-card">
                    <div class="section-header">
                        <h3>My Assigned Assets</h3>
                        <a href="../assets/my-assets.php" class="section-link">View All →</a>
                    </div>
                    <div class="section-body" id="myAssets">
                        <div class="loading-small">Loading assets...</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/internal_portal/public/js/mobile-menu.js"></script>
    <script src="/internal_portal/public/js/staff-dashboard.js"></script>
</body>
</html>