<?php
require_once __DIR__ . '/config/config.php';

$about = get_about_section();
$testimonials = get_testimonials(6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <?= render_seo_meta([
        'title' => 'About Us - ' . get_setting('site_name', 'A2Z Handicraft') . ' | Handcrafted Decor Manufacturer',
        'description' => truncate_text($about['content_p1'], 160),
        'canonical' => BASE_URL . '/about-us.php',
        'image' => $about['image']
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
                        <h1 class="text-anime-style-3" data-cursor="-opaque">About Us</h1>
                        <nav class="wow fadeInUp">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li> 
                                <li class="breadcrumb-item">//</li> 
                                <li class="breadcrumb-item active" aria-current="page">About Us</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Why Customer Choose Section Start -->
    <div class="why-customer-choose light-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-6 mb-4">
                    <div class="about_us">
                       <img src="<?= e(resolve_image_url($about['image'])) ?>" class="img-fluid rounded shadow-sm" alt="About A2Z Handicraft">
                    </div>
                </div>

                <div class="col-xl-6 mb-4">
                    <div class="why-customer-choose-content">
                        <div class="section-title">
                            <span class="section-sub-title wow fadeInUp"><?= e($about['subtitle']) ?></span>
                            <h2 class="text-anime-style-3"><?= e($about['title']) ?></h2>
                            <?php if (!empty($about['content_p1'])): ?>
                                <p><?= e($about['content_p1']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($about['content_p2'])): ?>
                                <p><?= e($about['content_p2']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($about['content_p3'])): ?>
                                <p><?= e($about['content_p3']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Counter Stats Metrics Row -->
            <div class="row g-4 mt-4 text-center">
                <div class="col-md-3 col-6">
                    <div class="p-4 bg-white rounded shadow-sm border">
                        <h2 class="fw-bold text-primary mb-1"><?= e($about['experience_years']) ?></h2>
                        <span class="text-muted small text-uppercase fw-semibold">Industry Experience</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-4 bg-white rounded shadow-sm border">
                        <h2 class="fw-bold text-success mb-1"><?= e($about['happy_customers']) ?></h2>
                        <span class="text-muted small text-uppercase fw-semibold">Satisfied Clients</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-4 bg-white rounded shadow-sm border">
                        <h2 class="fw-bold text-warning mb-1"><?= e($about['products_count']) ?></h2>
                        <span class="text-muted small text-uppercase fw-semibold">Handicraft Designs</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-4 bg-white rounded shadow-sm border">
                        <h2 class="fw-bold text-info mb-1"><?= e($about['craftsmen_count']) ?></h2>
                        <span class="text-muted small text-uppercase fw-semibold">Master Artisans</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Why Customer Choose Section End -->

    <!-- Our Testimonials Section Start -->
    <?php if (!empty($testimonials)): ?>
    <div class="our-testimonials dark-section parallaxie py-5">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="testimonials-content-box">
                        <div class="section-title">
                            <span class="section-sub-title wow fadeInUp">Testimonials</span>
                            <h2 class="text-anime-style-3" data-cursor="-opaque">Trusted Reviews From Decorators & Clients</h2>
                        </div>

                        <div class="testimonial-slider">
                            <div class="swiper">
                                <div class="swiper-wrapper" data-cursor-text="Drag">
                                    <?php foreach ($testimonials as $t): ?>
                                        <div class="swiper-slide">
                                            <div class="testimonial-item">
                                                <div class="testimonial-item-content-box">
                                                    <div class="testimonial-item-rating">
                                                        <?php for ($i = 0; $i < $t['rating']; $i++): ?>
                                                            <i class="fa-solid fa-star"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <div class="testimonial-item-content">
                                                        <p><?= e($t['content']) ?></p>
                                                    </div>
                                                </div>
                                                <div class="testimonial-item-author">
                                                    <div class="testimonial-author-image">
                                                        <figure class="image-anime">
                                                            <img src="<?= e(resolve_image_url($t['image'])) ?>" alt="<?= e($t['name']) ?>">
                                                        </figure>
                                                    </div>
                                                    <div class="testimonial-author-content">
                                                        <h2><?= e($t['name']) ?></h2>
                                                        <small><?= e($t['designation'] ?? '') ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="testimonial-btn">
                                    <div class="testimonial-button-prev"></div>
                                    <div class="testimonial-button-next"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <!-- Our Testimonials Section End -->

<?php include('includes/footer.php') ?>
</body>
</html>