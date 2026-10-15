<?php
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? '');
$id = (int)($_GET['id'] ?? 0);

if (!empty($slug)) {
    $product = get_product_by_slug($slug);
} elseif ($id > 0) {
    $product = get_product_by_id($id);
} else {
    $product = null;
}

if (!$product) {
    // If not found, redirect to products page
    set_flash('danger', 'Product not found.');
    header('Location: ' . BASE_URL . '/products.php');
    exit;
}

// Increment view count
global $pdo;
$pdo->query("UPDATE products SET views = views + 1 WHERE id = " . (int)$product['id']);

// Gallery images array
$gallery = !empty($product['gallery_images']) ? (json_decode($product['gallery_images'], true) ?: []) : [];
if (!in_array($product['featured_image'], $gallery)) {
    array_unshift($gallery, $product['featured_image']);
}

// WhatsApp order button setup
$whatsappNo = get_setting('whatsapp', '917983894672');
$cleanWaNumber = preg_replace('/[^0-9]/', '', $whatsappNo);
$waText = "Hello A2Z Handicraft, I would like to order: " . $product['name'] . " (SKU: " . ($product['sku'] ?? 'N/A') . ") - Price: " . format_price($product['price']) . ". Link: " . BASE_URL . "/product/" . $product['slug'];
$whatsappLink = "https://api.whatsapp.com/send?phone=" . $cleanWaNumber . "&text=" . urlencode($waText);

// Related products
$relatedProducts = get_related_products($product['category_id'], $product['id'], 6);

// Product reviews
$reviews = get_reviews($product['id'], 'approved');

// Handle Review Submission
$reviewMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $authorName = clean_input($_POST['author_name'] ?? '');
        $authorEmail = clean_input($_POST['author_email'] ?? '');
        $rating = (int)($_POST['rating'] ?? 5);
        $title = clean_input($_POST['title'] ?? '');
        $comment = clean_input($_POST['comment'] ?? '');

        if (!empty($authorName) && !empty($comment)) {
            $stmt = $pdo->prepare("INSERT INTO reviews (product_id, author_name, author_email, rating, title, comment, status) VALUES (?, ?, ?, ?, ?, ?, 'approved')");
            $stmt->execute([$product['id'], $authorName, $authorEmail, $rating, $title, $comment]);
            $reviewMsg = 'Thank you! Your review has been submitted successfully.';
            // Refresh reviews
            $reviews = get_reviews($product['id'], 'approved');
        }
    }
}

// SEO Meta
$seoMeta = [
    'title' => !empty($product['meta_title']) ? $product['meta_title'] : $product['name'] . ' | ' . get_setting('site_name', 'A2Z Handicraft'),
    'description' => !empty($product['meta_description']) ? $product['meta_description'] : truncate_text($product['description'], 160),
    'keywords' => $product['meta_keywords'] ?? '',
    'canonical' => BASE_URL . '/product/' . $product['slug'],
    'image' => $product['featured_image'],
    'product' => $product
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <?= render_seo_meta($seoMeta) ?>
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
                        <h1 class="text-anime-style-3" data-cursor="-opaque"><?= e($product['name']) ?></h1>
                        <nav class="wow fadeInUp">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li> 
                                <li class="breadcrumb-item">//</li> 
                                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($product['category_slug'] ?? '') ?>"><?= e($product['category_name'] ?? 'Products') ?></a></li>
                                <li class="breadcrumb-item">//</li> 
                                <li class="breadcrumb-item active" aria-current="page"><?= e($product['name']) ?></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Page Product Single Start -->
    <div class="page-product-single py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="page-product-single-content">
                        <!-- Product Single Info Box Start -->
                        <div class="product-single-info-box">
                            <!-- Product Single Image Box Start -->
                            <div class="product-single-image-box wow fadeInUp">
                                <!-- Main Large Swiper -->
                                <div class="swiper product-single-image-slider">
                                    <div class="swiper-wrapper">
                                        <?php foreach ($gallery as $img): ?>
                                            <div class="swiper-slide">
                                                <figure>
                                                    <img src="<?= e(resolve_image_url($img)) ?>" alt="<?= e($product['name']) ?>" class="img-fluid rounded">
                                                </figure>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- Thumbnails Swiper -->
                                <div class="swiper product-single-image-item mt-3">
                                    <div class="swiper-wrapper">
                                        <?php foreach ($gallery as $img): ?>
                                            <div class="swiper-slide">
                                                <figure class="imgae-anime">
                                                    <img src="<?= e(resolve_image_url($img)) ?>" alt="<?= e($product['name']) ?>" style="cursor: pointer; height: 75px; object-fit: cover;">
                                                </figure>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <!-- Product Single Image Box End -->

                            <!-- Product Single Info Content Start -->
                            <div class="product-single-info-content">
                                <div class="product-single-title">
                                    <h1><?= e($product['name']) ?></h1>
                                    <?php if (!empty($product['sku'])): ?>
                                        <p class="text-muted small">Product Code: <strong><?= e($product['sku']) ?></strong></p>
                                    <?php endif; ?>
                                </div>

                                <div class="product-single-price">
                                    <h4>
                                        Price: <?= format_price($product['price']) ?>
                                        <?php if (!empty($product['sale_price'])): ?>
                                            <span class="text-muted text-decoration-line-through fs-6 ms-2"><?= format_price($product['sale_price']) ?></span>
                                        <?php endif; ?>
                                    </h4> 
                                </div>

                                <?php if (!empty($product['sizes_pricing'])): ?>
                                    <div class="products_size my-3">
                                        <p><b>Available Sizes & Wholesale Pricing:</b></p>
                                        <ul>
                                            <?php 
                                            $sizes = explode("\n", trim($product['sizes_pricing']));
                                            foreach ($sizes as $sLine):
                                                if (trim($sLine)):
                                            ?>
                                                <li><?= e(trim($sLine)) ?></li>
                                            <?php 
                                                endif;
                                            endforeach; 
                                            ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <!-- Order Button on WhatsApp -->
                                <div class="product-single-content-body wow fadeInUp my-4" data-wow-delay="0.4s">
                                    <div class="product-single-content-btn order-btn">
                                        <a href="<?= e($whatsappLink) ?>" target="_blank" class="btn-default py-3 px-4 fs-6">
                                            <i class="fa-brands fa-whatsapp fs-5 me-2"></i> Order on WhatsApp Directly
                                        </a>
                                    </div>
                                    <div class="mt-2 text-muted small">
                                        <i class="fa-solid fa-check text-success me-1"></i> Instant Wholesale Rate Confirmation
                                    </div>
                                </div>

                                <!-- Shipping badges -->
                                <div class="product-single-content-footer wow fadeInUp" data-wow-delay="0.6s">
                                    <div class="product-shipping-box">
                                        <ul>
                                            <li><img src="<?= BASE_URL ?>/images/icon-product-shipping-1.svg" alt="">Direct Manufacturer Rates</li>
                                            <li><img src="<?= BASE_URL ?>/images/icon-product-shipping-2.svg" alt="">Secure Nationwide Delivery</li>
                                            <li><img src="<?= BASE_URL ?>/images/icon-product-shipping-3.svg" alt="">10,000+ Happy Customers</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Product Single Info Box End -->

                        <!-- Description & Reviews Tabs Start -->
                        <div class="product-single-review-box wow fadeInUp mt-5" data-wow-delay="0.2s">
                            <div class="product-single-review-tab tab-content">
                                <div class="product-step-nav mb-4">
                                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#descPane" type="button" role="tab">Product Description</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviewsPane" type="button" role="tab">Customer Reviews (<?= count($reviews) ?>)</button>
                                        </li>
                                    </ul>
                                </div>

                                <div class="tab-pane fade show active" id="descPane" role="tabpanel">
                                    <div class="product-tab-item-content">
                                        <?php if (!empty($product['description'])): ?>
                                            <?= nl2br(e($product['description'])) ?>
                                        <?php else: ?>
                                            <p><?= e($product['short_description'] ?? 'Exclusive handmade product by A2Z Handicraft.') ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="reviewsPane" role="tabpanel">
                                    <div class="product-tab-item-content">
                                        <?php if (!empty($reviewMsg)): ?>
                                            <div class="alert alert-success"><?= e($reviewMsg) ?></div>
                                        <?php endif; ?>

                                        <?php if (empty($reviews)): ?>
                                            <p class="text-muted">No reviews yet for this product. Be the first to share your feedback!</p>
                                        <?php else: ?>
                                            <div class="mb-4">
                                                <?php foreach ($reviews as $rev): ?>
                                                    <div class="border-bottom pb-3 mb-3">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <strong><?= e($rev['author_name']) ?></strong>
                                                            <span class="text-warning small"><?= str_repeat('★', $rev['rating']) ?></span>
                                                        </div>
                                                        <?php if (!empty($rev['title'])): ?>
                                                            <div class="fw-semibold mt-1"><?= e($rev['title']) ?></div>
                                                        <?php endif; ?>
                                                        <p class="text-secondary mt-1 mb-1"><?= e($rev['comment']) ?></p>
                                                        <small class="text-muted"><?= date('M d, Y', strtotime($rev['created_at'])) ?></small>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Review Form -->
                                        <div class="card p-4 bg-light border">
                                            <h5 class="fw-bold mb-3">Write a Customer Review</h5>
                                            <form action="#reviewsPane" method="POST">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="submit_review">

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-semibold">Your Name*</label>
                                                        <input type="text" name="author_name" class="form-control" required placeholder="e.g. Ramesh Kumar">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-semibold">Your Email (Optional)</label>
                                                        <input type="email" name="author_email" class="form-control" placeholder="ramesh@example.com">
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label fw-semibold">Rating</label>
                                                        <select name="rating" class="form-select">
                                                            <option value="5">⭐⭐⭐⭐⭐ (5 Stars)</option>
                                                            <option value="4">⭐⭐⭐⭐ (4 Stars)</option>
                                                            <option value="3">⭐⭐⭐ (3 Stars)</option>
                                                            <option value="2">⭐⭐ (2 Stars)</option>
                                                            <option value="1">⭐ (1 Star)</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-8 mb-3">
                                                        <label class="form-label fw-semibold">Review Headline</label>
                                                        <input type="text" name="title" class="form-control" placeholder="e.g. Great Finishing!">
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Review Comment*</label>
                                                    <textarea name="comment" class="form-control" rows="3" required placeholder="Tell other decorators about the quality and appearance..."></textarea>
                                                </div>

                                                <button type="submit" class="btn-default">Submit Review</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Products Single End -->

    <!-- Related Products Section Start -->
    <?php if (!empty($relatedProducts)): ?>
    <div class="our-products py-5 bg-light">
        <div class="container">
            <div class="row section-row">
                 <div class="col-xl-12">
                    <div class="section-title section-title-center">
                        <span class="section-sub-title wow fadeInUp">Explore More</span>
                        <h2 class="text-anime-style-3" data-cursor="-opaque">Similar Handicraft Products</h2>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="product-slider">
                        <div class="swiper">
                            <div class="swiper-wrapper">
                                <?php foreach ($relatedProducts as $rel): ?>
                                    <div class="swiper-slide">
                                        <div class="product-item">
                                            <div class="product-item-header">
                                                <div class="product-item-image">
                                                    <a href="<?= BASE_URL ?>/product/<?= e($rel['slug']) ?>">
                                                        <figure>
                                                            <img src="<?= e(resolve_image_url($rel['featured_image'])) ?>" alt="<?= e($rel['name']) ?>">
                                                        </figure>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="product-item-body">
                                                <div class="product-item-content">
                                                    <h2 class="product-item-title"><a href="<?= BASE_URL ?>/product/<?= e($rel['slug']) ?>"><?= e($rel['name']) ?></a></h2>
                                                    <div class="product-item-price mt-1">
                                                        <h3><?= format_price($rel['price']) ?></h3>
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
    <?php endif; ?>
    <!-- Related Products Section End -->

<?php include('includes/footer.php') ?>
</body>
</html>