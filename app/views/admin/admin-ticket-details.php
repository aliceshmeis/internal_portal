<?php
session_start();
require_once __DIR__ . '/../core/Auth.php';

// Auth check
if (!Auth::check()) {
    header('Location: /internal_portal/login.php');
    exit;
}

// Admin only
if (!Auth::isAdmin()) {
    header('Location: /internal_portal/staff-dashboard.php');
    exit;
}

// Get ticket ID from URL
$ticket_id = $_GET['id'] ?? null;
if (!$ticket_id) {
    header('Location: /internal_portal/admin-tickets.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Details - Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/admin-ticket-details.css">
</head>
<body>
    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-left">
            <h1>Internal Portal</h1>
        </div>
        <div class="topbar-right">
            <span class="user-name"><?= htmlspecialchars(Auth::userName()) ?></span>
            <span class="user-role">Admin</span>
            <button onclick="logout()" class="btn-logout">Logout</button>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <a href="/internal_portal/admin-dashboard.php" class="nav-item">
                <svg class="icon" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="/internal_portal/admin-tickets.php" class="nav-item active">
                <svg class="icon" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zM6 20V4h7v5h5v11H6z"/></svg>
                <span>Tickets</span>
            </a>
            <a href="/internal_portal/admin-assets.php" class="nav-item">
                <svg class="icon" viewBox="0 0 24 24"><path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z"/></svg>
                <span>Assets</span>
            </a>
            <a href="/internal_portal/admin-users.php" class="nav-item">
                <svg class="icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                <span>Users</span>
            </a>
        </div>

        <!-- Content Area -->
        <div class="content">
            <!-- Back Button -->
            <div class="back-nav">
                <a href="/internal_portal/admin-tickets.php" class="btn-back">
                    <svg viewBox="0 0 24 24" width="20" height="20">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                    </svg>
                    Back to Tickets
                </a>
            </div>

            <!-- Ticket Details Container -->
            <div class="ticket-container" id="ticketContainer">
                <div class="loading">Loading ticket details...</div>
            </div>
        </div>
    </div>

    <script src="/internal_portal/public/js/admin-ticket-details.js"></script>
</body>
</html>