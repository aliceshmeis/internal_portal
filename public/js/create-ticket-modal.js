// Create Ticket Modal

// =============================================
// MODAL HTML - Inject into page
// =============================================
function injectCreateTicketModal() {
    const modal = document.createElement('div');
    modal.id = 'createTicketModal';
    modal.innerHTML = `
        <div class="modal-overlay" id="modalOverlay" onclick="closeCreateModal()"></div>
        <div class="modal-container">
            <!-- Modal Header -->
            <div class="modal-header">
                <div>
                    <h2 class="modal-title">Create New Ticket</h2>
                    <p class="modal-subtitle">Submit a new support request</p>
                </div>
                <button class="modal-close" onclick="closeCreateModal()">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Error Message -->
                <div class="modal-error" id="modalError" style="display: none;"></div>

                <!-- Form -->
                <form id="createTicketForm" onsubmit="submitCreateTicket(event)">
                    
                    <!-- Title -->
                    <div class="form-group">
                        <label class="form-label">
                            Title <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            class="form-input" 
                            id="ticketTitle"
                            placeholder="Brief description of the issue"
                            maxlength="255"
                            required
                        >
                        <span class="form-hint">Be specific - e.g. "Printer not working in Lab 202"</span>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label class="form-label">
                            Description <span class="required">*</span>
                        </label>
                        <textarea 
                            class="form-textarea" 
                            id="ticketDescription"
                            placeholder="Describe the issue in detail..."
                            rows="4"
                            required
                        ></textarea>
                        <span class="form-hint">Include any relevant details, error messages, or steps to reproduce</span>
                    </div>

                    <!-- Priority & Assign Row -->
                    <div class="form-row">
                        <!-- Priority -->
                        <div class="form-group">
                            <label class="form-label">Priority</label>
                            <select class="form-select" id="ticketPriority">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
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
                    </div>

                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button class="btn-modal-cancel" onclick="closeCreateModal()">
                    Cancel
                </button>
                <button class="btn-modal-submit" id="submitBtn" onclick="submitCreateTicket(event)">
                    <span id="submitBtnText">Create Ticket</span>
                    <span id="submitBtnLoader" style="display:none;">Creating...</span>
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    loadUsersForAssign();
}

// =============================================
// OPEN MODAL
// =============================================
function openCreateModal() {
    const modal = document.getElementById('createTicketModal');
    if (!modal) {
        injectCreateTicketModal();
    }
    
    // Reset form
    resetForm();
    
    // Show modal
    document.getElementById('createTicketModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Focus title input
    setTimeout(() => {
        document.getElementById('ticketTitle')?.focus();
    }, 100);
}

// =============================================
// CLOSE MODAL
// =============================================
function closeCreateModal() {
    const modal = document.getElementById('createTicketModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Close on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeCreateModal();
});

// =============================================
// LOAD USERS FOR ASSIGN DROPDOWN
// =============================================
async function loadUsersForAssign() {
    try {
        const response = await fetch('/internal_portal/api/v1/users/list.php');
        const data = await response.json();

        if (data.success && data.data) {
            const select = document.getElementById('ticketAssignTo');
            if (!select) return;

            // Filter only Staff users
            const staffUsers = data.data.filter(u => u.role === 'Staff' || u.role === 'Asset Manager');

            staffUsers.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = `${user.name} (${user.role})`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Failed to load users:', error);
    }
}

// =============================================
// SUBMIT TICKET
// =============================================
async function submitCreateTicket(e) {
    e.preventDefault();

    const title = document.getElementById('ticketTitle').value.trim();
    const description = document.getElementById('ticketDescription').value.trim();
    const priority = document.getElementById('ticketPriority').value;
    const assignTo = document.getElementById('ticketAssignTo').value;

    // Validate
    if (!title) {
        showModalError('Title is required');
        document.getElementById('ticketTitle').focus();
        return;
    }

    if (!description) {
        showModalError('Description is required');
        document.getElementById('ticketDescription').focus();
        return;
    }

    // Build request body
    const body = { title, description, priority };
    if (assignTo) {
        body.assigned_to = parseInt(assignTo);
    }

    // Show loading state
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
            // Success!
            closeCreateModal();
            showSuccessToast('Ticket created successfully!');
            
            // Reload tickets list
            setTimeout(() => {
                loadTickets();
            }, 500);
        } else {
            showModalError(data.message || 'Failed to create ticket');
        }
    } catch (error) {
        console.error('Error:', error);
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
    
    // Reset priority to Medium
    const priority = document.getElementById('ticketPriority');
    if (priority) priority.value = 'Medium';
    
    // Reset assign to empty
    const assignTo = document.getElementById('ticketAssignTo');
    if (assignTo) assignTo.value = '';
}

function showModalError(message) {
    const errorDiv = document.getElementById('modalError');
    if (errorDiv) {
        errorDiv.textContent = '⚠ ' + message;
        errorDiv.style.display = 'block';
    }
}

function hideModalError() {
    const errorDiv = document.getElementById('modalError');
    if (errorDiv) errorDiv.style.display = 'none';
}

function setSubmitLoading(loading) {
    const btn = document.getElementById('submitBtn');
    const text = document.getElementById('submitBtnText');
    const loader = document.getElementById('submitBtnLoader');
    
    if (btn) btn.disabled = loading;
    if (text) text.style.display = loading ? 'none' : 'inline';
    if (loader) loader.style.display = loading ? 'inline' : 'none';
}

function showSuccessToast(message) {
    // Remove existing toast
    const existing = document.getElementById('successToast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'successToast';
    toast.className = 'success-toast';
    toast.textContent = '✅ ' + message;
    document.body.appendChild(toast);

    // Show and auto-hide
    setTimeout(() => toast.classList.add('visible'), 10);
    setTimeout(() => {
        toast.classList.remove('visible');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Initialize modal on page load
document.addEventListener('DOMContentLoaded', () => {
    injectCreateTicketModal();
});