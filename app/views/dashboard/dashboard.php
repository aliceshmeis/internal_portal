<?php
session_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: /internal_portal/app/views/auth/login.php');
    exit;
}

// Check if admin
if ($_SESSION['role'] !== 'Admin') {
    header('Location: /internal_portal/app/views/dashboard/staff-dashboard.php');
    exit;
}

$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
$user_email = $_SESSION['email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <style>
        /* Additional dashboard-specific styles */
        .stat-icon-blue { background: #dbeafe; color: #3b82f6; }
        .stat-icon-green { background: #dcfce7; color: #10b981; }
        .stat-icon-yellow { background: #fef3c7; color: #f59e0b; }
        .stat-icon-red { background: #fee2e2; color: #ef4444; }
        .stat-icon-purple { background: #f3e8ff; color: #a855f7; }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: var(--color-text-secondary);
        }
        
        .error-message {
            padding: 20px;
            background: var(--color-danger-bg);
            border: 1px solid var(--color-danger-border);
            border-radius: var(--radius-md);
            color: var(--color-danger);
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">IP</div>
                <div class="sidebar-title">Internal Portal</div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Main</div>
                    <a href="dashboard.php" class="sidebar-nav-item active">
                        <span class="sidebar-nav-icon">📊</span>
                        <span class="sidebar-nav-text">Dashboard</span>
                    </a>
                    <a href="../tickets/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon">🎫</span>
                        <span class="sidebar-nav-text">Tickets</span>
                    </a>
                    <a href="../assets/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon">💼</span>
                        <span class="sidebar-nav-text">Assets</span>
                    </a>
                </div>
                
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Management</div>
                    <a href="../purchase-orders/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon">📦</span>
                        <span class="sidebar-nav-text">Purchase Orders</span>
                    </a>
                    <a href="../stock/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon">📋</span>
                        <span class="sidebar-nav-text">Stock</span>
                    </a>
                </div>
                
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Settings</div>
                    <a href="../users/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon">👥</span>
                        <span class="sidebar-nav-text">Users</span>
                    </a>
                    <a href="../campuses/list.php" class="sidebar-nav-item">
                        <span class="sidebar-nav-icon">🏢</span>
                        <span class="sidebar-nav-text">Campuses</span>
                    </a>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <a href="/internal_portal/api/v1/auth/logout.php" class="sidebar-nav-item">
                    <span class="sidebar-nav-icon">🚪</span>
                    <span class="sidebar-nav-text">Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h1 class="header-title">Dashboard</h1>
                </div>
                
                <div class="header-right">
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
            </header>

            <!-- Page Content -->
            <div class="page-content">
                <!-- Loading State -->
                <div id="loading" class="loading">
                    <p>Loading dashboard data...</p>
                </div>

                <!-- Error State -->
                <div id="error" class="error-message" style="display: none;"></div>

                <!-- Stats Grid -->
                <div id="stats-grid" class="stats-grid" style="display: none;">
                    <!-- Tickets Card -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Total Tickets</span>
                            <div class="stat-card-icon stat-icon-blue">🎫</div>
                        </div>
                        <div class="stat-card-value" id="total-tickets">0</div>
                        <div class="stat-card-change" id="tickets-breakdown"></div>
                    </div>
                    
                    <!-- Assets Card -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Total Assets</span>
                            <div class="stat-card-icon stat-icon-green">💼</div>
                        </div>
                        <div class="stat-card-value" id="total-assets">0</div>
                        <div class="stat-card-change" id="assets-breakdown"></div>
                    </div>
                    
                    <!-- Pending POs Card -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Pending POs</span>
                            <div class="stat-card-icon stat-icon-yellow">📦</div>
                        </div>
                        <div class="stat-card-value" id="pending-pos">0</div>
                        <div class="stat-card-change" id="pos-amount"></div>
                    </div>
                    
                    <!-- Low Stock Items Card -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Low Stock Items</span>
                            <div class="stat-card-icon stat-icon-red">⚠️</div>
                        </div>
                        <div class="stat-card-value" id="low-stock-count">0</div>
                        <div class="stat-card-change" id="stock-status"></div>
                    </div>
                </div>

                <!-- Recent Tickets -->
                <div id="recent-tickets-card" class="card" style="display: none; margin-top: 32px;">
                    <div class="card-header">
                        <h2 class="card-title">Recent Tickets</h2>
                        <p class="card-subtitle">Latest support requests</p>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-tickets-tbody">
                                    <!-- Populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="../tickets/list.php" class="btn btn-secondary">View All Tickets</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // API Base URL
        const API_BASE = '/internal_portal/api/v1';

        // Load dashboard data
        async function loadDashboard() {
            try {
                const response = await fetch(`${API_BASE}/dashboard/stats.php`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include'
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Failed to load dashboard');
                }

                if (result.success && result.data) {
                    displayDashboard(result.data);
                } else {
                    throw new Error('Invalid response format');
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
                document.getElementById('loading').style.display = 'none';
                document.getElementById('error').style.display = 'block';
                document.getElementById('error').textContent = 'Failed to load dashboard data: ' + error.message;
            }
        }

        // Display dashboard data
        function displayDashboard(data) {
            // Hide loading
            document.getElementById('loading').style.display = 'none';
            
            // Show stats grid
            document.getElementById('stats-grid').style.display = 'grid';
            
            // Populate tickets stats
            if (data.tickets) {
                document.getElementById('total-tickets').textContent = data.tickets.total || 0;
                const ticketsBreakdown = `Open: ${data.tickets.by_status.Open || 0} | In Progress: ${data.tickets.by_status['In Progress'] || 0}`;
                document.getElementById('tickets-breakdown').textContent = ticketsBreakdown;
            }
            
            // Populate assets stats
            if (data.assets) {
                document.getElementById('total-assets').textContent = data.assets.total || 0;
                const assetsBreakdown = `Available: ${data.assets.by_status.Available || 0} | In Use: ${data.assets.by_status['In Use'] || 0}`;
                document.getElementById('assets-breakdown').textContent = assetsBreakdown;
            }
            
            // Populate PO stats
            if (data.purchase_orders) {
                document.getElementById('pending-pos').textContent = data.purchase_orders.by_status.Pending || 0;
                const amount = data.purchase_orders.pending_amount || 0;
                document.getElementById('pos-amount').textContent = `Total: $${amount.toFixed(2)}`;
            }
            
            // Populate stock stats
            if (data.stock) {
                document.getElementById('low-stock-count').textContent = data.stock.low_stock_count || 0;
                const outOfStock = data.stock.out_of_stock_count || 0;
                document.getElementById('stock-status').textContent = outOfStock > 0 ? `${outOfStock} items out of stock` : 'Stock levels good';
            }
            
            // Populate recent tickets
            if (data.recent_tickets && data.recent_tickets.length > 0) {
                document.getElementById('recent-tickets-card').style.display = 'block';
                displayRecentTickets(data.recent_tickets);
            }
        }

        // Display recent tickets
        function displayRecentTickets(tickets) {
            const tbody = document.getElementById('recent-tickets-tbody');
            tbody.innerHTML = '';
            
            tickets.slice(0, 5).forEach(ticket => {
                const row = document.createElement('tr');
                
                const priorityBadge = getPriorityBadge(ticket.priority);
                const statusBadge = getStatusBadge(ticket.status);
                const createdDate = new Date(ticket.created_at).toLocaleDateString();
                
                row.innerHTML = `
                    <td>#T-${ticket.ticket_id}</td>
                    <td>${escapeHtml(ticket.title)}</td>
                    <td>${priorityBadge}</td>
                    <td>${statusBadge}</td>
                    <td>${createdDate}</td>
                    <td>
                        <div class="table-actions">
                            <a href="../tickets/view.php?id=${ticket.ticket_id}" class="btn btn-sm btn-primary">View</a>
                        </div>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
        }

        // Helper: Get priority badge
        function getPriorityBadge(priority) {
            const badges = {
                'Low': '<span class="badge badge-info">Low</span>',
                'Medium': '<span class="badge badge-warning">Medium</span>',
                'High': '<span class="badge badge-danger">High</span>',
                'Critical': '<span class="badge badge-danger">Critical</span>'
            };
            return badges[priority] || priority;
        }

        // Helper: Get status badge
        function getStatusBadge(status) {
            const badges = {
                'Open': '<span class="badge badge-primary">Open</span>',
                'In Progress': '<span class="badge badge-warning">In Progress</span>',
                'Pending': '<span class="badge badge-warning">Pending</span>',
                'Resolved': '<span class="badge badge-success">Resolved</span>',
                'Closed': '<span class="badge badge-secondary">Closed</span>'
            };
            return badges[status] || status;
        }

        // Helper: Escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Load dashboard on page load
        document.addEventListener('DOMContentLoaded', loadDashboard);
    </script>
</body>
</html>