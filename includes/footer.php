<?php
$footerPhone = get_setting('phone', '+91-7983894672');
$footerEmail = get_setting('email', 'a2zhandicraft@gmail.com');
$footerAddress = get_setting('address', 'Main Hapur Road, Arifpur Saravni, Distt. Hapur, UP - 245101');
$footerCopyright = get_setting('copyright_text', 'Copyright © 2026 A2Z HANDICRAFT. All Rights Reserved!');
$footerCategories = get_categories(12);
$footerCol1 = array_slice($footerCategories, 0, 6);
$footerCol2 = array_slice($footerCategories, 6, 6);
?>
    <!-- Main Footer Start -->
    <footer class="main-footer dark-section">
        <div class="container">
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="footer-links footer-contact-box">
                        <h2>Contact Information</h2>
                        <div class="footer-contact-items-list">                           
                            <div class="footer-contact-item">
                                <div class="icon-box">
                                    <img src="<?= BASE_URL ?>/images/icon-phone-white.svg" alt="Phone">
                                </div>
                                <div class="footer-contact-item-content">
                                    <h3>Phone:</h3>
                                    <p><a href="tel:<?= preg_replace('/[^0-9+]/', '', $footerPhone) ?>"><?= e($footerPhone) ?></a></p>
                                </div>
                            </div>

                            <div class="footer-contact-item">
                                <div class="icon-box">
                                    <img src="<?= BASE_URL ?>/images/icon-location-white.svg" alt="Location">
                                </div>
                                <div class="footer-contact-item-content">
                                    <h3>Address</h3>
                                    <p><?= e($footerAddress) ?></p>
                                </div>
                            </div>

                            <div class="footer-contact-item">
                                <div class="icon-box">
                                    <i class="fa-regular fa-envelope"></i>
                                </div>
                                <div class="footer-contact-item-content">
                                    <h3>Email ID</h3>
                                    <p><a href="mailto:<?= e($footerEmail) ?>"><?= e($footerEmail) ?></a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-md-6 mb-4">
                    <div class="footer-links-box">
                        <div class="footer-links">
                            <h2>Products Categories</h2>
                            <div class="row">
                                <div class="col-6">
                                    <ul>
                                        <?php foreach ($footerCol1 as $c): ?>
                                            <li><a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($c['slug']) ?>"><?= e($c['name']) ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <div class="col-6">
                                    <ul>
                                        <?php foreach ($footerCol2 as $c): ?>
                                            <li><a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($c['slug']) ?>"><?= e($c['name']) ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="footer-links">
                        <h2>Quick Navigation</h2>
                        <ul>
                            <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                            <li><a href="<?= BASE_URL ?>/about-us.php">About Us</a></li>
                            <li><a href="<?= BASE_URL ?>/categories.php">All Categories</a></li>
                            <li><a href="<?= BASE_URL ?>/products.php">All Products</a></li>
                            <li><a href="<?= BASE_URL ?>/contact-us.php">Contact Us</a></li>
                            <li><a href="<?= ADMIN_URL ?>/login.php">Admin Login</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Copyright Start -->
        <div class="footer-copyright">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer-copyright-text text-center">
                            <p><?= e($footerCopyright) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer Copyright End -->
    </footer>
    <!-- Main Footer End -->

    <!-- Jquery Library File -->
    <script src="<?= BASE_URL ?>/js/jquery-3.7.1.min.js"></script>
    <script src="<?= BASE_URL ?>/js/bootstrap.min.js"></script>
    <script src="<?= BASE_URL ?>/js/validator.min.js"></script>
    <script src="<?= BASE_URL ?>/js/jquery.slicknav.js"></script>
    <script src="<?= BASE_URL ?>/js/swiper-bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/js/jquery.waypoints.min.js"></script>
    <script src="<?= BASE_URL ?>/js/jquery.counterup.min.js"></script>
    <script src="<?= BASE_URL ?>/js/jquery.magnific-popup.min.js"></script>
    <script src="<?= BASE_URL ?>/js/SmoothScroll.js"></script>
    <script src="<?= BASE_URL ?>/js/parallaxie.js"></script>
    <script src="<?= BASE_URL ?>/js/gsap.min.js"></script>
    <script src="<?= BASE_URL ?>/js/SplitText.min.js"></script>
    <script src="<?= BASE_URL ?>/js/ScrollTrigger.min.js"></script>
    <script src="<?= BASE_URL ?>/js/jquery.mb.YTPlayer.min.js"></script>
    <script src="<?= BASE_URL ?>/js/wow.min.js"></script>
    <script src="<?= BASE_URL ?>/js/function.js"></script>
