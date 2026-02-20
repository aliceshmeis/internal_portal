// Assets List JavaScript
const API_BASE = '/internal_portal/api/v1';

let allAssets      = [];
let filteredAssets = [];
let currentPage    = 1;
const itemsPerPage = 10;

document.addEventListener('DOMContentLoaded', () => {
    loadAssets();
    setupSearchDebounce();
    setupFilterListeners();
});

async function loadAssets() {
    try {
        const response = await fetch(`${API_BASE}/assets/list.php`, {
            method: 'GET', credentials: 'include'
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'Failed to load assets');
        if (result.success && result.data) {
            allAssets      = result.data;
            filteredAssets = [...allAssets];
            renderStats();
            displayAssets();
        } else throw new Error('Invalid response format');
    } catch (error) {
        console.error('Error:', error);
        showError(error.message);
    }
}

// ── Stats ─────────────────────────────────────
function renderStats() {
    const statsRow    = document.getElementById('stats-row');
    const total       = allAssets.length;
    const available   = allAssets.filter(a => a.status === 'Available').length;
    const inUse       = allAssets.filter(a => a.status === 'In Use').length;
    const maintenance = allAssets.filter(a => a.status === 'Maintenance').length;

    statsRow.innerHTML = `
        <div class="stat-card">
            <div class="stat-content"><div class="stat-value">${total}</div><div class="stat-label">Total Assets</div></div>
            <div class="stat-icon gray">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content"><div class="stat-value">${available}</div><div class="stat-label">Available</div></div>
            <div class="stat-icon green">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content"><div class="stat-value">${inUse}</div><div class="stat-label">In Use</div></div>
            <div class="stat-icon blue">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content"><div class="stat-value">${maintenance}</div><div class="stat-label">Maintenance</div></div>
            <div class="stat-icon yellow">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
            </div>
        </div>
    `;
}

// ── Display ───────────────────────────────────
function displayAssets() {
    const loading    = document.getElementById('loading');
    const container  = document.getElementById('assets-container');
    const emptyState = document.getElementById('empty-state');
    const tbody      = document.getElementById('assets-tbody');

    loading.style.display = 'none';

    if (filteredAssets.length === 0) {
        container.style.display  = 'none';
        emptyState.style.display = 'block';
        return;
    }

    container.style.display  = 'block';
    emptyState.style.display = 'none';

    const startIndex = (currentPage - 1) * itemsPerPage;
    const pageAssets = filteredAssets.slice(startIndex, startIndex + itemsPerPage);

    tbody.innerHTML = pageAssets.map(asset => `
        <tr onclick="viewAsset(${asset.id})" style="cursor:pointer;">
            <td><span class="asset-tag">${escapeHtml(asset.asset_tag || 'N/A')}</span></td>
            <td>
                <div class="asset-name">${escapeHtml(asset.name)}</div>
                <div class="asset-sub">${escapeHtml(asset.serial_number || '—')}</div>
            </td>
            <td>${getCategoryLabel(asset.category)}</td>
            <td>${getStatusBadge(asset.status)}</td>
            <td>${getAssignedUser(asset.assigned_user_name)}</td>
            <td>${escapeHtml(asset.campus_name || '—')}</td>
            <td>
                <div class="actions-cell">
                    ${asset.status === 'Available'
                        ? `<button class="btn-action-outline" onclick="event.stopPropagation(); openAssignModal(${asset.id}, '${escapeHtml(asset.name)}')">Assign</button>`
                        : asset.status === 'In Use'
                        ? `<button class="btn-action-outline danger" onclick="event.stopPropagation(); openReturnModal(${asset.id}, '${escapeHtml(asset.name)}')">Return</button>`
                        : ''}
                </div>
            </td>
        </tr>
    `).join('');

    renderPagination();
}

// ── Category / Status / User helpers ─────────
function getCategoryLabel(category) {
    const icons = {
        'Laptop':            `<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M0 21h24"/></svg>`,
        'Printer':           `<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7"/><rect x="6" y="14" width="12" height="8"/><rect x="6" y="9" width="12" height="5"/><path d="M18 11h.01"/></svg>`,
        'Network Equipment': `<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>`,
        'Furniture':         `<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v3"/><rect x="2" y="9" width="20" height="8" rx="1"/><path d="M6 17v2M18 17v2"/></svg>`,
        'Other':             `<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>`,
    };
    const icon = icons[category] || icons['Other'];
    return `<span class="category-label"><span class="category-icon-svg">${icon}</span>${escapeHtml(category || 'Other')}</span>`;
}

function getStatusBadge(status) {
    const classes = { 'Available': 'status-available', 'In Use': 'status-in-use', 'Maintenance': 'status-maintenance', 'Retired': 'status-retired' };
    return `<span class="asset-status ${classes[status] || 'status-retired'}">${escapeHtml(status || 'Unknown')}</span>`;
}

function getAssignedUser(userName) {
    if (!userName) return `<span class="unassigned-label">—</span>`;
    const initials = userName.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
    return `<div class="assigned-user"><div class="user-avatar-small">${initials}</div><span class="user-name-text">${escapeHtml(userName)}</span></div>`;
}

// ── Assign Modal ──────────────────────────────
let assignAssetId = null;

function openAssignModal(assetId, assetName) {
    assignAssetId = assetId;
    document.getElementById('assignAssetName').textContent       = assetName;
    document.getElementById('assignUserId').value                = '';
    document.getElementById('assignReturnDate').value            = '';
    document.getElementById('assignModalError').style.display    = 'none';
    loadStaffForAssign();
    document.getElementById('assignModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeAssignModal() {
    document.getElementById('assignModal').classList.remove('active');
    document.body.style.overflow = '';
}

async function loadStaffForAssign() {
    try {
        const res  = await fetch(`${API_BASE}/users/list.php`, { credentials: 'include' });
        const data = await res.json();
        const sel  = document.getElementById('assignUserId');
        sel.innerHTML = '<option value="">Select staff member...</option>';
        if (data.success && data.data) {
            data.data.filter(u => u.is_active == 1).forEach(u => {
                sel.innerHTML += `<option value="${u.id}">${escapeHtml(u.name)} (${escapeHtml(u.role)})</option>`;
            });
        }
    } catch (e) { console.error('Failed to load staff', e); }
}

async function submitAssign() {
    const userId     = document.getElementById('assignUserId').value;
    const returnDate = document.getElementById('assignReturnDate').value;

    if (!userId) {
        document.getElementById('assignModalError').textContent   = '⚠ Please select a staff member';
        document.getElementById('assignModalError').style.display = 'block';
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/assets/assign.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ asset_id: assignAssetId, assigned_to: parseInt(userId), expected_return_date: returnDate || null })
        });
        const data = await response.json();
        if (data.success) {
            closeAssignModal();
            showAssetToast('Asset assigned successfully!');
            loadAssets();
        } else {
            document.getElementById('assignModalError').textContent   = '⚠ ' + (data.message || 'Failed to assign');
            document.getElementById('assignModalError').style.display = 'block';
        }
    } catch (e) {
        document.getElementById('assignModalError').textContent   = '⚠ Failed to assign asset';
        document.getElementById('assignModalError').style.display = 'block';
    }
}

// ── Return Modal ──────────────────────────────
let returnAssetId = null;

function openReturnModal(assetId, assetName) {
    returnAssetId = assetId;
    document.getElementById('returnAssetName').textContent    = assetName;
    document.getElementById('returnModalError').style.display = 'none';
    document.getElementById('returnModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeReturnModal() {
    document.getElementById('returnModal').classList.remove('active');
    document.body.style.overflow = '';
}

async function submitReturn() {
    try {
        const response = await fetch(`${API_BASE}/assets/return.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ asset_id: returnAssetId })
        });
        const data = await response.json();
        if (data.success) {
            closeReturnModal();
            showAssetToast('Asset returned successfully!');
            loadAssets();
        } else {
            document.getElementById('returnModalError').textContent   = '⚠ ' + (data.message || 'Failed to return');
            document.getElementById('returnModalError').style.display = 'block';
        }
    } catch (e) {
        document.getElementById('returnModalError').textContent   = '⚠ Failed to return asset';
        document.getElementById('returnModalError').style.display = 'block';
    }
}

// ── Toast ─────────────────────────────────────
function showAssetToast(message) {
    const existing = document.getElementById('assetToast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.id        = 'assetToast';
    toast.className = 'success-toast';
    toast.textContent = '✅ ' + message;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('visible'), 10);
    setTimeout(() => { toast.classList.remove('visible'); setTimeout(() => toast.remove(), 300); }, 3000);
}

// ── Pagination ────────────────────────────────
function renderPagination() {
    const totalPages = Math.ceil(filteredAssets.length / itemsPerPage);
    const info       = document.getElementById('pagination-info');
    const controls   = document.getElementById('pagination-controls');

    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem   = Math.min(currentPage * itemsPerPage, filteredAssets.length);
    info.textContent = `Showing ${startItem}–${endItem} of ${filteredAssets.length}`;

    let html = `<button class="page-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>←</button>`;
    let startPage = Math.max(1, currentPage - 2);
    let endPage   = Math.min(totalPages, startPage + 4);
    if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);
    for (let i = startPage; i <= endPage; i++) {
        html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
    }
    html += `<button class="page-btn" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>→</button>`;
    controls.innerHTML = html;
}

function changePage(page) {
    const totalPages = Math.ceil(filteredAssets.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    displayAssets();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Filters ───────────────────────────────────
function applyFilters() {
    const searchTerm     = document.getElementById('search').value.toLowerCase().trim();
    const categoryFilter = document.getElementById('category-filter').value;
    const statusFilter   = document.getElementById('status-filter').value;

    filteredAssets = allAssets.filter(asset => {
        const matchesSearch   = !searchTerm     || (asset.name && asset.name.toLowerCase().includes(searchTerm)) || (asset.asset_tag && asset.asset_tag.toLowerCase().includes(searchTerm)) || (asset.serial_number && asset.serial_number.toLowerCase().includes(searchTerm));
        const matchesCategory = !categoryFilter || asset.category === categoryFilter;
        const matchesStatus   = !statusFilter   || asset.status   === statusFilter;
        return matchesSearch && matchesCategory && matchesStatus;
    });

    currentPage = 1;
    displayAssets();
}

let searchTimeout;
function setupSearchDebounce() {
    document.getElementById('search')?.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 400);
    });
}

function setupFilterListeners() {
    document.getElementById('category-filter')?.addEventListener('change', applyFilters);
    document.getElementById('status-filter')?.addEventListener('change', applyFilters);
}

function viewAsset(id) { window.location.href = `view.php?id=${id}`; }
function openCreateAssetModal() { alert('Create Asset modal — coming soon!'); }

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    document.getElementById('loading').style.display = 'none';
    const error = document.getElementById('error');
    error.style.display = 'block';
    error.textContent = `Error: ${message}`;
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') { closeAssignModal(); closeReturnModal(); }
});