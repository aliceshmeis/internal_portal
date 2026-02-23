// ============================================
// ASSET MANAGER JS — LIU Internal Portal
// ============================================

const AM_API = '/internal_portal/api/v1';

/* ─── HELPERS ─── */
function amEscape(text) {
    if (!text) return '—';
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}
function amDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' });
}
function amCurrency(val) {
    if (!val && val !== 0) return '—';
    return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits:2, maximumFractionDigits:2 });
}
function amToast(msg, type = 'success') {
    let t = document.getElementById('amToast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'amToast';
        t.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;padding:12px 20px;' +
            'border-radius:10px;font-size:13.5px;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,0.15);' +
            'transition:opacity 0.3s;font-family:inherit;';
        document.body.appendChild(t);
    }
    t.style.background = type === 'success' ? '#1a2a4a' : '#dc2626';
    t.style.color = '#fff';
    t.textContent = msg;
    t.style.opacity = '1';
    setTimeout(() => { t.style.opacity = '0'; }, 3200);
}
function openModal(id)  { document.getElementById(id)?.classList.add('active');    }
function closeModal(id) { document.getElementById(id)?.classList.remove('active'); }

/* ─── BADGES ─── */
function stockBadge(qty, min) {
    if (qty <= 0)   return '<span class="badge badge-critical">Out of Stock</span>';
    if (qty <= min) return '<span class="badge badge-low">Low</span>';
    return '<span class="badge badge-ok">OK</span>';
}
function assetStatusBadge(status) {
    const map = { 'Available':'badge-available','In Use':'badge-inuse','Maintenance':'badge-maintenance','Retired':'badge-retired' };
    return `<span class="badge ${map[status]||'badge-navy'}">${amEscape(status)}</span>`;
}
function poStatusBadge(status) {
    const map = {
        'Draft':'badge-draft','Pending Approval':'badge-pending',
        'Approved':'badge-approved','Completed':'badge-completed',
        'Rejected':'badge-rejected','Cancelled':'badge-cancelled'
    };
    return `<span class="badge ${map[status]||'badge-draft'}">${amEscape(status)}</span>`;
}

/* ═══════════════════════════════════════════
   DASHBOARD
═══════════════════════════════════════════ */
async function loadAMDashboard() {
    try {
        const res    = await fetch(`${AM_API}/asset_manager/dashboard.php`, { credentials:'include' });
        const result = await res.json();
        if (!result.success) return;
        const d = result.data;
        amSet('widgetLowStock',   d.low_stock_count ?? 0);
        amSet('widgetTotalStock', d.total_stock      ?? 0);
        amSet('widgetAssigned',   d.assigned_count   ?? 0);
        amSet('widgetOpenPOs',    d.open_po_count    ?? 0);
        renderLowStockAlerts(d.low_stock_items || []);
        renderPendingPOs(d.pending_pos || []);
    } catch(e) { console.error('Dashboard error:', e); }
}
function amSet(id, val) { const el = document.getElementById(id); if (el) el.textContent = val; }

function renderLowStockAlerts(items) {
    const el = document.getElementById('lowStockAlerts');
    if (!el) return;
    if (!items.length) {
        el.innerHTML = `<div class="alert-row"><div class="alert-row-name" style="color:#16a34a">✓ All stock levels OK</div></div>`;
        return;
    }
    el.innerHTML = items.map(s => `
        <div class="alert-row">
            <div>
                <div class="alert-row-name">${amEscape(s.item_name)}</div>
                <div class="alert-row-sub">Qty: <strong>${s.quantity}</strong> / Min: ${s.minimum_threshold}</div>
            </div>
            ${stockBadge(s.quantity, s.minimum_threshold)}
            <a href="/internal_portal/app/views/asset_manager/stock/edit.php?id=${s.id}" class="btn-primary btn-sm" style="margin-left:8px">Restock</a>
        </div>`).join('');
}
function renderPendingPOs(pos) {
    const el = document.getElementById('pendingPOs');
    if (!el) return;
    if (!pos.length) {
        el.innerHTML = `<div class="alert-row"><div class="alert-row-name" style="color:#6b7280">No pending approvals</div></div>`;
        return;
    }
    el.innerHTML = pos.map(p => `
        <div class="alert-row">
            <div>
                <div class="alert-row-name">${amEscape(p.po_number)}</div>
                <div class="alert-row-sub">${amEscape(p.supplier)} · ${amCurrency(p.total_amount)}</div>
            </div>
            <a href="/internal_portal/app/views/asset_manager/po/view.php?id=${p.id}" class="btn-secondary btn-sm">View</a>
        </div>`).join('');
}

/* ═══════════════════════════════════════════
   STOCK
═══════════════════════════════════════════ */
let amStock = [];

async function loadStock() {
    const tbody = document.getElementById('stockTbody');
    if (!tbody) return;
    try {
        const res    = await fetch(`${AM_API}/stock/list.php`, { credentials:'include' });
        const result = await res.json();
        amStock = result.data || [];
        renderStock();
    } catch(e) { console.error('Stock load error:', e); }
}

function renderStock() {
    const tbody  = document.getElementById('stockTbody');
    if (!tbody) return;
    const search = (document.getElementById('stockSearch')?.value || '').toLowerCase();
    const cat    = document.getElementById('stockCatFilter')?.value || '';
    const filtered = amStock.filter(s => {
        const matchSearch = !search || (s.item_name||'').toLowerCase().includes(search);
        const matchCat    = !cat    || s.category === cat;
        return matchSearch && matchCat;
    });
    if (!filtered.length) {
        tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><p>No stock items found</p></div></td></tr>`;
        return;
    }
    tbody.innerHTML = filtered.map(s => `
        <tr>
            <td><strong>${amEscape(s.item_name)}</strong></td>
            <td><span class="badge badge-navy">${amEscape(s.category)||'—'}</span></td>
            <td><strong>${s.quantity}</strong> ${amEscape(s.unit)}</td>
            <td>${s.minimum_threshold}</td>
            <td>${stockBadge(s.quantity, s.minimum_threshold)}</td>
            <td>
                <div class="action-btns">
                    <a href="edit.php?id=${s.id}" class="btn-secondary btn-sm">Edit</a>
                    <button class="btn-primary btn-sm" onclick="openAdjustModal(${s.id},'${amEscape(s.item_name)}',${s.quantity})">Adjust Qty</button>
                </div>
            </td>
        </tr>`).join('');
}

function openAdjustModal(id, name, qty) {
    document.getElementById('adjustStockId').value       = id;
    document.getElementById('adjustItemName').textContent   = name;
    document.getElementById('adjustCurrentQty').textContent = qty;
    document.getElementById('adjustQty').value   = '';
    document.getElementById('adjustNotes').value = '';
    openModal('adjustModal');
}

async function submitAdjust() {
    const id   = document.getElementById('adjustStockId').value;
    const type = document.getElementById('adjustType').value;
    const qty  = parseInt(document.getElementById('adjustQty').value);
    if (!qty || qty <= 0) { amToast('Enter a valid quantity', 'error'); return; }
    const item    = amStock.find(s => s.id == id);
    if (!item) return;
    const new_qty = type === 'increase' ? item.quantity + qty : Math.max(0, item.quantity - qty);
    try {
        const res    = await fetch(`${AM_API}/stock/update.php`, {
            method:'POST', credentials:'include',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id: parseInt(id), quantity: new_qty })
        });
        const result = await res.json();
        if (result.success) { amToast('Quantity updated'); closeModal('adjustModal'); loadStock(); }
        else amToast(result.message || 'Error', 'error');
    } catch(e) { amToast('Request failed', 'error'); }
}

async function submitStockForm(formId, isEdit) {
    const form = document.getElementById(formId);
    if (!form) return;
    const fd   = new FormData(form);
    const data = {
        item_name:         fd.get('item_name'),
        category:          fd.get('category'),
        quantity:          parseInt(fd.get('quantity')),
        minimum_threshold: parseInt(fd.get('minimum_threshold')),
        unit:              fd.get('unit') || 'units',
    };
    if (isEdit) data.id = parseInt(fd.get('id'));
    const url = isEdit ? `${AM_API}/stock/update.php` : `${AM_API}/stock/create.php`;
    try {
        const res    = await fetch(url, {
            method:'POST', credentials:'include',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            amToast(isEdit ? 'Stock item updated!' : 'Stock item created!');
            setTimeout(() => window.location.href = 'index.php', 1200);
        } else amToast(result.message || 'Error', 'error');
    } catch(e) { amToast('Request failed', 'error'); }
}

/* ═══════════════════════════════════════════
   INVENTORY (ASSETS)
═══════════════════════════════════════════ */
let amAssets = [];

async function loadInventory() {
    const tbody = document.getElementById('inventoryTbody');
    if (!tbody) return;
    try {
        const res    = await fetch(`${AM_API}/assets/list.php`, { credentials:'include' });
        const result = await res.json();
        amAssets = result.data || [];
        renderInventory();
    } catch(e) { console.error('Inventory load error:', e); }
}

function renderInventory() {
    const tbody  = document.getElementById('inventoryTbody');
    if (!tbody) return;
    const search = (document.getElementById('invSearch')?.value || '').toLowerCase();
    const status = document.getElementById('invStatusFilter')?.value || '';
    const filtered = amAssets.filter(a => {
        const matchSearch = !search ||
            (a.asset_tag||'').toLowerCase().includes(search) ||
            (a.serial_number||'').toLowerCase().includes(search) ||
            (a.assigned_user_name||'').toLowerCase().includes(search) ||
            (a.name||'').toLowerCase().includes(search);
        const matchStatus = !status || a.status === status;
        return matchSearch && matchStatus;
    });
    if (!filtered.length) {
        tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state"><p>No assets found</p></div></td></tr>`;
        return;
    }
    tbody.innerHTML = filtered.map(a => `
        <tr>
            <td><span class="badge badge-navy">${amEscape(a.asset_tag)}</span></td>
            <td>${amEscape(a.category)}</td>
            <td><strong>${amEscape(a.name)}</strong></td>
            <td>${amEscape(a.serial_number)}</td>
            <td>${assetStatusBadge(a.status)}</td>
            <td>${a.assigned_user_name ? amEscape(a.assigned_user_name) : '<span style="color:#9ca3af">Unassigned</span>'}</td>
            <td>${amEscape(a.campus_name)}</td>
            <td>
                <div class="action-btns">
                    <a href="view.php?id=${a.id}" class="btn-secondary btn-sm">View</a>
                    <a href="edit.php?id=${a.id}" class="btn-secondary btn-sm">Edit</a>
                    ${a.status === 'Available'
                        ? `<a href="assign.php?id=${a.id}" class="btn-primary btn-sm">Assign</a>`
                        : a.assigned_to
                        ? `<button class="btn-secondary btn-sm" onclick="returnAsset(${a.id})">Return</button>`
                        : ''}
                </div>
            </td>
        </tr>`).join('');
}

async function returnAsset(id) {
    if (!confirm('Unassign this asset and mark it as Available?')) return;
    try {
        const res    = await fetch(`${AM_API}/assets/return.php`, {
            method:'POST', credentials:'include',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ asset_id: id })
        });
        const result = await res.json();
        if (result.success) { amToast('Asset returned successfully'); loadInventory(); }
        else amToast(result.message || 'Error', 'error');
    } catch(e) { amToast('Request failed', 'error'); }
}

async function submitAssetForm() {
    const form = document.getElementById('assetForm');
    if (!form) return;
    const fd   = new FormData(form);
    const data = {
        name:            fd.get('name'),
        category:        fd.get('category'),
        serial_number:   fd.get('serial_number')   || null,
        purchase_date:   fd.get('purchase_date')   || null,
        purchase_cost:   fd.get('purchase_cost')   || null,
        warranty_expiry: fd.get('warranty_expiry') || null,
        status:          fd.get('status')          || 'Available',
        campus_id:       fd.get('campus_id')       || null,
        building:        fd.get('building')        || null,
        floor:           fd.get('floor')           || null,
        room:            fd.get('room')            || null,
        description:     fd.get('description')     || null,
    };
    try {
        const res    = await fetch(`${AM_API}/assets/create.php`, {
            method:'POST', credentials:'include',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) { amToast('Asset created!'); setTimeout(() => window.location.href = 'index.php', 1200); }
        else amToast(result.message || 'Error', 'error');
    } catch(e) { amToast('Request failed', 'error'); }
}

async function submitAssignForm() {
    const assetId = document.getElementById('assignAssetId')?.value;
    const userId  = document.getElementById('assignUserId')?.value;
    if (!userId) { amToast('Please select an employee', 'error'); return; }
    try {
        const res    = await fetch(`${AM_API}/assets/assign.php`, {
            method:'POST', credentials:'include',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id: parseInt(assetId), user_id: parseInt(userId) })
        });
        const result = await res.json();
        if (result.success) { amToast('Asset assigned!'); setTimeout(() => window.location.href = `view.php?id=${assetId}`, 1200); }
        else amToast(result.message || 'Error', 'error');
    } catch(e) { amToast('Request failed', 'error'); }
}

/* ═══════════════════════════════════════════
   PURCHASE ORDERS — LIST
═══════════════════════════════════════════ */
let amPOs = [];

async function loadPOs() {
    const tbody = document.getElementById('poTbody');
    if (!tbody) return;
    try {
        const res    = await fetch(`${AM_API}/purchase-orders/list.php`, { credentials:'include' });
        const result = await res.json();
        amPOs = result.data || [];
        renderPOs();
    } catch(e) { console.error('PO load error:', e); }
}

function renderPOs() {
    const tbody  = document.getElementById('poTbody');
    if (!tbody) return;
    const search = (document.getElementById('poSearch')?.value || '').toLowerCase();
    const status = document.getElementById('poStatusFilter')?.value || '';
    const filtered = amPOs.filter(p => {
        const matchSearch = !search ||
            (p.po_number||'').toLowerCase().includes(search) ||
            (p.supplier||'').toLowerCase().includes(search);
        const matchStatus = !status || p.status === status;
        return matchSearch && matchStatus;
    });
    if (!filtered.length) {
        tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><p>No purchase orders found</p></div></td></tr>`;
        return;
    }
    tbody.innerHTML = filtered.map(p => `
        <tr>
            <td><strong>${amEscape(p.po_number)}</strong></td>
            <td>${amEscape(p.supplier)}</td>
            <td>${p.items_count || 0}</td>
            <td>${amCurrency(p.total_amount)}</td>
            <td>${poStatusBadge(p.status)}</td>
            <td>${amDate(p.created_at)}</td>
            <td>
                <div class="action-btns">
                    <a href="view.php?id=${p.id}" class="btn-secondary btn-sm">View</a>
                    ${p.status === 'Draft' || p.status === 'Rejected'
                        ? `<button class="btn-primary btn-sm" onclick="submitPO(${p.id})">Submit</button>`
                        : ''}
                    ${p.status === 'Approved'
                        ? `<button class="btn-primary btn-sm" style="background:#16a34a;" onclick="receivePO(${p.id})">Receive</button>`
                        : ''}
                </div>
            </td>
        </tr>`).join('');
}

async function submitPO(id, callback) {
    if (!confirm('Submit this PO for admin approval?')) return;
    try {
        const res    = await fetch(`${AM_API}/purchase-orders/submit.php`, {
            method:'POST', credentials:'include',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id })
        });
        const result = await res.json();
        if (result.success) {
            amToast('PO submitted for approval');
            callback ? callback() : loadPOs();
        } else amToast(result.message || 'Error', 'error');
    } catch(e) { amToast('Request failed', 'error'); }
}

async function receivePO(id, callback) {
    if (!confirm('Mark as received? Stock will be updated and assets will be created automatically.')) return;
    try {
        const res    = await fetch(`${AM_API}/purchase-orders/receive.php`, {
            method:'POST', credentials:'include',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id })
        });
        const result = await res.json();
        if (result.success) {
            amToast('PO received — stock & assets updated!');
            callback ? callback() : loadPOs();
        } else amToast(result.message || 'Error', 'error');
    } catch(e) { amToast('Request failed', 'error'); }
}

async function cancelPO(id, callback) {
    const reason = prompt('Reason for cancellation (required):');
    if (!reason) return;
    try {
        const res    = await fetch(`${AM_API}/purchase-orders/cancel.php`, {
            method:'POST', credentials:'include',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id, reason })
        });
        const result = await res.json();
        if (result.success) {
            amToast('PO cancelled');
            callback ? callback() : loadPOs();
        } else amToast(result.message || 'Error', 'error');
    } catch(e) { amToast('Request failed', 'error'); }
}

/* ═══════════════════════════════════════════
   PURCHASE ORDERS — CREATE (dynamic rows)
═══════════════════════════════════════════ */
let poRowCount  = 0;
let stockOptions = []; // populated by loadStockOptions()

async function loadStockOptions() {
    try {
        const res    = await fetch(`${AM_API}/stock/list.php`, { credentials:'include' });
        const result = await res.json();
        stockOptions = result.data || [];
    } catch(e) {}
}

function buildStockSelect(name) {
    const opts = stockOptions.map(s =>
        `<option value="${s.id}" data-name="${amEscape(s.item_name)}">${amEscape(s.item_name)} (${s.quantity} ${s.unit})</option>`
    ).join('');
    return `<select name="${name}" required onchange="syncItemName(this)">
                <option value="">Select stock item</option>
                ${opts}
            </select>`;
}

function syncItemName(select) {
    const opt      = select.options[select.selectedIndex];
    const card     = select.closest('.po-item-card');
    const nameInput = card?.querySelector('input[data-role="item_name"]');
    if (nameInput && opt) nameInput.value = opt.getAttribute('data-name') || '';
}

function addPOItem(type) {
    poRowCount++;
    const id       = poRowCount;
    const container = document.getElementById('poItemsContainer');
    const noMsg    = document.getElementById('noItemsMsg');
    if (noMsg) noMsg.style.display = 'none';

    const div = document.createElement('div');
    div.id    = `poRow${id}`;
    div.className = `po-item-card ${type}-card`;

    if (type === 'stock') {
        div.innerHTML = `
            <div class="po-item-header">
                <span class="po-item-type-label">📦 Stock Item</span>
                <button type="button" class="btn-remove" onclick="removePOItem(${id})">✕ Remove</button>
            </div>
            <div class="po-item-grid">
                <div style="grid-column:1/-1;">
                    <label>Stock Item <span style="color:#ef4444">*</span></label>
                    ${buildStockSelect(`rows[${id}][stock_id]`)}
                    <input type="hidden" name="rows[${id}][item_type]" value="stock">
                    <input type="hidden" name="rows[${id}][item_name]" data-role="item_name" value="">
                </div>
                <div>
                    <label>Quantity <span style="color:#ef4444">*</span></label>
                    <input type="number" name="rows[${id}][quantity]" min="1" value="1" required onchange="calcPOTotal()">
                </div>
                <div>
                    <label>Unit Price ($)</label>
                    <input type="number" name="rows[${id}][unit_price]" min="0" step="0.01" placeholder="0.00" onchange="calcPOTotal()">
                </div>
                <div>
                    <label>Subtotal</label>
                    <input type="text" data-role="subtotal" readonly style="background:#f3f4f6;color:#6b7280;" value="$0.00">
                </div>
                <div class="full">
                    <label>Notes</label>
                    <input type="text" name="rows[${id}][notes]" placeholder="Optional notes">
                </div>
            </div>`;
    } else {
        div.innerHTML = `
            <div class="po-item-header">
                <span class="po-item-type-label">🖥 Asset Item</span>
                <button type="button" class="btn-remove" onclick="removePOItem(${id})">✕ Remove</button>
            </div>
            <div class="po-item-grid">
                <div>
                    <label>Item Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="rows[${id}][item_name]" data-role="item_name" required placeholder="e.g. Dell Laptop">
                    <input type="hidden" name="rows[${id}][item_type]" value="asset">
                    <input type="hidden" name="rows[${id}][stock_id]" value="">
                </div>
                <div>
                    <label>Category <span style="color:#ef4444">*</span></label>
                    <select name="rows[${id}][asset_category]" required>
                        <option value="">Select</option>
                        <option>Laptop</option>
                        <option>Printer</option>
                        <option>Network Equipment</option>
                        <option>Furniture</option>
                        <option>Other</option>
                    </select>
                </div>
                <div>
                    <label>Brand</label>
                    <input type="text" name="rows[${id}][asset_brand]" placeholder="e.g. Dell">
                </div>
                <div>
                    <label>Model</label>
                    <input type="text" name="rows[${id}][asset_model]" placeholder="e.g. Latitude 5520">
                </div>
                <div>
                    <label>Quantity <span style="color:#ef4444">*</span></label>
                    <input type="number" name="rows[${id}][quantity]" min="1" value="1" required onchange="calcPOTotal()">
                </div>
                <div>
                    <label>Unit Price ($)</label>
                    <input type="number" name="rows[${id}][unit_price]" min="0" step="0.01" placeholder="0.00" onchange="calcPOTotal()">
                </div>
                <div>
                    <label>Subtotal</label>
                    <input type="text" data-role="subtotal" readonly style="background:#f3f4f6;color:#6b7280;" value="$0.00">
                </div>
                <div class="full">
                    <label>Notes</label>
                    <input type="text" name="rows[${id}][notes]" placeholder="Optional notes">
                </div>
            </div>`;
    }

    container.appendChild(div);
    calcPOTotal();
}

function removePOItem(id) {
    document.getElementById(`poRow${id}`)?.remove();
    calcPOTotal();
    // Show empty message if no rows left
    if (!document.querySelector('.po-item-card')) {
        const noMsg = document.getElementById('noItemsMsg');
        if (noMsg) noMsg.style.display = 'block';
    }
}

function calcPOTotal() {
    let total = 0;
    document.querySelectorAll('.po-item-card').forEach(card => {
        const qty   = parseFloat(card.querySelector('input[name*="[quantity]"]')?.value)   || 0;
        const price = parseFloat(card.querySelector('input[name*="[unit_price]"]')?.value) || 0;
        const sub   = qty * price;
        const cell  = card.querySelector('[data-role="subtotal"]');
        if (cell) cell.value = '$' + sub.toFixed(2);
        total += sub;
    });
    const el = document.getElementById('poGrandTotal');
    if (el) el.textContent = '$' + total.toFixed(2);
}

async function submitPOForm(action) {
    const supplier = document.getElementById('poSupplier')?.value?.trim();
    if (!supplier) { amToast('Supplier name is required', 'error'); return; }

    const cards = document.querySelectorAll('.po-item-card');
    if (!cards.length) { amToast('Add at least one item', 'error'); return; }

    const items = [];
    let valid = true;
    cards.forEach(card => {
        const type       = card.querySelector('input[name*="[item_type]"]')?.value;
        const item_name  = card.querySelector('[data-role="item_name"]')?.value?.trim()
                        || card.querySelector('input[name*="[item_name]"]')?.value?.trim();
        const qty        = parseFloat(card.querySelector('input[name*="[quantity]"]')?.value)   || 0;
        const unit_price = parseFloat(card.querySelector('input[name*="[unit_price]"]')?.value) || 0;

        if (!item_name) { amToast('All items need a name', 'error'); valid = false; return; }
        if (qty <= 0)   { amToast('All items need a valid quantity', 'error'); valid = false; return; }

        const item = { item_type: type, item_name, quantity: qty, unit_price, notes: card.querySelector('input[name*="[notes]"]')?.value || null };

        if (type === 'stock') {
            const stock_id = card.querySelector('select[name*="[stock_id]"]')?.value;
            if (!stock_id) { amToast('Select a stock item for every stock row', 'error'); valid = false; return; }
            item.stock_id = parseInt(stock_id);
        } else {
            const cat = card.querySelector('select[name*="[asset_category]"]')?.value;
            if (!cat)  { amToast('Select a category for every asset row', 'error'); valid = false; return; }
            item.asset_category = cat;
            item.asset_brand    = card.querySelector('input[name*="[asset_brand]"]')?.value  || null;
            item.asset_model    = card.querySelector('input[name*="[asset_model]"]')?.value  || null;
        }
        items.push(item);
    });

    if (!valid) return;

    const data = {
        supplier:  supplier,
        notes:     document.getElementById('poNotes')?.value    || null,
        campus_id: document.getElementById('poCampus')?.value   || null,
        items,
    };

    try {
        const res    = await fetch(`${AM_API}/purchase-orders/create.php`, {
            method:'POST', credentials:'include',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (!result.success) { amToast(result.message || 'Error creating PO', 'error'); return; }

        const po_id = result.data.id;

        if (action === 'submit') {
            const res2    = await fetch(`${AM_API}/purchase-orders/submit.php`, {
                method:'POST', credentials:'include',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify({ id: po_id })
            });
            const result2 = await res2.json();
            if (!result2.success) { amToast('Created but failed to submit', 'error'); return; }
            amToast('PO submitted for approval!');
        } else {
            amToast('PO saved as draft!');
        }
        setTimeout(() => window.location.href = 'index.php', 1200);
    } catch(e) { amToast('Request failed', 'error'); }
}

/* ─── Campus & User helpers ─── */
async function loadCampusOptions(selectId) {
    try {
        const res    = await fetch(`${AM_API}/campuses/list.php`, { credentials:'include' });
        const result = await res.json();
        const sel    = document.getElementById(selectId);
        if (!sel) return;
        sel.innerHTML = '<option value="">Select campus</option>';
        (result.data || []).forEach(c => {
            sel.innerHTML += `<option value="${c.id}">${amEscape(c.campus_name)}</option>`;
        });
    } catch(e) {}
}
async function loadUserOptions(selectId) {
    try {
        const res    = await fetch(`${AM_API}/users/staff-list.php`, { credentials:'include' });
        const result = await res.json();
        const sel    = document.getElementById(selectId);
        if (!sel) return;
        sel.innerHTML = '<option value="">Select employee</option>';
        (result.data || []).forEach(u => {
            sel.innerHTML += `<option value="${u.id}">${amEscape(u.name)}${u.campus_name ? ' — ' + u.campus_name : ''}</option>`;
        });
    } catch(e) {}
}

/* ═══════════════════════════════════════════
   INIT
═══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    loadAMDashboard();

    loadStock();
    document.getElementById('stockSearch')?.addEventListener('input',    renderStock);
    document.getElementById('stockCatFilter')?.addEventListener('change', renderStock);

    loadInventory();
    document.getElementById('invSearch')?.addEventListener('input',       renderInventory);
    document.getElementById('invStatusFilter')?.addEventListener('change', renderInventory);

    loadPOs();
    document.getElementById('poSearch')?.addEventListener('input',        renderPOs);
    document.getElementById('poStatusFilter')?.addEventListener('change',  renderPOs);
});