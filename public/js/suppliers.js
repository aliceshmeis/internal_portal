// suppliers.js
const API_BASE = '/internal_portal/api/v1';

let allSuppliers = [];

document.addEventListener('DOMContentLoaded', () => {
    loadSuppliers();
});

// ─── LOAD ─────────────────────────────────────────────────────────────────────
async function loadSuppliers() {
    try {
        const res    = await fetch(`${API_BASE}/suppliers/list.php`, { credentials: 'include' });
        const result = await res.json();
        if (result.success) {
            allSuppliers = result.data || [];
            renderSuppliers(allSuppliers);
        } else {
            showError(result.message || 'Failed to load suppliers');
        }
    } catch (e) { showError('Network error'); }
}

function renderSuppliers(suppliers) {
    document.getElementById('loading').style.display = 'none';
    const tbody = document.getElementById('supplier-tbody');
    const empty = document.getElementById('empty-state');

    if (!suppliers.length) {
        document.getElementById('sup-table-wrap').style.display = 'none';
        empty.style.display = 'block';
        return;
    }

    document.getElementById('sup-table-wrap').style.display = 'block';
    empty.style.display = 'none';
    document.getElementById('sup-count').textContent = suppliers.length;

    tbody.innerHTML = suppliers.map(s => `
        <tr>
            <td class="quo-supplier">${escapeHtml(s.name)}</td>
            <td>${s.email ? `<a href="mailto:${escapeHtml(s.email)}" style="color:var(--color-primary);">${escapeHtml(s.email)}</a>` : '—'}</td>
            <td>${escapeHtml(s.phone || '—')}</td>
            <td style="font-size:12.5px;color:var(--color-text-secondary);">${escapeHtml(s.address || '—')}</td>
            <td>
                <div style="display:flex;gap:6px;">
                    <button class="quo-action-btn" onclick="openEditModal(${s.id})">Edit</button>
                    <button class="quo-action-btn danger" onclick="deactivateSupplier(${s.id}, '${escapeHtml(s.name)}')">Remove</button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ─── SEARCH ───────────────────────────────────────────────────────────────────
function filterSuppliers() {
    const q = document.getElementById('sup-search').value.toLowerCase();
    const filtered = allSuppliers.filter(s =>
        s.name.toLowerCase().includes(q) ||
        (s.email || '').toLowerCase().includes(q)
    );
    renderSuppliers(filtered);
}

// ─── ADD MODAL ────────────────────────────────────────────────────────────────
function openAddModal() {
    document.getElementById('modal-title').textContent = 'Add Supplier';
    document.getElementById('sup-form-id').value    = '';
    document.getElementById('sup-form-name').value  = '';
    document.getElementById('sup-form-email').value = '';
    document.getElementById('sup-form-phone').value = '';
    document.getElementById('sup-form-addr').value  = '';
    document.getElementById('sup-modal-overlay').classList.add('open');
}

function openEditModal(id) {
    const s = allSuppliers.find(s => s.id == id);
    if (!s) return;
    document.getElementById('modal-title').textContent = 'Edit Supplier';
    document.getElementById('sup-form-id').value    = s.id;
    document.getElementById('sup-form-name').value  = s.name;
    document.getElementById('sup-form-email').value = s.email  || '';
    document.getElementById('sup-form-phone').value = s.phone  || '';
    document.getElementById('sup-form-addr').value  = s.address || '';
    document.getElementById('sup-modal-overlay').classList.add('open');
}

function closeSupModal() {
    document.getElementById('sup-modal-overlay').classList.remove('open');
}

async function saveSupplier() {
    const id    = document.getElementById('sup-form-id').value;
    const name  = document.getElementById('sup-form-name').value.trim();
    const email = document.getElementById('sup-form-email').value.trim();
    const phone = document.getElementById('sup-form-phone').value.trim();
    const addr  = document.getElementById('sup-form-addr').value.trim();

    if (!name) { showToast('Name is required', 'error'); return; }

    const btn = document.getElementById('btn-save-sup');
    btn.disabled = true; btn.textContent = 'Saving...';

    const url  = id ? `${API_BASE}/suppliers/update.php` : `${API_BASE}/suppliers/create.php`;
    const body = id ? { id, name, email, phone, address: addr }
                    : {     name, email, phone, address: addr };

    try {
        const res  = await fetch(url, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
        const data = await res.json();
        if (data.success) {
            showToast(id ? 'Supplier updated' : 'Supplier added', 'success');
            closeSupModal();
            loadSuppliers();
        } else {
            showToast(data.message || 'Failed', 'error');
        }
    } catch (e) { showToast('Network error', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Save'; }
}

async function deactivateSupplier(id, name) {
    if (!confirm(`Remove supplier "${name}"?`)) return;
    try {
        const res  = await fetch(`${API_BASE}/suppliers/deactivate.php`, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.success) { showToast('Supplier removed', 'success'); loadSuppliers(); }
        else showToast(data.message || 'Failed', 'error');
    } catch (e) { showToast('Network error', 'error'); }
}

// ─── HELPERS ──────────────────────────────────────────────────────────────────
function escapeHtml(t) {
    if (!t) return '';
    const d = document.createElement('div');
    d.textContent = t;
    return d.innerHTML;
}
function showError(msg) {
    document.getElementById('loading').style.display = 'none';
    const el = document.getElementById('error');
    el.style.display = 'block';
    el.textContent   = msg;
}
function showToast(message, type = 'success') {
    const t = document.createElement('div');
    t.className   = `toast toast-${type}`;
    t.textContent = message;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3000);
}