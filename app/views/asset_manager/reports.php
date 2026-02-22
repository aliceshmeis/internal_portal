<?php
session_start();
if (!isset($_SESSION['user_id']))           { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] !== 'Asset Manager')  { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports – Asset Manager</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/asset-manager.css">
</head>
<body>
<div class="mobile-overlay" id="mobileOverlay"></div>
<div class="page-wrapper">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger-menu" id="hamburgerMenu">☰</button>
                <div class="breadcrumb">
                    <a href="dashboard.php" class="breadcrumb-item">Dashboard</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active">Reports</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="header-user">
                    <div class="header-user-avatar"><?= strtoupper(substr($user_name, 0, 1)) ?></div>
                    <div class="header-user-info">
                        <div class="header-user-name"><?= htmlspecialchars($user_name) ?></div>
                        <div class="header-user-role"><?= htmlspecialchars($user_role) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <div class="page-hero">
                <h1>Reports</h1>
                <p>Stock usage and purchase order history</p>
            </div>

            <!-- Filters -->
            <div class="am-card" style="margin-bottom:20px;">
                <div class="am-card-header"><div class="am-card-title">Filters</div></div>
                <div class="controls-row" style="flex-wrap:wrap;gap:12px;">
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;">Date From</label>
                        <input type="date" id="rFrom" style="padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;">Date To</label>
                        <input type="date" id="rTo" style="padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;">PO Status</label>
                        <select id="rStatus" style="padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;">
                            <option value="">All Statuses</option>
                            <option>Draft</option>
                            <option>Pending Approval</option>
                            <option>Approved</option>
                            <option>Completed</option>
                            <option>Rejected</option>
                            <option>Cancelled</option>
                        </select>
                    </div>
                    <div style="align-self:flex-end;display:flex;gap:8px;">
                        <button class="btn-primary" onclick="loadReports()">Apply Filters</button>
                        <button class="btn-secondary" onclick="exportReport()">Export CSV</button>
                    </div>
                </div>
            </div>

            <!-- Report grid -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

                <!-- Stock Usage -->
                <div class="am-card">
                    <div class="am-card-header"><div class="am-card-title">Stock Overview</div></div>
                    <table class="am-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Total Items</th>
                                <th>Low Stock</th>
                            </tr>
                        </thead>
                        <tbody id="stockReportTbody">
                            <tr><td colspan="3"><div class="loading-state">Loading...</div></td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- PO History -->
                <div class="am-card">
                    <div class="am-card-header"><div class="am-card-title">PO History</div></div>
                    <table class="am-table">
                        <thead>
                            <tr>
                                <th>PO Number</th>
                                <th>Supplier</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="poReportTbody">
                            <tr><td colspan="5"><div class="loading-state">Loading...</div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/asset-manager.js"></script>
<script>
async function loadReports() {
    const from   = document.getElementById('rFrom').value;
    const to     = document.getElementById('rTo').value;
    const status = document.getElementById('rStatus').value;

    const params = new URLSearchParams();
    if (from)   params.append('from', from);
    if (to)     params.append('to', to);
    if (status) params.append('status', status);

    // Load stock list filtered
    const sRes    = await fetch(`/internal_portal/api/v1/stock/list.php?${params}`, { credentials: 'include' });
    const sResult = await sRes.json();
    const stocks  = sResult.data || [];

    // Group by category
    const cats = {};
    stocks.forEach(s => {
        if (!cats[s.category]) cats[s.category] = { total: 0, low: 0 };
        cats[s.category].total++;
        if (s.is_low_stock == 1) cats[s.category].low++;
    });
    const stockRows = Object.entries(cats);
    document.getElementById('stockReportTbody').innerHTML = stockRows.length
        ? stockRows.map(([cat, d]) => `
            <tr>
                <td><span class="badge badge-navy">${amEscape(cat) || 'Uncategorised'}</span></td>
                <td>${d.total}</td>
                <td>${d.low > 0 ? `<span class="badge badge-low">${d.low}</span>` : '<span style="color:#16a34a">0</span>'}</td>
            </tr>`).join('')
        : `<tr><td colspan="3"><div class="empty-state"><p>No data</p></div></td></tr>`;

    // Load POs
    const pRes    = await fetch(`/internal_portal/api/v1/purchase-orders/list.php?${params}`, { credentials: 'include' });
    const pResult = await pRes.json();
    const pos     = pResult.data || [];

    document.getElementById('poReportTbody').innerHTML = pos.length
        ? pos.map(p => `
            <tr>
                <td><a href="/internal_portal/app/views/asset_manager/po/view.php?id=${p.id}" style="color:#1a2a4a;font-weight:600;">${amEscape(p.po_number)}</a></td>
                <td>${amEscape(p.supplier)}</td>
                <td>${amCurrency(p.total_amount)}</td>
                <td>${poStatusBadge(p.status)}</td>
                <td>${amDate(p.created_at)}</td>
            </tr>`).join('')
        : `<tr><td colspan="5"><div class="empty-state"><p>No POs found</p></div></td></tr>`;
}

function exportReport() {
    const from   = document.getElementById('rFrom').value;
    const to     = document.getElementById('rTo').value;
    const status = document.getElementById('rStatus').value;
    const params = new URLSearchParams();
    if (from)   params.append('from', from);
    if (to)     params.append('to', to);
    if (status) params.append('status', status);
    params.append('export', 'csv');
    window.location.href = `/internal_portal/api/v1/purchase-orders/list.php?${params}`;
}

loadReports();
</script>
</body>
</html>