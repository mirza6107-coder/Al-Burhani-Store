/* ========================================
   AL BURHAN STORE — CHECKOUT JS
   FIXED VERSION — all bugs resolved:
   1. form variable was never declared
   2. Cart items now embedded in form as JSON
   3. recalcTotals uses live cart data
   4. Proper form submit to check-out.php
   ======================================== */

document.addEventListener("DOMContentLoaded", () => {
  /* ----------------------------------------
     1. STICKY HEADER + ANNOUNCEMENT BAR
  ---------------------------------------- */
  const header = document.querySelector("header");
  const annBar = document.querySelector(".announcement-bar");

  window.addEventListener(
    "scroll",
    () => {
      if (window.scrollY > 80) {
        header.classList.add("scrolled");
        annBar?.classList.add("hidden");
      } else {
        header.classList.remove("scrolled");
        annBar?.classList.remove("hidden");
      }
    },
    { passive: true },
  );

  /* ----------------------------------------
     2. MOBILE HAMBURGER NAV
  ---------------------------------------- */
  const hamburger = document.querySelector(".hamburger");
  const navLinks = document.querySelector(".nav-links");
  const navDivider = document.querySelector(".nav-divider");

  hamburger?.addEventListener("click", () => {
    const isOpen = navLinks.classList.toggle("nav-open");
    hamburger.classList.toggle("active", isOpen);
    navDivider?.classList.toggle("nav-open", isOpen);
  });
  navLinks?.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      navLinks.classList.remove("nav-open");
      hamburger?.classList.remove("active");
    });
  });

  /* ----------------------------------------
     3. TOAST
  ---------------------------------------- */
  function showToast(msg, isError = false) {
    let toast = document.querySelector(".al-toast");
    if (!toast) {
      toast = document.createElement("div");
      toast.className = "al-toast";
      document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.style.background = isError ? "#e05c5c" : "var(--gold)";
    toast.style.color = isError ? "#fff" : "var(--deep-green)";
    toast.classList.add("show");
    clearTimeout(toast._t);
    toast._t = setTimeout(() => toast.classList.remove("show"), 3000);
  }

  /* ----------------------------------------
     4. CART — read from sessionStorage
        (written by cart.js when items added)
        Falls back to hardcoded demo items if
        sessionStorage is empty so page still works
  ---------------------------------------- */
  function getCart() {
    try {
      const raw =
        sessionStorage.getItem("alburhan_cart") ||
        localStorage.getItem("alburhan_cart");
      if (raw) return JSON.parse(raw);
    } catch (_) {}

    // Demo cart — remove once real cart.js writes to storage
    return [
     
    ];
  }

  /* Inject cart as JSON into hidden form field so PHP can read it */
  function syncCartToForm() {
    const cart = getCart();
    let hiddenCart = document.getElementById("hiddenCartJson");
    if (!hiddenCart) {
      hiddenCart = document.createElement("input");
      hiddenCart.type = "hidden";
      hiddenCart.name = "cart_json";
      hiddenCart.id = "hiddenCartJson";
      document.getElementById("checkoutForm")?.appendChild(hiddenCart);
    }
    hiddenCart.value = JSON.stringify(cart);
  }

  /* ----------------------------------------
     5. DELIVERY OPTIONS — toggle + recalc
  ---------------------------------------- */
  const DELIVERY_COSTS = { standard: 0, express: 35, sameday: 75 };
  let deliveryCost = 0;

  document.querySelectorAll(".payment-option").forEach((option) => {
    option.addEventListener("click", () => {
      const radio = option.querySelector("input[type='radio']");
      if (!radio) return;
      const group = radio.name;

      document.querySelectorAll(`input[name="${group}"]`).forEach((r) => {
        r.closest(".payment-option")?.classList.remove("selected");
      });

      option.classList.add("selected");
      radio.checked = true;

      if (group === "delivery") {
        deliveryCost = DELIVERY_COSTS[radio.value] || 0;
        recalcTotals();
      }

      if (group === "payment_method") {
        const cardFields = document.getElementById("cardFields");
        if (radio.value === "card") {
          cardFields?.classList.add("visible");
        } else {
          cardFields?.classList.remove("visible");
        }
        advanceStep(3);
      }
    });
  });

  function recalcTotals() {
    const cart = getCart();
    const subtotal = cart.reduce(
      (s, i) => s + (parseFloat(i.price) || 0) * (parseInt(i.qty) || 1),
      0,
    );
    const shipping = deliveryCost;
    const vat = subtotal * 0.05;
    const total = subtotal + shipping + vat;

    // Update sidebar display
    const subEl = document.getElementById("sumSubtotal");
    const shipEl = document.getElementById("sumShipping");
    const vatEl = document.getElementById("sumVat");
    const totEl = document.getElementById("sumTotal");

    if (subEl) subEl.textContent = `RS ${subtotal.toLocaleString()}`;
    if (shipEl) {
      shipEl.textContent = shipping === 0 ? "Free" : `RS ${shipping}`;
      shipEl.style.color =
        shipping === 0 ? "var(--success)" : "var(--text-white)";
    }
    if (vatEl) vatEl.textContent = `RS ${vat.toFixed(2)}`;
    if (totEl) totEl.textContent = `RS ${total.toFixed(2)}`;

    // Sync hidden fields so PHP gets correct values
    const hSub = document.getElementById("hiddenSubtotal");
    const hShip = document.getElementById("hiddenShipping");
    const hVat = document.getElementById("hiddenVat");
    const hTot = document.getElementById("hiddenTotal");

    if (hSub) hSub.value = subtotal.toFixed(2);
    if (hShip) hShip.value = shipping;
    if (hVat) hVat.value = vat.toFixed(2);
    if (hTot) hTot.value = total.toFixed(2);
  }

  /* ----------------------------------------
     6. PROGRESS STEPS
  ---------------------------------------- */
  const steps = document.querySelectorAll(".step");
  function advanceStep(n) {
    steps.forEach((s, i) => {
      s.classList.remove("active", "done");
      if (i < n - 1) s.classList.add("done");
      else if (i === n - 1) s.classList.add("active");
    });
  }

  /* ----------------------------------------
     7. FLOATING LABEL — select fix
  ---------------------------------------- */
  document.querySelectorAll(".field select").forEach((sel) => {
    sel.addEventListener("change", () => {
      const label = sel.nextElementSibling;
      if (label && sel.value) {
        label.style.top = "6px";
        label.style.fontSize = "9px";
        label.style.color = "var(--gold)";
        label.style.opacity = "0.8";
      }
    });
  });

  /* ----------------------------------------
     8. CARD NUMBER FORMATTING
  ---------------------------------------- */
  const cardNumberInput = document.getElementById("cardNumber");
  cardNumberInput?.addEventListener("input", (e) => {
    let val = e.target.value.replace(/\D/g, "").substring(0, 16);
    e.target.value = val.replace(/(.{4})/g, "$1 ").trim();
  });

  /* ----------------------------------------
     9. EXPIRY FORMATTING
  ---------------------------------------- */
  const cardExpiryInput = document.getElementById("cardExpiry");
  cardExpiryInput?.addEventListener("input", (e) => {
    let val = e.target.value.replace(/\D/g, "").substring(0, 4);
    if (val.length >= 3) val = val.slice(0, 2) + "/" + val.slice(2);
    e.target.value = val;
  });

  /* ----------------------------------------
     10. FORM VALIDATION
  ---------------------------------------- */
  function validateField(field) {
    const input = field.querySelector("input, select, textarea");
    if (!input || !input.required) return true;

    const val = input.value.trim();
    let valid = val.length > 0;

    if (input.type === "email") valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    if (input.type === "tel") valid = val.replace(/\D/g, "").length >= 7;

    field.classList.toggle("valid", valid);
    field.classList.toggle("invalid", !valid);
    return valid;
  }

  // Validate on blur
  document.querySelectorAll(".field input, .field select").forEach((input) => {
    input.addEventListener("blur", () =>
      validateField(input.closest(".field")),
    );
    input.addEventListener("input", () => {
      const field = input.closest(".field");
      if (field.classList.contains("invalid")) validateField(field);
    });
  });

  // Advance progress on name fill
  const firstNameInput = document.getElementById("firstname");
  const lastNameInput = document.getElementById("lastname");
  [firstNameInput, lastNameInput].forEach((inp) => {
    inp?.addEventListener("blur", () => {
      if (firstNameInput?.value && lastNameInput?.value) advanceStep(2);
    });
  });

  /* ----------------------------------------
     11. FORM SUBMIT — properly declared
         BUG FIX: form was never assigned!
  ---------------------------------------- */
  const form = document.getElementById("checkoutForm"); // ← was missing
  const placeOrderBtn = document.getElementById("placeOrderBtn");

  form?.addEventListener("submit", (e) => {
    e.preventDefault();

    // Client-side validation
    let allValid = true;
    document.querySelectorAll(".field").forEach((field) => {
      if (!validateField(field)) allValid = false;
    });

    // Payment must be selected
    const paymentSelected = document.querySelector(
      'input[name="payment_method"]:checked',
    );
    if (!paymentSelected) {
      showToast("Please select a payment method", true);
      allValid = false;
    }

    if (!allValid) {
      const firstInvalid = document.querySelector(
        ".field.invalid input, .field.invalid select",
      );
      firstInvalid?.scrollIntoView({ behavior: "smooth", block: "center" });
      firstInvalid?.focus();
      showToast("Please complete all required fields", true);
      return;
    }

    // Sync cart JSON into hidden field before submit
    syncCartToForm();

    // Loading state
    const originalHTML = placeOrderBtn.innerHTML;
    placeOrderBtn.innerHTML = `<span>Processing Order...</span> <i class="fas fa-circle-notch fa-spin"></i>`;
    placeOrderBtn.disabled = true;

    // Advance to final step visually
    advanceStep(4);

    // Submit the actual form to check-out.php
    form.submit();
  });

  /* ----------------------------------------
     12. SMOOTH SCROLL
  ---------------------------------------- */
  document.querySelectorAll('a[href^="#"]').forEach((a) => {
    a.addEventListener("click", (e) => {
      const target = document.querySelector(a.getAttribute("href"));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    });
  });

  /* ----------------------------------------
     13. NEWSLETTER FORM
  ---------------------------------------- */
  const nlForm = document.querySelector(".newsletter-form");
  const nlInput = nlForm?.querySelector("input");
  const nlButton = nlForm?.querySelector("button");

  nlButton?.addEventListener("click", () => {
    const email = nlInput?.value.trim();
    if (!email || !email.includes("@")) {
      nlInput?.classList.add("input-error");
      setTimeout(() => nlInput?.classList.remove("input-error"), 600);
      return;
    }
    showToast("Subscribed! Welcome to the elite ✦");
    if (nlInput) nlInput.value = "";
  });
  nlInput?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") nlButton?.click();
  });

  /* ----------------------------------------
     14. ANNOUNCEMENT BAR ROTATION
  ---------------------------------------- */
  const annMessages = [
    "✦ Free Shipping on Orders Above RS 500 ✦",
    "Complimentary Gift Wrapping on All Orders",
    "✦ 100% Authentic Luxury Guaranteed ✦",
    "Exclusive Members Get Early Access to Sales",
  ];
  const annText = document.querySelector(".ann-text");
  let annIdx = 0;

  if (annText) {
    setInterval(() => {
      annIdx = (annIdx + 1) % annMessages.length;
      annText.style.opacity = "0";
      setTimeout(() => {
        annText.textContent = annMessages[annIdx];
        annText.style.opacity = "1";
      }, 400);
    }, 3500);
  }

  /* ----------------------------------------
     15. ACTIVE NAV
  ---------------------------------------- */
  const currentPath = window.location.pathname.split("/").pop();
  document.querySelectorAll(".nav-links li a").forEach((link) => {
    if (link.getAttribute("href").includes(currentPath) && currentPath !== "") {
      link.classList.add("nav-active");
    }
  });

  /* ----------------------------------------
     INIT
  ---------------------------------------- */
  syncCartToForm(); // embed cart JSON in form on page load
  recalcTotals(); // set correct totals from real cart
});

/* ========================================
   INJECTED STYLES
   ======================================== */
const style = document.createElement("style");
style.textContent = `
  .input-error { animation: shake 0.4s ease; border-color: var(--error) !important; }
  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    25%       { transform: translateX(-6px); }
    75%       { transform: translateX(6px); }
  }
  header { transition: all 0.4s ease; }
  header.scrolled {
    box-shadow: 0 4px 40px rgba(0,0,0,0.7);
    background: rgba(0,22,13,0.97);
    backdrop-filter: blur(10px);
  }
  .announcement-bar {
    transition: all 0.4s ease;
    max-height: 50px; overflow: hidden;
  }
  .announcement-bar.hidden { max-height: 0; padding: 0; opacity: 0; }
  .ann-text { transition: opacity 0.4s ease; }
  @media (max-width: 768px) {
    .hamburger { display: flex; }
    .nav-links {
      display: none; flex-direction: column; align-items: center;
      max-height: 0; overflow: hidden;
      transition: max-height 0.4s ease, padding 0.3s ease;
    }
    .nav-links.nav-open { display: flex; max-height: 400px; padding: 10px 0 20px; }
    .nav-links li a { padding: 10px 20px; font-size: 11px; letter-spacing: 3px; }
    .nav-divider { display: none; }
  }
`;
document.head.appendChild(style);
