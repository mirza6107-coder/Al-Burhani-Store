/* ========================================
   AL BURHAN STORE — SIGNUP JS
   Matches burhani.js feature set
   ======================================== */

document.addEventListener("DOMContentLoaded", () => {

    /* ----------------------------------------
       1. STICKY HEADER — shrink on scroll
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
       2. MOBILE NAV TOGGLE (hamburger)
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
       3. PASSWORD STRENGTH METER
    ---------------------------------------- */
    const passwordInput = document.getElementById("password");
    const strengthBars = document.querySelectorAll(".strength-bar");

    if (passwordInput && strengthBars.length) {
        passwordInput.addEventListener("input", () => {
            const val = passwordInput.value;
            let strength = 0;
            if (val.length >= 8) strength++;
            if (/[A-Z]/.test(val)) strength++;
            if (/[0-9]/.test(val)) strength++;
            if (/[^A-Za-z0-9]/.test(val)) strength++;

            const colors = ["#e05c5c", "#e08c30", "#d4af37", "#4caf7d"];
            strengthBars.forEach((bar, i) => {
                bar.style.background = i < strength
                    ? colors[strength - 1]
                    : "var(--gold-border)";
            });
        });
    }

    /* ----------------------------------------
       4. CONFIRM PASSWORD MATCH INDICATOR
    ---------------------------------------- */
    const confirmInput = document.getElementById("confirmpassword");

    if (passwordInput && confirmInput) {
        confirmInput.addEventListener("input", () => {
            if (confirmInput.value.length === 0) {
                confirmInput.style.borderColor = "var(--gold-border)";
                return;
            }
            if (confirmInput.value === passwordInput.value) {
                confirmInput.style.borderColor = "#4caf7d";
            } else {
                confirmInput.style.borderColor = "#e05c5c";
            }
        });
    }

    /* ----------------------------------------
       5. FORM SUBMISSION FEEDBACK
    ---------------------------------------- */
    const form = document.querySelector(".luxury-form");
    const submitBtn = document.querySelector(".btn-submit");

    if (form && submitBtn) {
        form.addEventListener("submit", (e) => {
            // Confirm passwords match before submitting
            if (passwordInput.value !== confirmInput.value) {
                e.preventDefault();
                confirmInput.style.borderColor = "#e05c5c";
                showToast("Passwords do not match ✦");
                confirmInput.classList.add("shake");
                setTimeout(() => confirmInput.classList.remove("shake"), 500);
                return;
            }
            // Loading state
            submitBtn.innerHTML = `<span>Creating Account</span> <i class="fas fa-circle-notch fa-spin"></i>`;
            submitBtn.style.pointerEvents = "none";
        });
    }

    /* ----------------------------------------
       6. TOAST NOTIFICATION
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
        toast._timeout = setTimeout(() => toast.classList.remove("show"), 2800);
    }

    /* ----------------------------------------
       7. SCROLL REVEAL
    ---------------------------------------- */
    const revealElements = document.querySelectorAll(".form-card, .signup-brand, .footer-col");

    const revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("revealed");
                    revealObserver.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: "0px 0px -40px 0px" }
    );

    revealElements.forEach((el) => {
        el.classList.add("reveal-ready");
        revealObserver.observe(el);
    });

    /* ----------------------------------------
       8. SMOOTH SCROLL for anchor links
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
       9. NEWSLETTER FORM
    ---------------------------------------- */
    const newsletterForm = document.querySelector(".newsletter-form");
    if (newsletterForm) {
        const input = newsletterForm.querySelector("input");
        const button = newsletterForm.querySelector("button");

        button.addEventListener("click", () => {
            const email = input.value.trim();
            if (!email || !email.includes("@")) {
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
       10. ACTIVE NAV LINK highlight
    ---------------------------------------- */
    const currentPath = window.location.pathname.split("/").pop();
    document.querySelectorAll(".nav-links li a").forEach((link) => {
        if (link.getAttribute("href").includes(currentPath) && currentPath !== "") {
            link.classList.add("nav-active");
        }
    });

    /* ----------------------------------------
       11. ANNOUNCEMENT BAR — auto-rotate
    ---------------------------------------- */
    const announcements = [
        "✦ Free Shipping on Orders Above RS 500 ✦",
        "New Arrivals — Premium Collections Now Live",
        "✦ 100% Authentic Luxury Products ✦",
        "Exclusive Members Get Early Access to Sales",
    ];
    const announcementSpans = document.querySelectorAll(".announcement-bar span");
    if (announcementSpans.length > 0) {
        let annIdx = 0;
        setInterval(() => {
            annIdx = (annIdx + 1) % announcements.length;
            announcementSpans[0].style.opacity = "0";
            setTimeout(() => {
                announcementSpans[0].textContent = announcements[annIdx];
                announcementSpans[0].style.opacity = "1";
            }, 400);
        }, 3500);
    }

});

/* ========================================
   INJECTED STYLES for JS-driven UI
   ======================================== */
const style = document.createElement("style");
style.textContent = `

  /* Sticky header */
  header { transition: all 0.4s ease; }
  header.scrolled {
    box-shadow: 0 4px 40px rgba(0,0,0,0.7);
    background: rgba(0, 22, 13, 0.97);
    backdrop-filter: blur(10px);
  }
  .announcement-bar {
    transition: all 0.4s ease;
    max-height: 50px;
    overflow: hidden;
  }
  .announcement-bar.hidden {
    max-height: 0;
    padding: 0;
    opacity: 0;
  }
  .announcement-bar span { transition: opacity 0.4s ease; }

  /* Hamburger */
  .hamburger {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 6px;
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
  }
  .hamburger span {
    display: block;
    width: 22px;
    height: 1.5px;
    background: var(--gold);
    transition: all 0.3s ease;
    transform-origin: center;
  }
  .hamburger.active span:nth-child(1) { transform: translateY(6.5px) rotate(45deg); }
  .hamburger.active span:nth-child(2) { opacity: 0; transform: scaleX(0); }
  .hamburger.active span:nth-child(3) { transform: translateY(-6.5px) rotate(-45deg); }

  @media (max-width: 768px) {
    .hamburger { display: flex; }
    .nav-links {
      display: none;
      flex-direction: column;
      align-items: center;
      gap: 0 !important;
      padding: 0 !important;
      border-top: 1px solid rgba(212,175,55,0.15);
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.4s ease, padding 0.3s ease;
    }
    .nav-links.nav-open {
      display: flex;
      max-height: 400px;
      padding: 10px 0 20px !important;
    }
    .nav-links li a { padding: 10px 20px; font-size: 11px; letter-spacing: 3px; }
    .nav-divider { display: none; }
  }

  /* Toast */
  .al-toast {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%) translateY(20px);
    background: var(--gold);
    color: var(--deep-green);
    font-family: 'Raleway', sans-serif;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 2px;
    padding: 14px 30px;
    border-radius: 2px;
    z-index: 9999;
    opacity: 0;
    pointer-events: none;
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    white-space: nowrap;
    box-shadow: 0 8px 30px rgba(0,0,0,0.4);
  }
  .al-toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }

  /* Scroll reveal */
  .reveal-ready {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.7s ease, transform 0.7s ease;
  }
  .reveal-ready.revealed {
    opacity: 1;
    transform: translateY(0);
  }

  /* Active nav */
  .nav-active { color: var(--gold) !important; }
  .nav-active::after { width: 60% !important; }

  /* Newsletter error */
  .input-error {
    animation: shake 0.4s ease;
    border-color: #e05c5c !important;
  }

  /* Password / confirm shake */
  .shake { animation: shake 0.4s ease; }

  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-6px); }
    75% { transform: translateX(6px); }
  }
`;
document.head.appendChild(style);