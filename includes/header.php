<?php
// Retrieve Dynamic Header Data
$headerLogo = resolve_image_url(get_setting('site_logo', 'images/logo.png'));
$topbarInfo = get_setting('topbar_info', 'Exclusive Best Handmade Products - Your Perfect Choice!');
$whatsappNo = get_setting('whatsapp', '917983894672');
$phoneNo = get_setting('phone', '+91-7983894672');
$topbarWhatsappText = get_setting('topbar_whatsapp_text', 'Bulk Order Now: ' . $phoneNo);
$fbUrl = get_setting('facebook_url', '#');
$instaUrl = get_setting('instagram_url', '#');
$ytUrl = get_setting('youtube_url', '#');

// Fetch categories and subcategories for menu
$navCategories = get_categories(12);
?>
    <!-- Topbar Section Start -->
    <div class="topbar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 col-4">
                    <!-- Topbar Social List Start -->
                    <div class="topbar-social-list">
                        <ul>
                            <?php if (!empty($fbUrl)): ?><li><a href="<?= e($fbUrl) ?>" target="_blank"><i class="fa-brands fa-facebook-f"></i></a></li><?php endif; ?>
                            <?php if (!empty($instaUrl)): ?><li><a href="<?= e($instaUrl) ?>" target="_blank"><i class="fa-brands fa-instagram"></i></a></li><?php endif; ?>
                            <?php if (!empty($ytUrl)): ?><li><a href="<?= e($ytUrl) ?>" target="_blank"><i class="fa-brands fa-youtube"></i></a></li><?php endif; ?>
                        </ul>
                    </div>
                    <!-- Topbar Social List End -->
                </div>

                <div class="col-md-7 d-none d-md-block">
                    <!-- Topbar Info Content Start -->
                    <div class="topbar-info-content">
                        <p><?= e($topbarInfo) ?></p>
                    </div>
                    <!-- Topbar Info Content End -->
                </div>
                <div class="col-md-3 col-8 text-end">
                  <div class="topbar-currency-list">
                      <div class="top-right text-right">
                       <a href="https://api.whatsapp.com/send?phone=<?= preg_replace('/[^0-9]/', '', $whatsappNo) ?>&text=Hello%20A2Z%20Handicraft,%20I%20am%20interested%20in%20bulk%20order" target="_blank">
                           <i class="fa-brands fa-whatsapp text-success"></i> <?= e($topbarWhatsappText) ?>
                       </a>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar Section End -->

    <!-- Header Start -->
	<header class="main-header">
		<div class="header-sticky">
			<nav class="navbar navbar-expand-lg">
                <div class="container">
                    <!-- Logo Start -->
                    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
                        <img src="<?= e($headerLogo) ?>" alt="<?= e(get_setting('site_name', 'A2Z Handicraft')) ?>" style="max-height: 65px;">
                    </a>
                    <!-- Logo End -->

                    <!-- Main Menu Start -->
                    <div class="main-menu">
                        <!-- Navbar Collapse Start -->
                        <div class="collapse navbar-collapse">
                            <div class="nav-menu-wrapper">
                                <ul class="navbar-nav" id="menu">
                                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php">Home</a></li>                                
                                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/about-us.php">About Us</a></li>
                                    
                                    <li class="nav-item submenu"><a class="nav-link" href="<?= BASE_URL ?>/categories.php">Categories</a>
                                        <ul>
                                            <?php foreach ($navCategories as $cat): ?>
                                                <li><a class="nav-link" href="<?= BASE_URL ?>/products.php?category=<?= urlencode($cat['slug']) ?>"><?= e($cat['name']) ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </li>

                                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php">All Products</a></li>
                                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/contact-us.php">Contact Us</a></li>
                                </ul>
                            </div>
                        </div>                
                        <div class="navbar-toggle"></div>
                        <!-- Navbar Collapse End -->

                        <!-- Header Action Details Start -->
                        <div class="header-action-details">
                            <ul>
                                <li>
                                    <!-- Header Search Form Start -->
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#exampleModal" aria-label="Search">
                                        <img src="<?= BASE_URL ?>/images/icon-search-primary.svg" alt="Search">
                                    </button>
                                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                                <form action="<?= BASE_URL ?>/products.php" method="GET" class="modal-search-form">
                                                    <input type="text" name="search" class="form-control" id="search" placeholder="Search Your Product (e.g. Kite Hanging, Toran...)" required>
                                                    <button type="submit" class="modal-search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Header Search Form End -->
                                </li>
                                <li><a href="<?= ADMIN_URL ?>/index.php" title="Admin Panel"><img src="<?= BASE_URL ?>/images/icon-user-primary.svg" alt="Admin"></a></li>
                                <li><a href="https://api.whatsapp.com/send?phone=<?= preg_replace('/[^0-9]/', '', $whatsappNo) ?>&text=Hello%20A2Z%20Handicraft" target="_blank" title="WhatsApp Inquiries"><i class="fa-brands fa-whatsapp fs-4 text-success"></i></a></li>
                            </ul>
                        </div>
                        <!-- Header Action Details End -->                    
                    </div>
                    <!-- Main Menu End -->
                </div>
            </nav>
			<div class="responsive-menu"></div>
		</div>
	</header>
	<!-- Header End -->
