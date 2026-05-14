<?php
session_start();
$cart_items = $_SESSION['cart'] ?? [];
$subtotal = 0;

// Calculate Subtotal for the summary
foreach ($cart_items as $item) {
  $subtotal += $item['price'] * $item['qty'];
}

// Summary Logic
$shipping = ($subtotal >= 500 || $subtotal == 0) ? 0 : 50;
$vat = $subtotal * 0.05;
$promo_discount = ($subtotal * ($_SESSION['promo_discount'] ?? 0));
$total = $subtotal + $shipping + $vat - $promo_discount;
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Al Burhan Store — Your Bag</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Cinzel:wght@400;500;600&family=Raleway:wght@300;400;500;600&display=swap"
    rel="stylesheet" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="cart.css" />
</head>

<body>
  <!-- ANNOUNCEMENT BAR -->
  <div class="announcement-bar">
    <span class="ann-text">✦ Free Shipping on Orders Above RS 500 ✦</span>
    <span class="ann-sep">|</span>
    <span>Complimentary Gift Wrapping Available</span>
    <span class="ann-sep">|</span>
    <span>✦ 100% Authentic Luxury Guaranteed ✦</span>
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
        <a href="carts.php" class="nav-icon-link cart-link">
          <i class="fas fa-shopping-bag"></i>
          <span class="cart-badge" id="cartBadge">
            <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
          </span>
        </a>
      </div>
      <button class="hamburger" aria-label="Toggle navigation">
        <span></span><span></span><span></span>
      </button>
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

  <!-- PAGE HERO -->
  <section class="page-hero">
    <div class="page-hero-overlay"></div>
    <div class="page-hero-particles">
      <span class="particle p1">✦</span>
      <span class="particle p2">◆</span>
      <span class="particle p3">✦</span>
      <span class="particle p4">◆</span>
      <span class="particle p5">✦</span>
    </div>
    <div class="page-hero-content">
      <div class="hero-eyebrow">
        <span class="eyebrow-line"></span>
        <span>Your Selection</span>
        <span class="eyebrow-line"></span>
      </div>
      <h1 class="page-title">
        <span class="page-title-white">YOUR</span>
        <span class="page-title-gold">BAG</span>
      </h1>
      <p class="page-subtitle">Curated luxury, awaiting you</p>
    </div>
    <div class="breadcrumb">
      <a href="../Home/burhani.html">Home</a>
      <span class="breadcrumb-sep">◆</span>
      <span>Your Bag</span>
    </div>
  </section>

  <!-- CART SECTION -->
  <section class="cart-section">
    <div class="cart-inner">
      <!-- LEFT: Cart Items -->
      <div class="cart-items-panel">
        <div class="cart-panel-header">
          <div class="cart-panel-title">Your Items <span>◆</span></div>
          <div class="cart-count-badge" id="itemCountLabel">
            <?php echo count($cart_items); ?> items
          </div>
        </div>

        <?php if (empty($cart_items)): ?>
          <div class="cart-empty visible" id="cartEmpty">
            <i class="fas fa-shopping-bag"></i>
            <h3>Your Bag Is Empty</h3>
            <p>
              Discover our curated collections and find your perfect luxury
              piece.
            </p>
            <a href="../Perfumes/Perfumes.php" class="btn-continue">Continue Shopping</a>
          </div>
        <?php else: ?> <?php foreach ($cart_items as $key => $item):
                          $line_price = $item['price'] * $item['qty']; ?>
            <div class="cart-item" data-key="<?php echo $key; ?>">
              <img
                class="cart-item-img"
                src="<?php echo $item['img']; ?>"
                alt="<?php echo $item['name']; ?>" />
              <div class="cart-item-info">
                <div class="cart-item-category">Luxury Collection</div>
                <div class="cart-item-name"><?php echo $item['name']; ?></div>
                <div class="cart-item-meta">
                  <span class="cart-item-size"><?php echo $item['size']; ?></span>
                  <div class="qty-control">
                    <input
                      class="qty-value"
                      type="number"
                      value="<?php echo $item['qty']; ?>"
                      readonly />
                  </div>
                </div>
              </div>
              <div class="cart-item-right">
                <div class="cart-item-price">
                  RS <?php echo number_format($line_price); ?>
                </div>
                <button
                  class="cart-item-remove"
                  onclick="removeFromCart('<?php echo $key; ?>')">
                  <i class="fas fa-times"></i> Remove
                </button>
              </div>
            </div>
          <?php endforeach; ?> <?php endif; ?>
      </div>

      <!-- RIGHT: Order Summary -->
      <div class="summary-card">
        <div class="summary-title">
          <span class="summary-gem">◆</span>
          Order Summary
        </div>

        <div class="summary-rows">
          <div class="summary-row">
            <span class="label">Subtotal</span>
            <span class="value" id="summarySubtotal">RS <?php echo number_format($subtotal); ?></span>
          </div>
          <div class="summary-row">
            <span class="label">Shipping</span>
            <span class="value" id="summaryShipping"><?php echo ($shipping == 0) ? "Free" : "RS " . $shipping; ?></span>
          </div>
          <div class="summary-row" id="promoRow" <?php echo ($promo_discount > 0) ? '' : 'style="display: none;"'; ?>>
            <span class="label">Discount</span>
            <span class="value" id="promoDiscount">— RS <?php echo number_format($promo_discount); ?></span>
          </div>
          <div class="summary-row">
            <span class="label">VAT (5%)</span>
            <span class="value" id="summaryVat">RS <?php echo number_format($vat, 2); ?></span>
          </div>
        </div>
        <div class="summary-total">
          <span class="total-label">Total</span>
          <span class="total-value" id="summaryTotal">RS <?php echo number_format($total, 2); ?></span>
        </div>

        <div class="promo-wrap">
          <div class="promo-label">Promo Code</div>
          <div class="promo-form">
            <input class="promo-input" id="promoInput" type="text" placeholder="Enter code" />
            <button class="promo-btn" id="promoApplyBtn">Apply</button>
          </div>
        </div>

        <a href="checks-out.php" class="btn-checkout" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px;">
          <span>Proceed to Checkout</span>
          <i class="fas fa-arrow-right"></i>
        </a>

        <div class="summary-secure">
          <i class="fas fa-lock"></i>
          Secure Checkout
        </div>
      </div>
    </div>
  </section>

  <!-- TRUST STRIP -->
  <section class="trust-section">
    <div class="trust-grid">
      <div class="trust-item">
        <i class="fas fa-shield-alt"></i>
        <div>
          <h5>100% Authentic</h5>
          <p>Every item guaranteed genuine</p>
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
        <i class="fas fa-gift"></i>
        <div>
          <h5>Gift Wrapping</h5>
          <p>Complimentary luxury packaging</p>
        </div>
      </div>
    </div>
  </section>

  <!-- RECOMMENDED -->
  <section class="recommended-section">
    <div class="section-header">
      <div class="section-eyebrow">
        <span class="eyebrow-line-sm"></span>
        <span>You May Also Love</span>
        <span class="eyebrow-line-sm"></span>
      </div>
      <h2 class="section-title">Complete Your Collection</h2>
      <p class="section-subtitle">
        Handpicked luxury to complement your selection
      </p>
    </div>

    <div class="rec-grid">
      <div class="rec-card" style="--delay: 0s">
        <div class="rec-img-wrap">
          <img src="../Images/bently.jpg" alt="Black Oud Noir" />
          <div class="rec-img-overlay"></div>
        </div>
        <div class="rec-body">
          <div class="rec-name">Black Oud Noir</div>
          <div class="rec-note">Oud · Leather · Dark Amber</div>
          <div class="rec-price-row">
            <span class="rec-price">RS 680</span>
            <button class="rec-add-btn">
              <i class="fas fa-plus"></i> Add
            </button>
          </div>
        </div>
      </div>

      <div class="rec-card" style="--delay: 0.1s">
        <div class="rec-img-wrap">
          <img src="../Images/bently.jpg" alt="Rose Oud Elixir" />
          <div class="rec-img-overlay"></div>
        </div>
        <div class="rec-body">
          <div class="rec-name">Rose Oud Elixir</div>
          <div class="rec-note">Rose · Oud · Jasmine</div>
          <div class="rec-price-row">
            <span class="rec-price">RS 285</span>
            <button class="rec-add-btn">
              <i class="fas fa-plus"></i> Add
            </button>
          </div>
        </div>
      </div>

      <div class="rec-card" style="--delay: 0.2s">
        <div class="rec-img-wrap">
          <img src="../Images/watch.jpg" alt="Saffron Imperial" />
          <div class="rec-img-overlay"></div>
        </div>
        <div class="rec-body">
          <div class="rec-name">Saffron Imperial</div>
          <div class="rec-note">Saffron · Rose · Patchouli</div>
          <div class="rec-price-row">
            <span class="rec-price">RS 490</span>
            <button class="rec-add-btn">
              <i class="fas fa-plus"></i> Add
            </button>
          </div>
        </div>
      </div>

      <div class="rec-card" style="--delay: 0.3s">
        <div class="rec-img-wrap">
          <img src="../Images/bently.jpg" alt="Aqua Marine Breeze" />
          <div class="rec-img-overlay"></div>
        </div>
        <div class="rec-body">
          <div class="rec-name">Aqua Marine Breeze</div>
          <div class="rec-note">Sea Salt · Bergamot · Musk</div>
          <div class="rec-price-row">
            <span class="rec-price">RS 220</span>
            <button class="rec-add-btn">
              <i class="fas fa-plus"></i> Add
            </button>
          </div>
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
          <li><i class="fas fa-map-marker-alt"></i> Pakistan</li>
          <li><i class="fas fa-envelope"></i> info@alburhan.com</li>
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
          <li><a href="#">FAQ &amp; Help Center</a></li>
          <li><a href="#">Track Your Order</a></li>
          <li><a href="#">Returns &amp; Refunds</a></li>
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

  <script src="cart.js"></script>
</body>

</html>