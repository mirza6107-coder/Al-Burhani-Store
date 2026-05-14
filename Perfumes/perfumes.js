/* ========================================
   AL BURHAN STORE — PERFUMES PAGE JS
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

  
  /* ---------------- ADD TO CART ---------------- */
  document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();

      const product = {
        product_id: this.dataset.id,
        name: this.dataset.name,
        price: parseFloat(this.dataset.price),
        img: this.dataset.img,
        size: this.dataset.size || 'Standard',
        qty: 1
      };

      addToCart(product, this);
    });
  });

  async function addToCart(product, btnElement) {
    const originalHTML = btnElement.innerHTML;
    btnElement.innerHTML = `<span>ADDING...</span>`;
    btnElement.disabled = true;

    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', product.product_id);
    formData.append('name', product.name);
    formData.append('price', product.price);
    formData.append('img', product.img);
    formData.append('size', product.size);
    formData.append('qty', product.qty);

    try {
      // ✅ CORRECTED PATH - Important Fix
      const response = await fetch('../Cart-Checkout/cart.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        showToast(`✓ ${product.name} added to bag ✨`);
        
        // Update cart badge
        const badge = document.getElementById('cartBadge');
        if (badge) {
          badge.textContent = data.cart_count || (parseInt(badge.textContent || 0) + product.qty);
        }
      } else {
        showToast(data.message || "Failed to add item");
      }
    } catch (err) {
      console.error("Add to cart error:", err);
      showToast("Connection error. Please check your internet or try again.");
    } finally {
      btnElement.innerHTML = originalHTML;
      btnElement.disabled = false;
    }
  }

  /* ----------------------------------------
     4. TOAST NOTIFICATION
  ---------------------------------------- */
  function showToast(msg) {
    let toast = document.querySelector(".al-toast");
    if (!toast) {
      toast = document.createElement("div");
      toast.className = "al-toast";
      document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.classList.add("show");
    clearTimeout(toast._t);
    toast._t = setTimeout(() => toast.classList.remove("show"), 2800);
  }

  /* ----------------------------------------
     5. SCROLL REVEAL (IntersectionObserver)
  ---------------------------------------- */
  const revealEls = document.querySelectorAll(
    ".product-card, .trust-item, .footer-col, .section-header",
  );

  const revealObs = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          // stagger cards by their CSS --delay var
          const delay =
            parseFloat(
              getComputedStyle(entry.target).getPropertyValue("--delay") || "0",
            ) * 1000;
          setTimeout(() => entry.target.classList.add("revealed"), delay);
          revealObs.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.1, rootMargin: "0px 0px -40px 0px" },
  );

  revealEls.forEach((el) => revealObs.observe(el));

  

  

    
  /* ----------------------------------------
     8. WISHLIST TOGGLE
  ---------------------------------------- */
  document.querySelectorAll(".wishlist-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const isWished = btn.classList.toggle("wishlisted");
      const icon = btn.querySelector("i");
      icon.className = isWished ? "fas fa-heart" : "fas fa-heart";
      showToast(isWished ? "Added to wishlist ✦" : "Removed from wishlist");
    });
  });

  /* ----------------------------------------
     9. ADD TO CART (card button)
  ---------------------------------------- */
  document.querySelectorAll(".btn-add-cart").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      incrementCart();
      const name =
        btn.closest(".product-card")?.querySelector("h4")?.textContent ||
        "Item";
      showToast(`${name} added to bag ✦`);

      // Brief pulse animation on button
      btn.style.background = "var(--gold)";
      btn.style.color = "var(--deep-green)";
      setTimeout(() => {
        btn.style.background = "";
        btn.style.color = "";
      }, 600);
    });
  });

 
  /* ----------------------------------------
     11. CARD 3D TILT ON HOVER
  ---------------------------------------- */
  document.querySelectorAll(".product-card").forEach((card) => {
    card.addEventListener("mousemove", (e) => {
      const r = card.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width - 0.5;
      const y = (e.clientY - r.top) / r.height - 0.5;
      card.style.transform = `translateY(-6px) rotateX(${-y * 5}deg) rotateY(${x * 5}deg)`;
    });
    card.addEventListener("mouseleave", () => {
      card.style.transform = "";
    });
  });

  /* ----------------------------------------
     12. SMOOTH SCROLL FOR ANCHOR LINKS
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
     14. NEWSLETTER FORM
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
     15. ANNOUNCEMENT BAR — TEXT ROTATION
  ---------------------------------------- */
  const annMessages = [
    "✦ Free Shipping on Orders Above RS 500 ✦",
    "New Arrivals — Exclusive Scents Now Live",
    "✦ 100% Authentic Luxury Guaranteed ✦",
    "Complimentary Gift Wrapping Available",
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
});
