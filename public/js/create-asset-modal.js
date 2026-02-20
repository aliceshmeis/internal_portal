// =============================================
// CREATE ASSET MODAL
// Calls: POST /api/v1/assets/create.php
// =============================================

document.addEventListener('DOMContentLoaded', () => {
    injectCreateAssetModal();
});

function injectCreateAssetModal() {
    if (document.getElementById('createAssetModal')) return;

    const modal = document.createElement('div');
    modal.id = 'createAssetModal';
    modal.onclick = function(e) {
        if (e.target === modal) closeCreateAssetModal();
    };
    modal.innerHTML = `
        <div class="modal-container">

            <div class="modal-header">
                <div>
                    <h2 class="modal-title">Add New Asset</h2>
                    <p class="modal-subtitle">Register a new asset in the inventory</p>
                </div>
                <button class="modal-close" onclick="closeCreateAssetModal()">&#x2715;</button>
            </div>

            <div class="modal-body">
                <div class="modal-error" id="assetModalError" style="display:none;"></div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Asset Name <span class="required">*</span></label>
                        <input type="text" class="form-input" id="assetName" placeholder="e.g. HP LaserJet 1020">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select class="form-select" id="assetCategory">
                            <option value="">Select category</option>
                            <option value="Laptop">Laptop</option>
                            <option value="Printer">Printer</option>
                            <option value="Network Equipment">Network Equipment</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Serial Number</label>
                        <input type="text" class="form-input" id="assetSerial" placeholder="e.g. SN-PR-001">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="assetStatus">
                            <option value="Available" selected>Available</option>
                            <option value="In Use">In Use</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Retired">Retired</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" id="assetDescription" rows="2" placeholder="Optional description..."></textarea>
                </div>

                <!-- Location -->
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <div class="form-row-3">
                        <input type="text" class="form-input" id="assetBuilding" placeholder="Building / Block">
                        <input type="text" class="form-input" id="assetFloor"    placeholder="Floor">
                        <input type="text" class="form-input" id="assetRoom"     placeholder="Room No.">
                    </div>
                    <span class="form-hint">e.g. Block A · Floor 2 · Room 204</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Purchase Date</label>
                        <input type="date" class="form-input" id="assetPurchaseDate">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Purchase Cost ($)</label>
                        <input type="number" class="form-input" id="assetPurchaseCost" placeholder="0.00" min="0" step="0.01">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Warranty Expiry</label>
                        <input type="date" class="form-input" id="assetWarrantyExpiry">
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Assign To
                            <span class="admin-only-badge">Admin only</span>
                        </label>
                        <select class="form-select" id="assetAssignTo">
                            <option value="">Unassigned</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-modal-cancel" onclick="closeCreateAssetModal()">Cancel</button>
                <button class="btn-modal-submit" id="assetSubmitBtn" type="button" onclick="submitCreateAsset()">
                    <span id="assetSubmitText">Add Asset</span>
                    <span id="assetSubmitLoader" style="display:none;">Adding...</span>
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    loadUsersForAssetAssign();
}

function openCreateAssetModal() {
    if (!document.getElementById('createAssetModal')) injectCreateAssetModal();
    resetAssetForm();
    document.getElementById('createAssetModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('assetName')?.focus(), 100);
}

function closeCreateAssetModal() {
    document.getElementById('createAssetModal')?.classList.remove('active');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeCreateAssetModal();
});

async function loadUsersForAssetAssign() {
    try {
        const response = await fetch('/internal_portal/api/v1/users/list.php', { credentials: 'include' });
        const data = await response.json();
        if (data.success && data.data) {
            const select = document.getElementById('assetAssignTo');
            if (!select) return;
            data.data.forEach(user => {
                const opt = document.createElement('option');
                opt.value = user.id;
                opt.textContent = `${user.name} (${user.role})`;
                select.appendChild(opt);
            });
        }
    } catch (err) {
        console.error('Failed to load users:', err);
    }
}

async function submitCreateAsset() {
    const name           = document.getElementById('assetName').value.trim();
    const category       = document.getElementById('assetCategory').value;
    const serial         = document.getElementById('assetSerial').value.trim();
    const status         = document.getElementById('assetStatus').value;
    const description    = document.getElementById('assetDescription').value.trim();
    const building       = document.getElementById('assetBuilding').value.trim();
    const floor          = document.getElementById('assetFloor').value.trim();
    const room           = document.getElementById('assetRoom').value.trim();
    const purchaseDate   = document.getElementById('assetPurchaseDate').value;
    const purchaseCost   = document.getElementById('assetPurchaseCost').value;
    const warrantyExpiry = document.getElementById('assetWarrantyExpiry').value;
    const assignTo       = document.getElementById('assetAssignTo').value;

    if (!name)     { showAssetError('Asset name is required.');  document.getElementById('assetName').focus();     return; }
    if (!category) { showAssetError('Category is required.');    document.getElementById('assetCategory').focus(); return; }

    const body = { name, category, status };
    if (serial)         body.serial_number   = serial;
    if (description)    body.description     = description;
    if (building)       body.building        = building;
    if (floor)          body.floor           = floor;
    if (room)           body.room            = room;
    if (purchaseDate)   body.purchase_date   = purchaseDate;
    if (purchaseCost)   body.purchase_cost   = parseFloat(purchaseCost);
    if (warrantyExpiry) body.warranty_expiry = warrantyExpiry;
    if (assignTo)       body.assigned_to     = parseInt(assignTo);

    setAssetLoading(true);
    hideAssetError();

    try {
        const response = await fetch('/internal_portal/api/v1/assets/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(body)
        });
        const data = await response.json();

        if (data.success) {
            closeCreateAssetModal();
            showAssetToast('Asset added successfully!');
            setTimeout(() => loadAssets(), 400);
        } else {
            showAssetError(data.message || 'Failed to create asset.');
        }
    } catch (err) {
        console.error(err);
        showAssetError('Something went wrong. Please try again.');
    } finally {
        setAssetLoading(false);
    }
}

function resetAssetForm() {
    ['assetName','assetSerial','assetDescription','assetBuilding','assetFloor','assetRoom',
     'assetPurchaseDate','assetPurchaseCost','assetWarrantyExpiry']
        .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    document.getElementById('assetCategory').value = '';
    document.getElementById('assetStatus').value   = 'Available';
    document.getElementById('assetAssignTo').value = '';
    hideAssetError();
}

function showAssetError(msg) {
    const el = document.getElementById('assetModalError');
    if (el) { el.textContent = '⚠ ' + msg; el.style.display = 'block'; }
}

function hideAssetError() {
    const el = document.getElementById('assetModalError');
    if (el) el.style.display = 'none';
}

function setAssetLoading(loading) {
    document.getElementById('assetSubmitBtn').disabled         = loading;
    document.getElementById('assetSubmitText').style.display   = loading ? 'none'   : 'inline';
    document.getElementById('assetSubmitLoader').style.display = loading ? 'inline' : 'none';
}

function showAssetToast(message) {
    const old = document.getElementById('assetToast');
    if (old) old.remove();
    const toast = document.createElement('div');
    toast.id = 'assetToast';
    toast.className = 'success-toast';
    toast.textContent = '✅ ' + message;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('visible'), 10);
    setTimeout(() => { toast.classList.remove('visible'); setTimeout(() => toast.remove(), 300); }, 3000);
}