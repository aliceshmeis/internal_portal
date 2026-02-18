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
    const loading    = document.getElementById('loading');
    const container  = document.getElementById('users-container');
    const emptyState = document.getElementById('empty-state');
    const tbody      = document.getElementById('users-tbody');

    loading.style.display = 'none';

    if (filteredUsers.length === 0) {
        container.style.display  = 'none';
        emptyState.style.display = 'block';
        return;
    }

    container.style.display  = 'block';
    emptyState.style.display = 'none';

    // Pagination
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex   = startIndex + itemsPerPage;
    const pageUsers  = filteredUsers.slice(startIndex, endIndex);

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
                    <button class="user-action-btn danger" onclick="deleteUser(${user.id})">Delete</button>
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
        'Admin':         'role-admin',
        'Staff':         'role-staff',
        'Asset Manager': 'role-asset-manager',
        'Viewer':        'role-viewer'
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
    const info       = document.getElementById('pagination-info');
    const controls   = document.getElementById('pagination-controls');

    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem   = Math.min(currentPage * itemsPerPage, filteredUsers.length);
    info.textContent = `Showing ${startItem}-${endItem} of ${filteredUsers.length}`;

    let buttonsHTML = '';
    buttonsHTML += `<button class="page-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>←</button>`;

    const maxButtons = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
    let endPage   = Math.min(totalPages, startPage + maxButtons - 1);

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
    const searchTerm   = document.getElementById('search').value.toLowerCase();
    const roleFilter   = document.getElementById('role-filter').value;
    const statusFilter = document.getElementById('status-filter').value;

    filteredUsers = allUsers.filter(user => {
        const matchesSearch  = !searchTerm ||
            user.name.toLowerCase().includes(searchTerm) ||
            user.email.toLowerCase().includes(searchTerm);
        const matchesRole   = !roleFilter   || user.role === roleFilter;
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

// ─── EDIT USER ────────────────────────────────────────────────────────────────

async function editUser(id) {
    const user = allUsers.find(u => u.id == id);
    if (!user) return;

    document.getElementById('editUserId').value = user.id;
    document.getElementById('editName').value   = user.name;
    document.getElementById('editEmail').value  = user.email;
    document.getElementById('editRole').value   = user.role;
    document.getElementById('editStatus').value = user.is_active == 1 ? 'Active' : 'Inactive';

    await loadEditCampuses(user.campus_id);
    if (user.campus_id) await loadEditDepartments(user.campus_id, user.department_id);

    document.getElementById('editUserModal').classList.add('active');
}

async function loadEditCampuses(selectedId) {
    const res  = await fetch(`${API_BASE}/campuses/list.php`, { credentials: 'include' });
    const data = await res.json();
    const sel  = document.getElementById('editCampus');
    sel.innerHTML = '<option value="">Select campus...</option>';
    if (data.success) {
        data.data.forEach(c => {
            sel.innerHTML += `<option value="${c.id}" ${c.id == selectedId ? 'selected' : ''}>${c.name}</option>`;
        });
    }
}

async function loadEditDepartments(campusId, selectedId = null) {
    const sel = document.getElementById('editDepartment');
    if (!campusId) {
        sel.innerHTML = '<option value="">Select campus first...</option>';
        return;
    }
    const res  = await fetch(`${API_BASE}/departments/list.php?campus_id=${campusId}`, { credentials: 'include' });
    const data = await res.json();
    sel.innerHTML = '<option value="">No department</option>';
    if (data.success && data.data.length) {
        data.data.forEach(d => {
            sel.innerHTML += `<option value="${d.id}" ${d.id == selectedId ? 'selected' : ''}>${d.name}</option>`;
        });
    }
}

async function saveEditUser() {
    const id            = document.getElementById('editUserId').value;
    const name          = document.getElementById('editName').value.trim();
    const email         = document.getElementById('editEmail').value.trim();
    const role          = document.getElementById('editRole').value;
    const campus_id     = document.getElementById('editCampus').value     || null;
    const department_id = document.getElementById('editDepartment').value || null;
    const status        = document.getElementById('editStatus').value;

    if (!name || !email || !role) {
        showToast('Please fill in all required fields', 'error');
        return;
    }

    const btn = document.getElementById('saveEditBtn');
    btn.disabled    = true;
    btn.textContent = 'Saving...';

    try {
        const res  = await fetch(`${API_BASE}/users/update.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ id, name, email, role, campus_id, department_id, status })
        });
        const data = await res.json();
        if (data.success) {
            showToast('User updated successfully', 'success');
            closeEditModal();
            loadUsers();
        } else {
            showToast(data.message || 'Failed to update user', 'error');
        }
    } catch (e) {
        showToast('Network error', 'error');
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Save Changes';
    }
}

function closeEditModal() {
    document.getElementById('editUserModal').classList.remove('active');
}

// ─── DELETE USER ──────────────────────────────────────────────────────────────

function deleteUser(id) {
    const user = allUsers.find(u => u.id == id);
    if (!user) return;

    document.getElementById('deleteUserName').textContent  = user.name;
    document.getElementById('deleteUserEmail').textContent = user.email;
    document.getElementById('confirmDeleteBtn').onclick    = () => confirmDelete(id);

    document.getElementById('deleteUserModal').classList.add('active');
}

async function confirmDelete(id) {
    const btn = document.getElementById('confirmDeleteBtn');
    btn.disabled    = true;
    btn.textContent = 'Deleting...';

    try {
        const res  = await fetch(`${API_BASE}/users/delete.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.success) {
            showToast('User deleted successfully', 'success');
            closeDeleteModal();
            loadUsers();
        } else {
            showToast(data.message || 'Failed to delete user', 'error');
        }
    } catch (e) {
        showToast('Network error', 'error');
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Yes, Delete';
    }
}

function closeDeleteModal() {
    document.getElementById('deleteUserModal').classList.remove('active');
}

// ─── ADD USER MODAL ───────────────────────────────────────────────────────────

function openAddUserModal() {
    document.getElementById('addUserModal').classList.add('active');
}

function closeAddUserModal() {
    document.getElementById('addUserModal').classList.remove('active');
}

// ─── TOAST ────────────────────────────────────────────────────────────────────

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className   = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ─── HELPERS ──────────────────────────────────────────────────────────────────

function formatDate(dateString) {
    if (!dateString) return 'Never';

    const date = new Date(dateString);
    const now  = new Date();
    const diff = now - date;
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

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
    const loading = document.getElementById('loading');
    const error   = document.getElementById('error');
    loading.style.display = 'none';
    error.style.display   = 'block';
    error.textContent     = `Error: ${message}`;
}