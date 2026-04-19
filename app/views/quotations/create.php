<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /internal_portal/app/views/auth/login.php'); exit; }
if ($_SESSION['role'] !== 'Admin') { header('Location: /internal_portal/app/views/dashboard/dashboard.php'); exit; }
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];

require_once __DIR__ . '/../../../config/database.php';
try {
    $db       = (new Database())->getConnection();
    $stmt     = $db->query("SELECT id, name, email FROM suppliers WHERE is_active = 1 ORDER BY name ASC");
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $suppliers = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Quotation — Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/main-style.css">
    <link rel="stylesheet" href="/internal_portal/public/css/admin-layout.css">
    <link rel="stylesheet" href="/internal_portal/public/css/quotations.css">
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
                    <span class="breadcrumb-item active">Add Quotation</span>
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
            <div class="page-header">
                <h1 class="page-title">Add Quotation</h1>
                <p class="page-subtitle">Record a supplier quotation received by email or in person</p>
            </div>

            <div style="max-width:680px;">
                <div class="quo-detail-card">
                    <div class="quo-detail-card-header">
                        <span class="quo-detail-card-title">Quotation Details</span>
                    </div>
                    <div class="quo-detail-card-body">

                        <div class="qm-form-group">
                            <label class="qm-label">Supplier <span>*</span></label>
                            <select id="c-supplier" class="qm-select">
                                <option value="">Select supplier...</option>
                                <?php foreach ($suppliers as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?> <?php echo $s['email'] ? '('.$s['email'].')' : ''; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($suppliers)): ?>
                            <div style="font-size:12px;color:#d97706;margin-top:5px;">No suppliers found. <a href="../suppliers/list.php" style="color:var(--color-primary);">Add one first →</a></div>
                            <?php endif; ?>
                        </div>

                        <div class="qm-form-group">
                            <label class="qm-label">Supplier's Quotation Number <span>*</span></label>
                            <input type="text" id="c-quo-number" class="qm-input" placeholder="e.g. QUO-2026-001">
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div class="qm-form-group">
                                <label class="qm-label">Quotation Date <span>*</span></label>
                                <input type="date" id="c-quo-date" class="qm-input">
                            </div>
                            <div class="qm-form-group">
                                <label class="qm-label">Valid Until <span>*</span></label>
                                <input type="date" id="c-valid-until" class="qm-input">
                            </div>
                        </div>

                        <div class="qm-form-group">
                            <label class="qm-label">Total Amount (USD) <span>*</span></label>
                            <input type="number" id="c-total" class="qm-input" placeholder="0.00" min="0" step="0.01">
                        </div>

                        <div class="qm-form-group">
                            <label class="qm-label">Notes</label>
                            <textarea id="c-notes" class="qm-textarea" placeholder="Optional notes about this quotation..."></textarea>
                        </div>

                        <div class="qm-form-group">
                            <label class="qm-label">Upload Quotation PDF (optional)</label>
                            <div class="quo-upload-area" onclick="document.getElementById('c-file-input').click()">
                                <div style="font-size:28px;">📄</div>
                                <div class="quo-upload-text">Click to upload PDF or image (max 10MB)</div>
                                <div id="c-file-name" style="font-size:12px;color:var(--color-primary);margin-top:6px;"></div>
                            </div>
                            <input type="file" id="c-file-input" class="quo-upload-input" accept=".pdf,.jpg,.jpeg,.png" onchange="onFileSelect()">
                        </div>

                        <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:10px;">
                            <a href="list.php" class="btn-qm-cancel" style="text-decoration:none;display:inline-flex;align-items:center;">Cancel</a>
                            <button class="btn-qm-submit" id="btn-create" onclick="createQuotation()">Create Quotation</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="/internal_portal/public/js/mobile-menu.js"></script>
<script>
const API_BASE = '/internal_portal/api/v1';

function onFileSelect() {
    const f = document.getElementById('c-file-input').files[0];
    document.getElementById('c-file-name').textContent = f ? f.name : '';
}

async function createQuotation() {
    const supplier_id      = document.getElementById('c-supplier').value;
    const quotation_number = document.getElementById('c-quo-number').value.trim();
    const quotation_date   = document.getElementById('c-quo-date').value;
    const valid_until      = document.getElementById('c-valid-until').value;
    const total_amount     = document.getElementById('c-total').value;
    const notes            = document.getElementById('c-notes').value.trim();

    if (!supplier_id)      { showToast('Please select a supplier', 'error'); return; }
    if (!quotation_number) { showToast('Quotation number is required', 'error'); return; }
    if (!quotation_date)   { showToast('Quotation date is required', 'error'); return; }
    if (!valid_until)      { showToast('Valid until date is required', 'error'); return; }
    if (!total_amount)     { showToast('Total amount is required', 'error'); return; }

    const btn = document.getElementById('btn-create');
    btn.disabled = true; btn.textContent = 'Creating...';

    try {
        // Step 1: Create quotation record
        const res  = await fetch(`${API_BASE}/quotations/create.php`, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ supplier_id, quotation_number, quotation_date, valid_until, total_amount, notes })
        });
        const data = await res.json();
        if (!data.success) { showToast(data.message || 'Failed', 'error'); return; }

        const quotation_id = data.data.id;

        // Step 2: Upload file if selected
        const fileInput = document.getElementById('c-file-input');
        if (fileInput.files.length) {
            const formData = new FormData();
            formData.append('quotation_id', quotation_id);
            formData.append('file', fileInput.files[0]);
            await fetch(`${API_BASE}/quotations/upload.php`, {
                method: 'POST', credentials: 'include', body: formData
            });
        }

        showToast('Quotation created successfully', 'success');
        setTimeout(() => { window.location.href = `detail.php?id=${quotation_id}`; }, 1200);
    } catch (e) {
        showToast('Network error', 'error');
    } finally {
        btn.disabled = false; btn.textContent = 'Create Quotation';
    }
}

function showToast(message, type = 'success') {
    const t = document.createElement('div');
    t.className = `toast toast-${type}`; t.textContent = message;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3000);
}
</script>
</body>
</html>