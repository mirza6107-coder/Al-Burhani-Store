'use strict';

const Customer = (() => {
    let currentFilters = { status: 'all', search: '' };

    function showToast(msg, type = 'success') {
        const toast = document.getElementById('adminToast');
        toast.textContent = msg;
        toast.className = `admin-toast show ${type === 'error' ? 'toast-error' : ''}`;
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    function toggleSidebar() {
        document.getElementById('sidebar')?.classList.toggle('open');
    }

    async function fetchCustomers() {
        const params = new URLSearchParams(currentFilters);
        params.append('ajax', '1');
        const res = await fetch(`customers.php?${params}`);
        return res.json();
    }

    function renderTable(customers) {
        const tbody = document.getElementById('customersBody');
        tbody.innerHTML = '';

        if (!customers.length) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:60px;color:#aaa;">No customers found</td></tr>`;
            return;
        }

        customers.forEach(c => {
            const tr = document.createElement('tr');
            tr.dataset.id = c.id;
            tr.innerHTML = `
                <td><span class="order-id">${c.customer_id}</span></td>
                <td>
                    <div class="customer-cell">
                        <div class="cust-avatar">${c.name.substring(0,2).toUpperCase()}</div>
                        <div>
                            <div class="cust-name">${c.name}</div>
                            <div class="cust-email">${c.email}</div>
                        </div>
                    </div>
                </td>
                <td>${c.phone || ''}</td>
                <td>${c.city || ''}</td>
                <td style="text-align:center">${c.total_orders || 0}</td>
                <td style="color:#d4af37">RS ${Number(c.total_spent || 0).toLocaleString()}</td>
                <td>${c.joined_date ? new Date(c.joined_date).toLocaleDateString('en-GB', {day:'numeric', month:'short', year:'numeric'}) : ''}</td>
                <td>${getStatusBadge(c.status)}</td>
                <td>
                    <div class="row-actions">
                        <button class="action-btn view-btn" onclick='Customer.view(${JSON.stringify(c)})'><i class="fas fa-eye"></i></button>
                        <button class="action-btn edit-btn" onclick="Customer.toggleStatus(${c.id}, '${c.status}')"><i class="fas fa-edit"></i></button>
                        <button class="action-btn delete-btn" onclick="Customer.deleteCustomer(${c.id})"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function getStatusBadge(status) {
        const map = {
            active:  ['badge-delivered', 'fa-check-circle', 'Active'],
            inactive:['badge-pending',   'fa-clock',        'Inactive'],
            blocked: ['badge-cancelled', 'fa-ban',          'Blocked']
        };
        const [cls, ico, label] = map[status] || ['', 'fa-question', status];
        return `<span class="badge ${cls}"><i class="fas ${ico}"></i> ${label}</span>`;
    }

    async function applyFilters() {
        currentFilters.search = document.getElementById('searchInput').value.trim();
        currentFilters.status = document.getElementById('statusFilter').value;

        try {
            const data = await fetchCustomers();
            
            // Update stats
            document.querySelectorAll('.stat-value').forEach(el => {
                const key = el.dataset.key;
                if (data.stats[key] !== undefined) {
                    el.textContent = key === 'total_spent' 
                        ? `RS ${Number(data.stats[key]).toLocaleString()}` 
                        : Number(data.stats[key]).toLocaleString();
                }
            });

            renderTable(data.customers || []);
            showToast('Customers updated');
        } catch (e) {
            showToast('Failed to load customers', 'error');
        }
    }

    function view(customer) {
        alert(`Customer Details:\n\nID: ${customer.customer_id}\nName: ${customer.name}\nEmail: ${customer.email}\nPhone: ${customer.phone}\nCity: ${customer.city}\nTotal Spent: RS ${Number(customer.total_spent||0).toLocaleString()}\nOrders: ${customer.total_orders||0}`);
    }

    function toggleStatus(id, current) {
        if (!confirm(`Change status of customer #${id}?`)) return;
        const newStatus = current === 'active' ? 'inactive' : 'active';

        const fd = new FormData();
        fd.append('action', 'update_status');
        fd.append('id', id);
        fd.append('status', newStatus);

        fetch('customers.php', {method: 'POST', body: fd})
            .then(() => applyFilters())
            .catch(() => applyFilters());
    }

    function deleteCustomer(id) {
        if (!confirm(`Delete customer #${id} permanently?`)) return;

        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);

        fetch('customers.php', {method: 'POST', body: fd})
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Customer deleted');
                    applyFilters();
                }
            })
            .catch(() => applyFilters());
    }

    function exportCSV() {
        window.location.href = 'customers.php?export=csv';
        showToast('Exporting customers...');
    }

    function openCreate() {
        showToast('New Customer form coming soon...');
    }

    function init() {
        document.getElementById('searchInput').addEventListener('keypress', e => {
            if (e.key === 'Enter') applyFilters();
        });
        document.getElementById('statusFilter').addEventListener('change', applyFilters);

        showToast('Customers Dashboard Loaded Successfully ✨');
    }

    return {
        init, applyFilters, view, toggleStatus, deleteCustomer,
        exportCSV, openCreate, toggleSidebar
    };
})();

document.addEventListener('DOMContentLoaded', Customer.init);