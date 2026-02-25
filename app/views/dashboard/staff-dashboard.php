<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] === 'Admin') { header('Location: /internal_portal/app/views/dashboard/dashboard.php'); exit; }
$user_name  = $_SESSION['name'];
$user_role  = $_SESSION['role'];
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
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="/internal_portal/public/images/liulogo.png" alt="LIU" style="height:36px;object-fit:contain;flex-shrink:0;">
            <img src="/internal_portal/public/images/Logo-Text.png" alt="LIU" style="height:22px;object-fit:contain;flex-shrink:0;">
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
                    <span class="sidebar-nav-text">My Requests</span>
                </a>
                <a href="../tickets/assigned.php" class="sidebar-nav-item">
                    <span class="sidebar-nav-icon icon-tickets"></span>
                    <span class="sidebar-nav-text">Assigned To Me</span>
                </a>
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
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger-menu" id="hamburgerMenu">☰</button>
                <div class="breadcrumb">
                    <span class="breadcrumb-item">Home</span>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active">Dashboard</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="header-user">
                    <div class="header-user-avatar"><?= strtoupper(substr($user_name,0,1)) ?></div>
                    <div class="header-user-info">
                        <div class="header-user-name"><?= htmlspecialchars($user_name) ?></div>
                        <div class="header-user-role"><?= htmlspecialchars($user_role) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">

            <div class="action-header">
                <h1>What's the issue today, <?= htmlspecialchars($first_name) ?>?</h1>
                <p>Create a ticket or check your assigned requests.</p>
            </div>

            <!-- Pending Banner -->
            <div class="action-banner" id="actionBanner">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span id="bannerText"></span>
                <a href="../tickets/my-tickets.php?status=Pending">View Pending →</a>
            </div>

            <!-- Counters -->
            <div class="counters-row">
                <div class="counter-card" onclick="goToTickets()" title="My Requests">
                    <div class="counter-dot blue"></div>
                    <div class="counter-info">
                        <div class="counter-value" id="counterMyRequests">—</div>
                        <div class="counter-label">My Requests</div>
                    </div>
                    <span class="counter-arrow">→</span>
                </div>
                <div class="counter-card" onclick="goToAssigned()" title="Assigned To Me">
                    <div class="counter-dot orange"></div>
                    <div class="counter-info">
                        <div class="counter-value" id="counterAssigned">—</div>
                        <div class="counter-label">Assigned To Me</div>
                    </div>
                    <span class="counter-arrow">→</span>
                </div>
                <div class="counter-card" onclick="goToTickets('Pending')" title="Pending My Reply">
                    <div class="counter-dot yellow"></div>
                    <div class="counter-info">
                        <div class="counter-value" id="counterPending">—</div>
                        <div class="counter-label">Pending My Reply</div>
                    </div>
                    <span class="counter-arrow">→</span>
                </div>
            </div>

            <!-- Category Cards -->
            <div class="section-title">Submit a Request</div>
            <div class="action-grid">
                <div class="action-card-large" onclick="createTicket('IT & Systems')">
                    <div class="card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"></rect><path d="M8 21h8M12 17v4"></path></svg></div>
                    <div class="card-title">IT & Systems</div>
                    <div class="card-description">Login, email, network, hardware</div>
                </div>
                <div class="action-card-large" onclick="createTicket('Registrar Services')">
                    <div class="card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg></div>
                    <div class="card-title">Registrar</div>
                    <div class="card-description">Enrollment, transcripts, records</div>
                </div>
                <div class="action-card-large" onclick="createTicket('Human Resources')">
                    <div class="card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></div>
                    <div class="card-title">Human Resources</div>
                    <div class="card-description">Payroll, leave, HR documents</div>
                </div>
                <div class="action-card-large" onclick="createTicket('Finance')">
                    <div class="card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg></div>
                    <div class="card-title">Finance</div>
                    <div class="card-description">Tuition, invoices, refunds</div>
                </div>
                <div class="action-card-large" onclick="createTicket('Academic Request')">
                    <div class="card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg></div>
                    <div class="card-title">Academic Request</div>
                    <div class="card-description">Course override, grade, approvals</div>
                </div>
                <div class="action-card-large" onclick="createTicket('Facilities')">
                    <div class="card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg></div>
                    <div class="card-title">Facilities</div>
                    <div class="card-description">Classroom, AC, electrical</div>
                </div>
                <div class="action-card-large" onclick="createTicket('Library')">
                    <div class="card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg></div>
                    <div class="card-title">Library</div>
                    <div class="card-description">Borrowing, resources, account</div>
                </div>
                <div class="action-card-large" onclick="createTicket('Student Affairs')">
                    <div class="card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></div>
                    <div class="card-title">Student Affairs</div>
                    <div class="card-description">Activities, student ID, support</div>
                </div>
            </div>

            <!-- Two Tables -->
            <div class="tables-grid">
                <div class="table-section">
                    <div class="table-section-header">
                        <span class="table-section-title">🟧 Assigned To Me</span>
                        <a href="../tickets/assigned.php" class="table-view-all">View All →</a>
                    </div>
                    <div class="table-section-body">
                        <div id="assignedLoading" class="mini-table-empty">Loading...</div>
                        <div id="assignedEmpty" class="mini-table-empty" style="display:none;">No tickets assigned to you.</div>
                        <table class="mini-table" id="assignedTable" style="display:none;">
                            <thead><tr><th>Ticket</th><th>Subject</th><th>Status</th><th>From</th></tr></thead>
                            <tbody id="assignedTbody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="table-section">
                    <div class="table-section-header">
                        <span class="table-section-title">🟦 My Recent Requests</span>
                        <a href="../tickets/my-tickets.php" class="table-view-all">View All →</a>
                    </div>
                    <div class="table-section-body">
                        <div id="requestsLoading" class="mini-table-empty">Loading...</div>
                        <div id="requestsEmpty" class="mini-table-empty" style="display:none;">No tickets submitted yet.</div>
                        <table class="mini-table" id="requestsTable" style="display:none;">
                            <thead><tr><th>Ticket</th><th>Subject</th><th>Status</th><th>Updated</th></tr></thead>
                            <tbody id="requestsTbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/staff-dashboard.js"></script>
</body>
</html>