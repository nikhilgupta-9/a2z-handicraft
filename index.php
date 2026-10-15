<?php
require_once __DIR__ . '/config/config.php';

// Fetch Dynamic Home Page Components
$banners = get_banners(true);
$heroBanner = !empty($banners) ? $banners[0] : [
    'title' => 'Elevate Your Style With Handmade Fashion',
    'subtitle' => 'A2Z HANDICRAFT',
    'tagline' => 'Premium Quality Materials | Affordable Collection',
    'description' => 'A2Z Handicraft is a trusted manufacturer and supplier of handcrafted decorative products, offering a wide range of beautifully designed items for homes, offices, hotels, events, and interior décor.',
    'image' => 'images/banner.png',
    'button_text' => 'Show All Products',
    'button_link' => 'categories.php',
    'rating_text' => '4.9/5 Review'
];

$topCategories = get_categories(6);
$featuredProducts = get_products([], 10);
$testimonials = get_testimonials(6);
$faqs = get_faqs(null, 6);
$aboutData = get_about_section();
$phone = get_setting('phone', '+91-7983894672');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <?= render_seo_meta() ?>
	<?php include('includes/headcss.php') ?>
</head>
<body>
<?php include('includes/header.php') ?>

    <!-- Hero Section Start -->
    <div class="hero dark-section parallaxie" style="background-image: url('<?= e(resolve_image_url($heroBanner['image'])) ?>');">
        <div class="container">
            <div class="row">
                <div class="col-xl-7">
                    <!-- Hero Content Start -->
                    <div class="hero-content">
                        <!-- Section Title Start -->
                        <div class="section-title">
                            <span class="section-sub-title wow fadeInUp"><?= e($heroBanner['subtitle'] ?? 'A2Z HANDICRAFT') ?></span>
                            <h1 class="text-anime-style-3" data-cursor="-opaque"><?= e($heroBanner['title']) ?></h1>
                            <p class="wow fadeInUp" data-wow-delay="0.2s"><?= e($heroBanner['description'] ?? '') ?></p>
                        </div>
                        <!-- Section Title End -->

                        <!-- Hero Content List Start -->
                        <?php if (!empty($heroBanner['tagline'])): ?>
                            <div class="hero-content-list wow fadeInUp" data-wow-delay="0.4s">
                                <ul>
                                    <?php 
                                    $tags = explode('|', $heroBanner['tagline']);
                                    foreach ($tags as $tag): 
                                    ?>
                                        <li><?= e(trim($tag)) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <!-- Hero Content List End -->

                        <!-- Hero Content Body Start -->
                        <div class="hero-content-body wow fadeInUp" data-wow-delay="0.6s">
                            <!-- Hero Button Start -->
                            <div class="hero-btn">
                                <a href="<?= e($heroBanner['button_link'] ?? 'categories.php') ?>" class="btn-default btn-highlighted"><?= e($heroBanner['button_text'] ?? 'Show All Products') ?></a>
                            </div>
                            <!-- Hero Button End -->
                            
                            <!-- Google Rating Box Start -->
                            <div class="google-rating-box">
                                <div class="google-rating-logo">
                                    <img src="<?= BASE_URL ?>/images/icon-google.svg" alt="Google">
                                </div>
                                <div class="google-rating-content">
                                    <span class="google-rating-star"><i class="fa fa-solid fa-star"></i><i class="fa fa-solid fa-star"></i><i class="fa fa-solid fa-star"></i><i class="fa fa-solid fa-star"></i><i class="fa fa-solid fa-star"></i></span>
                                    <h2><span class="counter"><?= e(explode('/', $heroBanner['rating_text'] ?? '4.9/5')[0]) ?></span>/5 Review</h2>
                                </div>
                            </div>
                            <!-- Google Rating Box End -->
                        </div>
                        <!-- Hero Content Body End -->
                    </div>
                    <!-- Hero Content End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Hero Section End -->

    <!-- Hero Info Box Start -->
    <div class="key-benefit-box">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="key-benefit-item-list">
                        <div class="key-benefit-item wow fadeInUp">
                            <div class="icon-box">
                                <img src="<?= BASE_URL ?>/images/icon-key-benefit-item-1.svg" alt="">
                            </div>
                            <div class="key-benefit-item-content">
                                <h3>Direct Factory Supply</h3>
                                <p>Authentic handmade wholesale</p>
                            </div>
                        </div>

                        <div class="key-benefit-item wow fadeInUp" data-wow-delay="0.2s">
                            <div class="icon-box">
                                <img src="<?= BASE_URL ?>/images/icon-key-benefit-item-2.svg" alt="">
                            </div>
                            <div class="key-benefit-item-content">
                                <h3>Flexible Payment</h3>
                                <p>Pay with Multiple Options</p>
                            </div>
                        </div>

                        <div class="key-benefit-item wow fadeInUp" data-wow-delay="0.4s">
                            <div class="icon-box">
                                <img src="<?= BASE_URL ?>/images/icon-key-benefit-item-3.svg" alt="">
                            </div>
                            <div class="key-benefit-item-content">
                                <h3>Safe & Secure Delivery</h3>
                                <p>Reliable nationwide shipping</p>
                            </div>
                        </div>

                        <div class="key-benefit-item wow fadeInUp" data-wow-delay="0.6s">
                            <div class="icon-box">
                                <img src="<?= BASE_URL ?>/images/icon-key-benefit-item-4.svg" alt="">
                            </div>
                            <div class="key-benefit-item-content">
                                <h3>Dedicated Support</h3>
                                <p>Instant WhatsApp assistance</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Hero Info Box End -->

    <!-- Our Best Sellers / Categories Section Start -->
    <div class="our-best-sellers">
        <div class="container">
            <div class="row section-row align-items-center">
                <div class="col-xl-6">
                    <div class="section-title">
                        <span class="section-sub-title wow fadeInUp">Popular Categories</span>
                        <h2 class="text-anime-style-3" data-cursor="-opaque">Top Handcrafted Collections</h2>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="section-btn wow fadeInUp" data-wow-delay="0.4s">
                        <a href="<?= BASE_URL ?>/categories.php" class="btn-default">View All Collections</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="top-selling-item-list">
                        <?php foreach ($topCategories as $idx => $cat): ?>
                            <div class="top-selling-item wow fadeInUp" data-wow-delay="<?= ($idx * 0.15) ?>s">
                                <div class="top-selling-item-image">
                                    <a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($cat['slug']) ?>" data-cursor-text="View">
                                        <figure class="image-anime">
                                            <img src="<?= e(resolve_image_url($cat['image'])) ?>" alt="<?= e($cat['name']) ?>">
                                        </figure>
                                    </a>
                                </div>
                                <div class="top-selling-item-content">
                                    <h3><a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($cat['slug']) ?>"><?= e($cat['name']) ?></a></h3>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Our Best Sellers Section End -->

    <!-- Why Customer Choose Section Start -->
    <div class="why-customer-choose light-section pt-5">
        <div class="container">
            <div class="row">
                <div class="col-xl-6">
                    <div class="about_us">
                        <img src="<?= e(resolve_image_url($aboutData['image'])) ?>" class="img-fluid rounded" alt="About A2Z Handicraft">
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="why-customer-choose-content">
                        <div class="section-title">
                            <span class="section-sub-title wow fadeInUp"><?= e($aboutData['subtitle']) ?></span>
                            <h2 class="text-anime-style-3"><?= e($aboutData['title']) ?></h2>
                            <p><?= e($aboutData['content_p1']) ?></p>
                            <p><?= e($aboutData['content_p2']) ?></p>
                        </div>
                        <div class="section-btn mt-3">
                            <a href="<?= BASE_URL ?>/about-us.php" class="btn-default">Read More About Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Why Customer Choose Section End -->

    <!-- Our Products Section Start -->
    <div class="our-products">
        <div class="container">
            <div class="row section-row">
                 <div class="col-xl-12">
                    <div class="section-title section-title-center">
                        <span class="section-sub-title wow fadeInUp">Our Catalog</span>
                        <h2 class="text-anime-style-3" data-cursor="-opaque">Explore Our Handicraft Products</h2>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="product-slider">
                        <div class="swiper">
                            <div class="swiper-wrapper">
                                <?php foreach ($featuredProducts as $prod): ?>
                                    <div class="swiper-slide">
                                        <div class="product-item">
                                            <div class="product-item-header">
                                                <div class="product-item-image">
                                                    <a href="<?= BASE_URL ?>/product/<?= e($prod['slug']) ?>">
                                                        <figure>
                                                            <img src="<?= e(resolve_image_url($prod['featured_image'])) ?>" alt="<?= e($prod['name']) ?>">
                                                        </figure>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="product-item-body">
                                                <div class="product-item-content">
                                                    <h2 class="product-item-title"><a href="<?= BASE_URL ?>/product/<?= e($prod['slug']) ?>"><?= e($prod['name']) ?></a></h2>
                                                    <div class="product-item-price mt-1">
                                                        <h3><?= format_price($prod['price']) ?> <?php if (!empty($prod['sale_price'])): ?><span><?= format_price($prod['sale_price']) ?></span><?php endif; ?></h3>
                                                    </div>
                                                </div>                     
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="product-slider-btn">
                                <div class="product-slider-button-prev"></div>
                                <div class="product-slider-button-next"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Our Products Section End -->

    <!-- Our Testimonials Section Start -->
    <?php if (!empty($testimonials)): ?>
    <div class="our-testimonials dark-section parallaxie">
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

    <!-- Our FAQ Section Start -->
    <?php if (!empty($faqs)): ?>
    <div class="our-faqs">
        <div class="container">
            <div class="row">
                <div class="col-xl-5">
                    <div class="faqs-content-box">
                        <div class="section-title">
                            <span class="section-sub-title wow fadeInUp">Frequently Asked Questions</span>
                            <h2 class="text-anime-style-3" data-cursor="-opaque">Common Questions About Our Collection</h2>
                            <p class="wow fadeInUp" data-wow-delay="0.2s">A2Z Handicraft focuses on quality products, attractive designs, professional service, competitive pricing, and reliable supply to meet the requirements of customers and businesses.</p>
                        </div>

                        <div class="faq-content-btn wow fadeInUp" data-wow-delay="0.4s">
                            <a href="tel:<?= preg_replace('/[^0-9+]/', '', $phone) ?>" class="btn-default">Call Now: <?= e($phone) ?></a>
                        </div>
                    </div>
                </div>

                <div class="col-xl-7">
                    <div class="faq-accordion" id="accordion">
                        <?php foreach ($faqs as $fIndex => $faq): ?>
                            <div class="accordion-item wow fadeInUp" data-wow-delay="<?= ($fIndex * 0.15) ?>s">
                                <h2 class="accordion-header" id="heading<?= $faq['id'] ?>">
                                    <button class="accordion-button <?= ($fIndex !== 0) ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $faq['id'] ?>" aria-expanded="<?= ($fIndex === 0) ? 'true' : 'false' ?>" aria-controls="collapse<?= $faq['id'] ?>">
                                        <?= e($faq['question']) ?>
                                    </button>
                                </h2>
                                <div id="collapse<?= $faq['id'] ?>" class="accordion-collapse collapse <?= ($fIndex === 0) ? 'show' : '' ?>" aria-labelledby="heading<?= $faq['id'] ?>" data-bs-parent="#accordion">
                                    <div class="accordion-body">
                                        <p><?= e($faq['answer']) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <!-- Our FAQ Section End -->

<?php include('includes/footer.php') ?>
</body>
</html>