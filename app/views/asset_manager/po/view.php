<?php
session_start();
if (!isset($_SESSION['user_id']))           { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] !== 'Asset Manager')  { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
$po_id     = intval($_GET['id'] ?? 0);
if (!$po_id) { header('Location: index.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PO Details – Asset Manager</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/asset-manager.css">
</head>
<body>
<div class="mobile-overlay" id="mobileOverlay"></div>
<div class="page-wrapper">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger-menu" id="hamburgerMenu">☰</button>
                <div class="breadcrumb">
                    <a href="../dashboard.php" class="breadcrumb-item">Dashboard</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="index.php" class="breadcrumb-item">Purchase Orders</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active">PO Details</span>
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
                <h1>Purchase Order Details</h1>
                <p>Full PO summary, items and actions</p>
            </div>

            <div class="detail-grid">
                <!-- LEFT -->
                <div>
                    <!-- Rejection reason alert (shown only if rejected) -->
                    <div id="rejectionAlert" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 18px;margin-bottom:16px;">
                        <div style="font-size:12px;font-weight:700;color:#dc2626;text-transform:uppercase;margin-bottom:4px;">❌ Rejected by Admin</div>
                        <div id="rejectionReason" style="font-size:13.5px;color:#7f1d1d;"></div>
                    </div>

                    <!-- Summary -->
                    <div class="am-card" style="margin-bottom:20px;">
                        <div class="am-card-header">
                            <div class="am-card-title" id="poNumber">Loading...</div>
                            <div id="poStatusBadge"></div>
                        </div>
                        <div style="padding:20px;" id="poSummary">
                            <div class="loading-state">Loading...</div>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="am-card">
                        <div class="am-card-header"><div class="am-card-title">Items</div></div>
                        <div style="overflow-x:auto;">
                            <table class="po-items-table" style="min-width:560px;">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Item</th>
                                        <th>Details</th>
                                        <th>Qty</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="poItemsTbody">
                                    <tr><td colspan="6"><div class="loading-state">Loading...</div></td></tr>
                                </tbody>
                                <tfoot>
                                    <tr class="po-total-row">
                                        <td colspan="5" style="text-align:right;padding:12px;">Total</td>
                                        <td id="poTotalDisplay">—</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Actions -->
                <div>
                    <div class="am-card">
                        <div class="am-card-header"><div class="am-card-title">Actions</div></div>
                        <div style="padding:16px;display:flex;flex-direction:column;gap:10px;" id="poActions">
                            <div class="loading-state">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script src="/internal_portal/public/js/asset-manager.js"></script>
<script>
const PO_ID = <?= $po_id ?>;

async function loadPODetail() {
    const res    = await fetch(`/internal_portal/api/v1/purchase-orders/show.php?id=${PO_ID}`, { credentials: 'include' });
    const result = await res.json();
    const p      = result.data;
    if (!p) return;

    document.getElementById('poNumber').textContent    = p.po_number;
    document.getElementById('poStatusBadge').innerHTML = poStatusBadge(p.status);

    // Rejection alert
    if (p.status === 'Rejected' && p.rejection_reason) {
        document.getElementById('rejectionAlert').style.display  = 'block';
        document.getElementById('rejectionReason').textContent   = p.rejection_reason;
    }

    document.getElementById('poSummary').innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div class="detail-field"><label>Supplier</label><span>${amEscape(p.supplier)}</span></div>
            <div class="detail-field"><label>Requested By</label><span>${amEscape(p.created_by_name)}</span></div>
            <div class="detail-field"><label>Campus</label><span>${amEscape(p.campus_name)}</span></div>
            <div class="detail-field"><label>Created</label><span>${amDate(p.created_at)}</span></div>
            ${p.approved_by_name ? `<div class="detail-field"><label>Approved By</label><span>${amEscape(p.approved_by_name)}</span></div>` : ''}
            ${p.approved_at     ? `<div class="detail-field"><label>Approved At</label><span>${amDate(p.approved_at)}</span></div>`         : ''}
            <div class="detail-field" style="grid-column:1/-1"><label>Notes</label><span>${amEscape(p.notes)}</span></div>
        </div>`;

    // Items
    const items = p.items || [];
    let total   = 0;
    document.getElementById('poItemsTbody').innerHTML = items.length
        ? items.map(i => {
            const sub = parseFloat(i.total_price) || (i.quantity * i.unit_price);
            total += sub;
            const typeBadge = i.item_type === 'stock'
                ? '<span class="badge badge-ok">Stock</span>'
                : '<span class="badge badge-inuse">Asset</span>';
            const details = i.item_type === 'asset'
                ? [i.asset_brand, i.asset_model, i.asset_category].filter(Boolean).join(' · ') || '—'
                : (i.stock_item_name || '—');
            return `<tr>
                <td>${typeBadge}</td>
                <td><strong>${amEscape(i.item_name)}</strong></td>
                <td style="color:#6b7280;font-size:12.5px;">${amEscape(details)}</td>
                <td>${i.quantity}</td>
                <td>${amCurrency(i.unit_price)}</td>
                <td>${amCurrency(sub)}</td>
            </tr>`;
        }).join('')
        : `<tr><td colspan="6"><div class="empty-state">No items</div></td></tr>`;

    document.getElementById('poTotalDisplay').textContent = amCurrency(total);

    // Actions
    let actions = `<a href="index.php" class="btn-secondary" style="width:100%;justify-content:center;">← Back to POs</a>`;

    if (p.status === 'Draft') {
        actions = `<button class="btn-primary" style="width:100%;justify-content:center;"
            onclick="submitPO(${PO_ID}, loadPODetail)">Submit for Approval</button>` + actions;
    }
    if (p.status === 'Rejected') {
        actions = `<button class="btn-primary" style="width:100%;justify-content:center;"
            onclick="submitPO(${PO_ID}, loadPODetail)">Resubmit for Approval</button>` + actions;
    }
    if (p.status === 'Approved') {
        actions = `<button class="btn-primary" style="width:100%;justify-content:center;background:#16a34a;"
            onclick="receivePO(${PO_ID}, loadPODetail)">✓ Mark as Received</button>` + actions;
    }
    if (['Draft','Pending Approval','Rejected'].includes(p.status)) {
        actions = `<button class="btn-secondary" style="width:100%;justify-content:center;color:#dc2626;border-color:#fecaca;"
            onclick="cancelPO(${PO_ID}, loadPODetail)">Cancel PO</button>` + actions;
    }

    document.getElementById('poActions').innerHTML = actions;
}

loadPODetail();
</script>
</body>
</html>