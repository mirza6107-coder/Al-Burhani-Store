<?php session_start(); ?>
<?php
$cart_items = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cart_items as $item) {
  $subtotal += $item['price'] * $item['qty'];
}
$shipping = ($subtotal >= 500) ? 0 : 50;
$vat = round($subtotal * 0.05, 2);
$total = $subtotal + $shipping + $vat;
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Al Burhan Store — Checkout</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Cinzel:wght@400;500;600&family=Raleway:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="check-out.css" />
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
      <li><a href="../Contact US/ContactUs.html">Contact Us</a></li>
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
        <span>Almost There</span>
        <span class="eyebrow-line"></span>
      </div>
      <h1 class="page-title">
        <span class="page-title-white">SECURE</span>
        <span class="page-title-gold">CHECKOUT</span>
      </h1>
      <p class="page-subtitle">Complete your luxury order</p>
    </div>
    <div class="breadcrumb">
      <a href="../Home/burhani.html">Home</a>
      <span class="breadcrumb-sep">◆</span>
      <a href="../cart/cart.html">Your Bag</a>
      <span class="breadcrumb-sep">◆</span>
      <span>Checkout</span>
    </div>
  </section>

  <!-- PROGRESS STEPS -->
  <div class="progress-bar">
    <div class="progress-inner">
      <div class="step done">
        <div class="step-dot">
          <i class="fas fa-check" style="font-size: 11px"></i>
        </div>
        <div class="step-label">Cart</div>
      </div>
      <div class="step active">
        <div class="step-dot">2</div>
        <div class="step-label">Details</div>
      </div>
      <div class="step">
        <div class="step-dot">3</div>
        <div class="step-label">Payment</div>
      </div>
      <div class="step">
        <div class="step-dot">4</div>
        <div class="step-label">Confirm</div>
      </div>
    </div>
  </div>

  <!-- CHECKOUT SECTION -->
  <section class="checkout-section">
    <div class="checkout-inner">
      <!-- LEFT: Forms -->
      <div class="checkout-forms">
        <form
          id="checkoutForm"
          action="check-out.php"
          method="POST"
          novalidate>
          <!-- 1. Contact Information -->
          <div class="form-section">
            <div class="section-title-sm">
              <span class="section-gem">◆</span>
              Contact Information
            </div>
            <div class="form-grid">
              <div class="field">
                <input
                  type="text"
                  name="firstname"
                  id="firstname"
                  placeholder=" "
                  required />
                <label for="firstname">First Name</label>
                <div class="field-error">Please enter your first name</div>
              </div>
              <div class="field">
                <input
                  type="text"
                  name="lastname"
                  id="lastname"
                  placeholder=" "
                  required />
                <label for="lastname">Last Name</label>
                <div class="field-error">Please enter your last name</div>
              </div>
              <div class="field">
                <input
                  type="email"
                  name="email"
                  id="email"
                  placeholder=" "
                  required />
                <label for="email">Email Address</label>
                <div class="field-error">Please enter a valid email</div>
              </div>
              <div class="field">
                <input
                  type="tel"
                  name="phone"
                  id="phone"
                  placeholder=" "
                  required />
                <label for="phone">Phone Number</label>
                <div class="field-error">Please enter your phone number</div>
              </div>
            </div>
          </div>

          <!-- 2. Shipping Address -->
          <div class="form-section">
            <div class="section-title-sm">
              <span class="section-gem">◆</span>
              Shipping Address
            </div>
            <div class="form-grid">
              <div class="field full">
                <input
                  type="text"
                  name="address"
                  id="address"
                  placeholder=" "
                  required />
                <label for="address">Street Address</label>
                <div class="field-error">Please enter your address</div>
              </div>
              <div class="field">
                <input
                  type="text"
                  name="apartment"
                  id="apartment"
                  placeholder=" " />
                <label for="apartment">Apt / Suite (Optional)</label>
              </div>
              <div class="field">
                <input
                  type="text"
                  name="city"
                  id="city"
                  placeholder=" "
                  required />
                <label for="city">City</label>
                <div class="field-error">Please enter your city</div>
              </div>
              <div class="field field-select-wrap">
                <select name="country" id="country" required>
                  <option value="">Select Country</option>
                  <option value="AE">United Arab Emirates</option>
                  <option value="SA">Saudi Arabia</option>
                  <option value="KW">Kuwait</option>
                  <option value="QA">Qatar</option>
                  <option value="BH">Bahrain</option>
                  <option value="OM">Oman</option>
                  <option value="PK">Pakistan</option>
                  <option value="IN">India</option>
                  <option value="GB">United Kingdom</option>
                  <option value="US">United States</option>
                  <option value="OTHER">Other</option>
                </select>
                <label for="country">Country</label>
                <div class="field-error">Please select your country</div>
              </div>
              <div class="field">
                <input
                  type="text"
                  name="postal"
                  id="postal"
                  placeholder=" "
                  required />
                <label for="postal">Postal / ZIP Code</label>
                <div class="field-error">Please enter your postal code</div>
              </div>
              <div class="field">
                <input type="text" name="state" id="state" placeholder=" " />
                <label for="state">State / Emirate</label>
              </div>
            </div>
          </div>

          <!-- 3. Delivery Options -->
          <div class="form-section">
            <div class="section-title-sm">
              <span class="section-gem">◆</span>
              Delivery Method
            </div>
            <div class="payment-options">
              <label class="payment-option selected" id="deliveryStandard">
                <input
                  type="radio"
                  name="delivery"
                  value="standard"
                  checked />
                <div class="payment-radio-dot"></div>
                <div class="payment-label">
                  Standard Delivery
                  <span
                    style="opacity: 0.5; font-size: 10px; margin-left: 8px">5–7 business days</span>
                </div>
                <div
                  style="
                      font-family: var(--font-display);
                      font-size: 13px;
                      color: var(--gold);
                    ">
                  Free
                </div>
              </label>
              <label class="payment-option" id="deliveryExpress">
                <input type="radio" name="delivery" value="express" />
                <div class="payment-radio-dot"></div>
                <div class="payment-label">
                  Express Delivery
                  <span
                    style="opacity: 0.5; font-size: 10px; margin-left: 8px">1–3 business days</span>
                </div>
                <div
                  style="
                      font-family: var(--font-display);
                      font-size: 13px;
                      color: var(--gold);
                    ">
                  RS 35
                </div>
              </label>
              <label class="payment-option" id="deliverySameDay">
                <input type="radio" name="delivery" value="sameday" />
                <div class="payment-radio-dot"></div>
                <div class="payment-label">
                  Same-Day (Dubai)
                  <span
                    style="opacity: 0.5; font-size: 10px; margin-left: 8px">Order before 12pm</span>
                </div>
                <div
                  style="
                      font-family: var(--font-display);
                      font-size: 13px;
                      color: var(--gold);
                    ">
                  RS 75
                </div>
              </label>
            </div>
            <div class="field" style="margin-top: 16px">
              <input type="text" name="notes" id="notes" placeholder=" " />
              <label for="notes">Order Notes (Optional)</label>
            </div>
          </div>

          <!-- 4. Payment -->
          <div class="form-section">
            <div class="section-title-sm">
              <span class="section-gem">◆</span>
              Payment Method
            </div>
            <div class="payment-options" id="paymentOptions">
              <label class="payment-option" data-method="card">
                <input type="radio" name="payment_method" value="card" />
                <div class="payment-radio-dot"></div>
                <div class="payment-label">Credit / Debit Card</div>
                <div class="payment-icons-row">
                  <i class="fab fa-cc-visa"></i>
                  <i class="fab fa-cc-mastercard"></i>
                  <i class="fab fa-cc-amex"></i>
                </div>
              </label>
              <label class="payment-option" data-method="paypal">
                <input type="radio" name="payment_method" value="paypal" />
                <div class="payment-radio-dot"></div>
                <div class="payment-label">PayPal</div>
                <div class="payment-icons-row">
                  <i class="fab fa-cc-paypal"></i>
                </div>
              </label>
              <label class="payment-option" data-method="cod">
                <input type="radio" name="payment_method" value="cod" />
                <div class="payment-radio-dot"></div>
                <div class="payment-label">Cash on Delivery</div>
                <div class="payment-icons-row">
                  <i
                    class="fas fa-money-bill-wave"
                    style="font-size: 16px; color: var(--gold); opacity: 0.5"></i>
                </div>
              </label>
            </div>

            <!-- Card fields (shown when card selected) -->
            <div class="card-fields" id="cardFields">
              <div class="form-grid cols-1" style="margin-bottom: 14px">
                <div class="field">
                  <input
                    type="text"
                    name="card_number"
                    id="cardNumber"
                    placeholder=" "
                    maxlength="19" />
                  <label for="cardNumber">Card Number</label>
                </div>
              </div>
              <div class="card-field-row">
                <div class="field">
                  <input
                    type="text"
                    name="card_name"
                    id="cardName"
                    placeholder=" " />
                  <label for="cardName">Name on Card</label>
                </div>
                <div class="field">
                  <input
                    type="text"
                    name="card_expiry"
                    id="cardExpiry"
                    placeholder=" "
                    maxlength="5" />
                  <label for="cardExpiry">MM / YY</label>
                </div>
                <div class="field">
                  <input
                    type="text"
                    name="card_cvv"
                    id="cardCvv"
                    placeholder=" "
                    maxlength="4" />
                  <label for="cardCvv">CVV</label>
                </div>
              </div>
            </div>
          </div>

          <!-- Hidden fields -->
          <input type="hidden" name="cart_json" id="cartInput">
          <input type="hidden" name="subtotal" value="<?= $subtotal ?>">
          <input type="hidden" name="shipping_cost" value="<?= $shipping ?>">
          <input type="hidden" name="vat" value="<?= $vat ?>">
          <input type="hidden" name="total" value="<?= $total ?>">
        </form>
      </div>

      <!-- RIGHT: Order Summary (Dynamic) -->
      <div class="summary-card">
        <div class="summary-title">
          <span class="summary-gem">◆</span> Order Summary
        </div>

        <div class="summary-items">
          <?php if (empty($cart_items)): ?>
            <p style="color:#f87171; text-align:center; padding:20px;">Your cart is empty.</p>
          <?php else: ?>
            <?php foreach ($cart_items as $item): ?>
              <div class="summary-item">
                <img class="summary-item-img" src="<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <div class="summary-item-info">
                  <div class="summary-item-name"><?= htmlspecialchars($item['name']) ?></div>
                  <div class="summary-item-meta"><?= htmlspecialchars($item['size']) ?></div>
                  <div class="summary-item-qty">Qty: <?= $item['qty'] ?></div>
                </div>
                <div class="summary-item-price">RS <?= number_format($item['price'] * $item['qty']) ?></div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="summary-divider"></div>

        <div class="summary-rows">
          <div class="summary-row">
            <span class="label">Subtotal</span>
            <span class="value">RS <?= number_format($subtotal) ?></span>
          </div>
          <div class="summary-row">
            <span class="label">Shipping</span>
            <span class="value"><?= $shipping == 0 ? 'Free' : 'RS ' . $shipping ?></span>
          </div>
          <div class="summary-row">
            <span class="label">VAT (5%)</span>
            <span class="value">RS <?= number_format($vat, 2) ?></span>
          </div>
        </div>

        <div class="summary-divider"></div>

        <div class="summary-total">
          <span class="total-label">Total</span>
          <span class="total-value">RS <?= number_format($total, 2) ?></span>
        </div>

        <button type="submit" form="checkoutForm" class="btn-place-order" id="placeOrderBtn">
          <span>Place Secure Order</span>
          <i class="fas fa-arrow-right"></i>
        </button>
      </div>
    </div>
  </section>

  <!-- ORDER CONFIRMATION OVERLAY -->
  <div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
      <i class="fas fa-gem confirm-icon"></i>
      <div class="confirm-ornament">✦ ◆ ✦</div>
      <h2 class="confirm-title">Order Placed</h2>
      <p class="confirm-subtitle">
        Thank you for choosing Al Burhan Store.<br />
        Your luxury selections are being prepared.
      </p>
      <div class="confirm-order-id" id="confirmOrderId">
        Order #ALB-2026-00001
      </div>
      <div class="confirm-divider"></div>
      <a href="../Home/burhani.html" class="btn-confirm-home">
        <span>Continue Shopping</span>
        <i class="fas fa-arrow-right"></i>
      </a>
    </div>
  </div>

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
          <li><i class="fas fa-map-marker-alt"></i> Dubai, UAE</li>
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

  <script src="check-out.js"></script>
</body>

</html>