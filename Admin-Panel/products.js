/* ========================================
   AL BURHAN ADMIN — Products JavaScript
   Fetches from api.php (MySQL backend)
   ======================================== */

const categoryIcons = {
  Perfumes: "fas fa-spray-can",
  Watches: "fas fa-clock",
  Sunglasses: "fas fa-glasses",
  Dress: "fas fa-tshirt",
};

/* ----------------------------------------
   RENDER PRODUCTS
---------------------------------------- */
function renderProducts(list) {
  const grid = document.getElementById("product-grid");
  const countEl = document.getElementById("products-count");
  if (countEl)
    countEl.textContent = `${list.length} product${list.length !== 1 ? "s" : ""}`;

  if (!list.length) {
    grid.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h4>No Products Found</h4>
                <p>Try adjusting your filters or add a new product</p>
            </div>`;
    return;
  }

  grid.innerHTML = "";
  list.forEach((product, idx) => {
    const statusClass = product.status.toLowerCase().replace(/\s+/g, "-");
    const icon = categoryIcons[product.category] || "fas fa-box";
    const imgHTML = product.image
      ? `<img src="${product.image}" alt="${product.name}" onerror="this.style.display='none'">`
      : `<i class="${icon}"></i>`;

    const card = document.createElement("div");
    card.className = "product-card reveal-ready";
    card.innerHTML = `
            <div class="card-img">${imgHTML}</div>
            <span class="card-id">#${String(product.id).padStart(3, "0")}</span>
            <div class="product-info">
                <h3>${product.name}</h3>
                <span class="category-tag">${product.category}</span>
                <p class="price">RS ${parseFloat(product.price).toLocaleString()}</p>
                <p class="stock-info">Stock: <strong style="color:${product.stock < 15 ? "#fbbf24" : "inherit"}">${product.stock}</strong></p>
                <span class="status ${statusClass}">${product.status}</span>
            </div>
            <div class="product-actions">
                <button class="edit-btn"   onclick="editProduct(${product.id})">Edit</button>
                <button class="delete-btn" onclick="deleteProduct(${product.id}, this)">Delete</button>
            </div>
        `;
    grid.appendChild(card);
    requestAnimationFrame(() =>
      setTimeout(() => card.classList.add("revealed"), idx * 50),
    );
  });
}

/* ----------------------------------------
   LOAD FROM API
---------------------------------------- */
async function loadProducts() {
  const category = document.getElementById("category-filter")?.value || "";
  const search = document.getElementById("search-input")?.value.trim() || "";

  const params = new URLSearchParams({ action: "list" });
  if (category) params.append("category", category);
  if (search) params.append("search", search);

  try {
    const res = await fetch(`api.php?${params}`);
    const data = await res.json();
    if (data.success) {
      renderProducts(data.data);
    } else {
      showToast("Failed to load products ✦");
    }
  } catch (err) {
    showToast("Server error — check your connection ✦");
  }
}

/* ----------------------------------------
   DELETE
---------------------------------------- */
async function deleteProduct(id, btn) {
  if (!confirm(`Remove product #${id} from the catalogue?`)) return;

  const card = btn.closest(".product-card");
  card.style.transition = "opacity 0.4s ease, transform 0.4s ease";
  card.style.opacity = "0";
  card.style.transform = "scale(0.95)";

  try {
    const res = await fetch(`api.php?action=delete&id=${id}`, {
      method: "POST",
    });
    const data = await res.json();
    if (data.success) {
      setTimeout(() => {
        card.remove();
        showToast(`Product #${id} removed ✦`);
      }, 400);
      loadProducts();
    } else {
      card.style.opacity = "1";
      card.style.transform = "";
      showToast("Delete failed: " + (data.error || "Unknown error"));
    }
  } catch {
    showToast("Network error ✦");
  }
}

/* ----------------------------------------
   EDIT (redirect to edit page)
---------------------------------------- */
/* ----------------------------------------
   MODAL LOGIC
---------------------------------------- */
async function editProduct(id) {
  // 1. Fetch current product data from the API
  try {
    const res = await fetch(`api.php?action=list&id=${id}`);
    const result = await res.json();

    if (result.success && result.data.length > 0) {
      const product = result.data.find((p) => p.id == id);

      // 2. Fill the form fields
      document.getElementById("edit-id").value = product.id;
      document.getElementById("edit-name").value = product.name;
      document.getElementById("edit-category").value = product.category;
      document.getElementById("edit-price").value = product.price;
      document.getElementById("edit-stock").value = product.stock;

      // 3. Show the modal
      document.getElementById("edit-modal").classList.add("active");
    } else {
      showToast("Could not load product data ✦");
    }
  } catch (err) {
    showToast("Server error ✦");
  }
}

function closeEditModal() {
  document.getElementById("edit-modal").classList.remove("active");
}

// Close modal when clicking outside the content box
window.onclick = function (event) {
  const modal = document.getElementById("edit-modal");
  if (event.target == modal) closeEditModal();
};

/* ----------------------------------------
   SUBMIT EDIT FORM
---------------------------------------- */
document.getElementById("edit-form")?.addEventListener("submit", async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);

  try {
    const res = await fetch("api.php?action=update", {
      method: "POST",
      body: formData,
    });
    const data = await res.json();

    if (data.success) {
      showToast("Product updated successfully ✦");
      closeEditModal();
      loadProducts(); // Refresh the grid
    } else {
      showToast("Update failed: " + data.error);
    }
  } catch (err) {
    showToast("Network error ✦");
  }
});

/* ----------------------------------------
   VIEW TOGGLE
---------------------------------------- */
function setView(mode) {
  const grid = document.getElementById("product-grid");
  document
    .querySelectorAll(".view-btn")
    .forEach((b) => b.classList.toggle("active", b.dataset.view === mode));
  grid.classList.toggle("table-view", mode === "table");
  localStorage.setItem("productsView", mode);
}

/* ----------------------------------------
   TOAST
---------------------------------------- */
function showToast(message) {
  let toast = document.querySelector(".al-toast");
  if (!toast) {
    toast = document.createElement("div");
    toast.className = "al-toast";
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.classList.add("show");
  clearTimeout(toast._timeout);
  toast._timeout = setTimeout(() => toast.classList.remove("show"), 3000);
}

/* ----------------------------------------
   INIT
---------------------------------------- */
document.addEventListener("DOMContentLoaded", () => {
  setView(localStorage.getItem("productsView") || "grid");
  loadProducts();

  document
    .getElementById("category-filter")
    ?.addEventListener("change", loadProducts);
  document
    .getElementById("search-input")
    ?.addEventListener("input", loadProducts);
  document
    .querySelectorAll(".view-btn")
    .forEach((btn) =>
      btn.addEventListener("click", () => setView(btn.dataset.view)),
    );
  document
    .querySelector(".notif-btn")
    ?.addEventListener("click", () => showToast("No new notifications ✦"));
});

/* Scroll reveal */
const _s = document.createElement("style");
_s.textContent = `.reveal-ready{opacity:0;transform:translateY(20px);transition:opacity .5s ease,transform .5s ease}.reveal-ready.revealed{opacity:1;transform:translateY(0)}`;
document.head.appendChild(_s);
