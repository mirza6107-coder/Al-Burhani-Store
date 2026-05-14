/* ========================================
   AL BURHAN STORE — FIXED CART PAGE JS
   Communicates with cart.php for real-time updates
   ======================================== */

document.addEventListener("DOMContentLoaded", () => {

    // --- 1. RECALCULATE (The PHP Way) ---
    // Instead of local math, we ask cart.php for the current totals
    function syncWithServer() {
        fetch('cart.php?action=summary')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateUI(data);
                }
            });
    }

    function updateUI(data) {
        // Update Summary Card
        document.getElementById("summarySubtotal").textContent = `RS ${data.subtotal.toLocaleString()}`;
        document.getElementById("summaryShipping").textContent = data.shipping === 0 ? "Free" : `RS ${data.shipping}`;
        document.getElementById("summaryVat").textContent = `RS ${data.vat.toFixed(2)}`;
        document.getElementById("summaryTotal").textContent = `RS ${data.total.toFixed(2)}`;
        
        // Update Item Labels and Badges
        const count = data.cart_count;
        document.getElementById("itemCountLabel").textContent = `${count} item${count !== 1 ? "s" : ""}`;
        const badge = document.getElementById("cartBadge");
        if (badge) badge.textContent = count;

        // Toggle Empty State
        const empty = document.getElementById("cartEmpty");
        if (count === 0) {
            empty?.classList.add("visible");
        } else {
            empty?.classList.remove("visible");
        }

        // Show Discount if applicable
        const promoRow = document.getElementById("promoRow");
        if (data.discount > 0) {
            promoRow.style.display = "";
            document.getElementById("promoDiscount").textContent = `— RS ${data.discount.toFixed(2)}`;
        }
    }

    // --- 2. QUANTITY CONTROLS (Updated) ---
    document.querySelectorAll(".cart-item").forEach((item) => {
        const key = item.dataset.key; // Make sure your HTML has data-key from PHP session
        const qtyInput = item.querySelector(".qty-value");

        item.querySelector(".qty-minus")?.addEventListener("click", () => {
            let val = parseInt(qtyInput.value);
            if (val > 1) updateQuantity(key, val - 1);
        });

        item.querySelector(".qty-plus")?.addEventListener("click", () => {
            let val = parseInt(qtyInput.value);
            if (val < 10) updateQuantity(key, val + 1);
        });
    });

    function updateQuantity(key, newQty) {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('key', key);
        formData.append('qty', newQty);

        fetch('cart.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload(); // Simplest way to sync state
            });
    }

    // --- 3. REMOVE ITEM (Updated) ---
    document.querySelectorAll(".cart-item-remove").forEach((btn) => {
        btn.addEventListener("click", () => {
            const item = btn.closest(".cart-item");
            const key = item.dataset.key;
            const name = item.querySelector(".cart-item-name")?.textContent || "Item";

            // Visual removal animation
            item.classList.add("removing");
            item.style.opacity = "0";
            item.style.transform = "translateX(30px)";

            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('key', key);

            fetch('cart.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        setTimeout(() => {
                            item.remove();
                            syncWithServer();
                            showToast(`${name} removed from bag`);
                        }, 400);
                    }
                });
        });
    });

    // --- 4. PROMO CODE (Updated) ---
    const promoBtn = document.getElementById("promoApplyBtn");
    promoBtn?.addEventListener("click", () => {
        const code = document.getElementById("promoInput")?.value.trim().toUpperCase();
        
        const formData = new FormData();
        formData.append('action', 'promo');
        formData.append('code', code);

        fetch('cart.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    syncWithServer();
                    promoBtn.textContent = "Applied ✓";
                    promoBtn.style.background = "#4caf7d";
                } else {
                    showToast("Invalid promo code");
                }
            });
    });

    // --- 5. INITIALIZE ---
    syncWithServer();

    // Standard UI helpers (Sticky header, mobile nav, etc. remain the same)
    // ... (Keep your existing code for Hamburger, Toast, and Announcement Bar rotation)
});

function showToast(msg) {
    let toast = document.querySelector(".al-toast") || document.createElement("div");
    toast.className = "al-toast";
    document.body.appendChild(toast);
    toast.textContent = msg;
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 2800);
}
function updateCartBadge(count) {
    const badge = document.getElementById("cartBadge");
    if (badge) {
        badge.textContent = count;
        
        // Optional: Add a little "pop" animation when the number changes
        badge.classList.add("pop");
        setTimeout(() => badge.classList.remove("pop"), 300);
    }
}