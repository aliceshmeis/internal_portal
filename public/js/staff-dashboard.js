// Staff Dashboard JavaScript
const API_BASE = '/internal_portal/api/v1';

document.addEventListener('DOMContentLoaded', () => {
    loadDashboard();
});

async function loadDashboard() {
    try {
        const response = await fetch(`${API_BASE}/staff/dashboard.php`, {
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (result.success && result.data) {
            displayOpenTickets(result.data.my_tickets || []);
            displayMyAssets(result.data.my_assets || []);
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
}

function displayOpenTickets(tickets) {
    const container = document.getElementById('openTickets');
    
    // Filter only open and in-progress tickets
    const openTickets = tickets.filter(t => 
        t.status === 'Open' || t.status === 'In Progress'
    );
    
    if (openTickets.length === 0) {
        container.innerHTML = `
            <div class="empty-clean">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <p>You have no open tickets</p>
                <button class="btn-create-small" onclick="createTicket()">Create Ticket</button>
            </div>
        `;
        return;
    }
    
    container.innerHTML = `
        <table class="tickets-table-clean">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                ${openTickets.slice(0, 5).map(ticket => `
                    <tr onclick="viewTicket(${ticket.id})">
                        <td><span class="ticket-id-clean">#T-${String(ticket.id).padStart(4, '0')}</span></td>
                        <td><span class="ticket-title-clean">${escapeHtml(ticket.title)}</span></td>
                        <td>${getStatusBadge(ticket.status)}</td>
                        <td><span class="ticket-updated">${formatDate(ticket.updated_at)}</span></td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

function displayMyAssets(assets) {
    const container = document.getElementById('myAssets');
    
    if (assets.length === 0) {
        container.innerHTML = `
            <div class="empty-clean">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                <p>No assets assigned yet</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = `
        <div class="assets-list-clean">
            ${assets.slice(0, 5).map(asset => `
                <div class="asset-row">
                    <div class="asset-left">
                        <span class="asset-tag-clean">A-${String(asset.id).padStart(4, '0')}</span>
                        <div class="asset-info">
                            <span class="asset-name-clean">${escapeHtml(asset.name)}</span>
                            <span class="asset-type">${escapeHtml(asset.model || 'N/A')}</span>
                        </div>
                    </div>
                    <div>
                        <span class="asset-status-clean in-use">${asset.status}</span>
                        <button class="btn-report" onclick="reportIssue(${asset.id})">Report Issue</button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function getStatusBadge(status) {
    const classes = {
        'Open': 'badge-open',
        'In Progress': 'badge-in-progress',
        'Resolved': 'badge-resolved'
    };
    return `<span class="badge-clean ${classes[status] || 'badge-open'}">${status}</span>`;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7) return `${days}d ago`;
    
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Actions
function createTicket(category = null) {
    const url = category 
        ? `../tickets/create.php?category=${encodeURIComponent(category)}`
        : '../tickets/create.php';
    window.location.href = url;
}

function viewTicket(id) {
    window.location.href = `../tickets/view.php?id=${id}`;
}

function reportIssue(assetId) {
    window.location.href = `../tickets/create.php?asset_id=${assetId}`;
}