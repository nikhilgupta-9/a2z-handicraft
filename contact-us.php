<?php
require_once __DIR__ . '/config/config.php';

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = process_contact_submission($_POST);
    if ($result['success']) {
        $successMessage = $result['message'];
        $_POST = [];
    } else {
        $errorMessage = $result['message'];
    }
}

// Fetch dynamic contact settings
$contactPhone = get_setting('phone', '+91-7983894672');
$contactEmail = get_setting('email', 'a2zhandicraft@gmail.com');
$contactAddress = get_setting('address', 'Main Hapur Road, Arifpur Saravni, Distt. Hapur, UP - 245101');
$mapIframeUrl = get_setting('google_map_iframe', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3321.7140488191794!2d77.9146990755088!3d28.850016875546647!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390c81006e5b797d%3A0x7f2601683ef1701!2sArifpur%20sarawini%20plant!5e1!3m2!1sen!2sin!4v1791610367406!5m2!1sen!2sin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <?= render_seo_meta([
        'title' => 'Contact Us - A2Z Handicraft | Wholesale Decor Inquiries',
        'description' => 'Get in touch with A2Z Handicraft for bulk orders, wholesale prices, custom event decor, and catalog requests.',
        'canonical' => BASE_URL . '/contact-us.php'
    ]) ?>
	<?php include('includes/headcss.php') ?>
</head>
<body>
<?php include('includes/header.php') ?>

    <!-- Page Header Start -->
    <div class="page-header dark-section parallaxie">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="page-header-box">
                        <h1 class="text-anime-style-3" data-cursor="-opaque">Contact Us</h1>
                        <nav class="wow fadeInUp">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li> 
                                <li class="breadcrumb-item">//</li> 
                                <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Contact Info Box Start -->
    <div class="contact-info-box py-5">
        <div class="container">
            <div class="row section-row">
                <div class="col-lg-12">
                    <div class="section-title section-title-center">
                        <span class="section-sub-title wow fadeInUp">Contact Information</span>
                        <h2 class="text-anime-style-3" data-cursor="-opaque">Reach Us Anytime Anywhere Easily</h2>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="contact-info-item wow fadeInUp h-100">
                        <div class="icon-box">
                            <img src="<?= BASE_URL ?>/images/icon-phone-white.svg" alt="Phone">
                        </div>
                        <div class="contact-info-content">
                            <p>Phone Number</p>
                            <ul>
                                <li><a href="tel:<?= preg_replace('/[^0-9+]/', '', $contactPhone) ?>"><?= e($contactPhone) ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="contact-info-item wow fadeInUp h-100" data-wow-delay="0.2s">
                        <div class="icon-box">
                            <img src="<?= BASE_URL ?>/images/icon-mail-white.svg" alt="Email">
                        </div>
                        <div class="contact-info-content">
                            <p>Email Address</p>
                            <ul>
                                <li><a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="contact-info-item wow fadeInUp h-100" data-wow-delay="0.4s">
                        <div class="icon-box">
                            <img src="<?= BASE_URL ?>/images/icon-location-white.svg" alt="Location">
                        </div>
                        <div class="contact-info-content">
                            <p>Our Location</p>
                            <ul>
                                <li><?= e($contactAddress) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Contact Info Box End -->

    <!-- Page Contact Us Section Start -->
    <div class="page-contact-us py-5 bg-light">
        <div class="container">
            <div class="row section-row">
                <div class="col-lg-12">
                    <div class="section-title section-title-center">
                        <span class="section-sub-title wow fadeInUp">Get in touch</span>
                        <h2 class="text-anime-style-3">Send Us a Direct Wholesale Message</h2>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-6 mb-4 order-2 order-xl-1">
                    <!-- Google Map Start -->
                    <div class="google-map-iframe wow fadeInUp shadow rounded overflow-hidden">
                        <iframe src="<?= e($mapIframeUrl) ?>" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                    </div>
                    <!-- Google Map End -->
                </div>
                
                <div class="col-xl-6 mb-4 order-1 order-xl-2">
                    <!-- Contact Form Start -->
                    <div class="contact-form bg-white p-3 p-sm-4 p-md-5 rounded shadow-sm">
                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <i class="fa-solid fa-circle-check fs-4 me-2"></i>
                                <div><?= e($successMessage) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="fa-solid fa-triangle-exclamation fs-4 me-2"></i>
                                <div><?= e($errorMessage) ?></div>
                            </div>
                        <?php endif; ?>

                        <form id="contactForm" action="" method="POST" class="wow fadeInUp" data-wow-delay="0.2s">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="form-group col-md-6 mb-4">
                                    <label for="fname" class="form-label fw-semibold">Full Name*</label>
                                    <input type="text" name="fname" class="form-control" id="fname" autocomplete="name" required placeholder="Your full name" value="<?= e($_POST['fname'] ?? '') ?>">
                                </div>

                                <div class="form-group col-md-6 mb-4">
                                    <label for="email" class="form-label fw-semibold">Email Address*</label>
                                    <input type="email" name="email" class="form-control" id="email" autocomplete="email" inputmode="email" required placeholder="your.email@example.com" value="<?= e($_POST['email'] ?? '') ?>">
                                </div>

                                <div class="form-group col-md-6 mb-4">
                                    <label for="phone" class="form-label fw-semibold">Phone Number*</label>
                                    <input type="tel" name="phone" class="form-control" id="phone" autocomplete="tel" inputmode="tel" required placeholder="+91 9876543210" value="<?= e($_POST['phone'] ?? '') ?>">
                                </div>

                                <div class="form-group col-md-6 mb-4">
                                    <label for="subject" class="form-label fw-semibold">Subject*</label>
                                    <input type="text" name="subject" class="form-control" id="subject" required placeholder="e.g. Bulk Order Inquiry" value="<?= e($_POST['subject'] ?? '') ?>">
                                </div>

                                <div class="form-group col-md-12 mb-4">
                                    <label for="message" class="form-label fw-semibold">Message*</label>
                                    <textarea name="message" class="form-control" id="message" rows="5" required placeholder="Enter details about required quantities, designs, or target delivery date..."><?= e($_POST['message'] ?? '') ?></textarea>
                                </div>

                                <div class="col-md-12">
                                    <div class="contact-form-btn">
                                        <button type="submit" class="btn-default w-100 py-3">Send Message</button>
                                        <div id="msgSubmit" class="h4 hidden" style="margin-top: 15px;"></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- Contact Form End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Page Contact Us End -->

<?php include('includes/footer.php') ?>
</body>
</html>