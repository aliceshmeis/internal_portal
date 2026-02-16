// Assets List JavaScript
const API_BASE = '/internal_portal/api/v1';

let allAssets = [];
let filteredAssets = [];
let currentPage = 1;
const itemsPerPage = 10;

// Load assets on page load
document.addEventListener('DOMContentLoaded', () => {
    loadAssets();
    setupSearchDebounce();
    setupFilterListeners();
});

// Load assets from API
async function loadAssets() {
    try {
        const response = await fetch(`${API_BASE}/assets/list.php`, {
            method: 'GET',
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.message || 'Failed to load assets');
        }
        
        if (result.success && result.data) {
            allAssets = result.data;
            filteredAssets = [...allAssets];
            renderStats();
            displayAssets();
        } else {
            throw new Error('Invalid response format');
        }
    } catch (error) {
        console.error('Error:', error);
        showError(error.message);
    }
}

// Render stats cards
function renderStats() {
    const statsRow = document.getElementById('stats-row');
    
    const total = allAssets.length;
    const available = allAssets.filter(a => a.status === 'Available').length;
    const inUse = allAssets.filter(a => a.status === 'In Use').length;
    const maintenance = allAssets.filter(a => a.status === 'Maintenance').length;
    
    statsRow.innerHTML = `
        <div class="stat-card">
            <div class="stat-icon blue">💼</div>
            <div class="stat-content">
                <div class="stat-label">Total Assets</div>
                <div class="stat-value">${total}</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon green">✓</div>
            <div class="stat-content">
                <div class="stat-label">Available</div>
                <div class="stat-value">${available}</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon blue">🔧</div>
            <div class="stat-content">
                <div class="stat-label">In Use</div>
                <div class="stat-value">${inUse}</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon yellow">⚠️</div>
            <div class="stat-content">
                <div class="stat-label">Maintenance</div>
                <div class="stat-value">${maintenance}</div>
            </div>
        </div>
    `;
}

// Display assets in table
function displayAssets() {
    const loading = document.getElementById('loading');
    const container = document.getElementById('assets-container');
    const emptyState = document.getElementById('empty-state');
    const tbody = document.getElementById('assets-tbody');
    
    loading.style.display = 'none';
    
    if (filteredAssets.length === 0) {
        container.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    container.style.display = 'block';
    emptyState.style.display = 'none';
    
    // Pagination
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageAssets = filteredAssets.slice(startIndex, endIndex);
    
    // Render assets
    tbody.innerHTML = pageAssets.map(asset => `
        <tr>
            <td><span class="asset-tag">A-${String(asset.asset_tag || asset.id).padStart(4, '0')}</span></td>
            <td>
                <div class="asset-name">${escapeHtml(asset.name)}</div>
                <div class="asset-model">${escapeHtml(asset.model || 'N/A')}</div>
            </td>
            <td>${getCategoryBadge(asset.category_name)}</td>
            <td>${getStatusBadge(asset.status)}</td>
            <td>${getAssignedUser(asset.assigned_user_name)}</td>
            <td>${escapeHtml(asset.location || 'N/A')}</td>
            <td>
                <div class="actions-cell">
                    <button class="action-btn-small" onclick="viewAsset(${asset.id})">View</button>
                    ${asset.status === 'Available' ? 
                        `<button class="action-btn-small" onclick="assignAsset(${asset.id})">Assign</button>` : 
                        asset.status === 'In Use' ? 
                        `<button class="action-btn-small" onclick="returnAsset(${asset.id})">Return</button>` : 
                        ''
                    }
                </div>
            </td>
        </tr>
    `).join('');
    
    renderPagination();
}

// Category Badge with Icons
function getCategoryBadge(category) {
    const categoryMap = {
        'Laptop': { icon: '💻', class: 'category-laptop' },
        'Printer': { icon: '🖨️', class: 'category-printer' },
        'Network Equipment': { icon: '🌐', class: 'category-network' },
        'Furniture': { icon: '🪑', class: 'category-furniture' }
    };
    
    const cat = categoryMap[category] || { icon: '📦', class: 'category-other' };
    return `<span class="category-badge ${cat.class}">
        <span class="category-icon">${cat.icon}</span>
        ${category || 'Other'}
    </span>`;
}

// Status Badge
function getStatusBadge(status) {
    const classes = {
        'Available': 'status-available',
        'In Use': 'status-in-use',
        'Maintenance': 'status-maintenance',
        'Retired': 'status-retired'
    };
    const className = classes[status] || 'status-available';
    return `<span class="asset-status ${className}">${status}</span>`;
}

// Assigned User
function getAssignedUser(userName) {
    if (!userName) {
        return `<span class="unassigned-label">Unassigned</span>`;
    }
    
    const initials = userName.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
    return `
        <div class="assigned-user">
            <div class="user-avatar-small">${initials}</div>
            <span class="user-name">${escapeHtml(userName)}</span>
        </div>
    `;
}

// Pagination
function renderPagination() {
    const totalPages = Math.ceil(filteredAssets.length / itemsPerPage);
    const info = document.getElementById('pagination-info');
    const controls = document.getElementById('pagination-controls');
    
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, filteredAssets.length);
    info.textContent = `Showing ${startItem}-${endItem} of ${filteredAssets.length}`;
    
    let buttonsHTML = '';
    buttonsHTML += `<button class="page-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>←</button>`;
    
    const maxButtons = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
    let endPage = Math.min(totalPages, startPage + maxButtons - 1);
    
    if (endPage - startPage < maxButtons - 1) {
        startPage = Math.max(1, endPage - maxButtons + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        buttonsHTML += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
    }
    
    buttonsHTML += `<button class="page-btn" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>→</button>`;
    
    controls.innerHTML = buttonsHTML;
}

function changePage(page) {
    const totalPages = Math.ceil(filteredAssets.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    displayAssets();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Apply filters
function applyFilters() {
    const searchTerm = document.getElementById('search').value.toLowerCase();
    const categoryFilter = document.getElementById('category-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    
    filteredAssets = allAssets.filter(asset => {
        const matchesSearch = !searchTerm || 
            asset.name.toLowerCase().includes(searchTerm) ||
            asset.model?.toLowerCase().includes(searchTerm) ||
            String(asset.asset_tag || asset.id).includes(searchTerm);
        
        const matchesCategory = !categoryFilter || asset.category_name === categoryFilter;
        const matchesStatus = !statusFilter || asset.status === statusFilter;
        
        return matchesSearch && matchesCategory && matchesStatus;
    });
    
    currentPage = 1;
    displayAssets();
}

// Search debounce
let searchTimeout;
function setupSearchDebounce() {
    document.getElementById('search').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });
}

// Filter listeners
function setupFilterListeners() {
    document.getElementById('category-filter').addEventListener('change', applyFilters);
    document.getElementById('status-filter').addEventListener('change', applyFilters);
}

// Actions
function viewAsset(id) {
    window.location.href = `view.php?id=${id}`;
}

function assignAsset(id) {
    alert(`Assign asset ${id} - Modal coming soon!`);
}

function returnAsset(id) {
    if (confirm('Return this asset?')) {
        alert(`Return asset ${id} - API call coming soon!`);
    }
}

function openCreateAssetModal() {
    alert('Create Asset modal will be implemented next!');
}

// Helpers
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    const loading = document.getElementById('loading');
    const error = document.getElementById('error');
    
    loading.style.display = 'none';
    error.style.display = 'block';
    error.textContent = `Error: ${message}`;
}