// =============================================
// CREATE TICKET PAGE JS
// Calls: POST /api/v1/tickets/create.php
// =============================================

const API_BASE = '/internal_portal/api/v1';

document.addEventListener('DOMContentLoaded', () => {
    loadDynamicAssets();//this ensures html elements exist before js tries to access them
});

// Load assets for Printer / Hardware dropdowns
async function loadDynamicAssets() {
    if (TICKET_CATEGORY === 'Printer Issue') {
        await loadPrinters();
    } else if (TICKET_CATEGORY === 'Hardware Issue') {
        await loadMyAssets();
    }
}

// Load all printers from assets API
async function loadPrinters() {
    const select = document.getElementById('printerName');
    const hint   = select?.nextElementSibling;
    if (!select) return;

    try {
        const response = await fetch(`${API_BASE}/assets/list.php?category=Printer`, {
            credentials: 'include'
        });
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            data.data.forEach(asset => {
                const opt = document.createElement('option');
                opt.value = asset.name;
                opt.textContent = `${asset.name} (${asset.campus_name || 'Campus'})`;
                select.appendChild(opt);
            });
            if (hint) hint.style.display = 'none';
        } else {
            if (hint) hint.textContent = 'No printers found. You can describe it in the description.';
        }
    } catch (err) {
        console.error('Failed to load printers:', err);
        if (hint) hint.textContent = 'Could not load printers.';
    }
}

// Load current user's assigned assets
async function loadMyAssets() {
    const select = document.getElementById('hardwareAsset');
    const hint   = select?.nextElementSibling;
    if (!select) return;

    try {
        const response = await fetch(`${API_BASE}/assets/list.php`, {
            credentials: 'include'
        });
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            // Show assets assigned to logged-in user
            const myAssets = data.data.filter(a => a.status === 'In Use');
            if (myAssets.length > 0) {
                myAssets.forEach(asset => {
                    const opt = document.createElement('option');
                    opt.value = asset.name;
                    opt.textContent = `${asset.name} (${asset.category})`;
                    select.appendChild(opt);
                });
                if (hint) hint.style.display = 'none';
            } else {
                if (hint) hint.textContent = 'No assigned devices found.';
            }
        }
    } catch (err) {
        console.error('Failed to load assets:', err);
    }
}

// Build extra details from dynamic fields into description
function buildExtraDetails() {
    let extra = '';

    if (TICKET_CATEGORY === 'Printer Issue') {
        const printer   = document.getElementById('printerName')?.value;
        const issueType = document.getElementById('printerIssueType')?.value;
        const urgent    = document.getElementById('printerUrgent')?.checked;
        if (printer)   extra += `\nPrinter: ${printer}`;
        if (issueType) extra += `\nIssue Type: ${issueType}`;
        if (urgent)    extra += `\nUrgent: Yes`;

    } else if (TICKET_CATEGORY === 'IT & Software') {
        const software  = document.getElementById('softwareName')?.value;
        const error     = document.getElementById('errorMessage')?.value;
        const happened  = document.getElementById('whatHappened')?.value;
        if (software)  extra += `\nSoftware: ${software}`;
        if (error)     extra += `\nError Message: ${error}`;
        if (happened)  extra += `\nWhat happened: ${happened}`;

    } else if (TICKET_CATEGORY === 'Network Problem') {
        const location  = document.getElementById('networkLocation')?.value;
        const connType  = document.querySelector('input[name="connectionType"]:checked')?.value;
        const affecting = document.getElementById('affectingOthers')?.checked;
        if (location)  extra += `\nLocation: ${location}`;
        if (connType)  extra += `\nConnection Type: ${connType}`;
        if (affecting) extra += `\nAffecting others: Yes`;

    } else if (TICKET_CATEGORY === 'Hardware Issue') {
        const asset     = document.getElementById('hardwareAsset')?.value;
        const issueType = document.getElementById('hardwareIssueType')?.value;
        if (asset)     extra += `\nDevice: ${asset}`;
        if (issueType) extra += `\nIssue Type: ${issueType}`;

    } else if (TICKET_CATEGORY === 'Access Request') {
        const system    = document.getElementById('systemName')?.value;
        const level     = document.getElementById('accessLevel')?.value;
        const reason    = document.getElementById('accessReason')?.value;
        const approved  = document.getElementById('managerApproved')?.checked;
        if (system)    extra += `\nSystem: ${system}`;
        if (level)     extra += `\nAccess Level: ${level}`;
        if (reason)    extra += `\nReason: ${reason}`;
        if (approved)  extra += `\nManager Approved: Yes`;

    } else if (TICKET_CATEGORY === 'Item Request') {
        const item      = document.getElementById('itemName')?.value;
        const qty       = document.getElementById('itemQuantity')?.value;
        const reason    = document.getElementById('itemReason')?.value;
        if (item)      extra += `\nItem: ${item}`;
        if (qty)       extra += `\nQuantity: ${qty}`;
        if (reason)    extra += `\nReason: ${reason}`;
    }

    return extra;
}

// Submit ticket → POST /api/v1/tickets/create.php
async function submitTicket() {
    const title       = document.getElementById('ticketTitle').value.trim();
    const description = document.getElementById('ticketDescription').value.trim();
    const priority    = document.querySelector('input[name="priority"]:checked')?.value || 'Medium';

    // Validate
    if (!title) {
        showError('Ticket title is required.');
        document.getElementById('ticketTitle').focus();
        return;
    }
    if (!description) {
        showError('Description is required.');
        document.getElementById('ticketDescription').focus();
        return;
    }

    // Category-specific validation
    if (TICKET_CATEGORY === 'IT & Software') {
        const software = document.getElementById('softwareName')?.value.trim();
        if (!software) { showError('Software / System name is required.'); return; }
    }
    if (TICKET_CATEGORY === 'Access Request') {
        const system = document.getElementById('systemName')?.value.trim();
        if (!system) { showError('System / Resource name is required.'); return; }
    }
    if (TICKET_CATEGORY === 'Item Request') {
        const item = document.getElementById('itemName')?.value.trim();
        if (!item) { showError('Item name is required.'); return; }
    }

    // Build full description with extra details
    const extraDetails = buildExtraDetails();
    const fullDescription = description + (extraDetails ? '\n\n--- Additional Details ---' + extraDetails : '');

    const body = {
        title:       title,
        description: fullDescription,
        priority:    priority,
        category:    TICKET_CATEGORY || null,
    };

    setLoading(true);
    hideError();

    try {
        const response = await fetch(`${API_BASE}/tickets/create.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(body)
        });

        const data = await response.json();

        if (data.success) {
            showSuccess('✅ Ticket submitted successfully! Redirecting...');
            setTimeout(() => {
                window.location.href = '../tickets/my-tickets.php';
            }, 1500);
        } else {
            showError(data.message || 'Failed to submit ticket.');
        }
    } catch (err) {
        console.error(err);
        showError('Something went wrong. Please try again.');
    } finally {
        setLoading(false);
    }
}

// Helpers
function showError(msg) {
    const el = document.getElementById('ctError');
    el.textContent = '⚠ ' + msg;
    el.style.display = 'block';
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideError() {
    document.getElementById('ctError').style.display = 'none';
}

function showSuccess(msg) {
    document.getElementById('ctSuccess').textContent = msg;
    document.getElementById('ctSuccess').style.display = 'block';
    document.getElementById('ctError').style.display   = 'none';
}

function setLoading(loading) {
    document.getElementById('submitBtn').disabled          = loading;
    document.getElementById('submitText').style.display    = loading ? 'none'   : 'inline';
    document.getElementById('submitLoader').style.display  = loading ? 'inline' : 'none';
}