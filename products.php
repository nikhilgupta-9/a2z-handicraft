<?php
require_once __DIR__ . '/config/config.php';

$categorySlug = request_string($_GET, 'category');
$subcategorySlug = request_string($_GET, 'subcategory');
$search = request_string($_GET, 'search');

$currentCategory = null;
$currentSubcategory = null;
$filters = ['status' => 1];

if (!empty($categorySlug)) {
    $currentCategory = get_category_by_slug($categorySlug);
    if ($currentCategory) {
        $filters['category_id'] = $currentCategory['id'];
    }
}

if (!empty($subcategorySlug)) {
    $currentSubcategory = get_subcategory_by_slug($subcategorySlug);
    if ($currentSubcategory) {
        $filters['subcategory_id'] = $currentSubcategory['id'];
    }
}

if (!empty($search)) {
    $filters['search'] = $search;
}

$products = get_products($filters);
$subcategories = $currentCategory ? get_subcategories($currentCategory['id']) : [];

// Determine SEO metadata
if ($currentCategory) {
    $pageHeading = $currentCategory['name'];
    $seoMeta = [
        'title' => !empty($currentCategory['meta_title']) ? $currentCategory['meta_title'] : $currentCategory['name'] . ' - A2Z Handicraft',
        'description' => !empty($currentCategory['meta_description']) ? $currentCategory['meta_description'] : $currentCategory['description'],
        'keywords' => $currentCategory['meta_keywords'] ?? '',
        'canonical' => BASE_URL . '/category/' . $currentCategory['slug'],
        'image' => $currentCategory['image']
    ];
} elseif ($currentSubcategory) {
    $pageHeading = $currentSubcategory['name'];
    $seoMeta = [
        'title' => !empty($currentSubcategory['meta_title']) ? $currentSubcategory['meta_title'] : $currentSubcategory['name'] . ' - A2Z Handicraft',
        'description' => $currentSubcategory['meta_description'] ?? '',
        'canonical' => BASE_URL . '/subcategory/' . $currentSubcategory['slug']
    ];
} elseif (!empty($search)) {
    $pageHeading = 'Search Results for: "' . e($search) . '"';
    $seoMeta = [
        'title' => 'Search: ' . e($search) . ' - A2Z Handicraft'
    ];
} else {
    $pageHeading = 'All Handicraft Products';
    $seoMeta = [
        'title' => 'All Products - Handmade Fashion & Decor | A2Z Handicraft',
        'description' => 'Explore our full catalog of wholesale event decoration products and handcrafted items.',
        'canonical' => BASE_URL . '/products.php'
    ];
}
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
                        <h1 class="text-anime-style-3" data-cursor="-opaque"><?= e($pageHeading) ?></h1>
                        <nav class="wow fadeInUp">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li> 
                                <li class="breadcrumb-item">//</li> 
                                <?php if ($currentCategory): ?>
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/categories.php">Categories</a></li>
                                    <li class="breadcrumb-item">//</li>
                                    <li class="breadcrumb-item active" aria-current="page"><?= e($currentCategory['name']) ?></li>
                                <?php elseif ($currentSubcategory): ?>
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/categories.php">Categories</a></li>
                                    <li class="breadcrumb-item">//</li>
                                    <li class="breadcrumb-item active" aria-current="page"><?= e($currentSubcategory['name']) ?></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item active" aria-current="page">Products</li>
                                <?php endif; ?>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Subcategories filter buttons if present -->
    <?php if (!empty($subcategories)): ?>
        <div class="bg-light py-3 border-bottom">
            <div class="container">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="fw-bold me-2"><i class="fa-solid fa-tags me-1"></i>Subcategories:</span>
                    <a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($currentCategory['slug']) ?>" class="btn btn-sm <?= empty($subcategorySlug) ? 'btn-primary-a2z' : 'btn-outline-secondary' ?>">All in <?= e($currentCategory['name']) ?></a>
                    <?php foreach ($subcategories as $sub): ?>
                        <a href="<?= BASE_URL ?>/products.php?subcategory=<?= urlencode($sub['slug']) ?>" class="btn btn-sm <?= ($subcategorySlug === $sub['slug']) ? 'btn-primary-a2z' : 'btn-outline-secondary' ?>">
                            <?= e($sub['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Page Products Section Start -->
    <div class="page-products py-5">
        <div class="container">
            <?php if (empty($products)): ?>
                <div class="row">
                    <div class="col-12 text-center py-5">
                        <i class="fa-solid fa-box-open fs-1 text-muted mb-3 d-block"></i>
                        <h3>No products found</h3>
                        <p class="text-muted">There are no products listed in this collection yet. Please check back soon or contact us for custom orders.</p>
                        <a href="<?= BASE_URL ?>/categories.php" class="btn-default mt-2">Browse All Categories</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($products as $prod): ?>
                        <div class="col-xl-3 col-lg-4 col-sm-6">
                            <div class="product-items h-100">
                                <div class="product-item h-100 d-flex flex-column justify-content-between">
                                    <a href="<?= BASE_URL ?>/product/<?= e($prod['slug']) ?>" class="d-block overflow-hidden" style="height: 240px;">
                                        <img src="<?= e(resolve_image_url($prod['featured_image'])) ?>" alt="<?= e($prod['name']) ?>" class="w-100 h-100" style="object-fit: cover;">
                                    </a>
                                    <div class="product-item-content mt-3">
                                        <h2 class="product-item-title">
                                            <a href="<?= BASE_URL ?>/product/<?= e($prod['slug']) ?>"><?= e($prod['name']) ?></a>
                                        </h2>
                                        <div class="product-item-price">
                                            <h3>
                                                <?= format_price($prod['price']) ?> 
                                                <?php if (!empty($prod['sale_price'])): ?>
                                                    <span><?= format_price($prod['sale_price']) ?></span>
                                                <?php endif; ?>
                                            </h3>
                                        </div>   
                                        <div class="product-single-content-btn mt-3">
                                            <a href="<?= BASE_URL ?>/product/<?= e($prod['slug']) ?>" class="btn-default w-100 text-center">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Page Products Section End -->

<?php include('includes/footer.php') ?>
</body>
</html>