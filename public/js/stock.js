// Stock List JavaScript
const API_BASE = '/internal_portal/api/v1';

let allStock = [];
let filteredStock = [];
let currentPage = 1;
const itemsPerPage = 10;

// Load stock on page load
document.addEventListener('DOMContentLoaded', () => {
    loadStock();
    setupSearchDebounce();
    setupFilterListeners();
});

// Load stock from API
async function loadStock() {
    try {
        const response = await fetch(`${API_BASE}/stock/list.php`, {
            method: 'GET',
            credentials: 'include'
        });

        const result = await response.json();

        if (!response.ok) throw new Error(result.message || 'Failed to load stock');

        if (result.success && result.data) {
            allStock = result.data;
            filteredStock = [...allStock];
            populateCategoryFilter();
            renderStats();
            renderAlertBanner();
            displayStock();
        } else {
            throw new Error('Invalid response format');
        }
    } catch (error) {
        console.error('Error:', error);
        showError(error.message);
    }
}

// Populate category filter
function populateCategoryFilter() {
    const categoryFilter = document.getElementById('category-filter');
    const categories = [...new Set(allStock.map(item => item.category_name).filter(Boolean))];
    categoryFilter.innerHTML = '<option value="">All Categories</option>';
    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category;
        option.textContent = category;
        categoryFilter.appendChild(option);
    });
}

// Render stats cards
function renderStats() {
    const statsRow   = document.getElementById('stats-row');
    const total      = allStock.length;
    const lowStock   = allStock.filter(item => item.quantity <= item.minimum_threshold).length;
    const totalValue = allStock.reduce((sum, item) => sum + (item.quantity * (item.unit_cost || 0)), 0);
    const okStock    = total - lowStock;

    statsRow.innerHTML = `
        <div class="stat-card">
            <div class="stat-icon blue">📋</div>
            <div class="stat-content">
                <div class="stat-label">Total Items</div>
                <div class="stat-value">${total}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">✓</div>
            <div class="stat-content">
                <div class="stat-label">Stock OK</div>
                <div class="stat-value">${okStock}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow">⚠️</div>
            <div class="stat-content">
                <div class="stat-label">Low Stock</div>
                <div class="stat-value">${lowStock}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon gray">💰</div>
            <div class="stat-content">
                <div class="stat-label">Total Value</div>
                <div class="stat-value">$${totalValue.toLocaleString()}</div>
            </div>
        </div>
    `;
}

// Render alert banner
function renderAlertBanner() {
    const banner        = document.getElementById('alert-banner');
    const lowStock      = allStock.filter(item => item.quantity <= item.minimum_threshold);
    const criticalStock = allStock.filter(item => item.quantity === 0);

    if (criticalStock.length > 0) {
        banner.className = 'alert-banner critical';
        banner.innerHTML = `
            <div class="alert-icon">🚨</div>
            <div class="alert-content">
                <div class="alert-title">Critical Stock Alert!</div>
                <div class="alert-text">${criticalStock.length} item(s) are out of stock</div>
            </div>
            <button class="alert-action" onclick="showLowStockOnly()">View Items</button>
        `;
        banner.style.display = 'flex';
    } else if (lowStock.length > 0) {
        banner.className = 'alert-banner';
        banner.innerHTML = `
            <div class="alert-icon">⚠️</div>
            <div class="alert-content">
                <div class="alert-title">Low Stock Warning</div>
                <div class="alert-text">${lowStock.length} item(s) below minimum threshold</div>
            </div>
            <button class="alert-action" onclick="showLowStockOnly()">View Items</button>
        `;
        banner.style.display = 'flex';
    } else {
        banner.style.display = 'none';
    }
}

// Display stock in table
function displayStock() {
    const loading    = document.getElementById('loading');
    const container  = document.getElementById('stock-container');
    const emptyState = document.getElementById('empty-state');
    const tbody      = document.getElementById('stock-tbody');

    loading.style.display = 'none';

    if (filteredStock.length === 0) {
        container.style.display  = 'none';
        emptyState.style.display = 'block';
        return;
    }

    container.style.display  = 'block';
    emptyState.style.display = 'none';

    const startIndex = (currentPage - 1) * itemsPerPage;
    const pageStock  = filteredStock.slice(startIndex, startIndex + itemsPerPage);

    tbody.innerHTML = pageStock.map(item => {
        const isLowStock    = item.quantity <= item.minimum_threshold;
        const percentage    = item.minimum_threshold > 0
            ? Math.min((item.quantity / item.minimum_threshold) * 100, 100)
            : 100;
        const progressClass = item.quantity === 0 ? 'critical' : isLowStock ? 'low' : '';

        return `
            <tr class="${isLowStock ? 'low-stock' : ''}">
                <td>
                    <div class="stock-item-name">${escapeHtml(item.name)}</div>
                    <div class="stock-sku">${escapeHtml(item.category_name || 'Uncategorized')}</div>
                </td>
                <td><span class="stock-sku">${escapeHtml(item.sku || 'N/A')}</span></td>
                <td>
                    <div class="quantity-display">
                        <div class="quantity-number">${item.quantity}</div>
                        <div class="quantity-progress">
                            <div class="quantity-progress-bar ${progressClass}" style="width: ${percentage}%"></div>
                        </div>
                    </div>
                </td>
                <td>${item.minimum_threshold}</td>
                <td>${getStockStatus(item.quantity, item.minimum_threshold)}</td>
                <td><span class="last-updated">${formatDate(item.last_updated)}</span></td>
                <td style="text-align: center;">
                    <div class="actions-cell">
                        <button class="adjust-btn" onclick="openAdjustModal(${item.id})">⚙️ Adjust</button>
                        ${isLowStock ? `<button class="request-po-btn" onclick="requestPO(${item.id})">📦 Request PO</button>` : ''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    renderPagination();
}

// Stock status badge
function getStockStatus(quantity, threshold) {
    if (quantity === 0)        return `<span class="stock-status stock-status-critical">OUT OF STOCK</span>`;
    if (quantity <= threshold) return `<span class="stock-status stock-status-low">LOW STOCK</span>`;
    return `<span class="stock-status stock-status-ok">IN STOCK</span>`;
}

// Pagination
function renderPagination() {
    const totalPages = Math.ceil(filteredStock.length / itemsPerPage);
    const info       = document.getElementById('pagination-info');
    const controls   = document.getElementById('pagination-controls');

    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem   = Math.min(currentPage * itemsPerPage, filteredStock.length);
    info.textContent = `Showing ${startItem}-${endItem} of ${filteredStock.length}`;

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
    const totalPages = Math.ceil(filteredStock.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    displayStock();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Apply filters
function applyFilters() {
    const searchTerm     = document.getElementById('search').value.toLowerCase();
    const categoryFilter = document.getElementById('category-filter').value;
    const lowStockOnly   = document.getElementById('low-stock-filter').checked;

    filteredStock = allStock.filter(item => {
        const matchesSearch   = !searchTerm || item.name.toLowerCase().includes(searchTerm) || item.sku?.toLowerCase().includes(searchTerm);
        const matchesCategory = !categoryFilter || item.category_name === categoryFilter;
        const matchesLowStock = !lowStockOnly   || item.quantity <= item.minimum_threshold;
        return matchesSearch && matchesCategory && matchesLowStock;
    });

    currentPage = 1;
    displayStock();
}

function showLowStockOnly() {
    document.getElementById('low-stock-filter').checked = true;
    document.getElementById('low-stock-toggle').classList.add('active');
    applyFilters();
}

let searchTimeout;
function setupSearchDebounce() {
    document.getElementById('search').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });
}

function setupFilterListeners() {
    document.getElementById('category-filter').addEventListener('change', applyFilters);
    document.getElementById('low-stock-filter').addEventListener('change', function () {
        document.getElementById('low-stock-toggle').classList.toggle('active', this.checked);
        applyFilters();
    });
}

// ─── ADD STOCK MODAL ──────────────────────────────────────────────────────────

async function openAddStockModal() {
    document.getElementById('addStockForm').reset();
    await loadStockCampuses();
    document.getElementById('addStockModal').classList.add('active');
}

function closeAddStockModal() {
    document.getElementById('addStockModal').classList.remove('active');
}

async function loadStockCampuses() {
    try {
        const res  = await fetch(`${API_BASE}/campuses/list.php`, { credentials: 'include' });
        const data = await res.json();
        const sel  = document.getElementById('addCampus');
        sel.innerHTML = '<option value="">Select campus...</option>';
        if (data.success && data.data) {
            data.data.forEach(c => {
                sel.innerHTML += `<option value="${c.id}">${c.campus_name}</option>`;
            });
        }
    } catch (e) {
        console.error('Failed to load campuses:', e);
    }
}

async function submitAddStock() {
    const name      = document.getElementById('addName').value.trim();
    const category  = document.getElementById('addCategory').value.trim();
    const sku       = document.getElementById('addSku').value.trim();
    const quantity  = parseInt(document.getElementById('addQuantity').value);
    const unit      = document.getElementById('addUnit').value.trim() || 'units';
    const threshold = parseInt(document.getElementById('addThreshold').value) || 10;
    const unit_cost = parseFloat(document.getElementById('addUnitCost').value) || 0;
    const campus_id = document.getElementById('addCampus').value;

    if (!name)                           { showToast('Item name is required', 'error');      return; }
    if (isNaN(quantity) || quantity < 0) { showToast('Valid quantity is required', 'error'); return; }
    if (!campus_id)                      { showToast('Please select a campus', 'error');     return; }

    const btn = document.getElementById('addStockBtn');
    btn.disabled = true; btn.textContent = 'Adding...';

    try {
        const res  = await fetch(`${API_BASE}/stock/create.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ item_name: name, category, sku, quantity, unit, minimum_threshold: threshold, unit_cost, campus_id })
        });
        const data = await res.json();

        if (data.success) {
            showToast('Stock item added successfully', 'success');
            closeAddStockModal();
            loadStock();
        } else {
            showToast(data.message || 'Failed to add item', 'error');
        }
    } catch (e) {
        showToast('Network error', 'error');
    } finally {
        btn.disabled = false; btn.textContent = 'Add Item';
    }
}

// ─── ADJUST STOCK MODAL ───────────────────────────────────────────────────────

function openAdjustModal(id) {
    const item = allStock.find(s => s.id == id);
    if (!item) return;

    document.getElementById('adjustStockId').value         = item.id;
    document.getElementById('adjustItemName').textContent   = item.name;
    document.getElementById('adjustCurrentQty').textContent = item.quantity;
    document.getElementById('adjustQuantity').value         = item.quantity;
    document.getElementById('adjustThreshold').value        = item.minimum_threshold;

    document.getElementById('adjustStockModal').classList.add('active');
}

function closeAdjustModal() {
    document.getElementById('adjustStockModal').classList.remove('active');
}

async function submitAdjustStock() {
    const id        = document.getElementById('adjustStockId').value;
    const quantity  = parseInt(document.getElementById('adjustQuantity').value);
    const threshold = parseInt(document.getElementById('adjustThreshold').value);

    if (isNaN(quantity) || quantity < 0) { showToast('Valid quantity is required', 'error'); return; }

    const btn = document.getElementById('adjustStockBtn');
    btn.disabled = true; btn.textContent = 'Saving...';

    try {
        const res  = await fetch(`${API_BASE}/stock/update.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ id, quantity, minimum_threshold: threshold })
        });
        const data = await res.json();

        if (data.success) {
            showToast('Stock updated successfully', 'success');
            closeAdjustModal();
            loadStock();
        } else {
            showToast(data.message || 'Failed to update stock', 'error');
        }
    } catch (e) {
        showToast('Network error', 'error');
    } finally {
        btn.disabled = false; btn.textContent = 'Save Changes';
    }
}

// ─── REQUEST PO ───────────────────────────────────────────────────────────────

function requestPO(id) {
    window.location.href = `../purchase-orders/create.php?stock_id=${id}`;
}

// ─── TOAST ────────────────────────────────────────────────────────────────────

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className   = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
}

// ─── HELPERS ──────────────────────────────────────────────────────────────────

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const now  = new Date();
    const days = Math.floor((now - date) / (1000 * 60 * 60 * 24));
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7)  return `${days} days ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    document.getElementById('loading').style.display = 'none';
    const error = document.getElementById('error');
    error.style.display = 'block';
    error.textContent   = `Error: ${message}`;
}