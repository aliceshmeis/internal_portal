// Purchase Orders List JavaScript
const API_BASE = '/internal_portal/api/v1';

let allPOs = [];
let filteredPOs = [];
let currentPage = 1;
const itemsPerPage = 10;

document.addEventListener('DOMContentLoaded', () => {
    loadPurchaseOrders();
    setupSearchDebounce();
    setupFilterListeners();
});

async function loadPurchaseOrders() {
    try {
        const response = await fetch(`${API_BASE}/purchase-orders/list.php`, {
            method: 'GET', credentials: 'include'
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'Failed to load purchase orders');
        if (result.success && result.data) {
            allPOs      = result.data;
            filteredPOs = [...allPOs];
            renderStats();
            displayPOs();
        } else {
            throw new Error('Invalid response format');
        }
    } catch (error) {
        console.error('Error:', error);
        showError(error.message);
    }
}

function renderStats() {
    const summary = document.getElementById('stats-summary');
    const total        = allPOs.length;
    const pending      = allPOs.filter(po => po.status === 'Pending Approval').length;
    const approved     = allPOs.filter(po => po.status === 'Approved').length;
    const totalValue   = allPOs.reduce((sum, po) => sum + parseFloat(po.total_amount || 0), 0);

    summary.innerHTML = `
        <div class="summary-card blue">
            <div class="summary-value">${total}</div>
            <div class="summary-label">Total POs</div>
        </div>
        <div class="summary-card yellow">
            <div class="summary-value">${pending}</div>
            <div class="summary-label">Pending Approval</div>
        </div>
        <div class="summary-card green">
            <div class="summary-value">${approved}</div>
            <div class="summary-label">Approved</div>
        </div>
        <div class="summary-card purple">
            <div class="summary-value">$${totalValue.toLocaleString()}</div>
            <div class="summary-label">Total Value</div>
        </div>
    `;
}

function displayPOs() {
    const loading    = document.getElementById('loading');
    const container  = document.getElementById('po-container');
    const emptyState = document.getElementById('empty-state');
    const tbody      = document.getElementById('po-tbody');

    loading.style.display = 'none';

    if (filteredPOs.length === 0) {
        container.style.display  = 'none';
        emptyState.style.display = 'block';
        return;
    }

    container.style.display  = 'block';
    emptyState.style.display = 'none';

    const startIndex = (currentPage - 1) * itemsPerPage;
    const pagePOs    = filteredPOs.slice(startIndex, startIndex + itemsPerPage);

    tbody.innerHTML = pagePOs.map(po => `
        <tr>
            <td><span class="po-id">${escapeHtml(po.po_number)}</span></td>
            <td>
                <div class="vendor-name">${escapeHtml(po.supplier || 'Unknown')}</div>
            </td>
            <td><span class="total-amount">$${parseFloat(po.total_amount || 0).toLocaleString()}</span></td>
            <td>${getStatusBadge(po.status)}</td>
            <td>${getCreatedBy(po.created_by_name)}</td>
            <td><span class="po-date">${formatDate(po.created_at)}</span></td>
            <td>
                <div class="actions-cell">
                    <button class="po-action-btn" onclick="viewPO(${po.id})">View</button>
                    ${getActionButtons(po)}
                </div>
            </td>
        </tr>
    `).join('');

    renderPagination();
}

function getStatusBadge(status) {
    const statusMap = {
        'Draft':            'status-draft',
        'Pending Approval': 'status-pending',
        'Approved':         'status-approved',
        'Completed':        'status-received',
        'Rejected':         'status-rejected',
        'Cancelled':        'status-draft',
    };
    const className = statusMap[status] || 'status-draft';
    return `<span class="po-status ${className}">${escapeHtml(status)}</span>`;
}

function getCreatedBy(name) {
    if (!name) return '<span class="creator-name">Unknown</span>';
    const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
    return `
        <div class="created-by">
            <div class="creator-avatar">${initials}</div>
            <span class="creator-name">${escapeHtml(name)}</span>
        </div>`;
}

function getActionButtons(po) {
    if (po.status === 'Pending Approval') {
        return `
            <button class="po-action-btn approve" onclick="approvePO(${po.id})">✓ Approve</button>
            <button class="po-action-btn reject"  onclick="rejectPO(${po.id})">✗ Reject</button>`;
    }
    if (po.status === 'Approved') {
        return `<button class="po-action-btn" onclick="receivePO(${po.id})">📦 Receive</button>`;
    }
    return '';
}

// ─── ACTIONS ──────────────────────────────────────────────────────────────────

function viewPO(id) {
    window.location.href = `view.php?id=${id}`;
}

async function approvePO(id) {
    if (!confirm('Approve this purchase order?')) return;
    try {
        const res    = await fetch(`${API_BASE}/purchase-orders/approve.php`, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, action: 'approve' })
        });
        const result = await res.json();
        if (result.success) { showToast('Purchase order approved', 'success'); loadPurchaseOrders(); }
        else showToast(result.message || 'Failed to approve', 'error');
    } catch(e) { showToast('Network error', 'error'); }
}

async function rejectPO(id) {
    const reason = prompt('Enter rejection reason (required):');
    if (!reason || !reason.trim()) { showToast('Rejection reason is required', 'error'); return; }
    try {
        const res    = await fetch(`${API_BASE}/purchase-orders/approve.php`, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, action: 'reject', reason: reason.trim() })
        });
        const result = await res.json();
        if (result.success) { showToast('Purchase order rejected', 'success'); loadPurchaseOrders(); }
        else showToast(result.message || 'Failed to reject', 'error');
    } catch(e) { showToast('Network error', 'error'); }
}

async function receivePO(id) {
    if (!confirm('Mark as received? Stock will be updated and assets will be created automatically.')) return;
    try {
        const res    = await fetch(`${API_BASE}/purchase-orders/receive.php`, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await res.json();
        if (result.success) { showToast('PO received — stock & assets updated!', 'success'); loadPurchaseOrders(); }
        else showToast(result.message || 'Failed to receive', 'error');
    } catch(e) { showToast('Network error', 'error'); }
}

function openCreatePOModal() {
    window.location.href = '/internal_portal/app/views/asset_manager/po/create.php';
}

// ─── PAGINATION ───────────────────────────────────────────────────────────────

function renderPagination() {
    const totalPages = Math.ceil(filteredPOs.length / itemsPerPage);
    const info       = document.getElementById('pagination-info');
    const controls   = document.getElementById('pagination-controls');

    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem   = Math.min(currentPage * itemsPerPage, filteredPOs.length);
    info.textContent = `Showing ${startItem}-${endItem} of ${filteredPOs.length}`;

    let buttonsHTML = `<button class="page-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>←</button>`;
    const maxButtons = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
    let endPage   = Math.min(totalPages, startPage + maxButtons - 1);
    if (endPage - startPage < maxButtons - 1) startPage = Math.max(1, endPage - maxButtons + 1);
    for (let i = startPage; i <= endPage; i++) {
        buttonsHTML += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
    }
    buttonsHTML += `<button class="page-btn" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>→</button>`;
    controls.innerHTML = buttonsHTML;
}

function changePage(page) {
    const totalPages = Math.ceil(filteredPOs.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    displayPOs();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ─── FILTERS ──────────────────────────────────────────────────────────────────

function applyFilters() {
    const searchTerm   = document.getElementById('search').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value;
    const dateFilter   = document.getElementById('date-filter').value;

    filteredPOs = allPOs.filter(po => {
        const matchesSearch = !searchTerm ||
            (po.po_number||'').toLowerCase().includes(searchTerm) ||
            (po.supplier||'').toLowerCase().includes(searchTerm);

        const matchesStatus = !statusFilter || po.status === statusFilter;

        let matchesDate = true;
        if (dateFilter) {
            const poDate = new Date(po.created_at);
            const now    = new Date();
            switch(dateFilter) {
                case 'today':   matchesDate = poDate.toDateString() === now.toDateString(); break;
                case 'week':    matchesDate = poDate >= new Date(now - 7*86400000); break;
                case 'month':   matchesDate = poDate.getMonth() === now.getMonth() && poDate.getFullYear() === now.getFullYear(); break;
                case 'quarter':
                    matchesDate = Math.floor(poDate.getMonth()/3) === Math.floor(now.getMonth()/3) && poDate.getFullYear() === now.getFullYear();
                    break;
            }
        }
        return matchesSearch && matchesStatus && matchesDate;
    });

    currentPage = 1;
    renderStats();
    displayPOs();
}

let searchTimeout;
function setupSearchDebounce() {
    document.getElementById('search').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });
}
function setupFilterListeners() {
    document.getElementById('status-filter').addEventListener('change', applyFilters);
    document.getElementById('date-filter').addEventListener('change', applyFilters);
}

// ─── HELPERS ──────────────────────────────────────────────────────────────────

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const now  = new Date();
    const days = Math.floor((now - date) / 86400000);
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7)  return `${days} days ago`;
    return date.toLocaleDateString('en-US', { month:'short', day:'numeric', year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className   = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
}

function showError(message) {
    document.getElementById('loading').style.display = 'none';
    const error = document.getElementById('error');
    error.style.display = 'block';
    error.textContent   = `Error: ${message}`;
}