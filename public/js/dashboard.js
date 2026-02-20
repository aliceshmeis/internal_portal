// Dashboard JavaScript
const API_BASE = '/internal_portal/api/v1';

let donutChartInstance = null;
let lineChartInstance  = null;

async function loadDashboard() {
    try {
        const [statsRes, trendRes] = await Promise.all([
            fetch(`${API_BASE}/dashboard/stats.php`,        { credentials: 'include' }),
            fetch(`${API_BASE}/dashboard/ticket-trend.php`, { credentials: 'include' })
        ]);

        const stats = await statsRes.json();
        const trend = await trendRes.json();

        if (!statsRes.ok) throw new Error(stats.message || 'Failed to load dashboard');
        if (stats.success && stats.data) {
            displayDashboard(stats.data, trend.success ? trend.data : null);
        } else {
            throw new Error('Invalid response format');
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('loading').innerHTML = `
            <p style="color:var(--color-danger);">Failed to load dashboard: ${error.message}</p>
        `;
    }
}

function displayDashboard(data, trendData) {
    document.getElementById('loading').style.display       = 'none';
    document.getElementById('dashboardContent').style.display = 'block';

    renderKPICards(data);
    renderDonutChart(data.tickets?.by_status || {});
    renderLineChart(trendData);
    renderRecentTickets(data.recent_tickets || []);
    renderActivityFeed(data);
}

// ── KPI Cards ─────────────────────────────────
function renderKPICards(data) {
    const container = document.getElementById('kpiCards');
    const cards = [
        { title: 'Open Tickets',    value: data.tickets?.by_status?.Open || 0,                  trend: '+12%', trendUp: true,  icon: '🎫', color: 'blue'   },
        { title: 'In Progress',     value: data.tickets?.by_status?.['In Progress'] || 0,       trend: '+8%',  trendUp: true,  icon: '⚡', color: 'yellow' },
        { title: 'Total Assets',    value: data.assets?.total || 0,                              trend: '+5%',  trendUp: true,  icon: '💼', color: 'green'  },
        { title: 'Pending POs',     value: data.purchase_orders?.by_status?.Pending || 0,       trend: '-3%',  trendUp: false, icon: '📦', color: 'purple' },
        { title: 'Low Stock Items', value: data.stock?.low_stock_count || 0,                     trend: data.stock?.low_stock_count > 0 ? 'Action needed' : 'All good', trendUp: false, icon: '⚠️', color: 'red'    },
        { title: 'Active Assets',   value: data.assets?.by_status?.['In Use'] || 0,             trend: '+15%', trendUp: true,  icon: '🔧', color: 'indigo' }
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

// ── Donut Chart ───────────────────────────────
function renderDonutChart(byStatus) {
    const statusConfig = [
        { key: 'Open',        color: '#3b82f6', light: '#dbeafe' },
        { key: 'In Progress', color: '#f97316', light: '#ffedd5' },
        { key: 'Pending',     color: '#8b5cf6', light: '#ede9fe' },
        { key: 'Resolved',    color: '#10b981', light: '#d1fae5' },
        { key: 'Closed',      color: '#6b7280', light: '#f3f4f6' },
    ];

    const labels = statusConfig.map(s => s.key);
    const values = statusConfig.map(s => byStatus[s.key] || 0);
    const colors = statusConfig.map(s => s.color);
    const total  = values.reduce((a, b) => a + b, 0);

    document.getElementById('donutTotal').textContent = total;

    const ctx = document.getElementById('donutChart').getContext('2d');
    if (donutChartInstance) donutChartInstance.destroy();

    donutChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverBorderWidth: 2,
            }]
        },
        options: {
            cutout: '68%',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.raw} (${total ? Math.round(ctx.raw / total * 100) : 0}%)`
                    }
                }
            }
        }
    });

    // Custom legend
    const legend = document.getElementById('donutLegend');
    legend.innerHTML = statusConfig.map((s, i) => `
        <div class="legend-item">
            <span class="legend-dot" style="background:${s.color};"></span>
            <span class="legend-label">${s.key}</span>
            <span class="legend-value">${values[i]}</span>
        </div>
    `).join('');
}

// ── Line Chart ────────────────────────────────
function renderLineChart(trendData) {
    const ctx = document.getElementById('lineChart').getContext('2d');
    if (lineChartInstance) lineChartInstance.destroy();

    // Build last 7 days labels
    const days   = [];
    const labels = [];
    for (let i = 6; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        days.push(d.toISOString().slice(0, 10));
        labels.push(d.toLocaleDateString('en-US', { weekday: 'short' }));
    }

    // Map API data to days
    const dataMap = {};
    if (trendData && Array.isArray(trendData)) {
        trendData.forEach(row => { dataMap[row.date] = parseInt(row.count); });
    }
    const values = days.map(d => dataMap[d] || 0);

    lineChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Tickets Created',
                data: values,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.08)',
                borderWidth: 2,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.raw} ticket${ctx.raw !== 1 ? 's' : ''}`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 12 }, color: '#9ca3af' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f3f4f6', drawBorder: false },
                    ticks: {
                        font: { size: 12 },
                        color: '#9ca3af',
                        stepSize: 1,
                        precision: 0
                    }
                }
            }
        }
    });
}

// ── Recent Tickets ────────────────────────────
function renderRecentTickets(tickets) {
    const tbody = document.getElementById('recentTickets');
    if (tickets.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--color-text-secondary);">No recent tickets</td></tr>';
        return;
    }
    tbody.innerHTML = tickets.slice(0, 5).map(ticket => `
        <tr>
            <td><span style="font-family:var(--font-mono);font-size:13px;font-weight:600;">#T-${String(ticket.ticket_id || ticket.id).padStart(4,'0')}</span></td>
            <td>${escapeHtml(ticket.title)}</td>
            <td>${getPriorityBadge(ticket.priority)}</td>
            <td>${getStatusBadge(ticket.status)}</td>
            <td>${formatDate(ticket.created_at)}</td>
        </tr>
    `).join('');
}

// ── Activity Feed ─────────────────────────────
function renderActivityFeed(data) {
    const feed = document.getElementById('activityFeed');
    const activities = [
        { icon: '🎫', color: 'blue',   text: '<strong>New ticket</strong> created by John Doe',        time: '2 minutes ago'  },
        { icon: '📦', color: 'green',  text: '<strong>PO-0012</strong> approved',                       time: '15 minutes ago' },
        { icon: '⚠️', color: 'yellow', text: '<strong>Stock alert:</strong> Mouse below threshold',     time: '1 hour ago'     },
        { icon: '💼', color: 'blue',   text: '<strong>Asset A-0045</strong> assigned to Sarah',         time: '2 hours ago'    },
        { icon: '👤', color: 'green',  text: '<strong>New user</strong> registered',                    time: '3 hours ago'    }
    ];
    feed.innerHTML = activities.map(a => `
        <div class="activity-item">
            <div class="activity-icon ${a.color}">${a.icon}</div>
            <div class="activity-content">
                <div class="activity-text">${a.text}</div>
                <div class="activity-time">${a.time}</div>
            </div>
        </div>
    `).join('');
}

// ── Helpers ───────────────────────────────────
function getPriorityBadge(priority) {
    const classes = { 'Low': 'badge-info', 'Medium': 'badge-warning', 'High': 'badge-danger', 'Critical': 'badge-danger' };
    return `<span class="badge ${classes[priority] || 'badge-info'}">${priority}</span>`;
}

function getStatusBadge(status) {
    const classes = { 'Open': 'badge-primary', 'In Progress': 'badge-warning', 'Resolved': 'badge-success', 'Closed': 'badge-secondary' };
    return `<span class="badge ${classes[status] || 'badge-primary'}">${status}</span>`;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now  = new Date();
    const days = Math.floor((now - date) / (1000 * 60 * 60 * 24));
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

document.addEventListener('DOMContentLoaded', loadDashboard);