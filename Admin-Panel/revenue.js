/* ========================================
   AL BURHAN STORE — revenue.js (FIXED)
   ======================================== */

'use strict';

const Revenue = (() => {
    let monthlyChart  = null;
    let paymentChart  = null;
    let currentFilters = { period: 'this_month' };
    let sortDirection  = {};

    /* ── Toast ──────────────────────────── */
    function showToast(msg, type = 'success') {
        const toast = document.getElementById('adminToast');
        if (!toast) return;
        toast.textContent = msg;
        toast.style.borderColor = type === 'error' ? '#ff4444' : '#d4af37';
        toast.style.color       = type === 'error' ? '#ffaaaa' : '#d4af37';
        toast.classList.add('show');
        clearTimeout(toast._t);
        toast._t = setTimeout(() => toast.classList.remove('show'), 3000);
    }

    /* ── Toggle Sidebar ─────────────────── */
    function toggleSidebar() {
        document.getElementById('sidebar')?.classList.toggle('open');
    }

    /* ── Clear Filters ──────────────────── */
    function clearFilters() {
        document.getElementById('periodFilter').value  = 'this_month';
        document.getElementById('dateStart').value      = '';
        document.getElementById('dateEnd').value        = '';
        currentFilters = { period: 'this_month' };
        applyFilters();
    }

    /* ── Fetch Data via AJAX ────────────── */
    async function fetchRevenueData(filters) {
        const params = new URLSearchParams();
        params.set('ajax', '1');

        // FIX: Only send date range if BOTH dates are filled
        if (filters.start_date && filters.end_date) {
            params.set('start_date', filters.start_date);
            params.set('end_date', filters.end_date);
            params.set('period', 'all'); // Ignore period when custom range
        } else {
            params.set('period', filters.period || 'this_month');
        }

        const response = await fetch('revenue.php?' + params.toString());
        return response.json();
    }

    /* ── Format Currency ────────────────── */
    function formatRS(amount) {
        const num = parseFloat(amount) || 0;
        return 'RS ' + num.toLocaleString('en-PK', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    /* ── Update Stat Cards ──────────────── */
    function updateStats(stats) {
        if (!stats) return;

        document.querySelectorAll('.stat-value[data-key]').forEach(el => {
            const key = el.dataset.key;
            if (stats[key] === undefined) return;

            const val = parseFloat(stats[key]) || 0;

            // Determine if this key shows currency
            const isCurrency = [
                'total_revenue', 'this_month', 'avg_order',
                'total_shipping', 'total_vat', 'total_discount',
                'delivered_revenue'
            ].includes(key);

            // Animate the value change
            animateValue(el, val, isCurrency);
        });
    }

    /* ── Animate Counter ────────────────── */
    function animateValue(el, target, isCurrency) {
        const duration = 600;
        const startTime = performance.now();
        const startVal  = 0;

        function step(now) {
            const progress  = Math.min((now - startTime) / duration, 1);
            const eased     = 1 - Math.pow(1 - progress, 3); // easeOutCubic
            const current   = startVal + (target - startVal) * eased;

            el.textContent = isCurrency
                ? formatRS(current)
                : Math.round(current).toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = isCurrency ? formatRS(target) : Math.round(target).toLocaleString();
            }
        }

        requestAnimationFrame(step);
    }

    /* ── Monthly Trend Chart ────────────── */
    function initMonthlyChart(monthlyData) {
        const ctx = document.getElementById('monthlyTrendChart');
        if (!ctx) return;

        if (monthlyChart) monthlyChart.destroy();

        const labels   = monthlyData.map(m => formatMonthLabel(m.month));
        const revenues = monthlyData.map(m => parseFloat(m.revenue) || 0);
        const orders   = monthlyData.map(m => parseInt(m.orders) || 0);

        monthlyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Revenue (RS)',
                        data: revenues,
                        backgroundColor: 'rgba(212, 175, 55, 0.6)',
                        borderColor: '#d4af37',
                        borderWidth: 2,
                        borderRadius: 6,
                        yAxisID: 'y',
                        order: 2,
                    },
                    {
                        label: 'Orders',
                        data: orders,
                        type: 'line',
                        borderColor: '#00cc88',
                        backgroundColor: 'rgba(0, 204, 136, 0.1)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#00cc88',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'y1',
                        order: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: '#aaa',
                            font: { family: 'Raleway', size: 12 },
                            usePointStyle: true,
                            padding: 20,
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(26,26,46,0.95)',
                        titleColor: '#d4af37',
                        bodyColor: '#fff',
                        borderColor: '#d4af37',
                        borderWidth: 1,
                        padding: 14,
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.dataset.yAxisID === 'y') {
                                    return 'Revenue: RS ' + Number(ctx.parsed.y).toLocaleString('en-PK', {minimumFractionDigits:2});
                                }
                                return 'Orders: ' + ctx.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#888',
                            font: { size: 11 },
                            callback: (v) => 'RS ' + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v),
                        },
                        grid: { color: 'rgba(255,255,255,0.05)' },
                        title: { display: true, text: 'Revenue (RS)', color: '#d4af37' },
                    },
                    y1: {
                        position: 'right',
                        beginAtZero: true,
                        ticks: {
                            color: '#888',
                            font: { size: 11 },
                            stepSize: 1,
                        },
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Orders', color: '#00cc88' },
                    },
                    x: {
                        ticks: { color: '#888', font: { size: 11 } },
                        grid: { color: 'rgba(255,255,255,0.03)' },
                    }
                }
            }
        });
    }

    /* ── Payment Methods Doughnut Chart ─── */
    function initPaymentChart(paymentData) {
        const ctx = document.getElementById('paymentChart');
        if (!ctx) return;

        if (paymentChart) paymentChart.destroy();

        const paymentLabels = {
            'card': 'Credit Card',
            'cod':  'Cash on Delivery',
            'bank': 'Bank Transfer',
        };

        const labels   = paymentData.map(p => paymentLabels[p.payment_method] || p.payment_method);
        const revenues = paymentData.map(p => parseFloat(p.revenue) || 0);
        const colors   = ['#d4af37', '#00cc88', '#7c8cf8', '#f39c12', '#e05c5c'];

        paymentChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: revenues,
                    backgroundColor: colors.slice(0, labels.length),
                    borderColor: 'rgba(26,26,46,0.8)',
                    borderWidth: 3,
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#aaa',
                            font: { family: 'Raleway', size: 12 },
                            padding: 16,
                            usePointStyle: true,
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(26,26,46,0.95)',
                        titleColor: '#d4af37',
                        bodyColor: '#fff',
                        borderColor: '#d4af37',
                        borderWidth: 1,
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ctx.label + ': RS ' + Number(ctx.parsed).toLocaleString('en-PK', {minimumFractionDigits:2}) + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    /* ── Render Top Products Table ───────── */
    function renderTopProducts(products, totalRevenue) {
        const tbody = document.getElementById('topProductsBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (!products || products.length === 0) {
            tbody.innerHTML = `
                <tr>
                  <td colspan="6" style="text-align:center; padding:40px; color:var(--text-muted);">
                    <i class="fas fa-box-open" style="font-size:24px; display:block; margin-bottom:10px;"></i>
                    No product data for this period
                  </td>
                </tr>`;
            return;
        }

        const totalRev = totalRevenue || products.reduce((s, p) => s + (parseFloat(p.revenue) || 0), 0);

        products.forEach((p, idx) => {
            const share = totalRev > 0 ? ((parseFloat(p.revenue) / totalRev) * 100) : 0;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="color:var(--gold); font-weight:600;">${idx + 1}</td>
                <td>${escapeHtml(p.product || 'N/A')}</td>
                <td><span class="cat-badge">${escapeHtml(p.category || 'N/A')}</span></td>
                <td style="text-align:center">${Number(p.units || 0).toLocaleString()}</td>
                <td style="text-align:right; color:#d4af37; font-weight:600;">${formatRS(p.revenue)}</td>
                <td style="text-align:right;">
                  <div class="share-bar-wrap">
                    <div class="share-bar" style="width:${Math.min(100, share)}%"></div>
                    <span class="share-text">${share.toFixed(1)}%</span>
                  </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    /* ── Helpers ─────────────────────────── */
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function formatMonthLabel(monthStr) {
        // "2025-01" → "Jan 2025"
        const [year, month] = monthStr.split('-');
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return (months[parseInt(month) - 1] || month) + ' ' + year;
    }

    /* ── Apply Filters ──────────────────── */
    async function applyFilters() {
        const period    = document.getElementById('periodFilter')?.value || 'this_month';
        const startDate = document.getElementById('dateStart')?.value || '';
        const endDate   = document.getElementById('dateEnd')?.value || '';

        // FIX: Validate date range
        if ((startDate && !endDate) || (!startDate && endDate)) {
            showToast('Please select both start and end dates', 'error');
            return;
        }

        if (startDate && endDate && startDate > endDate) {
            showToast('Start date must be before end date', 'error');
            return;
        }

        currentFilters = { period, start_date: startDate, end_date: endDate };

        try {
            const data = await fetchRevenueData(currentFilters);

            if (data.stats)    updateStats(data.stats);
            if (data.monthly)  initMonthlyChart(data.monthly);
            if (data.payment_data) initPaymentChart(data.payment_data);
            if (data.top_products) renderTopProducts(data.top_products, data.stats?.total_revenue);

            showToast('Dashboard updated ✦');

        } catch (e) {
            showToast('Failed to load data', 'error');
            console.error('Revenue fetch error:', e);
        }
    }

    /* ── Export CSV ──────────────────────── */
    function exportRevenueCSV() {
        const params = new URLSearchParams();
        params.set('export', 'csv');

        // FIX: Pass current filters to export
        if (currentFilters.start_date && currentFilters.end_date) {
            params.set('start_date', currentFilters.start_date);
            params.set('end_date', currentFilters.end_date);
        } else {
            params.set('period', currentFilters.period || 'this_month');
        }

        window.location.href = 'revenue.php?' + params.toString();
        showToast('Exporting report...');
    }

    /* ── Table Sorting ──────────────────── */
    function initTableSort() {
        document.querySelectorAll('#topProductsTable th[data-sort]').forEach(th => {
            th.style.cursor = 'pointer';
            th.addEventListener('click', () => {
                const colIdx  = parseInt(th.dataset.col);
                const sortType = th.dataset.sort;
                const tbody   = document.getElementById('topProductsBody');
                const rows    = Array.from(tbody.querySelectorAll('tr'));

                // FIX: Track sort direction per column
                const dir = sortDirection[colIdx] = (sortDirection[colIdx] === 'asc') ? 'desc' : 'asc';

                rows.sort((a, b) => {
                    let valA = a.children[colIdx]?.textContent.trim() || '';
                    let valB = b.children[colIdx]?.textContent.trim() || '';

                    if (sortType === 'number') {
                        valA = parseFloat(valA.replace(/[^0-9.\-]/g, '')) || 0;
                        valB = parseFloat(valB.replace(/[^0-9.\-]/g, '')) || 0;
                        return dir === 'asc' ? valA - valB : valB - valA;
                    }
                    return dir === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
                });

                rows.forEach(row => tbody.appendChild(row));

                // Visual indicator
                th.querySelectorAll('.sort-icon').forEach(i => i.remove());
                const icon = document.createElement('i');
                icon.className = 'sort-icon fas fa-sort-' + (dir === 'asc' ? 'up' : 'down');
                icon.style.marginLeft = '6px';
                icon.style.fontSize = '10px';
                th.appendChild(icon);
            });
        });
    }

    /* ── Live Product Search ────────────── */
    function initProductSearch() {
        const input = document.getElementById('productSearch');
        if (!input) return;

        input.addEventListener('input', () => {
            const term = input.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#topProductsBody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    /* ── Initialize ─────────────────────── */
    function init() {
        // FIX: Use PHP-injected initial data for first render
        const data = window.__revenueData;

        if (data) {
            // Render charts immediately with server data
            if (data.monthly)      initMonthlyChart(data.monthly);
            if (data.payment_data) initPaymentChart(data.payment_data);

            // Animate stat cards on first load
            if (data.stats) {
                document.querySelectorAll('.stat-value[data-key]').forEach(el => {
                    const key    = el.dataset.key;
                    const val    = parseFloat(data.stats[key]) || 0;
                    const isCurr = [
                        'total_revenue', 'this_month', 'avg_order',
                        'total_shipping', 'total_vat', 'total_discount',
                        'delivered_revenue'
                    ].includes(key);

                    // Delay for visual effect
                    setTimeout(() => animateValue(el, val, isCurr), 150);
                });
            }
        }

        // Filter listeners
        document.getElementById('periodFilter')?.addEventListener('change', applyFilters);
        document.getElementById('applyDateFilter')?.addEventListener('click', applyFilters);

        // Enter key on date inputs triggers filter
        document.getElementById('dateStart')?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') applyFilters();
        });
        document.getElementById('dateEnd')?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') applyFilters();
        });

        // Product search
        initProductSearch();

        // Table sorting
        initTableSort();

        showToast('Revenue Dashboard Loaded ✦');
    }

    return {
        init,
        exportRevenueCSV,
        toggleSidebar,
        clearFilters,
    };
})();

document.addEventListener('DOMContentLoaded', Revenue.init);