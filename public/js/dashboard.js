// Dashboard JavaScript
const API_BASE = '/internal_portal/api/v1';

async function loadDashboard() {
    try {
        const response = await fetch(`${API_BASE}/dashboard/stats.php`, {
            method: 'GET',
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (!response.ok) throw new Error(result.message || 'Failed to load dashboard');
        if (result.success && result.data) {
            displayDashboard(result.data);
        } else {
            throw new Error('Invalid response format');
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('loading').innerHTML = `
            <p style="color: var(--color-danger);">Failed to load dashboard: ${error.message}</p>
        `;
    }
}

function displayDashboard(data) {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('dashboardContent').style.display = 'block';
    
    renderKPICards(data);
    renderRecentTickets(data.recent_tickets || []);
    renderActivityFeed(data);
}

function renderKPICards(data) {
    const container = document.getElementById('kpiCards');
    
    const cards = [
        {
            title: 'Open Tickets',
            value: data.tickets?.by_status?.Open || 0,
            trend: '+12%',
            trendUp: true,
            icon: '🎫',
            color: 'blue'
        },
        {
            title: 'In Progress',
            value: data.tickets?.by_status?.['In Progress'] || 0,
            trend: '+8%',
            trendUp: true,
            icon: '⚡',
            color: 'yellow'
        },
        {
            title: 'Total Assets',
            value: data.assets?.total || 0,
            trend: '+5%',
            trendUp: true,
            icon: '💼',
            color: 'green'
        },
        {
            title: 'Pending POs',
            value: data.purchase_orders?.by_status?.Pending || 0,
            trend: '-3%',
            trendUp: false,
            icon: '📦',
            color: 'purple'
        },
        {
            title: 'Low Stock Items',
            value: data.stock?.low_stock_count || 0,
            trend: data.stock?.low_stock_count > 0 ? 'Action needed' : 'All good',
            trendUp: false,
            icon: '⚠️',
            color: 'red'
        },
        {
            title: 'Active Assets',
            value: data.assets?.by_status?.['In Use'] || 0,
            trend: '+15%',
            trendUp: true,
            icon: '🔧',
            color: 'indigo'
        }
    ];
    
    container.innerHTML = cards.map(card => `
        <div class="kpi-card ${card.color}">
            <div class="kpi-header">
                <div class="kpi-title">${card.title}</div>
                <div class="kpi-icon">${card.icon}</div>
            </div>
            <div class="kpi-value">${card.value}</div>
            <div class="kpi-trend ${card.trendUp ? 'up' : 'down'}">
                <span class="kpi-trend-icon">${card.trendUp ? '↑' : '↓'}</span>
                ${card.trend}
            </div>
        </div>
    `).join('');
}

function renderRecentTickets(tickets) {
    const tbody = document.getElementById('recentTickets');
    
    if (tickets.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--color-text-secondary);">No recent tickets</td></tr>';
        return;
    }
    
    tbody.innerHTML = tickets.slice(0, 5).map(ticket => `
        <tr>
            <td><span style="font-family: var(--font-mono); font-size: 13px; font-weight: 600;">#T-${ticket.ticket_id || ticket.id}</span></td>
            <td>${escapeHtml(ticket.title)}</td>
            <td>${getPriorityBadge(ticket.priority)}</td>
            <td>${getStatusBadge(ticket.status)}</td>
            <td>${formatDate(ticket.created_at)}</td>
        </tr>
    `).join('');
}

function renderActivityFeed(data) {
    const feed = document.getElementById('activityFeed');
    
    const activities = [
        { icon: '🎫', color: 'blue', text: '<strong>New ticket</strong> created by John Doe', time: '2 minutes ago' },
        { icon: '📦', color: 'green', text: '<strong>PO-0012</strong> approved', time: '15 minutes ago' },
        { icon: '⚠️', color: 'yellow', text: '<strong>Stock alert:</strong> Mouse below threshold', time: '1 hour ago' },
        { icon: '💼', color: 'blue', text: '<strong>Asset A-0045</strong> assigned to Sarah', time: '2 hours ago' },
        { icon: '👤', color: 'green', text: '<strong>New user</strong> registered', time: '3 hours ago' }
    ];
    
    feed.innerHTML = activities.map(activity => `
        <div class="activity-item">
            <div class="activity-icon ${activity.color}">${activity.icon}</div>
            <div class="activity-content">
                <div class="activity-text">${activity.text}</div>
                <div class="activity-time">${activity.time}</div>
            </div>
        </div>
    `).join('');
}

function getPriorityBadge(priority) {
    const classes = {
        'Low': 'badge-info',
        'Medium': 'badge-warning',
        'High': 'badge-danger',
        'Critical': 'badge-danger'
    };
    return `<span class="badge ${classes[priority] || 'badge-info'}">${priority}</span>`;
}

function getStatusBadge(status) {
    const classes = {
        'Open': 'badge-primary',
        'In Progress': 'badge-warning',
        'Resolved': 'badge-success',
        'Closed': 'badge-secondary'
    };
    return `<span class="badge ${classes[status] || 'badge-primary'}">${status}</span>`;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7) return `${days} days ago`;
    
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load dashboard on page load
document.addEventListener('DOMContentLoaded', loadDashboard);