// Create Ticket Modal - Full version with location, category, campus

let allStaff = [];

// =============================================
// MODAL HTML
// =============================================
function injectCreateTicketModal() {
    const modal = document.createElement('div');
    modal.id = 'createTicketModal';
    modal.innerHTML = `
        <div class="modal-overlay" id="modalOverlay" onclick="closeCreateModal()"></div>
        <div class="modal-container">

            <div class="modal-header">
                <div>
                    <h2 class="modal-title">Create New Ticket</h2>
                    <p class="modal-subtitle">Submit a new support request</p>
                </div>
                <button class="modal-close" type="button" onclick="closeCreateModal()">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <div class="modal-error" id="modalError" style="display:none;"></div>

                <form id="createTicketForm">

                    <!-- Title -->
                    <div class="form-group">
                        <label class="form-label">Title <span class="required">*</span></label>
                        <input type="text" class="form-input" id="ticketTitle"
                            placeholder='e.g. "Printer not working in Lab 202"'
                            maxlength="255">
                    </div>

                    <!-- Category -->
                    <div class="form-group">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select class="form-select" id="ticketCategory" onchange="handleCategoryChange()">
                            <option value="">Select category...</option>
                            <option value="Printer Issue">🖨 Printer Issue</option>
                            <option value="IT & Software">💻 IT & Software</option>
                            <option value="Network Problem">🌐 Network Problem</option>
                            <option value="Hardware Issue">🔧 Hardware Issue</option>
                            <option value="Access Request">🔑 Access Request</option>
                            <option value="Item Request">📦 Item Request</option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label class="form-label">Description <span class="required">*</span></label>
                        <textarea class="form-textarea" id="ticketDescription"
                            placeholder="Describe the issue in detail..."
                            rows="4"></textarea>
                    </div>

                    <!-- Network SSID (only for Network Problem) -->
                    <div class="form-group" id="ssidGroup" style="display:none;">
                        <label class="form-label">WiFi Network (SSID)</label>
                        <input type="text" class="form-input" id="ticketSsid"
                            placeholder='e.g. "LIU-Staff"'>
                        <span class="form-hint">Enter the WiFi network name you are having issues with</span>
                    </div>

                    <!-- Priority & Campus Row -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Priority</label>
                            <select class="form-select" id="ticketPriority">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Campus</label>
                            <select class="form-select" id="ticketCampus" onchange="handleCampusChange()">
                                <option value="">Select campus...</option>
                            </select>
                        </div>
                    </div>

                    <!-- Location Row -->
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <div class="form-row-3">
                            <input type="text" class="form-input" id="ticketBuilding" placeholder="Building / Block">
                            <input type="text" class="form-input" id="ticketFloor" placeholder="Floor">
                            <input type="text" class="form-input" id="ticketRoom" placeholder="Room No.">
                        </div>
                        <span class="form-hint">e.g. Block A · Floor 2 · Room 204</span>
                    </div>

                    <!-- Assign To (Admin only) -->
                    <div class="form-group" id="assignToGroup">
                        <label class="form-label">
                            Assign To
                            <span class="admin-only-badge">Admin only</span>
                        </label>
                        <select class="form-select" id="ticketAssignTo">
                            <option value="">Unassigned</option>
                        </select>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button class="btn-modal-cancel" type="button" onclick="closeCreateModal()">Cancel</button>
                <button class="btn-modal-submit" id="submitBtn" type="button" onclick="submitCreateTicket()">
                    <span id="submitBtnText">Create Ticket</span>
                    <span id="submitBtnLoader" style="display:none;">Creating...</span>
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    loadUsersForAssign();
    loadCampuses();
}

// =============================================
// OPEN / CLOSE
// =============================================
function openCreateModal() {
    if (!document.getElementById('createTicketModal')) injectCreateTicketModal();
    resetForm();
    document.getElementById('createTicketModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('ticketTitle')?.focus(), 100);
}

function closeCreateModal() {
    const modal = document.getElementById('createTicketModal');
    if (modal) { modal.classList.remove('active'); document.body.style.overflow = ''; }
}

document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeCreateModal(); });

// =============================================
// CATEGORY CHANGE
// =============================================
function handleCategoryChange() {
    const category  = document.getElementById('ticketCategory').value;
    const ssidGroup = document.getElementById('ssidGroup');
    const priority  = document.getElementById('ticketPriority');

    ssidGroup.style.display = (category === 'Network Problem') ? 'block' : 'none';

    const priorityMap = {
        'Network Problem': 'High',
        'Access Request':  'Low',
        'Item Request':    'Low',
        'Printer Issue':   'Medium',
        'IT & Software':   'High',
        'Hardware Issue':  'Medium',
    };
    if (priorityMap[category]) priority.value = priorityMap[category];
}

// =============================================
// CAMPUS CHANGE — filter assign dropdown
// =============================================
function handleCampusChange() {
    const campusId = document.getElementById('ticketCampus').value;
    populateAssignTo(campusId);
}

function populateAssignTo(campusId) {
    const select = document.getElementById('ticketAssignTo');
    if (!select) return;

    const filtered = campusId
        ? allStaff.filter(u => String(u.campus_id) === String(campusId))
        : allStaff;

    select.innerHTML = '<option value="">Unassigned</option>';

    if (filtered.length === 0) {
        select.innerHTML += '<option value="" disabled>No IT staff in this campus</option>';
        return;
    }

    filtered.forEach(user => {
        const opt = document.createElement('option');
        opt.value = user.id;
        opt.textContent = `${user.name} (${user.role})`;
        select.appendChild(opt);
    });
}

// =============================================
// LOAD CAMPUSES
// =============================================
async function loadCampuses() {
    try {
        const res  = await fetch('/internal_portal/api/v1/campuses/list.php', { credentials: 'include' });
        const data = await res.json();
        const sel  = document.getElementById('ticketCampus');
        if (!sel) return;

        sel.innerHTML = '<option value="">Select campus...</option>';
        if (data.success && data.data) {
            data.data.forEach(c => {
                sel.innerHTML += `<option value="${c.id}">${c.campus_name}</option>`;
            });
        }
    } catch (e) {
        const sel = document.getElementById('ticketCampus');
        if (sel) sel.innerHTML = '<option value="">Could not load</option>';
    }
}

// =============================================
// LOAD USERS FOR ASSIGN
// =============================================
async function loadUsersForAssign() {
    try {
        const response = await fetch('/internal_portal/api/v1/users/list.php', { credentials: 'include' });
        const data     = await response.json();

        if (data.success && data.data) {
            allStaff = data.data.filter(u =>
                (u.role === 'Staff' && u.department_name === 'IT') ||
                u.role === 'Asset Manager'
            );
            populateAssignTo('');
        }
    } catch (e) {
        console.error('Failed to load users:', e);
    }
}

// =============================================
// SUBMIT — no event parameter, type=button prevents form submission
// =============================================
async function submitCreateTicket() {
    const title       = document.getElementById('ticketTitle').value.trim();
    const category    = document.getElementById('ticketCategory').value;
    const description = document.getElementById('ticketDescription').value.trim();
    const priority    = document.getElementById('ticketPriority').value;
    const campus_id   = document.getElementById('ticketCampus').value;
    const building    = document.getElementById('ticketBuilding').value.trim();
    const floor       = document.getElementById('ticketFloor').value.trim();
    const room        = document.getElementById('ticketRoom').value.trim();
    const ssid        = document.getElementById('ticketSsid')?.value.trim() || '';
    const assignTo    = document.getElementById('ticketAssignTo').value;

    if (!title)       { showModalError('Title is required');        document.getElementById('ticketTitle').focus();       return; }
    if (!category)    { showModalError('Please select a category'); document.getElementById('ticketCategory').focus();    return; }
    if (!description) { showModalError('Description is required');  document.getElementById('ticketDescription').focus(); return; }

    const body = { title, category, description, priority };
    if (campus_id) body.campus_id   = parseInt(campus_id);
    if (building)  body.building    = building;
    if (floor)     body.floor       = floor;
    if (room)      body.room        = room;
    if (ssid)      body.ssid        = ssid;
    if (assignTo)  body.assigned_to = parseInt(assignTo);

    setSubmitLoading(true);
    hideModalError();

    try {
        const response = await fetch('/internal_portal/api/v1/tickets/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(body)
        });
        const data = await response.json();

        if (data.success) {
            closeCreateModal();
            showSuccessToast('Ticket created successfully!');
            setTimeout(() => loadTickets(), 500);
        } else {
            showModalError(data.message || 'Failed to create ticket');
        }
    } catch (error) {
        showModalError('Failed to create ticket. Please try again.');
    } finally {
        setSubmitLoading(false);
    }
}

// =============================================
// HELPERS
// =============================================
function resetForm() {
    const form = document.getElementById('createTicketForm');
    if (form) form.reset();
    hideModalError();
    document.getElementById('ticketPriority').value    = 'Medium';
    document.getElementById('ticketAssignTo').value    = '';
    document.getElementById('ssidGroup').style.display = 'none';
    populateAssignTo('');
}

function showModalError(message) {
    const el = document.getElementById('modalError');
    if (el) { el.textContent = '⚠ ' + message; el.style.display = 'block'; }
}

function hideModalError() {
    const el = document.getElementById('modalError');
    if (el) el.style.display = 'none';
}

function setSubmitLoading(loading) {
    const btn    = document.getElementById('submitBtn');
    const text   = document.getElementById('submitBtnText');
    const loader = document.getElementById('submitBtnLoader');
    if (btn)    btn.disabled          = loading;
    if (text)   text.style.display   = loading ? 'none'   : 'inline';
    if (loader) loader.style.display = loading ? 'inline' : 'none';
}

function showSuccessToast(message) {
    const existing = document.getElementById('successToast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.id        = 'successToast';
    toast.className = 'success-toast';
    toast.textContent = '✅ ' + message;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('visible'), 10);
    setTimeout(() => { toast.classList.remove('visible'); setTimeout(() => toast.remove(), 300); }, 3000);
}

document.addEventListener('DOMContentLoaded', () => { injectCreateTicketModal(); });