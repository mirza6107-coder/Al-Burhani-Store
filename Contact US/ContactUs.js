/* ========================================
   AL BURHAN STORE — CONTACT US JS
   POSTs to contact_handler.php (MySQL)
   ======================================== */

document.addEventListener("DOMContentLoaded", () => {
  /* ----------------------------------------
     1. STICKY HEADER
  ---------------------------------------- */
  const header = document.querySelector("header");
  const announcementBar = document.querySelector(".announcement-bar");

  window.addEventListener("scroll", () => {
    if (window.scrollY > 80) {
      header.classList.add("scrolled");
      announcementBar?.classList.add("hidden");
    } else {
      header.classList.remove("scrolled");
      announcementBar?.classList.remove("hidden");
    }
  });

  /* ----------------------------------------
     2. MOBILE NAV HAMBURGER
  ---------------------------------------- */
  const mainNav = document.querySelector(".main-nav");
  const navLinks = document.querySelector(".nav-links");
  const navDivider = document.querySelector(".nav-divider");

  const hamburger = document.createElement("button");
  hamburger.className = "hamburger";
  hamburger.setAttribute("aria-label", "Toggle navigation");
  hamburger.innerHTML = `<span></span><span></span><span></span>`;
  mainNav.appendChild(hamburger);

  hamburger.addEventListener("click", () => {
    const isOpen = navLinks.classList.toggle("nav-open");
    hamburger.classList.toggle("active", isOpen);
    navDivider?.classList.toggle("nav-open", isOpen);
  });
  navLinks.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      navLinks.classList.remove("nav-open");
      hamburger.classList.remove("active");
      navDivider?.classList.remove("nav-open");
    });
  });

  /* ----------------------------------------
     3. CONTACT FORM — fetch → contact_handler.php
  ---------------------------------------- */
  const contactForm = document.getElementById("contactForm");
  const submitBtn = document.getElementById("submitBtn");
  const formResult = document.getElementById("form-result");

  /* Field-level validation helpers */
  const rules = [
    { id: "firstname", label: "First name", required: true },
    { id: "lastname", label: "Last name", required: true },
    { id: "email", label: "Email address", required: true, email: true },
    { id: "subject", label: "Subject", required: true },
    { id: "message", label: "Message", required: true, minLen: 10 },
  ];

  function clearErrors() {
    document.querySelectorAll(".field-error").forEach((el) => {
      el.textContent = "";
      el.style.display = "none";
    });
    document.querySelectorAll(".field input, .field textarea").forEach((el) => {
      el.classList.remove("field-invalid");
    });
  }

  function showFieldError(id, msg) {
    const errEl = document.getElementById("err-" + id);
    const input = document.getElementById(id);
    if (errEl) {
      errEl.textContent = msg;
      errEl.style.display = "block";
    }
    input?.classList.add("field-invalid");
  }

  function validateForm() {
    let valid = true;
    rules.forEach((rule) => {
      const input = document.getElementById(rule.id);
      const val = input?.value.trim() || "";
      if (rule.required && !val) {
        showFieldError(rule.id, `${rule.label} is required.`);
        valid = false;
      } else if (rule.email && val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
        showFieldError(rule.id, "Please enter a valid email address.");
        valid = false;
      } else if (rule.minLen && val.length < rule.minLen) {
        showFieldError(
          rule.id,
          `${rule.label} must be at least ${rule.minLen} characters.`,
        );
        valid = false;
      }
    });
    return valid;
  }

  if (contactForm && submitBtn) {
    /* Clear error on input */
    contactForm.querySelectorAll("input, textarea").forEach((el) => {
      el.addEventListener("input", () => {
        const errEl = document.getElementById("err-" + el.id);
        if (errEl) {
          errEl.textContent = "";
          errEl.style.display = "none";
        }
        el.classList.remove("field-invalid");
      });
    });

    contactForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      clearErrors();

      if (!validateForm()) {
        showToast("Please fill all required fields ✦");
        return;
      }

      /* Loading state */
      const origHTML = submitBtn.innerHTML;
      submitBtn.innerHTML = `<span>Sending</span> <i class="fas fa-circle-notch fa-spin"></i>`;
      submitBtn.disabled = true;
      formResult.innerHTML = "";

      const formData = new FormData(contactForm);

      try {
        const res = await fetch("contact_handler.php", {
          method: "POST",
          body: formData,
        });
        const data = await res.json();

        if (data.success) {
          /* Success */
          formResult.innerHTML = `
            <div class="form-alert form-alert-success">
              <i class="fas fa-check-circle"></i>
              ${data.message}
            </div>`;
          showToast("Your message has been sent ✦");
          contactForm.reset();
          submitBtn.innerHTML = `<span>Message Sent</span> <i class="fas fa-check"></i>`;
          setTimeout(() => {
            submitBtn.innerHTML = origHTML;
            submitBtn.disabled = false;
            formResult.innerHTML = "";
          }, 4000);
        } else {
          /* Server-side validation errors */
          const errorList = data.errors || [
            data.error || "Something went wrong.",
          ];
          formResult.innerHTML = `
            <div class="form-alert form-alert-error">
              <i class="fas fa-exclamation-circle"></i>
              ${errorList.join("<br>")}
            </div>`;
          showToast("Please check your entries ✦");
          submitBtn.innerHTML = origHTML;
          submitBtn.disabled = false;
        }
      } catch (err) {
        formResult.innerHTML = `
          <div class="form-alert form-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            Network error — please check your connection and try again.
          </div>`;
        showToast("Network error ✦");
        submitBtn.innerHTML = origHTML;
        submitBtn.disabled = false;
      }
    });
  }

  /* ----------------------------------------
     4. TOAST NOTIFICATION
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
     5. SCROLL REVEAL
  ---------------------------------------- */
  const revealEls = document.querySelectorAll(
    ".form-card, .contact-info, .info-card, .footer-col",
  );
  const revealObs = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("revealed");
          revealObs.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.12, rootMargin: "0px 0px -40px 0px" },
  );

  revealEls.forEach((el, i) => {
    el.classList.add("reveal-ready");
    if (el.classList.contains("info-card"))
      el.style.transitionDelay = `${i * 0.06}s`;
    revealObs.observe(el);
  });

  /* ----------------------------------------
     6. SMOOTH SCROLL
  ---------------------------------------- */
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", (e) => {
      const target = document.querySelector(anchor.getAttribute("href"));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    });
  });

  /* ----------------------------------------
     7. NEWSLETTER FORM
  ---------------------------------------- */
  const newsletterForm = document.querySelector(".newsletter-form");
  if (newsletterForm) {
    const input = newsletterForm.querySelector("input");
    const button = newsletterForm.querySelector("button");
    button.addEventListener("click", () => {
      if (!input.value.trim() || !input.value.includes("@")) {
        input.classList.add("input-error");
        setTimeout(() => input.classList.remove("input-error"), 600);
        return;
      }
      showToast("Thank you for subscribing ✦");
      input.value = "";
    });
    input.addEventListener("keydown", (e) => {
      if (e.key === "Enter") button.click();
    });
  }

  /* ----------------------------------------
     8. ACTIVE NAV LINK
  ---------------------------------------- */
  const currentPath = window.location.pathname.split("/").pop();
  document.querySelectorAll(".nav-links li a").forEach((link) => {
    if (link.getAttribute("href").includes(currentPath) && currentPath !== "") {
      link.classList.add("nav-active");
    }
  });

  /* ----------------------------------------
     9. ANNOUNCEMENT BAR AUTO-ROTATE
  ---------------------------------------- */
  const announcements = [
    "✦ Free Shipping on Orders Above RS 500 ✦",
    "New Arrivals — Premium Collections Now Live",
    "✦ 100% Authentic Luxury Products ✦",
    "Exclusive Members Get Early Access to Sales",
  ];
  const annSpans = document.querySelectorAll(".announcement-bar span");
  if (annSpans.length > 0) {
    let annIdx = 0;
    setInterval(() => {
      annIdx = (annIdx + 1) % announcements.length;
      annSpans[0].style.opacity = "0";
      setTimeout(() => {
        annSpans[0].textContent = announcements[annIdx];
        annSpans[0].style.opacity = "1";
      }, 400);
    }, 3500);
  }
});

/* ========================================
   INJECTED STYLES
   ======================================== */
const style = document.createElement("style");
style.textContent = `

  header { transition: all 0.4s ease; }
  header.scrolled {
    box-shadow: 0 4px 40px rgba(0,0,0,0.7);
    background: rgba(0,22,13,0.97);
    backdrop-filter: blur(10px);
  }
  .announcement-bar { transition: all 0.4s ease; max-height: 50px; overflow: hidden; }
  .announcement-bar.hidden { max-height: 0; padding: 0; opacity: 0; }
  .announcement-bar span { transition: opacity 0.4s ease; }

  .hamburger {
    display: none; flex-direction: column; gap: 5px;
    background: none; border: none; cursor: pointer; padding: 6px;
    position: absolute; right: 20px; top: 50%; transform: translateY(-50%);
  }
  .hamburger span {
    display: block; width: 22px; height: 1.5px;
    background: var(--gold); transition: all 0.3s ease; transform-origin: center;
  }
  .hamburger.active span:nth-child(1) { transform: translateY(6.5px) rotate(45deg); }
  .hamburger.active span:nth-child(2) { opacity: 0; transform: scaleX(0); }
  .hamburger.active span:nth-child(3) { transform: translateY(-6.5px) rotate(-45deg); }

  @media (max-width: 768px) {
    .hamburger { display: flex; }
    .nav-links {
      display: none; flex-direction: column; align-items: center;
      gap: 0 !important; padding: 0 !important;
      border-top: 1px solid rgba(212,175,55,0.15);
      max-height: 0; overflow: hidden;
      transition: max-height 0.4s ease, padding 0.3s ease;
    }
    .nav-links.nav-open { display: flex; max-height: 400px; padding: 10px 0 20px !important; }
    .nav-links li a { padding: 10px 20px; font-size: 11px; letter-spacing: 3px; }
    .nav-divider { display: none; }
  }

  /* Toast */
  .al-toast {
    position: fixed; bottom: 30px; left: 50%;
    transform: translateX(-50%) translateY(20px);
    background: var(--gold); color: var(--deep-green);
    font-family: 'Raleway', sans-serif;
    font-size: 12px; font-weight: 600; letter-spacing: 2px;
    padding: 14px 30px; border-radius: 2px;
    z-index: 9999; opacity: 0; pointer-events: none;
    transition: all 0.4s cubic-bezier(0.25,0.8,0.25,1);
    white-space: nowrap; box-shadow: 0 8px 30px rgba(0,0,0,0.4);
  }
  .al-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

  /* Scroll reveal */
  .reveal-ready { opacity: 0; transform: translateY(30px); transition: opacity 0.7s ease, transform 0.7s ease; }
  .reveal-ready.revealed { opacity: 1; transform: translateY(0); }

  /* Nav active */
  .nav-active { color: var(--gold) !important; }
  .nav-active::after { width: 60% !important; }

  /* Field errors */
  .field-error {
    font-family: 'Raleway', sans-serif;
    font-size: 10px;
    letter-spacing: 1px;
    color: #f87171;
    margin-top: 5px;
    display: none;
  }
  .field-invalid { border-color: rgba(248,113,113,0.6) !important; }

  /* Form alerts */
  .form-alert {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 16px 18px;
    border-radius: 3px;
    font-family: 'Raleway', sans-serif;
    font-size: 12px;
    letter-spacing: 1px;
    margin-bottom: 20px;
    line-height: 1.6;
  }
  .form-alert i { font-size: 16px; margin-top: 1px; flex-shrink: 0; }
  .form-alert-success {
    background: rgba(74,222,128,0.08);
    border: 1px solid rgba(74,222,128,0.3);
    color: #4ade80;
  }
  .form-alert-error {
    background: rgba(248,113,113,0.08);
    border: 1px solid rgba(248,113,113,0.3);
    color: #f87171;
  }

  /* Newsletter error */
  .input-error { animation: shake 0.4s ease; border-color: #e05c5c !important; }
  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-6px); }
    75% { transform: translateX(6px); }
  }
`;
document.head.appendChild(style);
