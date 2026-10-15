<?php
// Dynamic Footer Settings
$siteName = get_setting('site_name', 'A2Z Handicraft');
$footerLogo = resolve_image_url(get_setting('footer_logo', get_setting('site_logo', 'images/logo.png')));
$footerAbout = get_setting('footer_about_text', 'A2Z Handicraft is a leading manufacturer and exporter of traditional Indian handcrafted home decor, wedding props, torans, dream catchers, and festival gifts.');
$footerPhone = get_setting('phone', '+91-7983894672');
$footerWhatsapp = get_setting('whatsapp', '917983894672');
$footerEmail = get_setting('email', 'a2zhandicraft@gmail.com');
$footerAddress = get_setting('address', 'Main Hapur Road, Arifpur Saravni, Distt. Hapur, UP - 245101');
$footerTimings = get_setting('workshop_timings', 'Mon - Sat: 9:00 AM - 8:00 PM (Sunday Closed)');
$footerCopyright = get_setting('copyright_text', 'Copyright © {year} A2Z HANDICRAFT. All Rights Reserved!');
$footerCopyright = str_replace('{year}', date('Y'), $footerCopyright);

// Dynamic Social URLs
$facebookUrl = get_setting('facebook_url', 'https://facebook.com/');
$instagramUrl = get_setting('instagram_url', 'https://instagram.com/');
$youtubeUrl = get_setting('youtube_url', 'https://youtube.com/');

// Dynamic Categories for Footer (active only)
$footerCategories = get_categories(12);
$footerCol1 = array_slice($footerCategories, 0, 6);
$footerCol2 = array_slice($footerCategories, 6, 6);

// Dynamic Newsletter Content
$newsletterTitle = get_setting('footer_newsletter_title', 'Stay In Touch');
$newsletterSubtitle = get_setting('footer_newsletter_subtitle', 'Subscribe for latest arrivals & bulk festival discounts.');
$cleanPhone = preg_replace('/[^0-9+]/', '', $footerPhone);
$cleanWhatsapp = preg_replace('/[^0-9]/', '', $footerWhatsapp);
?>
    <!-- Main Footer Start -->
    <footer class="main-footer dark-section">
        <div class="container">
            <div class="row">
                <!-- Col 1: Brand About & Newsletter -->
                <div class="col-xl-4 col-lg-4 col-md-6 mb-4">
                    <div class="footer-links pe-lg-3">
                        <div class="footer-logo mb-3">
                            <a href="<?= BASE_URL ?>/">
                                <img src="<?= e($footerLogo) ?>" alt="<?= e($siteName) ?>" style="max-height: 48px; background: #fff; padding: 4px 8px; border-radius: 6px;">
                            </a>
                        </div>
                        <div class="about-footer-content mt-2">
                            <p style="font-size: 0.92rem; line-height: 1.6;"><?= e($footerAbout) ?></p>
                        </div>

                        <!-- Footer Newsletter Form -->
                        <div class="footer-newsletter-form mt-3">
                            <h2 style="font-size: 1.1rem; margin-bottom: 8px;"><?= e($newsletterTitle) ?></h2>
                            <p class="small text-white-50 mb-2"><?= e($newsletterSubtitle) ?></p>
                            <form id="footerNewsletterForm" action="<?= BASE_URL ?>/ajax/subscribe.php" method="POST">
                                <div class="form-group d-flex align-items-center">
                                    <input type="email" name="email" id="newsletterEmail" class="form-control" placeholder="Enter your email address..." required>
                                    <button type="submit" class="newsletter-btn" id="newsletterBtn" title="Subscribe">
                                        <i class="fa-solid fa-paper-plane text-dark"></i>
                                    </button>
                                </div>
                                <div id="newsletterAlert" class="small mt-2" style="display: none;"></div>
                            </form>
                        </div>

                        <!-- Social Media Links -->
                        <div class="footer-social-links mt-3 d-flex gap-2 align-items-center">
                            <?php if (!empty($facebookUrl)): ?>
                                <a href="<?= e($facebookUrl) ?>" target="_blank" class="btn btn-sm btn-outline-light rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($instagramUrl)): ?>
                                <a href="<?= e($instagramUrl) ?>" target="_blank" class="btn btn-sm btn-outline-light rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($youtubeUrl)): ?>
                                <a href="<?= e($youtubeUrl) ?>" target="_blank" class="btn btn-sm btn-outline-light rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;" title="YouTube"><i class="fa-brands fa-youtube"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($cleanWhatsapp)): ?>
                                <a href="https://wa.me/<?= e($cleanWhatsapp) ?>?text=<?= urlencode('Hello A2Z Handicraft, I have an inquiry about handcrafted decor products.') ?>" target="_blank" class="btn btn-sm btn-outline-success text-success bg-white rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;" title="Chat on WhatsApp"><i class="fa-brands fa-whatsapp fs-6"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Col 2: Dynamic Categories -->
                <div class="col-xl-3 col-lg-3 col-md-6 mb-4">
                    <div class="footer-links">
                        <h2>Product Categories</h2>
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

                <!-- Col 3: Quick Navigation -->
                <div class="col-xl-2 col-lg-2 col-md-6 mb-4">
                    <div class="footer-links">
                        <h2>Quick Links</h2>
                        <ul>
                            <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                            <li><a href="<?= BASE_URL ?>/about-us.php">About Us</a></li>
                            <li><a href="<?= BASE_URL ?>/categories.php">All Categories</a></li>
                            <li><a href="<?= BASE_URL ?>/products.php">All Products</a></li>
                            <li><a href="<?= BASE_URL ?>/contact-us.php">Contact Us</a></li>
                            <li><a href="<?= ADMIN_URL ?>/login.php">Admin Portal</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Col 4: Contact Information -->
                <div class="col-xl-3 col-lg-3 col-md-6 mb-4">
                    <div class="footer-links footer-contact-box">
                        <h2>Contact Info</h2>
                        <div class="footer-contact-items-list">                           
                            <div class="footer-contact-item">
                                <div class="icon-box">
                                    <img src="<?= BASE_URL ?>/images/icon-phone-white.svg" alt="Phone">
                                </div>
                                <div class="footer-contact-item-content">
                                    <h3>Phone:</h3>
                                    <p><a href="tel:<?= e($cleanPhone) ?>"><?= e($footerPhone) ?></a></p>
                                </div>
                            </div>

                            <div class="footer-contact-item">
                                <div class="icon-box">
                                    <img src="<?= BASE_URL ?>/images/icon-location-white.svg" alt="Location">
                                </div>
                                <div class="footer-contact-item-content">
                                    <h3>Workshop Address</h3>
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

                            <?php if (!empty($footerTimings)): ?>
                                <div class="footer-contact-item">
                                    <div class="icon-box">
                                        <i class="fa-regular fa-clock"></i>
                                    </div>
                                    <div class="footer-contact-item-content">
                                        <h3>Working Hours</h3>
                                        <p class="small text-white-50"><?= e($footerTimings) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Copyright Start -->
        <div class="footer-copyright">
            <div class="container">
                <div class="row align-items-center">
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

    <!-- Footer Dynamic Newsletter Handler -->
    <script>
    $(document).ready(function() {
        $('#footerNewsletterForm').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $('#newsletterBtn');
            var $alert = $('#newsletterAlert');
            var email = $('#newsletterEmail').val();

            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin text-dark"></i>');
            $alert.hide();

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: { email: email },
                dataType: 'json',
                success: function(res) {
                    $btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane text-dark"></i>');
                    if (res.success) {
                        $alert.removeClass('text-danger').addClass('text-success').html('<i class="fa-solid fa-check me-1"></i> ' + res.message).fadeIn();
                        $('#newsletterEmail').val('');
                    } else {
                        $alert.removeClass('text-success').addClass('text-danger').html('<i class="fa-solid fa-triangle-exclamation me-1"></i> ' + res.message).fadeIn();
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane text-dark"></i>');
                    $alert.removeClass('text-success').addClass('text-danger').html('<i class="fa-solid fa-triangle-exclamation me-1"></i> An error occurred. Please try again.').fadeIn();
                }
            });
        });
    });
    </script>
