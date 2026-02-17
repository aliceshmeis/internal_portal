// =============================================
// ADD USER MODAL
// Calls: POST /api/v1/users/create.php
// =============================================

document.addEventListener('DOMContentLoaded', () => {
    injectAddUserModal();
});

function injectAddUserModal() {
    if (document.getElementById('addUserModal')) return;

    const modal = document.createElement('div');
    modal.id = 'addUserModal';
    modal.onclick = function(e) {
        if (e.target === modal) closeAddUserModal();
    };
    modal.innerHTML = `
        <div class="aum-container">

            <div class="aum-header">
                <div>
                    <h2 class="aum-title">Add New User</h2>
                    <p class="aum-subtitle">Create a new account for the internal portal</p>
                </div>
                <button class="aum-close" onclick="closeAddUserModal()">&#x2715;</button>
            </div>

            <div class="aum-body">
                <div class="aum-error" id="aumError" style="display:none;"></div>

                <!-- Name & Email -->
                <div class="aum-row">
                    <div class="aum-field">
                        <label class="aum-label">Full Name <span class="aum-required">*</span></label>
                        <input type="text" class="aum-input" id="aumName" placeholder="e.g. John Smith">
                    </div>
                    <div class="aum-field">
                        <label class="aum-label">Email Address <span class="aum-required">*</span></label>
                        <input type="email" class="aum-input" id="aumEmail" placeholder="e.g. john@liu.edu.lb">
                    </div>
                </div>

                <!-- Password -->
                <div class="aum-field">
                    <label class="aum-label">Password <span class="aum-required">*</span></label>
                    <div class="aum-password-wrapper">
                        <input type="password" class="aum-input" id="aumPassword" placeholder="Minimum 8 characters">
                        <button type="button" class="aum-toggle-password" onclick="toggleAumPassword()">👁</button>
                    </div>
                    <span class="aum-hint">Must be at least 8 characters</span>
                </div>

                <!-- Role & Campus -->
                <div class="aum-row">
                    <div class="aum-field">
                        <label class="aum-label">Role <span class="aum-required">*</span></label>
                        <select class="aum-select" id="aumRole">
                            <option value="">Select role...</option>
                            <option value="Staff">Staff</option>
                            <option value="Asset Manager">Asset Manager</option>
                            <option value="Viewer">Viewer</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                    <div class="aum-field">
                        <label class="aum-label">Campus <span class="aum-required">*</span></label>
                        <select class="aum-select" id="aumCampus">
                            <option value="">Select campus...</option>
                        </select>
                    </div>
                </div>

                <!-- Status -->
                <div class="aum-field">
                    <label class="aum-label">Status</label>
                    <div class="aum-radio-group">
                        <label class="aum-radio-option">
                            <input type="radio" name="aumStatus" value="1" checked>
                            <span class="aum-radio-btn active-opt">Active</span>
                        </label>
                        <label class="aum-radio-option">
                            <input type="radio" name="aumStatus" value="0">
                            <span class="aum-radio-btn inactive-opt">Inactive</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="aum-footer">
                <button class="aum-btn-cancel" onclick="closeAddUserModal()">Cancel</button>
                <button class="aum-btn-submit" id="aumSubmitBtn" onclick="submitAddUser()">
                    <span id="aumSubmitText">Add User</span>
                    <span id="aumSubmitLoader" style="display:none;">Adding...</span>
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    loadCampuses();
}

function openAddUserModal() {
    if (!document.getElementById('addUserModal')) injectAddUserModal();
    resetAumForm();
    document.getElementById('addUserModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('aumName')?.focus(), 100);
}

function closeAddUserModal() {
    document.getElementById('addUserModal')?.classList.remove('active');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeAddUserModal();
});

// Load campuses into dropdown
async function loadCampuses() {
    try {
        const response = await fetch('/internal_portal/api/v1/campuses/list.php', {
            credentials: 'include'
        });
        const data = await response.json();
        const select = document.getElementById('aumCampus');
        if (!select) return;

        if (data.success && data.data?.length > 0) {
            data.data.forEach(campus => {
                const opt = document.createElement('option');
                opt.value = campus.id;
                opt.textContent = campus.campus_name;
                select.appendChild(opt);
            });
        } else {
            // Fallback hardcoded campuses if API not available
            [{ id: 1, name: 'Beirut Campus' }, { id: 2, name: 'Tripoli Campus' }].forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                select.appendChild(opt);
            });
        }
    } catch (err) {
        // Fallback if campuses API doesn't exist yet
        const select = document.getElementById('aumCampus');
        if (!select) return;
        [{ id: 1, name: 'Beirut Campus' }, { id: 2, name: 'Tripoli Campus' }].forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            select.appendChild(opt);
        });
    }
}

function toggleAumPassword() {
    const input = document.getElementById('aumPassword');
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Submit → POST /api/v1/users/create.php
async function submitAddUser() {
    const name     = document.getElementById('aumName').value.trim();
    const email    = document.getElementById('aumEmail').value.trim();
    const password = document.getElementById('aumPassword').value;
    const role     = document.getElementById('aumRole').value;
    const campus   = document.getElementById('aumCampus').value;
    const status   = document.querySelector('input[name="aumStatus"]:checked')?.value ?? '1';

    // Validate
    if (!name)              { showAumError('Full name is required.');        document.getElementById('aumName').focus();     return; }
    if (!email)             { showAumError('Email address is required.');    document.getElementById('aumEmail').focus();    return; }
    if (!isValidEmail(email)) { showAumError('Please enter a valid email.'); document.getElementById('aumEmail').focus();   return; }
    if (!password)          { showAumError('Password is required.');         document.getElementById('aumPassword').focus(); return; }
    if (password.length < 8){ showAumError('Password must be at least 8 characters.'); return; }
    if (!role)              { showAumError('Please select a role.');          document.getElementById('aumRole').focus();    return; }
    if (!campus)            { showAumError('Please select a campus.');        document.getElementById('aumCampus').focus();  return; }

    const body = { name, email, password, role, campus_id: parseInt(campus), is_active: parseInt(status) };

    setAumLoading(true);
    hideAumError();

    try {
        const response = await fetch('/internal_portal/api/v1/users/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(body)
        });

        const data = await response.json();

        if (data.success) {
            closeAddUserModal();
            showAumToast('User added successfully!');
            setTimeout(() => loadUsers(), 400);
        } else {
            showAumError(data.message || 'Failed to add user.');
        }
    } catch (err) {
        console.error(err);
        showAumError('Something went wrong. Please try again.');
    } finally {
        setAumLoading(false);
    }
}

// Helpers
function resetAumForm() {
    ['aumName', 'aumEmail', 'aumPassword'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    document.getElementById('aumRole').value   = '';
    document.getElementById('aumCampus').value = '';
    document.querySelector('input[name="aumStatus"][value="1"]').checked = true;
    hideAumError();
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showAumError(msg) {
    const el = document.getElementById('aumError');
    if (el) { el.textContent = '⚠ ' + msg; el.style.display = 'block'; }
}

function hideAumError() {
    const el = document.getElementById('aumError');
    if (el) el.style.display = 'none';
}

function setAumLoading(loading) {
    document.getElementById('aumSubmitBtn').disabled          = loading;
    document.getElementById('aumSubmitText').style.display    = loading ? 'none'   : 'inline';
    document.getElementById('aumSubmitLoader').style.display  = loading ? 'inline' : 'none';
}

function showAumToast(message) {
    const old = document.getElementById('aumToast');
    if (old) old.remove();
    const toast = document.createElement('div');
    toast.id = 'aumToast';
    toast.className = 'success-toast';
    toast.textContent = '✅ ' + message;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('visible'), 10);
    setTimeout(() => { toast.classList.remove('visible'); setTimeout(() => toast.remove(), 300); }, 3000);
}