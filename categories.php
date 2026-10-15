<?php
require_once __DIR__ . '/config/config.php';

$categories = get_categories();
$pageTitle = 'All Handicraft Categories | ' . get_setting('site_name', 'A2Z Handicraft');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <?= render_seo_meta([
        'title' => 'Explore All Handicraft Categories - A2Z Handicraft',
        'description' => 'Browse our complete catalog of event decor, RDI hangings, iron frames, flower bunches, dream catchers, and fancy torans.',
        'canonical' => BASE_URL . '/categories.php'
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
                        <h1 class="text-anime-style-3" data-cursor="-opaque">All Categories</h1>
                        <nav class="wow fadeInUp">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li> 
                                <li class="breadcrumb-item">//</li> 
                                <li class="breadcrumb-item active" aria-current="page">All Categories</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Categories Section Start -->
    <div class="our-best-sellers py-5">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="top-selling-item-list">
                        <?php if (empty($categories)): ?>
                            <div class="col-12 text-center py-5">
                                <h4>No categories found.</h4>
                            </div>
                        <?php else: ?>
                            <?php foreach ($categories as $idx => $cat): ?>
                                <div class="top-selling-item wow fadeInUp" data-wow-delay="<?= ($idx * 0.1) ?>s">
                                    <div class="top-selling-item-image">
                                        <a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($cat['slug']) ?>" data-cursor-text="View">
                                            <figure class="image-anime">
                                                <img src="<?= e(resolve_image_url($cat['image'])) ?>" alt="<?= e($cat['name']) ?>">
                                            </figure>
                                        </a>
                                    </div>
                                    <div class="top-selling-item-content">
                                        <h3><a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($cat['slug']) ?>"><?= e($cat['name']) ?></a></h3>
                                        <small class="text-muted"><?= $cat['product_count'] ?> Products</small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Categories Section End -->

<?php include('includes/footer.php') ?>
</body>
</html>