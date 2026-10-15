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
?>
    <!-- Topbar Section Start -->
    <div class="topbar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 ">
                    <!-- Topbar Social List Start -->
                    <div class="topbar-social-list">
                        <ul>
                            <li><a href="<?= e($fbUrl) ?>"><i class="fa-brands fa-facebook-f"></i></a></li>
                            <li><a href="<?= e($instaUrl) ?>"><i class="fa-brands fa-instagram"></i></a></li>
                            <li><a href="<?= e($ytUrl) ?>"><i class="fa-brands fa-youtube"></i></a></li>
                        </ul>
                    </div>
                    <!-- Topbar Social List End -->
                </div>

                <div class="col-md-7">
                    <!-- Topbar Info Content Start -->
                    <div class="topbar-info-content">
                        <p><?= e($topbarInfo) ?></p>
                    </div>
                    <!-- Topbar Info Content End -->
                </div>
                <div class="col-md-3">
                  <div class="topbar-currency-list ">
                      <div class="top-right text-right">
                       <a href="https://api.whatsapp.com/send?phone=<?= preg_replace('/[^0-9]/', '', $whatsappNo) ?>&text=Hello%20A2Z%20Handicraft,%20I%20am%20interested%20in%20bulk%20order" target="_blank"> <i class="fa-brands fa-whatsapp"></i> <?= e($topbarWhatsappText) ?></a>
                      </div>
                    </div>
                </div>
               
                <!-- Topbar Content Box End -->
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
                        <img src="<?= e($headerLogo) ?>" alt="Logo">
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
                                    <li class="nav-item submenu"><a class="nav-link" href="<?= BASE_URL ?>/categories.php">Event Decoration</a>
                                        <ul>
                                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?category=rdi-hanging">RDI Hanging</a></li>
                                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?category=iron-frame">Iron Frame</a></li>
                                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?category=artificial-flower-patta">Artificial Flower Patta</a></li>
                                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?category=dream-catcher">Dream Catcher</a></li>
                                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?category=flower-bunch">Flower Bunch</a></li>
                                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?category=net-line-hanging">Net Line Hanging</a></li>
                                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?category=ring-hanging">Ring Hanging</a></li>
                                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?subcategory=artificial-ring-hanging">Artificial Ring Hanging</a></li>
                                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?subcategory=kite-hanging">Kite Hanging</a></li>
                                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?subcategory=net-chakra-hanging">Net Chakra Hanging</a></li>
                                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?subcategory=genda-lines">Genda Lines</a></li>
                                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?subcategory=tokri-jumer">Tokri Jumer</a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item submenu"><a class="nav-link" href="<?= BASE_URL ?>/categories.php">Gift Items</a>
                                        <ul>
                                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?category=potli-bags">Potli bags</a></li>
                                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?category=puppets-items">Puppets Items</a></li>
                                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?category=fancy-toran">Fancy Toran</a></li>
                                        </ul>
                                    </li>
                                    
                                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php?category=iron-frame">Wooden & Irons</a></li>
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
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                        <img src="<?= BASE_URL ?>/images/icon-search-primary.svg" alt="">
                                    </button>
                                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                                <form action="<?= BASE_URL ?>/products.php" method="GET" class="modal-search-form">
                                                    <input type="text" name="search" class="form-control" id="search" placeholder="Search Your Product">
                                                    <button type="submit" class="modal-search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Header Search Form End -->
                                </li>
                                <li><a href="<?= ADMIN_URL ?>/index.php" title="Admin Panel"><img src="<?= BASE_URL ?>/images/icon-user-primary.svg" alt=""></a></li>
                                <li><a href="<?= BASE_URL ?>/products.php"><img src="<?= BASE_URL ?>/images/icon-cart-primary.svg" alt=""></a></li>
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
