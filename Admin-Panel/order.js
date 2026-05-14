/* ========================================
   AL BURHAN STORE — order.js (FIXED)
   ======================================== */

const Order = {
  // ── Selected rows for bulk actions ──
  selectedIds: [],
  pendingDeleteId: null,

  /* ────────────────────────────────────
     TOAST
  ──────────────────────────────────── */
  toast(msg, isError = false) {
    const t = document.getElementById("adminToast");
    if (!t) return;
    t.textContent = msg;
    t.style.background = isError ? "var(--red)" : "var(--green-ok)";
    t.style.color = "#fff";
    t.classList.add("show");
    clearTimeout(t._t);
    t._t = setTimeout(() => t.classList.remove("show"), 3000);
  },

  /* ────────────────────────────────────
     MODAL OPEN / CLOSE
  ──────────────────────────────────── */
  openModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.add("open");
  },
  closeModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.remove("open");
  },

  /* ────────────────────────────────────
     VIEW ORDER — AJAX
  ──────────────────────────────────── */
  view(orderId) {
    this.openModal("viewModal");
    const body = document.getElementById("viewModalBody");
    body.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-circle-notch fa-spin" style="font-size:24px;color:var(--gold);"></i></div>';

    fetch("order-actions.php?action=get_order&id=" + orderId)
      .then((res) => res.json())
      .then((data) => {
        if (!data.success) {
          body.innerHTML = '<div style="text-align:center;padding:30px;color:var(--red);">Error: ' + (data.message || "Failed to load") + "</div>";
          return;
        }

        const o = data.order;
        const items = data.items || [];

        // Header info
        document.getElementById("vm_id").textContent = o.order_ref;
        document.getElementById("vm_date").textContent = o.created_at ? new Date(o.created_at).toLocaleDateString("en-GB", { day: "numeric", month: "long", year: "numeric" }) : "";
        document.getElementById("vm_status").innerHTML = this._statusBadge(o.status);

        // Build items HTML
        let itemsHTML = "";
        if (items.length > 0) {
          itemsHTML = items.map((item) => `
            <div class="vm-item">
              <div class="vm-item-img">
                ${item.img ? '<img src="' + item.img + '" alt="" onerror="this.style.display=\'none\'">' : '<i class="fas fa-spray-can"></i>'}
              </div>
              <div class="vm-item-info">
                <div class="vm-item-name">${item.name}</div>
                <div class="vm-item-meta">${item.size ? item.size + " · " : ""}Qty: ${item.qty}</div>
              </div>
              <div class="vm-item-total">RS ${Number(item.line_total).toLocaleString("en-PK", {minimumFractionDigits:2})}</div>
            </div>
          `).join("");
        } else {
          itemsHTML = '<div style="padding:16px;text-align:center;color:var(--text-muted);">No items found</div>';
        }

        body.innerHTML = `
          <!-- Customer Section -->
          <div class="vm-section">
            <div class="vm-section-title"><i class="fas fa-user"></i> Customer</div>
            <div class="vm-grid">
              <div><span class="vm-label">Name</span><span class="vm-val">${o.customer}</span></div>
              <div><span class="vm-label">Email</span><span class="vm-val">${o.email}</span></div>
              <div><span class="vm-label">Phone</span><span class="vm-val">${o.phone}</span></div>
              <div><span class="vm-label">City</span><span class="vm-val">${o.city}</span></div>
            </div>
          </div>

          <!-- Shipping Address -->
          <div class="vm-section">
            <div class="vm-section-title"><i class="fas fa-map-marker-alt"></i> Shipping Address</div>
            <div class="vm-address">
              ${o.address ? o.address + "<br>" : ""}
              ${o.apartment ? o.apartment + "<br>" : ""}
              ${o.city}${o.state ? ", " + o.state : ""} ${o.postal ? o.postal : ""}<br>
              ${o.country || ""}
            </div>
          </div>

          <!-- Items -->
          <div class="vm-section">
            <div class="vm-section-title"><i class="fas fa-box"></i> Items (${items.length})</div>
            <div class="vm-items">${itemsHTML}</div>
          </div>

          <!-- Payment & Totals -->
          <div class="vm-section">
            <div class="vm-section-title"><i class="fas fa-receipt"></i> Payment & Totals</div>
            <div class="vm-totals">
              <div class="vm-total-row"><span>Payment Method</span><span>${o.payment_method}</span></div>
              <div class="vm-total-row"><span>Delivery</span><span>${o.delivery_method || "Standard"}</span></div>
              <div class="vm-total-row"><span>Subtotal</span><span>RS ${Number(o.subtotal).toLocaleString("en-PK", {minimumFractionDigits:2})}</span></div>
              <div class="vm-total-row"><span>Shipping</span><span>${o.shipping_cost > 0 ? "RS " + Number(o.shipping_cost).toFixed(2) : "Free"}</span></div>
              <div class="vm-total-row"><span>VAT (5%)</span><span>RS ${Number(o.vat).toFixed(2)}</span></div>
              ${o.discount > 0 ? '<div class="vm-total-row"><span>Discount</span><span style="color:var(--green-ok);">-RS ' + Number(o.discount).toFixed(2) + "</span></div>" : ""}
              ${o.promo_code ? '<div class="vm-total-row"><span>Promo</span><span>' + o.promo_code + "</span></div>" : ""}
              <div class="vm-total-row vm-grand"><span>Total</span><span>RS ${Number(o.total).toLocaleString("en-PK", {minimumFractionDigits:2})}</span></div>
            </div>
          </div>

          ${o.notes ? '<div class="vm-section"><div class="vm-section-title"><i class="fas fa-sticky-note"></i> Notes</div><div class="vm-notes">' + o.notes.replace(/\\n/g, "<br>") + "</div></div>" : ""}
        `;
      })
      .catch((err) => {
        body.innerHTML = '<div style="text-align:center;padding:30px;color:var(--red);">Failed to load order details</div>';
      });
  },

  /* ────────────────────────────────────
     STATUS BADGE (mirrors PHP helper)
  ──────────────────────────────────── */
  _statusBadge(status) {
    const map = {
      pending:   ["badge-pending",   "fa-clock",        "Pending"],
      confirmed: ["badge-confirmed", "fa-check",        "Confirmed"],
      shipped:   ["badge-shipped",   "fa-truck",        "Shipped"],
      delivered: ["badge-delivered", "fa-check-circle", "Delivered"],
      cancelled: ["badge-cancelled", "fa-times-circle", "Cancelled"],
    };
    const [cls, ico, label] = map[status] || ["", "fa-question", status];
    return `<span class="badge ${cls}"><i class="fas ${ico}"></i> ${label}</span>`;
  },

  /* ────────────────────────────────────
     EDIT STATUS
  ──────────────────────────────────── */
  editStatus(orderId, currentStatus) {
    document.getElementById("edit_order_id").value = orderId;
    document.getElementById("edit_display_id").value = "ALB-2026-" + String(orderId).padStart(5, "0");
    document.getElementById("edit_status_select").value = currentStatus || "pending";
    document.getElementById("edit_note").value = "";
    this.openModal("editModal");
  },

  saveStatus() {
    const orderId = document.getElementById("edit_order_id").value;
    const status = document.getElementById("edit_status_select").value;
    const note = document.getElementById("edit_note").value;

    const formData = new FormData();
    formData.append("action", "update_status");
    formData.append("order_id", orderId);
    formData.append("status", status);
    formData.append("note", note);

    fetch("orders.php", { method: "POST", body: formData })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          this.toast("Status updated successfully");
          this.closeModal("editModal");
          setTimeout(() => location.reload(), 600);
        } else {
          this.toast(data.message || "Update failed", true);
        }
      })
      .catch(() => this.toast("Network error", true));
  },

  /* ────────────────────────────────────
     DELETE
  ──────────────────────────────────── */
  delete(orderId) {
    this.pendingDeleteId = orderId;
    document.getElementById("delete_display_id").textContent = "ALB-2026-" + String(orderId).padStart(5, "0");
    this.openModal("deleteModal");
  },

  confirmDelete() {
    if (!this.pendingDeleteId) return;

    const formData = new FormData();
    formData.append("action", "delete");
    formData.append("order_id", this.pendingDeleteId);

    fetch("orders.php", { method: "POST", body: formData })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          this.toast("Order deleted");
          this.closeModal("deleteModal");
          // Remove row from table
          const row = document.querySelector(`tr[data-id="${this.pendingDeleteId}"]`);
          if (row) row.remove();
          this.pendingDeleteId = null;
          setTimeout(() => location.reload(), 600);
        } else {
          this.toast(data.message || "Delete failed", true);
        }
      })
      .catch(() => this.toast("Network error", true));
  },

  /* ────────────────────────────────────
     BULK ACTIONS
  ──────────────────────────────────── */
  toggleAll(checkbox) {
    const checked = checkbox.checked;
    this.selectedIds = [];
    document.querySelectorAll(".row-check").forEach((cb) => {
      cb.checked = checked;
      if (checked) this.selectedIds.push(cb.value);
    });
    this._updateBulkBar();
  },

  onRowCheck() {
    this.selectedIds = [];
    document.querySelectorAll(".row-check:checked").forEach((cb) => {
      this.selectedIds.push(cb.value);
    });
    const all = document.getElementById("checkAll");
    if (all) all.checked = this.selectedIds.length === document.querySelectorAll(".row-check").length;
    this._updateBulkBar();
  },

  _updateBulkBar() {
    const bar = document.getElementById("bulkBar");
    const count = document.getElementById("bulkCount");
    if (bar) bar.style.display = this.selectedIds.length > 0 ? "flex" : "none";
    if (count) count.textContent = this.selectedIds.length;
  },

  bulkStatus(status) {
    if (this.selectedIds.length === 0) return;
    const formData = new FormData();
    formData.append("action", "bulk_status");
    formData.append("status", status);
    this.selectedIds.forEach((id) => formData.append("ids[]", id));

    fetch("orders.php", { method: "POST", body: formData })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          this.toast(`${this.selectedIds.length} orders marked as ${status}`);
          setTimeout(() => location.reload(), 600);
        } else {
          this.toast(data.message || "Bulk update failed", true);
        }
      })
      .catch(() => this.toast("Network error", true));
  },

  bulkDelete() {
    if (this.selectedIds.length === 0) return;
    if (!confirm(`Delete ${this.selectedIds.length} orders? This cannot be undone.`)) return;

    const formData = new FormData();
    formData.append("action", "bulk_delete");
    this.selectedIds.forEach((id) => formData.append("ids[]", id));

    fetch("orders.php", { method: "POST", body: formData })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          this.toast(`${this.selectedIds.length} orders deleted`);
          setTimeout(() => location.reload(), 600);
        } else {
          this.toast(data.message || "Bulk delete failed", true);
        }
      })
      .catch(() => this.toast("Network error", true));
  },

  /* ────────────────────────────────────
     CREATE ORDER
  ──────────────────────────────────── */
  openCreate() {
    this.openModal("createModal");
  },

  createOrder() {
    const name      = document.getElementById("create_name")?.value.trim();
    const email     = document.getElementById("create_email")?.value.trim();
    const phone     = document.getElementById("create_phone")?.value.trim();
    const city      = document.getElementById("create_city")?.value.trim();
    const productId = document.getElementById("create_product")?.value;
    const qty       = document.getElementById("create_qty")?.value || 1;
    const payment   = document.getElementById("create_payment")?.value;
    const status    = document.getElementById("create_status")?.value;
    const notes     = document.getElementById("create_notes")?.value.trim();

    if (!name || !email || !productId) {
      this.toast("Please fill in customer name, email, and select a product", true);
      return;
    }

    const formData = new FormData();
    formData.append("action", "create_order");
    formData.append("name", name);
    formData.append("email", email);
    formData.append("phone", phone);
    formData.append("city", city);
    formData.append("product_id", productId);
    formData.append("qty", qty);
    formData.append("payment", payment);
    formData.append("status", status);
    formData.append("notes", notes);

    fetch("order-actions.php", { method: "POST", body: formData })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          this.toast("Order created successfully!");
          this.closeModal("createModal");
          setTimeout(() => location.reload(), 600);
        } else {
          this.toast(data.message || "Create failed", true);
        }
      })
      .catch(() => this.toast("Network error", true));
  },

  /* ────────────────────────────────────
     PRINT INVOICE
  ──────────────────────────────────── */
  printInvoice(orderId) {
    fetch("order-actions.php?action=get_order&id=" + orderId)
      .then((res) => res.json())
      .then((data) => {
        if (!data.success) {
          this.toast("Failed to load order", true);
          return;
        }
        const o = data.order;
        const items = data.items || [];

        const itemsRows = items.map((item) => `
          <tr>
            <td style="padding:8px;border-bottom:1px solid #eee;">${item.name}</td>
            <td style="padding:8px;border-bottom:1px solid #eee;text-align:center;">${item.size || "—"}</td>
            <td style="padding:8px;border-bottom:1px solid #eee;text-align:center;">${item.qty}</td>
            <td style="padding:8px;border-bottom:1px solid #eee;text-align:right;">RS ${Number(item.price).toFixed(2)}</td>
            <td style="padding:8px;border-bottom:1px solid #eee;text-align:right;">RS ${Number(item.line_total).toFixed(2)}</td>
          </tr>
        `).join("");

        const printHtml = `
          <!DOCTYPE html>
          <html><head><title>Invoice ${o.order_ref}</title>
          <style>
            body { font-family: 'Segoe UI', sans-serif; color: #1a1a2e; padding: 40px; }
            .inv-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
            .inv-brand { font-size: 24px; font-weight: 700; color: #1a1a2e; }
            .inv-ref { font-size: 14px; color: #888; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th { background: #1a1a2e; color: #fff; padding: 10px 8px; text-align: left; font-size: 12px; text-transform: uppercase; }
            .totals { margin-left: auto; width: 300px; }
            .totals tr td { padding: 6px 10px; font-size: 13px; }
            .totals tr td:last-child { text-align: right; font-weight: 600; }
            .grand td { border-top: 2px solid #1a1a2e; font-size: 16px; font-weight: 700; }
          </style></head><body>
            <div class="inv-header">
              <div>
                <div class="inv-brand">AL BURHAN STORE</div>
                <div class="inv-ref">Invoice</div>
              </div>
              <div style="text-align:right;">
                <div style="font-weight:700;font-size:18px;">${o.order_ref}</div>
                <div style="font-size:13px;color:#888;">${o.created_at ? new Date(o.created_at).toLocaleDateString("en-GB", { day: "numeric", month: "long", year: "numeric" }) : ""}</div>
                <div style="font-size:13px;margin-top:4px;color:${o.status === 'cancelled' ? '#e05c5c' : o.status === 'delivered' ? '#2ecc71' : '#c9a96e'};font-weight:600;text-transform:uppercase;">${o.status}</div>
              </div>
            </div>
            <div style="display:flex;gap:40px;margin-bottom:24px;">
              <div style="flex:1;"><strong>Bill To:</strong><br>${o.customer}<br>${o.email}<br>${o.phone}</div>
              <div style="flex:1;"><strong>Ship To:</strong><br>${o.address || ""} ${o.apartment || ""}<br>${o.city}${o.state ? ", " + o.state : ""} ${o.postal || ""}<br>${o.country || ""}</div>
            </div>
            <table>
              <thead><tr><th>Product</th><th style="text-align:center;">Size</th><th style="text-align:center;">Qty</th><th style="text-align:right;">Price</th><th style="text-align:right;">Total</th></tr></thead>
              <tbody>${itemsRows}</tbody>
            </table>
            <table class="totals">
              <tr><td>Subtotal</td><td>RS ${Number(o.subtotal).toFixed(2)}</td></tr>
              <tr><td>Shipping</td><td>${o.shipping_cost > 0 ? "RS " + Number(o.shipping_cost).toFixed(2) : "Free"}</td></tr>
              <tr><td>VAT (5%)</td><td>RS ${Number(o.vat).toFixed(2)}</td></tr>
              ${o.discount > 0 ? "<tr><td>Discount</td><td>-RS " + Number(o.discount).toFixed(2) + "</td></tr>" : ""}
              <tr class="grand"><td>Total</td><td>RS ${Number(o.total).toFixed(2)}</td></tr>
            </table>
            <div style="margin-top:40px;padding-top:16px;border-top:1px solid #eee;font-size:11px;color:#888;text-align:center;">
              Thank you for shopping with Al Burhan Store — 100% Authentic Luxury Guaranteed
            </div>
          </body></html>
        `;

        const win = window.open("", "_blank");
        win.document.write(printHtml);
        win.document.close();
        win.print();
      })
      .catch(() => this.toast("Failed to load order for printing", true));
  },

  /* ────────────────────────────────────
     EXPORT CSV
  ──────────────────────────────────── */
  exportCSV() {
    window.location.href = "order-actions.php?action=export_csv";
  },

  /* ────────────────────────────────────
     PRINT ALL (visible table)
  ──────────────────────────────────── */
  printAll() {
    window.print();
  },
};