// quotation-detail.js
const API_BASE    = '/internal_portal/api/v1';
const QUOTATION_ID = parseInt(document.getElementById('quotation-id-data').dataset.id);

document.addEventListener('DOMContentLoaded', () => {
    loadQuotation();
});

// ─── LOAD ─────────────────────────────────────────────────────────────────────
async function loadQuotation() {
    try {
        const res    = await fetch(`${API_BASE}/quotations/show.php?id=${QUOTATION_ID}`, { credentials: 'include' });
        const result = await res.json();
        if (result.success) {
            renderDetail(result.data);
        } else showError(result.message || 'Failed to load');
    } catch (e) { showError('Network error'); }
}

function renderDetail(q) {
    document.getElementById('loading').style.display  = 'none';
    document.getElementById('quo-detail').style.display = 'block';

    // Header
    document.getElementById('quo-number-title').textContent = '#' + q.quotation_number;
    document.getElementById('quo-status-badge').innerHTML   = statusBadge(q.status);

    // Left panel — info
    document.getElementById('quo-supplier-name').textContent  = q.supplier_name   || '—';
    document.getElementById('quo-supplier-email').textContent = q.supplier_email  || '—';
    document.getElementById('quo-supplier-phone').textContent = q.supplier_phone  || '—';
    document.getElementById('quo-campus').textContent         = q.campus_name     || '—';
    document.getElementById('quo-total').textContent          = '$' + parseFloat(q.total_amount).toLocaleString();
    document.getElementById('quo-date').textContent           = formatDate(q.quotation_date);
    document.getElementById('quo-valid').textContent          = formatDate(q.valid_until);
    document.getElementById('quo-created-by').textContent     = q.created_by_name || '—';
    document.getElementById('quo-created-at').textContent     = formatDate(q.created_at);
    document.getElementById('quo-notes').textContent          = q.notes || 'No notes';

    if (q.approved_by_name) {
        document.getElementById('quo-approved-row').style.display = 'flex';
        document.getElementById('quo-approved-by').textContent    = q.approved_by_name + ' on ' + formatDate(q.approved_at);
    }

    if (q.rejection_reason) {
        document.getElementById('quo-rejection-row').style.display = 'block';
        document.getElementById('quo-rejection-reason').textContent = q.rejection_reason;
    }

    // File
    renderFile(q.file_path, q.uploaded_at);

    // Action buttons
    renderActions(q);

    // Quotation requests table
    renderRequests(q.requests || []);
}

function renderFile(path, uploaded_at) {
    const box = document.getElementById('quo-file-box');
    if (path) {
        const uploadedText = uploaded_at ? `Uploaded ${formatDate(uploaded_at)}` : '';
        box.innerHTML = `
            <div class="quo-file-box">
                <span class="quo-file-icon">📄</span>
                <div style="flex:1;">
                    <div class="quo-file-name">${escapeHtml(path.split('/').pop())}</div>
                    <div style="display:flex;align-items:center;gap:12px;margin-top:4px;">
                        <a href="${escapeHtml(path)}" target="_blank" class="quo-file-link">View / Download PDF</a>
                        ${uploadedText ? `<span style="font-size:11.5px;color:var(--color-text-secondary);">${uploadedText}</span>` : ''}
                    </div>
                </div>
                <span style="font-size:11px;background:#dcfce7;color:#166534;padding:3px 8px;border-radius:20px;font-weight:600;">PDF</span>
            </div>`;
    } else {
        box.innerHTML = `<div class="quo-no-file">No supplier PDF uploaded yet.</div>`;
    }
}

function renderActions(q) {
    const isExpired  = q.valid_until < today();
    const isPending  = q.status === 'Pending';
    const isApproved = q.status === 'Approved';

    // Approve/Reject buttons
    const approveBtn = document.getElementById('btn-approve');
    const rejectBtn  = document.getElementById('btn-reject');
    if (approveBtn) {
        approveBtn.disabled = !isPending || isExpired;
        approveBtn.title    = isExpired ? 'Cannot approve an expired quotation' : '';
    }
    if (rejectBtn) {
        rejectBtn.disabled = !isPending;
    }

    // Generate PO button
    const poBtn = document.getElementById('btn-generate-po');
    if (poBtn) {
        poBtn.style.display = isApproved ? 'block' : 'none';
    }

    // Expiry warning
    if (isExpired && isPending) {
        const warn = document.getElementById('expiry-warning');
        if (warn) warn.style.display = 'block';
    }
}

function renderRequests(requests) {
    const tbody = document.getElementById('req-tbody');
    if (!requests.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;color:var(--color-text-secondary);">No requests sent yet.</td></tr>';
        return;
    }
    tbody.innerHTML = requests.map(r => `
        <tr>
            <td><strong>${escapeHtml(r.supplier_name)}</strong></td>
            <td style="font-size:12px;color:var(--color-text-secondary);">${escapeHtml(r.supplier_email)}</td>
            <td>${formatDate(r.requested_at)}</td>
            <td>${r.response_due_date ? formatDate(r.response_due_date) : '—'}</td>
            <td>${reqStatusBadge(r.status)}</td>
            <td>
                <div style="display:flex;gap:6px;">
                    ${r.status !== 'Received' ? `<button class="quo-action-btn" onclick="resendRequest(${r.id})">Resend</button>` : ''}
                    ${r.status === 'Sent'     ? `<button class="quo-action-btn success" onclick="markReceived(${r.id})">✓ Received</button>` : ''}
                </div>
            </td>
        </tr>`).join('');
}

// ─── APPROVE / REJECT ─────────────────────────────────────────────────────────
async function approveQuotation() {
    if (!confirm('Approve this quotation? This will lock it for editing.')) return;
    const btn = document.getElementById('btn-approve');
    btn.disabled = true; btn.textContent = 'Approving...';

    try {
        const res  = await fetch(`${API_BASE}/quotations/approve.php`, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: QUOTATION_ID, action: 'approve' })
        });
        const data = await res.json();
        if (data.success) {
    showToast('Quotation approved — PO generated!', 'success');
    setTimeout(() => {
        window.location.href = '/internal_portal/app/views/purchase-orders/list.php';
    }, 1500);
}
        else { showToast(data.message || 'Failed', 'error'); btn.disabled = false; btn.textContent = '✓ Approve'; }
    } catch (e) { showToast('Network error', 'error'); btn.disabled = false; btn.textContent = '✓ Approve'; }
}

async function rejectQuotation() {
    const reason = prompt('Rejection reason (required):');
    if (!reason?.trim()) { showToast('Reason is required', 'error'); return; }

    const btn = document.getElementById('btn-reject');
    btn.disabled = true; btn.textContent = 'Rejecting...';

    try {
        const res  = await fetch(`${API_BASE}/quotations/approve.php`, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: QUOTATION_ID, action: 'reject', reason: reason.trim() })
        });
        const data = await res.json();
        if (data.success) { showToast('Quotation rejected', 'success'); loadQuotation(); }
        else { showToast(data.message || 'Failed', 'error'); btn.disabled = false; btn.textContent = '✗ Reject'; }
    } catch (e) { showToast('Network error', 'error'); btn.disabled = false; btn.textContent = '✗ Reject'; }
}

// ─── GENERATE PO ──────────────────────────────────────────────────────────────
async function generatePO() {
    if (!confirm('Generate a Purchase Order from this approved quotation?')) return;
    const btn = document.getElementById('btn-generate-po');
    btn.disabled = true; btn.textContent = 'Generating PO...';

    try {
        const res  = await fetch(`${API_BASE}/quotations/generate-po.php`, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: QUOTATION_ID })
        });
        const data = await res.json();
        if (data.success) {
            showToast('PO generated: ' + data.data.po_number, 'success');
            setTimeout(() => {
                window.location.href = `/internal_portal/app/views/asset_manager/po/view.php?id=${data.data.po_id}`;
            }, 1500);
        } else {
            showToast(data.message || 'Failed', 'error');
            btn.disabled = false; btn.textContent = '🗂 Generate PO';
        }
    } catch (e) { showToast('Network error', 'error'); btn.disabled = false; btn.textContent = '🗂 Generate PO'; }
}

// ─── SEND REQUEST MODAL ───────────────────────────────────────────────────────
async function openSendModal() {
    document.getElementById('send-modal-overlay').classList.add('open');
    // Load suppliers into checklist
    const list = document.getElementById('qm-supplier-list');
    list.innerHTML = '<div style="padding:12px;font-size:13px;color:var(--color-text-secondary);">Loading...</div>';
    try {
        const res  = await fetch(`${API_BASE}/suppliers/list.php`, { credentials: 'include' });
        const data = await res.json();
        if (data.success && data.data.length) {
            list.innerHTML = data.data.map(s => `
                <label class="qm-supplier-item">
                    <input type="checkbox" value="${s.id}" name="qm-suppliers">
                    <span class="qm-supplier-name">${escapeHtml(s.name)}</span>
                    <span class="qm-supplier-email">${escapeHtml(s.email || 'No email')}</span>
                </label>`).join('');
        } else {
            list.innerHTML = '<div style="padding:12px;font-size:13px;color:var(--color-text-secondary);">No suppliers found.</div>';
        }
    } catch (e) {
        list.innerHTML = '<div style="padding:12px;color:#dc2626;">Failed to load suppliers.</div>';
    }
}

function closeSendModal() {
    document.getElementById('send-modal-overlay').classList.remove('open');
}

async function submitSendRequest() {
    const supplier_ids = Array.from(document.querySelectorAll('input[name="qm-suppliers"]:checked'))
                              .map(cb => parseInt(cb.value));
    if (!supplier_ids.length) { showToast('Select at least one supplier', 'error'); return; }

    const btn = document.getElementById('btn-send-submit');
    btn.disabled = true; btn.textContent = 'Sending...';

    try {
        const res  = await fetch(`${API_BASE}/quotations/send-request.php`, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                quotation_id:      QUOTATION_ID,
                supplier_ids,
                response_due_date: document.getElementById('qm-due-date').value || null,
                notes:             document.getElementById('qm-notes').value.trim(),
            })
        });
        const data = await res.json();
        showToast(data.message || 'Requests sent', data.success ? 'success' : 'error');
        closeSendModal();
        loadQuotation();
    } catch (e) { showToast('Network error', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Send Requests'; }
}

// ─── RESEND / MARK RECEIVED ───────────────────────────────────────────────────
async function resendRequest(request_id) {
    if (!confirm('Resend email to this supplier?')) return;
    const res  = await fetch(`${API_BASE}/quotations/resend.php`, {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ request_id })
    });
    const data = await res.json();
    showToast(data.success ? 'Email resent' : (data.message || 'Failed'), data.success ? 'success' : 'error');
    if (data.success) loadQuotation();
}

async function markReceived(request_id) {
    const res  = await fetch(`${API_BASE}/quotations/mark-received.php`, {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ request_id })
    });
    const data = await res.json();
    showToast(data.success ? 'Marked as received' : 'Failed', data.success ? 'success' : 'error');
    if (data.success) loadQuotation();
}

// ─── UPLOAD FILE ──────────────────────────────────────────────────────────────
function onFileSelected() {
    const input    = document.getElementById('file-input');
    const errEl    = document.getElementById('upload-error');
    const nameEl   = document.getElementById('upload-filename');
    const uploadBtn = document.getElementById('btn-upload');

    errEl.style.display = 'none';
    if (!input.files.length) return;

    const file = input.files[0];

    // Client-side PDF check
    if (!file.name.toLowerCase().endsWith('.pdf')) {
        errEl.textContent    = 'Only PDF files are allowed.';
        errEl.style.display  = 'block';
        uploadBtn.style.display = 'none';
        nameEl.textContent   = '';
        input.value          = '';
        return;
    }

    // Client-side size check (10MB)
    if (file.size > 10 * 1024 * 1024) {
        errEl.textContent    = 'File too large. Maximum size is 10MB.';
        errEl.style.display  = 'block';
        uploadBtn.style.display = 'none';
        nameEl.textContent   = '';
        input.value          = '';
        return;
    }

    nameEl.textContent       = '📎 ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
    uploadBtn.style.display  = 'block';
}

function handleDrop(event) {
    event.preventDefault();
    document.getElementById('upload-drop-area').style.borderColor = '';
    const dt = event.dataTransfer;
    if (dt.files.length) {
        document.getElementById('file-input').files = dt.files;
        onFileSelected();
    }
}

async function uploadFile() {
    const input = document.getElementById('file-input');
    if (!input.files.length) return;

    const formData = new FormData();
    formData.append('quotation_id', QUOTATION_ID);
    formData.append('file', input.files[0]);

    const btn  = document.getElementById('btn-upload');
    const errEl = document.getElementById('upload-error');
    btn.disabled = true; btn.textContent = 'Uploading...';
    errEl.style.display = 'none';

    try {
        const res  = await fetch(`${API_BASE}/quotations/upload.php`, {
            method: 'POST', credentials: 'include', body: formData
        });
        const data = await res.json();
        if (data.success) {
            showToast('Supplier PDF uploaded successfully', 'success');
            btn.style.display = 'none';
            document.getElementById('upload-filename').textContent = '';
            input.value = '';
            loadQuotation(); // Reload to show the new file + uploaded_at
        } else {
            errEl.textContent   = data.message || 'Upload failed.';
            errEl.style.display = 'block';
            btn.disabled = false; btn.textContent = 'Upload PDF';
        }
    } catch (e) {
        errEl.textContent   = 'Network error. Please try again.';
        errEl.style.display = 'block';
        btn.disabled = false; btn.textContent = 'Upload PDF';
    }
}

// ─── HELPERS ──────────────────────────────────────────────────────────────────
function statusBadge(status) {
    const map = { Pending: 'pending', Approved: 'approved', Rejected: 'rejected', Expired: 'expired' };
    return `<span class="quo-badge ${map[status] || ''}">${status}</span>`;
}
function reqStatusBadge(status) {
    const map = { Sent: 'sent', Failed: 'failed', Received: 'received' };
    return `<span class="quo-badge ${map[status] || ''}">${status}</span>`;
}
function today() { return new Date().toISOString().split('T')[0]; }
function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
}
function escapeHtml(t) {
    if (!t) return '';
    const d = document.createElement('div'); d.textContent = t; return d.innerHTML;
}
function showError(msg) {
    document.getElementById('loading').style.display = 'none';
    const el = document.getElementById('error');
    el.style.display = 'block'; el.textContent = msg;
}
function showToast(message, type = 'success') {
    const t = document.createElement('div');
    t.className = `toast toast-${type}`; t.textContent = message;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3000);
}