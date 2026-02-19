// Create Ticket Page JavaScript
const API_BASE = '/internal_portal/api/v1';

let selectedFiles = [];

// ─── INIT ─────────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    if (TICKET_CATEGORY === 'Printer Issue')  loadPrinters();
    if (TICKET_CATEGORY === 'Hardware Issue') loadMyAssets();
    if (TICKET_CATEGORY === 'Network Problem') setupConnectionTypeListener();
    setupDropZone();
});

// ─── PRINTERS ─────────────────────────────────────────────────────────────────

async function loadPrinters() {
    try {
        const res  = await fetch(`${API_BASE}/assets/list.php?type=Printer`, { credentials: 'include' });
        const data = await res.json();
        const sel  = document.getElementById('printerName');

        if (data.success && data.data && data.data.length > 0) {
            sel.innerHTML = '<option value="">Select printer...</option>';
            data.data.forEach(p => {
                const location = buildLocation(p.building, p.floor, p.room);
                sel.innerHTML += `<option value="${p.id}" data-building="${p.building || ''}" data-floor="${p.floor || ''}" data-room="${p.room || ''}">${escapeHtml(p.name)}${location ? ' – ' + location : ''}</option>`;
            });
            sel.addEventListener('change', handlePrinterSelect);
        } else {
            sel.innerHTML = '<option value="">No printers found</option>';
        }
    } catch (e) {
        document.getElementById('printerName').innerHTML = '<option value="">Failed to load printers</option>';
    }
}

function handlePrinterSelect() {
    const sel     = document.getElementById('printerName');
    const opt     = sel.options[sel.selectedIndex];
    const box     = document.getElementById('printerLocationBox');
    const locText = document.getElementById('printerLocationText');
    const building = opt.dataset.building;
    const floor    = opt.dataset.floor;
    const room     = opt.dataset.room;

    if (sel.value && (building || floor || room)) {
        const loc = buildLocation(building, floor, room);
        locText.textContent = loc || '—';
        box.style.display   = 'block';

        // Auto-fill location fields
        if (building) document.getElementById('locationBuilding').value = building;
        if (floor)    document.getElementById('locationFloor').value    = floor;
        if (room)     document.getElementById('locationRoom').value     = room;
    } else {
        box.style.display = 'none';
    }
}

function buildLocation(building, floor, room) {
    return [building, floor ? `Floor ${floor}` : '', room ? `Room ${room}` : ''].filter(Boolean).join(', ');
}

function handlePrinterUrgent(checkbox) {
    if (checkbox.checked) {
        document.querySelector('input[name="priority"][value="High"]').checked = true;
    }
}

// ─── HARDWARE ASSETS ──────────────────────────────────────────────────────────

async function loadMyAssets() {
    try {
        const res  = await fetch(`${API_BASE}/assets/my-assets.php`, { credentials: 'include' });
        const data = await res.json();
        const sel  = document.getElementById('hardwareAsset');
        const hint = document.getElementById('hardwareHint');

        if (data.success && data.data && data.data.length > 0) {
            sel.innerHTML = '<option value="">Select your device...</option>';
            sel.innerHTML += '<option value="not-listed">⚠️ My device is not listed</option>';
            data.data.forEach(a => {
                sel.innerHTML += `<option value="${a.id}">${escapeHtml(a.name)} (${escapeHtml(a.asset_tag || 'No tag')})</option>`;
            });
            hint.textContent = `${data.data.length} device(s) assigned to you`;
        } else {
            sel.innerHTML = '<option value="">No assigned devices found</option>';
            sel.innerHTML += '<option value="not-listed">⚠️ Report a device not listed</option>';
            hint.textContent = 'No assets assigned to you — select "Report a device not listed"';
        }
    } catch (e) {
        document.getElementById('hardwareAsset').innerHTML = '<option value="">Failed to load devices</option>';
    }
}

// ─── NETWORK – SSID TOGGLE ────────────────────────────────────────────────────

function setupConnectionTypeListener() {
    document.querySelectorAll('input[name="connectionType"]').forEach(radio => {
        radio.addEventListener('change', () => {
            const ssidField = document.getElementById('ssidField');
            ssidField.style.display = radio.value === 'Wired' ? 'none' : 'block';
        });
    });
}

function handleAffectingOthers(checkbox) {
    if (checkbox.checked) {
        document.querySelector('input[name="priority"][value="High"]').checked = true;
    }
}

// ─── FILE UPLOAD ──────────────────────────────────────────────────────────────

function setupDropZone() {
    const zone = document.getElementById('dropZone');
    if (!zone) return;

    zone.addEventListener('dragover', e => {
        e.preventDefault();
        zone.classList.add('drag-over');
    });

    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));

    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('drag-over');
        handleFileSelect(e.dataTransfer.files);
    });
}

function handleFileSelect(files) {
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowed = ['image/png', 'image/jpeg', 'image/gif', 'application/pdf',
                     'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];

    Array.from(files).forEach(file => {
        if (!allowed.includes(file.type)) {
            showError(`${file.name}: File type not allowed`); return;
        }
        if (file.size > maxSize) {
            showError(`${file.name}: File exceeds 5MB limit`); return;
        }
        if (selectedFiles.find(f => f.name === file.name && f.size === file.size)) return;
        selectedFiles.push(file);
    });

    renderFileList();
}

function renderFileList() {
    const list = document.getElementById('fileList');
    if (!selectedFiles.length) { list.innerHTML = ''; return; }

    list.innerHTML = selectedFiles.map((file, i) => `
        <div class="ct-file-item">
            <span class="ct-file-icon">${getFileIcon(file.type)}</span>
            <div class="ct-file-info">
                <div class="ct-file-name">${escapeHtml(file.name)}</div>
                <div class="ct-file-size">${formatFileSize(file.size)}</div>
            </div>
            <button class="ct-file-remove" onclick="removeFile(${i})">✕</button>
        </div>
    `).join('');
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    renderFileList();
}

function getFileIcon(type) {
    if (type.startsWith('image/'))       return '🖼️';
    if (type === 'application/pdf')      return '📄';
    if (type.includes('word'))           return '📝';
    return '📎';
}

function formatFileSize(bytes) {
    if (bytes < 1024)       return bytes + ' B';
    if (bytes < 1024*1024)  return (bytes/1024).toFixed(1) + ' KB';
    return (bytes/(1024*1024)).toFixed(1) + ' MB';
}

// ─── SUBMIT TICKET ────────────────────────────────────────────────────────────

async function submitTicket() {
    clearMessages();

    const title       = document.getElementById('ticketTitle').value.trim();
    const description = document.getElementById('ticketDescription').value.trim();
    const priority    = document.querySelector('input[name="priority"]:checked')?.value || DEFAULT_PRIORITY;
    const building    = document.getElementById('locationBuilding').value.trim();
    const floor       = document.getElementById('locationFloor').value.trim();
    const room        = document.getElementById('locationRoom').value.trim();

    if (!title)       { showError('Ticket title is required');       return; }
    if (!description) { showError('Description is required');        return; }

    // Build extra fields per category
    let extra = {};
    if (TICKET_CATEGORY === 'Printer Issue') {
        const printer = document.getElementById('printerName').value;
        const issue   = document.getElementById('printerIssueType').value;
        if (!printer) { showError('Please select a printer'); return; }
        if (!issue)   { showError('Please select an issue type'); return; }
        extra = { printer_id: printer, printer_issue: issue, urgent: document.getElementById('printerUrgent').checked };
    }
    else if (TICKET_CATEGORY === 'IT & Software') {
        const sw = document.getElementById('softwareName').value.trim();
        if (!sw) { showError('Software / system name is required'); return; }
        extra = {
            software_name: sw,
            error_message: document.getElementById('errorMessage').value.trim(),
            what_happened: document.getElementById('whatHappened').value.trim()
        };
    }
    else if (TICKET_CATEGORY === 'Network Problem') {
        extra = {
            connection_type:   document.querySelector('input[name="connectionType"]:checked')?.value,
            ssid:              document.getElementById('networkSsid')?.value.trim(),
            affecting_others:  document.getElementById('affectingOthers').checked
        };
    }
    else if (TICKET_CATEGORY === 'Hardware Issue') {
        const asset = document.getElementById('hardwareAsset').value;
        const issue = document.getElementById('hardwareIssueType').value;
        if (!issue) { showError('Please select an issue type'); return; }
        extra = { asset_id: asset === 'not-listed' ? null : asset, hardware_issue: issue };
    }
    else if (TICKET_CATEGORY === 'Access Request') {
        const sys = document.getElementById('systemName').value.trim();
        if (!sys) { showError('System / resource name is required'); return; }
        extra = {
            system_name:      sys,
            access_level:     document.getElementById('accessLevel').value,
            access_reason:    document.getElementById('accessReason').value.trim(),
            manager_approved: document.getElementById('managerApproved').checked
        };
    }
    else if (TICKET_CATEGORY === 'Item Request') {
        const item = document.getElementById('itemName').value.trim();
        if (!item) { showError('Item name is required'); return; }
        extra = {
            item_name:     item,
            item_quantity: document.getElementById('itemQuantity').value,
            item_reason:   document.getElementById('itemReason').value.trim()
        };
    }

    // Build description with extra fields appended
    let fullDescription = description;
    if (Object.keys(extra).length) {
        fullDescription += '\n\n--- Details ---\n' + Object.entries(extra)
            .filter(([, v]) => v !== null && v !== '' && v !== false && v !== undefined)
            .map(([k, v]) => `${k.replace(/_/g, ' ')}: ${v === true ? 'Yes' : v}`)
            .join('\n');
    }

    setLoading(true);

    try {
        // Step 1: Create ticket
        const res  = await fetch(`${API_BASE}/tickets/create.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                title,
                description: fullDescription,
                priority,
                category: TICKET_CATEGORY,
                building: building || null,
                floor:    floor    || null,
                room:     room     || null,
                ssid:     extra.ssid || null
            })
        });

        const data = await res.json();

        if (!data.success) {
            showError(data.message || 'Failed to submit ticket');
            setLoading(false);
            return;
        }

        const ticketId = data.data?.id;

        // Step 2: Upload attachments if any
        if (selectedFiles.length > 0 && ticketId) {
            await uploadAttachments(ticketId);
        }

        showSuccess('Ticket submitted successfully! Redirecting...');
        setTimeout(() => {
            window.location.href = '../tickets/my-tickets.php';
        }, 1500);

    } catch (e) {
        showError('Network error. Please try again.');
        setLoading(false);
    }
}

// ─── UPLOAD ATTACHMENTS ───────────────────────────────────────────────────────

async function uploadAttachments(ticketId) {
    const formData = new FormData();
    formData.append('ticket_id', ticketId);
    selectedFiles.forEach(file => formData.append('attachments[]', file));

    try {
        const res  = await fetch(`${API_BASE}/tickets/upload.php`, {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        const data = await res.json();
        if (!data.success) console.warn('Attachment upload failed:', data.message);
    } catch (e) {
        console.warn('Attachment upload error:', e);
    }
}

// ─── UI HELPERS ───────────────────────────────────────────────────────────────

function setLoading(loading) {
    const btn    = document.getElementById('submitBtn');
    const text   = document.getElementById('submitText');
    const loader = document.getElementById('submitLoader');
    btn.disabled    = loading;
    text.style.display   = loading ? 'none'  : 'inline';
    loader.style.display = loading ? 'inline' : 'none';
}

function showError(msg) {
    const el = document.getElementById('ctError');
    el.textContent    = msg;
    el.style.display  = 'block';
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function showSuccess(msg) {
    const el = document.getElementById('ctSuccess');
    el.textContent   = msg;
    el.style.display = 'block';
}

function clearMessages() {
    document.getElementById('ctError').style.display   = 'none';
    document.getElementById('ctSuccess').style.display = 'none';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}