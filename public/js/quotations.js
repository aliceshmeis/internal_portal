// quotations.js — list page
const API_BASE = '/internal_portal/api/v1';

let allQuotations = [];
let filtered      = [];

document.addEventListener('DOMContentLoaded', () => {
    loadQuotations();
});

// ─── LOAD ─────────────────────────────────────────────────────────────────────
async function loadQuotations() {
    try {
        const res    = await fetch(`${API_BASE}/quotations/list.php`, { credentials: 'include' });
        const result = await res.json();
        if (result.success) {
            allQuotations = result.data || [];
            filtered      = [...allQuotations];
            renderStats();
            renderTable();
        } else showError(result.message || 'Failed to load');
    } catch (e) { showError('Network error'); }
}

// ─── STATS ────────────────────────────────────────────────────────────────────
function renderStats() {
    const total    = allQuotations.length;
    const pending  = allQuotations.filter(q => q.status === 'Pending').length;
    const approved = allQuotations.filter(q => q.status === 'Approved').length;
    const expired  = allQuotations.filter(q => q.status === 'Expired').length;
    const value    = allQuotations.reduce((s, q) => s + parseFloat(q.total_amount || 0), 0);

    document.getElementById('quo-stats').innerHTML = `
        <div class="quo-stat-card blue">
            <div class="quo-stat-value">${total}</div>
            <div class="quo-stat-label">Total Quotations</div>
        </div>
        <div class="quo-stat-card amber">
            <div class="quo-stat-value">${pending}</div>
            <div class="quo-stat-label">Pending Approval</div>
        </div>
        <div class="quo-stat-card green">
            <div class="quo-stat-value">${approved}</div>
            <div class="quo-stat-label">Approved</div>
        </div>
        <div class="quo-stat-card red">
            <div class="quo-stat-value">${expired}</div>
            <div class="quo-stat-label">Expired</div>
        </div>`;
}

// ─── TABLE ────────────────────────────────────────────────────────────────────
function renderTable() {
    document.getElementById('loading').style.display = 'none';
    const empty  = document.getElementById('empty-state');
    const wrap   = document.getElementById('quo-table-wrap');
    const tbody  = document.getElementById('quo-tbody');
    const countEl = document.getElementById('quo-count');

    if (!filtered.length) {
        wrap.style.display  = 'none';
        empty.style.display = 'block';
        return;
    }
    wrap.style.display  = 'block';
    empty.style.display = 'none';
    countEl.textContent = filtered.length;

    tbody.innerHTML = filtered.map(q => {
        const isExpired = q.valid_until < today();
        const dueClass  = isExpired && q.status !== 'Approved' ? 'quo-due overdue' : 'quo-due';
        return `
        <tr>
            <td><span class="quo-number">${escapeHtml(q.quotation_number)}</span></td>
            <td><span class="quo-supplier">${escapeHtml(q.supplier_name)}</span></td>
            <td><span class="quo-amount">$${parseFloat(q.total_amount).toLocaleString()}</span></td>
            <td><span class="${dueClass}">${formatDate(q.valid_until)}</span></td>
            <td>${statusBadge(q.status)}</td>
            <td><span class="quo-date">${formatDate(q.created_at)}</span></td>
            <td>
                <div style="display:flex;gap:6px;">
                    <button class="quo-action-btn primary" onclick="viewQuotation(${q.id})">View</button>
                    ${q.status === 'Pending' ? `
                        <button class="quo-action-btn success" onclick="quickApprove(${q.id})">✓ Approve</button>
                        <button class="quo-action-btn danger"  onclick="quickReject(${q.id})">✗ Reject</button>
                    ` : ''}
                </div>
            </td>
        </tr>`;
    }).join('');
}

// ─── FILTER ───────────────────────────────────────────────────────────────────
function applyFilters() {
    const search   = document.getElementById('quo-search').value.toLowerCase();
    const status   = document.getElementById('quo-status').value;
    const supplier = document.getElementById('quo-supplier').value;

    filtered = allQuotations.filter(q =>
        (!search   || q.quotation_number.toLowerCase().includes(search) || q.supplier_name.toLowerCase().includes(search)) &&
        (!status   || q.status      === status) &&
        (!supplier || q.supplier_id == supplier)
    );
    renderTable();
}

// ─── QUICK ACTIONS ────────────────────────────────────────────────────────────
async function quickApprove(id) {
    if (!confirm('Approve this quotation?')) return;
    const res  = await fetch(`${API_BASE}/quotations/approve.php`, {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, action: 'approve' })
    });
    const data = await res.json();
    if (data.success) { showToast('Quotation approved', 'success'); loadQuotations(); }
    else showToast(data.message || 'Failed', 'error');
}

async function quickReject(id) {
    const reason = prompt('Rejection reason (required):');
    if (!reason?.trim()) { showToast('Reason required', 'error'); return; }
    const res  = await fetch(`${API_BASE}/quotations/approve.php`, {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, action: 'reject', reason: reason.trim() })
    });
    const data = await res.json();
    if (data.success) { showToast('Quotation rejected', 'success'); loadQuotations(); }
    else showToast(data.message || 'Failed', 'error');
}

function viewQuotation(id) {
    window.location.href = `detail.php?id=${id}`;
}
function goCreate() {
    window.location.href = 'create.php';
}

// ─── HELPERS ──────────────────────────────────────────────────────────────────
function statusBadge(status) {
    const map = { Pending: 'pending', Approved: 'approved', Rejected: 'rejected', Expired: 'expired' };
    return `<span class="quo-badge ${map[status] || ''}">${status}</span>`;
}
function today() { return new Date().toISOString().split('T')[0]; }
function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
}
function escapeHtml(t) {
    if (!t) return '';
    const d = document.createElement('div'); d.textContent = t; return d.innerHTML;
}
function showError(msg) {
    document.getElementById('loading').style.display = 'none';
    const el = document.getElementById('error');
    el.style.display = 'block'; el.textContent = msg;
}
function showToast(message, type = 'success') {
    const t = document.createElement('div');
    t.className = `toast toast-${type}`; t.textContent = message;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3000);
}