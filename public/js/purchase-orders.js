// Purchase Orders List JavaScript
const API_BASE = '/internal_portal/api/v1';

let allPOs = [];
let filteredPOs = [];
let currentPage = 1;
const itemsPerPage = 10;

// Load POs on page load
document.addEventListener('DOMContentLoaded', () => {
    loadPurchaseOrders();
    setupSearchDebounce();
    setupFilterListeners();
});

// Load purchase orders from API
async function loadPurchaseOrders() {
    try {
        const response = await fetch(`${API_BASE}/purchase-orders/list.php`, {
            method: 'GET',
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.message || 'Failed to load purchase orders');
        }
        
        if (result.success && result.data) {
            allPOs = result.data;
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

// Render stats summary
function renderStats() {
    const summary = document.getElementById('stats-summary');
    
    const total = allPOs.length;
    const pending = allPOs.filter(po => po.status === 'Pending').length;
    const approved = allPOs.filter(po => po.status === 'Approved').length;
    const totalValue = allPOs.reduce((sum, po) => sum + parseFloat(po.total_amount || 0), 0);
    
    summary.innerHTML = `
        <div class="summary-card blue">
            <div class="summary-value">${total}</div>
            <div class="summary-label">Total POs</div>
        </div>
        
        <div class="summary-card yellow">
            <div class="summary-value">${pending}</div>
            <div class="summary-label">Pending</div>
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

// Display POs in table
function displayPOs() {
    const loading = document.getElementById('loading');
    const container = document.getElementById('po-container');
    const emptyState = document.getElementById('empty-state');
    const tbody = document.getElementById('po-tbody');
    
    loading.style.display = 'none';
    
    if (filteredPOs.length === 0) {
        container.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    container.style.display = 'block';
    emptyState.style.display = 'none';
    
    // Pagination
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pagePOs = filteredPOs.slice(startIndex, endIndex);
    
    // Render POs
    tbody.innerHTML = pagePOs.map(po => `
        <tr>
            <td><span class="po-id">PO-${String(po.id).padStart(4, '0')}</span></td>
            <td>
                <div class="vendor-name">${escapeHtml(po.vendor_name || 'Unknown Vendor')}</div>
                ${po.vendor_contact ? `<div class="vendor-contact">${escapeHtml(po.vendor_contact)}</div>` : ''}
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

// Status badge
function getStatusBadge(status) {
    const statusMap = {
        'Draft': 'status-draft',
        'Pending': 'status-pending',
        'Approved': 'status-approved',
        'Ordered': 'status-ordered',
        'Received': 'status-received',
        'Rejected': 'status-rejected'
    };
    
    const className = statusMap[status] || 'status-draft';
    return `<span class="po-status ${className}">${status}</span>`;
}

// Created by display
function getCreatedBy(name) {
    if (!name) return '<span class="creator-name">Unknown</span>';
    
    const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
    return `
        <div class="created-by">
            <div class="creator-avatar">${initials}</div>
            <span class="creator-name">${escapeHtml(name)}</span>
        </div>
    `;
}

// Action buttons based on status
function getActionButtons(po) {
    if (po.status === 'Pending') {
        return `
            <button class="po-action-btn approve" onclick="approvePO(${po.id})">✓ Approve</button>
            <button class="po-action-btn reject" onclick="rejectPO(${po.id})">✗ Reject</button>
        `;
    } else if (po.status === 'Approved') {
        return `<button class="po-action-btn" onclick="markOrdered(${po.id})">📦 Mark Ordered</button>`;
    } else if (po.status === 'Ordered') {
        return `<button class="po-action-btn" onclick="markReceived(${po.id})">✓ Mark Received</button>`;
    }
    return '';
}

// Pagination
function renderPagination() {
    const totalPages = Math.ceil(filteredPOs.length / itemsPerPage);
    const info = document.getElementById('pagination-info');
    const controls = document.getElementById('pagination-controls');
    
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, filteredPOs.length);
    info.textContent = `Showing ${startItem}-${endItem} of ${filteredPOs.length}`;
    
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
    const totalPages = Math.ceil(filteredPOs.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    displayPOs();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Apply filters
function applyFilters() {
    const searchTerm = document.getElementById('search').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value;
    const dateFilter = document.getElementById('date-filter').value;
    
    filteredPOs = allPOs.filter(po => {
        const matchesSearch = !searchTerm || 
            String(po.id).includes(searchTerm) ||
            po.vendor_name?.toLowerCase().includes(searchTerm);
        
        const matchesStatus = !statusFilter || po.status === statusFilter;
        
        let matchesDate = true;
        if (dateFilter) {
            const poDate = new Date(po.created_at);
            const now = new Date();
            
            switch(dateFilter) {
                case 'today':
                    matchesDate = poDate.toDateString() === now.toDateString();
                    break;
                case 'week':
                    const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                    matchesDate = poDate >= weekAgo;
                    break;
                case 'month':
                    matchesDate = poDate.getMonth() === now.getMonth() && 
                                  poDate.getFullYear() === now.getFullYear();
                    break;
                case 'quarter':
                    const quarter = Math.floor(now.getMonth() / 3);
                    const poQuarter = Math.floor(poDate.getMonth() / 3);
                    matchesDate = poQuarter === quarter && 
                                  poDate.getFullYear() === now.getFullYear();
                    break;
            }
        }
        
        return matchesSearch && matchesStatus && matchesDate;
    });
    
    currentPage = 1;
    renderStats();
    displayPOs();
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
    document.getElementById('status-filter').addEventListener('change', applyFilters);
    document.getElementById('date-filter').addEventListener('change', applyFilters);
}

// Actions
function viewPO(id) {
    window.location.href = `view.php?id=${id}`;
}

function approvePO(id) {
    if (confirm('Approve this purchase order?')) {
        alert(`Approve PO ${id} - API call coming soon!`);
        // TODO: API call to approve
    }
}

function rejectPO(id) {
    if (confirm('Reject this purchase order?')) {
        alert(`Reject PO ${id} - API call coming soon!`);
        // TODO: API call to reject
    }
}

function markOrdered(id) {
    if (confirm('Mark this PO as ordered?')) {
        alert(`Mark ordered PO ${id} - API call coming soon!`);
        // TODO: API call to mark ordered
    }
}

function markReceived(id) {
    if (confirm('Mark this PO as received?')) {
        alert(`Mark received PO ${id} - API call coming soon!`);
        // TODO: API call to mark received
    }
}

function openCreatePOModal() {
    alert('Create Purchase Order modal will be implemented next!');
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7) return `${days} days ago`;
    
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
    });
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