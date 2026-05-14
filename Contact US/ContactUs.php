<?php session_start(); ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Al Burhan Store — Contact Us</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Cinzel:wght@400;500;600&family=Raleway:wght@300;400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="ContactUs.css" />
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
                    <span class="cart-badge">0</span>
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
            <li><a href="../Contact US/ContactUs.php" class="nav-active">Contact Us</a></li>
            <li> <div class="nav-actions">
          <?php if (isset($_SESSION['admin_firstname'])): ?>
            <div class="user-menu-container">
              <button class="signup-btn user-toggle">
                <i class="fas fa-user-circle"></i>
                <?php echo htmlspecialchars($_SESSION['admin_firstname']); ?>
                <i class="fas fa-chevron-down" style="font-size: 8px; margin-left: 5px;"></i>
              </button>
              <div class="user-dropdown">
                <a href="../Reports/reports.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                <a href="../login/logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
              </div>
            </div>
          <?php else: ?>
            <a href="../login/login.html" class="signup-btn">SIGN IN</a>
          <?php endif; ?>
        </div></li>
        </ul>
    </header>

    <!-- HERO -->
    <section class="contact-hero">
        <div class="contact-hero-overlay"></div>

        <div class="contact-hero-particles">
            <span class="particle p1">✦</span>
            <span class="particle p2">◆</span>
            <span class="particle p3">✦</span>
            <span class="particle p4">◆</span>
            <span class="particle p5">✦</span>
        </div>

        <div class="contact-hero-content">
            <div class="hero-eyebrow">
                <span class="eyebrow-line"></span>
                <span>Get In Touch</span>
                <span class="eyebrow-line"></span>
            </div>
            <h1 class="hero-title">
                <span class="hero-title-white">CONTACT</span>
                <span class="hero-title-gold">US</span>
            </h1>
            <p class="hero-tagline">
                We are here to serve you.<br>
                "نحن هنا لخدمتكم"
            </p>
            <p class="hero-arabic">Where You Are Royalty — "خدمتكم شرف لنا"</p>
        </div>
    </section>

    <!-- CONTACT SECTION -->
    <section class="contact-section" id="contact">
        <div class="contact-inner">

            <!-- Info Panel -->
            <div class="contact-info">
                <div class="info-header">
                    <div class="info-eyebrow">✦ Reach Out ✦</div>
                    <h2 class="info-title">We'd Love<br>To Hear From You</h2>
                    <p class="info-desc">
                        Whether you have a question about our collections,
                        need assistance with an order, or simply wish to share
                        your experience — we are at your service.
                    </p>
                </div>

                <div class="info-cards">
                    <div class="info-card">
                        <div class="info-icon"><i class="fas fa-phone"></i></div>
                        <div class="info-card-body">
                            <div class="info-card-label">Phone</div>
                            <div class="info-card-value">+92 342 936 8829</div>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon"><i class="fab fa-whatsapp"></i></div>
                        <div class="info-card-body">
                            <div class="info-card-label">WhatsApp</div>
                            <div class="info-card-value">+92 331 652 2672</div>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon"><i class="fas fa-envelope"></i></div>
                        <div class="info-card-body">
                            <div class="info-card-label">Email</div>
                            <div class="info-card-value">info@alburhan.com</div>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="info-card-body">
                            <div class="info-card-label">Location</div>
                            <div class="info-card-value">Dubai, UAE</div>
                        </div>
                    </div>
                </div>

                <div class="info-social">
                    <div class="info-social-label">Follow Us</div>
                    <div class="social-row">
                        <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="form-card">
                <div class="form-header">
                    <div class="form-eyebrow">✦ Send A Message ✦</div>
                    <div class="form-title">Write To Us</div>
                    <p class="form-subtitle">We typically respond within 24 hours</p>
                </div>

                <!-- SUCCESS / ERROR MESSAGES (shown after page reload if JS is off) -->
                <?php
                // This block handles non-JS fallback — JS fetch is the primary path
                if (isset($_GET['sent']) && $_GET['sent'] === '1'): ?>
                    <div class="form-alert form-alert-success">
                        <i class="fas fa-check-circle"></i>
                        Your message has been received. We'll respond within 24 hours.
                    </div>
                <?php elseif (isset($_GET['error'])): ?>
                    <div class="form-alert form-alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <form class="luxury-form" id="contactForm" novalidate>
                    <div class="form-row">
                        <div class="field">
                            <input type="text" name="firstname" id="firstname" placeholder=" " required />
                            <label for="firstname">First Name</label>
                            <div class="field-error" id="err-firstname"></div>
                        </div>
                        <div class="field">
                            <input type="text" name="lastname" id="lastname" placeholder=" " required />
                            <label for="lastname">Last Name</label>
                            <div class="field-error" id="err-lastname"></div>
                        </div>
                    </div>

                    <div class="field">
                        <input type="tel" name="phone" id="phone" placeholder=" " />
                        <label for="phone">Phone Number</label>
                    </div>

                    <div class="field">
                        <input type="email" name="email" id="email" placeholder=" " required />
                        <label for="email">Email Address</label>
                        <div class="field-error" id="err-email"></div>
                    </div>

                    <div class="field">
                        <input type="text" name="subject" id="subject" placeholder=" " required />
                        <label for="subject">Subject</label>
                        <div class="field-error" id="err-subject"></div>
                    </div>

                    <div class="field">
                        <textarea name="message" id="message" placeholder=" " required rows="5"></textarea>
                        <label for="message">Your Message</label>
                        <div class="field-error" id="err-message"></div>
                    </div>

                    <div class="form-divider">
                        <span class="form-divider-line"></span>
                        <span class="form-divider-gem">◆</span>
                        <span class="form-divider-line"></span>
                    </div>

                    <!-- Global form message -->
                    <div id="form-result"></div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span>Send Message</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
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
<li><i class="fas fa-map-marker-alt"></i> Pakistan</li>                    <li><i class="fas fa-envelope"></i> info@alburhan.com</li>
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

    <script src="ContactUs.js"></script>
</body>

</html>