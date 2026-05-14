<?php session_start(); ?>
<?php
$CATEGORY = 'Watches';
require_once '../Admin-Panel/config.php';
$db   = getDB();
$stmt = $db->prepare("SELECT * FROM products WHERE category = :cat ORDER BY created_at DESC");
$stmt->execute([':cat' => $CATEGORY]);
$db_products = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Al Burhan Store — Luxury Watches</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Cinzel:wght@400;500;600&family=Raleway:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="Watches.css" />
</head>

<body>

  <!-- ANNOUNCEMENT BAR -->
  <div class="announcement-bar">
    <span>✦ Free Shipping on Orders Above RS 500 ✦</span>
    <span>Premium Timepiece Collections Now Available</span>
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
      <li><a href="../Watches/Watches.php" class="nav-active">Watches</a></li>
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
        <span>TIME REDEFINED</span>
        <span class="tag-line"></span>
      </div>
      <h2 class="hero-title">
        <span class="title-main">WATCHES BUILT</span>
        <span class="title-accent">TO IMPRESS</span>
      </h2>
      <p class="hero-desc">
        Discover luxury timepieces crafted for bold style,<br>precision, and timeless elegance.
      </p>
      <div class="hero-cta-group">
        <a href="#collection" class="btn-main">
          <span>SHOP TIMEPIECES</span>
          <i class="fas fa-arrow-right"></i>
        </a>
        <a href="#collection" class="btn-ghost">VIEW COLLECTION</a>
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
      <span>SWISS PRECISION</span><span class="marquee-gem">✦</span>
      <span>LUXURY MOVEMENTS</span><span class="marquee-gem">✦</span>
      <span>SAPPHIRE CRYSTAL</span><span class="marquee-gem">✦</span>
      <span>TIMELESS ELEGANCE</span><span class="marquee-gem">✦</span>
      <span>SWISS PRECISION</span><span class="marquee-gem">✦</span>
      <span>LUXURY MOVEMENTS</span><span class="marquee-gem">✦</span>
      <span>SAPPHIRE CRYSTAL</span><span class="marquee-gem">✦</span>
      <span>TIMELESS ELEGANCE</span><span class="marquee-gem">✦</span>
    </div>
  </div>

  <!-- DYNAMIC PRODUCTS FROM DATABASE -->
  <?php if (!empty($db_products)): ?>
    <section style="padding:0 0 80px;">
      <div class="section-header" style="padding-top:0;">
        <div class="section-eyebrow">
          <span class="eyebrow-line"></span>
          <span>FULL CATALOGUE</span>
          <span class="eyebrow-line"></span>
        </div>
        <h2 class="section-title">All Watches Products</h2>
        <p class="section-subtitle"><?= count($db_products) ?> products available</p>
      </div>
      <div class="category-container" style="flex-wrap:wrap;padding:0 40px;">
        <?php foreach ($db_products as $p): ?>
          <div class="card" style="--delay:0s">
            <div class="card-img-wrap">
              <?php if ($p['image']): ?>
                <img src="<?= htmlspecialchars('../Admin-Panel/' . $p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
              <?php else: ?>
                <div style="height:220px;display:flex;align-items:center;justify-content:center;background:rgba(212,175,55,.06);font-size:40px;color:rgba(212,175,55,.3)"><i class="fas fa-clock"></i></div>
              <?php endif; ?>
              <div class="card-img-overlay"></div>
              <?php if ($p['status'] === 'Low Stock'): ?>
                <div class="card-badge" style="background:#fbbf24;color:#001f13;">LOW STOCK</div>
              <?php elseif ($p['status'] === 'Out of Stock'): ?>
                <div class="card-badge" style="background:#f87171;color:#fff;">OUT OF STOCK</div>
              <?php else: ?>
                <div class="card-badge">IN STOCK</div>
              <?php endif; ?>
            </div>
            <div class="card-body">
              <div class="card-number"><?= str_pad($p['id'], 2, '0', STR_PAD_LEFT) ?></div>
              <h4><?= htmlspecialchars($p['name']) ?></h4>
              <p class="card-desc"><?= htmlspecialchars($p['description'] ?: 'Premium quality product') ?></p>
              <div style="font-family:'Cinzel',serif;color:#d4af37;font-size:15px;margin:8px 0 12px;letter-spacing:1px;">
                RS <?= number_format($p['price'], 2) ?>
              </div>
              <?php if ($p['status'] !== 'Out of Stock'): ?>
                <button
                  class="btn-card add-to-cart-btn"
                  data-id="<?= $p['id'] ?>"
                  data-name="<?= htmlspecialchars($p['name']) ?>"
                  data-price="<?= $p['price'] ?>"
                  data-img="<?= htmlspecialchars('../Admin-Panel/' . ($p['image'] ?? '')) ?>"
                  data-size="Standard">
                  <span>SHOP NOW</span>
                  <i class="fas fa-arrow-right"></i>
                </button>
              <?php else: ?>
                <span style="color:#f87171;font-size:11px;letter-spacing:2px;">SOLD OUT</span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- TRUST BADGES -->
  <section class="trust-section">
    <div class="trust-grid">
      <div class="trust-item">
        <i class="fas fa-shield-alt"></i>
        <div>
          <h5>100% Authentic</h5>
          <p>Every timepiece guaranteed genuine</p>
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

  <script src="Watches.js"></script>
</body>

</html>