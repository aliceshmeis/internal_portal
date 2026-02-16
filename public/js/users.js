// Users List JavaScript
const API_BASE = '/internal_portal/api/v1';

let allUsers = [];
let filteredUsers = [];
let currentPage = 1;
const itemsPerPage = 10;

// Load users on page load
document.addEventListener('DOMContentLoaded', () => {
    loadUsers();
    setupSearchDebounce();
    setupFilterListeners();
});

// Load users from API
async function loadUsers() {
    try {
        const response = await fetch(`${API_BASE}/users/list.php`, {
            method: 'GET',
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.message || 'Failed to load users');
        }
        
        if (result.success && result.data) {
            allUsers = result.data;
            filteredUsers = [...allUsers];
            displayUsers();
        } else {
            throw new Error('Invalid response format');
        }
    } catch (error) {
        console.error('Error:', error);
        showError(error.message);
    }
}

// Display users in table
function displayUsers() {
    const loading = document.getElementById('loading');
    const container = document.getElementById('users-container');
    const emptyState = document.getElementById('empty-state');
    const tbody = document.getElementById('users-tbody');
    
    loading.style.display = 'none';
    
    if (filteredUsers.length === 0) {
        container.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    container.style.display = 'block';
    emptyState.style.display = 'none';
    
    // Pagination
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageUsers = filteredUsers.slice(startIndex, endIndex);
    
    // Render users
    tbody.innerHTML = pageUsers.map(user => `
        <tr>
            <td>
                <div class="user-info">
                    <div class="user-avatar">${getInitials(user.name)}</div>
                    <div class="user-details">
                        <div class="user-name">${escapeHtml(user.name)}</div>
                        <div class="user-login-method">${user.login_method === 'google' ? '🔗 Google' : '✉️ Email'}</div>
                    </div>
                </div>
            </td>
            <td><span class="user-email">${escapeHtml(user.email)}</span></td>
            <td>${getRoleBadge(user.role)}</td>
            <td><span class="user-campus">${escapeHtml(user.campus_name || 'N/A')}</span></td>
            <td>${getStatusBadge(user.is_active)}</td>
            <td><span class="last-login">${formatDate(user.last_login)}</span></td>
            <td>
                <div class="user-actions">
                    <button class="user-action-btn" onclick="editUser(${user.id})">Edit</button>
                    <button class="user-action-btn danger" onclick="deleteUser(${user.id}, '${escapeHtml(user.name)}')">Delete</button>
                </div>
            </td>
        </tr>
    `).join('');
    
    renderPagination();
}

// Get user initials
function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

// Role Badge
function getRoleBadge(role) {
    const roleMap = {
        'Admin': 'role-admin',
        'Staff': 'role-staff',
        'Asset Manager': 'role-asset-manager',
        'Viewer': 'role-viewer'
    };
    
    const className = roleMap[role] || 'role-staff';
    return `<span class="role-badge ${className}">${role}</span>`;
}

// Status Badge
function getStatusBadge(isActive) {
    if (isActive == 1) {
        return `<span class="status-badge-small status-active">Active</span>`;
    } else {
        return `<span class="status-badge-small status-inactive">Inactive</span>`;
    }
}

// Pagination
function renderPagination() {
    const totalPages = Math.ceil(filteredUsers.length / itemsPerPage);
    const info = document.getElementById('pagination-info');
    const controls = document.getElementById('pagination-controls');
    
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, filteredUsers.length);
    info.textContent = `Showing ${startItem}-${endItem} of ${filteredUsers.length}`;
    
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
    const totalPages = Math.ceil(filteredUsers.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    displayUsers();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Apply filters
function applyFilters() {
    const searchTerm = document.getElementById('search').value.toLowerCase();
    const roleFilter = document.getElementById('role-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    
    filteredUsers = allUsers.filter(user => {
        const matchesSearch = !searchTerm || 
            user.name.toLowerCase().includes(searchTerm) ||
            user.email.toLowerCase().includes(searchTerm);
        
        const matchesRole = !roleFilter || user.role === roleFilter;
        const matchesStatus = !statusFilter || String(user.is_active) === statusFilter;
        
        return matchesSearch && matchesRole && matchesStatus;
    });
    
    currentPage = 1;
    displayUsers();
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
    document.getElementById('role-filter').addEventListener('change', applyFilters);
    document.getElementById('status-filter').addEventListener('change', applyFilters);
}

// Actions
function editUser(id) {
    alert(`Edit user ${id} - Modal coming soon!`);
}

function deleteUser(id, name) {
    if (confirm(`Are you sure you want to delete user "${name}"?`)) {
        alert(`Delete user ${id} - API call coming soon!`);
    }
}

function openAddUserModal() {
    alert('Add User modal will be implemented next!');
}

// Format date
function formatDate(dateString) {
    if (!dateString) return 'Never';
    
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7) return `${days} days ago`;
    
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric'
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