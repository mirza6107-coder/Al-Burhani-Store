/* ========================================
   AL BURHAN ADMIN — Dashboard JavaScript
   ======================================== */

document.addEventListener('DOMContentLoaded', () => {

    /* ----------------------------------------
       SAMPLE PRODUCT DATA
    ---------------------------------------- */
    const sampleProducts = [
        {
            id: 1,
            name: "Oud Majesty Perfume",
            category: "Perfumes",
            price: 450,
            stock: 34,
            status: "In Stock",
            image: "../Images/bently.jpg"
        },
        {
            id: 2,
            name: "Rolex Submariner",
            category: "Watches",
            price: 1250,
            stock: 12,
            status: "Low Stock",
            image: "../Images/watch.jpg"
        },
        {
            id: 3,
            name: "Luxury Aviator Sunglasses",
            category: "Sunglasses",
            price: 320,
            stock: 67,
            status: "In Stock",
            image: "../Images/glasses_portrait_fixed.jpg"
        },
        {
            id: 4,
            name: "Embroidered Black Thobe",
            category: "Dress",
            price: 680,
            stock: 23,
            status: "In Stock",
            image: "../Images/Gemini.png"
        },
        {
            id: 5,
            name: "Amber Noir Eau de Parfum",
            category: "Perfumes",
            price: 390,
            stock: 0,
            status: "Out of Stock",
            image: "../Images/bently.jpg"
        }
    ];

    /* ----------------------------------------
       POPULATE TABLE
    ---------------------------------------- */
    const tbody = document.querySelector('#recent-table tbody');
    if (tbody) {
        tbody.innerHTML = '';
        sampleProducts.forEach(product => {
            const statusClass = product.status.toLowerCase().replace(' ', '-');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="product-cell">
                        <img src="${product.image}" alt="${product.name}" onerror="this.style.background='rgba(212,175,55,0.1)'">
                        <strong>${product.name}</strong>
                    </div>
                </td>
                <td><span class="category-tag">${product.category}</span></td>
                <td class="price-cell">RS ${product.price.toLocaleString()}</td>
                <td style="color:${product.stock < 15 ? '#fbbf24' : 'inherit'}">${product.stock}</td>
                <td><span class="status ${statusClass}">${product.status}</span></td>
                <td>
                    <button class="edit-btn" onclick="editProduct(${product.id})">Edit</button>
                    <button class="delete-btn" onclick="deleteProduct(${product.id}, this)">Delete</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    /* ----------------------------------------
       ACTIVE NAV LINK
    ---------------------------------------- */
    const currentPage = window.location.pathname.split('/').pop();
    document.querySelectorAll('.sidebar-nav a').forEach(link => {
        if (link.getAttribute('href') === currentPage || currentPage === '' && link.getAttribute('href') === 'dashboard.php') {
            link.classList.add('active');
        }
    });

    /* ----------------------------------------
       SEARCH FILTER
    ---------------------------------------- */
    const searchInput = document.querySelector('.search-bar input');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase();
            document.querySelectorAll('#recent-table tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    /* ----------------------------------------
       STAT CARD COUNTER ANIMATION
    ---------------------------------------- */
    document.querySelectorAll('.stat-number').forEach(el => {
        const target = parseInt(el.dataset.target, 10);
        if (isNaN(target)) return;
        let current = 0;
        const step = Math.ceil(target / 60);
        const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = el.dataset.prefix
                ? el.dataset.prefix + current.toLocaleString()
                : current.toLocaleString();
            if (current >= target) clearInterval(timer);
        }, 20);
    });

    /* ----------------------------------------
       NOTIFICATION BELL
    ---------------------------------------- */
    const notifBtn = document.querySelector('.notif-btn');
    if (notifBtn) {
        notifBtn.addEventListener('click', () => {
            showToast('No new notifications ✦');
        });
    }

});

/* ----------------------------------------
   EDIT PRODUCT
---------------------------------------- */
function editProduct(id) {
    showToast(`Opening editor for product #${id} ✦`);
    // Connect to edit page: window.location.href = `edit-product.php?id=${id}`;
}

/* ----------------------------------------
   DELETE PRODUCT
---------------------------------------- */
function deleteProduct(id, btn) {
    if (!confirm(`Remove product #${id} from the catalogue?`)) return;
    const row = btn.closest('tr');
    row.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
    row.style.opacity = '0';
    row.style.transform = 'translateX(20px)';
    setTimeout(() => row.remove(), 400);
    showToast(`Product #${id} removed ✦`);
}

/* ----------------------------------------
   TOAST NOTIFICATION
---------------------------------------- */
function showToast(message) {
    let toast = document.querySelector('.al-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'al-toast';
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.add('show');
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => toast.classList.remove('show'), 2800);
}