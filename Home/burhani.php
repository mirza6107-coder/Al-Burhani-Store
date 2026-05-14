<?php session_start(); ?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Al Burhan Store — Curated Excellence</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Cinzel:wght@400;500;600&family=Raleway:wght@300;400;500&display=swap"
    rel="stylesheet" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="burhani.css" />
</head>

<body>
  <!-- ANNOUNCEMENT BAR -->
  <div class="announcement-bar">
    <span>✦ Free Shipping on Orders Above RS 500 ✦</span>
    <span>Premium Collections Now Available</span>
    <span>✦ Authentic Luxury Guaranteed ✦</span>
  </div>

  <!-- HEADER -->
  <header>
    <nav class="main-nav">
      <div class="nav-left">
        
      </div>
      <div class="logo">
        <div class="logo-ornament">✦</div>
        <h1>AL BURHAN</h1>
        <div class="logo-subtitle">STORE</div>
        <div class="logo-ornament">✦</div>
      </div>
      <div class="nav-right">
        <a href="../Cart-Checkout/carts.php" class="nav-icon-link cart-link">
          <i class="fas fa-shopping-bag"></i>
          <span class="cart-badge" id="cartBadge">
            <?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0; ?>
          </span>
        </a>
      </div>
    </nav>

    <div class="nav-divider">
      <span class="divider-line"></span>
      <span class="divider-gem">◆</span>
      <span class="divider-line"></span>
    </div>

    <ul class="nav-links">
      <li><a href="../Home/burhani.php">Home</a></li>
      <li><a href="../Perfumes/Perfumes.php">Perfumes</a></li>
      <li><a href="../Watches/Watches.php">Watches</a></li>
      <li><a href="../Dress/dress.php">Dress</a></li>
      <li><a href="../Sunglasses/glasses.php">Sunglasses</a></li>
      <li><a href="../Contact US/ContactUs.php">Contact Us</a></li>
      <li>
        <div class="nav-actions">
          <?php if (isset($_SESSION['admin_firstname'])): ?>
            <div class="user-menu-container">
              <button class="signup-btn user-toggle">
                <i class="fas fa-user-circle"></i>
                <?php echo htmlspecialchars($_SESSION['admin_firstname']); ?>
                <i class="fas fa-chevron-down" style="font-size: 8px; margin-left: 5px;"></i>
              </button>
              <div class="user-dropdown">
                <a href="../Admin-Panel/dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                <a href="../login/logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
              </div>
            </div>
          <?php else: ?>
            <a href="../login/login.html" class="signup-btn">SIGN IN</a>
          <?php endif; ?>
        </div>
      </li>
    </ul>
  </header>

  <!-- HERO SECTION -->
  <section class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-particles">
      <span class="particle p1">✦</span>
      <span class="particle p2">◆</span>
      <span class="particle p3">✦</span>
      <span class="particle p4">◆</span>
      <span class="particle p5">✦</span>
    </div>

    <div class="hero-content">
      <div class="hero-tag">
        <span class="tag-line"></span>
        <span>PREMIUM QUALITY</span>
        <span class="tag-line"></span>
      </div>
      <h2 class="hero-title">
        <span class="title-main">CURATED</span>
        <span class="title-accent">EXCELLENCE</span>
      </h2>
      <p class="hero-desc">
        Discover a premium collection of perfumes, watches,<br />apparel &amp;
        accessories for the discerning elite.
      </p>
      <div class="hero-cta-group">
        <a href="../Products/products.html" class="btn-main">
          <span>EXPLORE COLLECTION</span>
          <i class="fas fa-arrow-right"></i>
        </a>
        <a href="#categories" class="btn-ghost">VIEW CATEGORIES</a>
      </div>
    </div>

    <div class="hero-scroll-indicator">
      <span>SCROLL</span>
      <div class="scroll-line"></div>
    </div>
  </section>

  <!-- MARQUEE STRIP -->
  <div class="marquee-strip">
    <div class="marquee-track">
      <span>SIGNATURE PERFUMES</span><span class="marquee-gem">✦</span>
      <span>LUXURY WATCHES</span><span class="marquee-gem">✦</span>
      <span>PREMIUM SUNGLASSES</span><span class="marquee-gem">✦</span>
      <span>EXCLUSIVE APPAREL</span><span class="marquee-gem">✦</span>
      <span>SIGNATURE PERFUMES</span><span class="marquee-gem">✦</span>
      <span>LUXURY WATCHES</span><span class="marquee-gem">✦</span>
      <span>PREMIUM SUNGLASSES</span><span class="marquee-gem">✦</span>
      <span>EXCLUSIVE APPAREL</span><span class="marquee-gem">✦</span>
    </div>
  </div>

  <!-- CATEGORIES SECTION -->
  <section class="categories-section" id="categories">
    <div class="section-header">
      <div class="section-eyebrow">
        <span class="eyebrow-line"></span>
        <span>OUR COLLECTIONS</span>
        <span class="eyebrow-line"></span>
      </div>
      <h2 class="section-title">Shop By Category</h2>
      <p class="section-subtitle">Handpicked luxury for the connoisseur</p>
    </div>

    <div class="category-container">
      <div class="card" style="--delay: 0s">
        <div class="card-img-wrap">
          <img src="../Images/bently.jpg" alt="Perfume" />
          <div class="card-img-overlay"></div>
          <div class="card-badge">NEW</div>
        </div>
        <div class="card-body">
          <div class="card-number">01</div>
          <h4>Signature Scents</h4>
          <p class="card-desc">Rare oud, amber & floral compositions</p>
          <a href="../Products/products.php" class="btn-card">
            <span>SHOP NOW</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>

      <div class="card" style="--delay: 0.1s">
        <div class="card-img-wrap">
          <img src="../Images/watch.jpg" alt="Watch" />
          <div class="card-img-overlay"></div>
        </div>
        <div class="card-body">
          <div class="card-number">02</div>
          <h4>Luxury Watches</h4>
          <p class="card-desc">Timepieces of precision & prestige</p>
          <a href="../Products/products.php" class="btn-card">
            <span>SHOP NOW</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>

      <div class="card" style="--delay: 0.2s">
        <div class="card-img-wrap">
          <img src="../Images/glasses_portrait_fixed.jpg" alt="Sunglasses" />
          <div class="card-img-overlay"></div>
          <div class="card-badge">HOT</div>
        </div>
        <div class="card-body">
          <div class="card-number">03</div>
          <h4>Premium Sunglasses</h4>
          <p class="card-desc">Vision meets designer elegance</p>
          <a href="../Products/products.php" class="btn-card">
            <span>SHOP NOW</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>

      <div class="card" style="--delay: 0.3s">
        <div class="card-img-wrap">
          <img src="../Images/Gemini.png" alt="Dress" />
          <div class="card-img-overlay"></div>
        </div>
        <div class="card-body">
          <div class="card-number">04</div>
          <h4>Exclusive Apparel</h4>
          <p class="card-desc">Couture craftsmanship & refined style</p>
          <a href="../Products/products.php" class="btn-card">
            <span>SHOP NOW</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- TRUST BADGES -->
  <section class="trust-section">
    <div class="trust-grid">
      <div class="trust-item">
        <i class="fas fa-shield-alt"></i>
        <div>
          <h5>100% Authentic</h5>
          <p>Every product guaranteed genuine</p>
        </div>
      </div>
      <div class="trust-divider">◆</div>
      <div class="trust-item">
        <i class="fas fa-shipping-fast"></i>
        <div>
          <h5>Express Delivery</h5>
          <p>Worldwide shipping available</p>
        </div>
      </div>
      <div class="trust-divider">◆</div>
      <div class="trust-item">
        <i class="fas fa-undo-alt"></i>
        <div>
          <h5>Easy Returns</h5>
          <p>30-day hassle-free returns</p>
        </div>
      </div>
      <div class="trust-divider">◆</div>
      <div class="trust-item">
        <i class="fas fa-headset"></i>
        <div>
          <h5>24/7 Support</h5>
          <p>Always here to assist you</p>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <div class="footer-top">
      <div class="footer-logo-area">
        <div class="footer-ornament">✦ ◆ ✦</div>
        <h2>AL BURHAN</h2>
        <p class="footer-tagline">Curated Excellence Since 2020</p>
        <div class="footer-ornament">✦ ◆ ✦</div>
      </div>
    </div>

    <div class="footer-grid">
      <div class="footer-col">
        <h3><span class="col-gem">◆</span> Contact Us</h3>
        <ul>
          <li><i class="fas fa-phone"></i> +92 342 936 8829</li>
          <li><i class="fab fa-whatsapp"></i> +92 331 652 2672</li>
          <li><i class="fas fa-map-marker-alt"></i> Pakistan</li>          <li><i class="fas fa-envelope"></i> info@alburhan.com</li>
        </ul>
      </div>
      <div class="footer-col">
        <h3><span class="col-gem">◆</span> Quick Links</h3>
        <ul>
          <li><a href="../Perfumes/Perfumes.php">Perfumes</a></li>
          <li><a href="../Watches/Watches.php">Watches</a></li>
          <li><a href="../Dress/dress.php">Dress</a></li>
          <li><a href="../Sunglasses/glasses.php">Sunglasses</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h3><span class="col-gem">◆</span> Customer Care</h3>
        <ul>
          <li><a href="#">FAQ & Help Center</a></li>
          <li><a href="#">Track Your Order</a></li>
          <li><a href="#">Returns & Refunds</a></li>
          <li><a href="#">Size Guide</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h3><span class="col-gem">◆</span> Follow Us</h3>
        <div class="social-icons">
          <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-btn"><i class="fab fa-twitter"></i></a>
          <a href="#" class="social-btn"><i class="fab fa-youtube"></i></a>
        </div>
        <div class="newsletter">
          <p>Join our exclusive list</p>
          <div class="newsletter-form">
            <input type="email" placeholder="Your email" />
            <button><i class="fas fa-arrow-right"></i></button>
          </div>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="footer-bottom-line"></div>
      <div class="copyright">
        <span>© 2026 Al Burhan Store. All Rights Reserved.</span>
        <span class="sep">✦</span>
        <span>Crafted with excellence</span>
      </div>
    </div>
  </footer>
  <script src="burhani.js"></script>
</body>

</html>