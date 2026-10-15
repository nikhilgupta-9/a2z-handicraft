<?php
$pageTitle = 'Edit Product';
require_once __DIR__ . '/includes/header.php';

global $pdo;

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('danger', 'Product not found.');
    header('Location: ' . ADMIN_URL . '/products.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        if (empty($slug)) {
            $slug = slugify($name);
        } else {
            $slug = slugify($slug);
        }

        $sku = trim($_POST['sku'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $subcategoryId = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
        $price = (float)($_POST['price'] ?? 0);
        $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
        $sizesPricing = trim($_POST['sizes_pricing'] ?? '');
        $shortDescription = trim($_POST['short_description'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $inStock = isset($_POST['in_stock']) ? 1 : 0;
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;
        $status = isset($_POST['status']) ? 1 : 0;

        // Yoast SEO
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $metaKeywords = trim($_POST['meta_keywords'] ?? '');
        $canonicalUrl = trim($_POST['canonical_url'] ?? '');

        if (empty($name) || empty($categoryId)) {
            $error = 'Product name and Category are required.';
        } else {
            // Slug uniqueness check
            $slugCheck = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
            $slugCheck->execute([$slug, $id]);
            if ($slugCheck->fetch()) {
                $slug = $slug . '-' . time();
            }

            // Featured Image
            $featuredImage = $product['featured_image'];
            if (!empty($_POST['preset_image'])) {
                $featuredImage = $_POST['preset_image'];
            }
            if (!empty($_FILES['featured_image']['name'])) {
                $upload = upload_media($_FILES['featured_image'], 'products');
                if ($upload['success']) {
                    $featuredImage = $upload['path'];
                } else {
                    $error = 'Featured image upload failed: ' . $upload['error'];
                }
            }

            // Gallery Images
            $existingGallery = json_decode($product['gallery_images'] ?? '[]', true) ?: [];
            if (!empty($_POST['preset_gallery'])) {
                $existingGallery = array_unique(array_merge($existingGallery, (array)$_POST['preset_gallery']));
            }
            if (!empty($_FILES['gallery_files']['name'][0])) {
                $fileCount = count($_FILES['gallery_files']['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    if (!empty($_FILES['gallery_files']['name'][$i])) {
                        $singleFile = [
                            'name' => $_FILES['gallery_files']['name'][$i],
                            'type' => $_FILES['gallery_files']['type'][$i],
                            'tmp_name' => $_FILES['gallery_files']['tmp_name'][$i],
                            'error' => $_FILES['gallery_files']['error'][$i],
                            'size' => $_FILES['gallery_files']['size'][$i]
                        ];
                        $uploadG = upload_media($singleFile, 'products');
                        if ($uploadG['success']) {
                            $existingGallery[] = $uploadG['path'];
                        }
                    }
                }
            }
            $galleryJson = !empty($existingGallery) ? json_encode(array_values($existingGallery)) : null;

            if (empty($error)) {
                $stmt = $pdo->prepare("UPDATE products SET 
                    category_id = ?, subcategory_id = ?, name = ?, slug = ?, sku = ?, price = ?, sale_price = ?,
                    sizes_pricing = ?, short_description = ?, description = ?, featured_image = ?, gallery_images = ?,
                    is_featured = ?, is_bestseller = ?, in_stock = ?, status = ?,
                    meta_title = ?, meta_description = ?, meta_keywords = ?, canonical_url = ? 
                    WHERE id = ?");
                
                $stmt->execute([
                    $categoryId, $subcategoryId, $name, $slug, $sku, $price, $salePrice,
                    $sizesPricing, $shortDescription, $description, $featuredImage, $galleryJson,
                    $isFeatured, $isBestseller, $inStock, $status,
                    $metaTitle, $metaDescription, $metaKeywords, $canonicalUrl,
                    $id
                ]);

                log_activity('PRODUCT_UPDATE', "Updated product: $name (ID: $id)");
                set_flash('success', 'Product updated successfully!');
                header('Location: ' . ADMIN_URL . '/products.php');
                exit;
            }
        }
    }
}

// Fetch categories & subcategories for dropdowns
$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name ASC")->fetchAll();
$subcategories = $pdo->query("SELECT id, category_id, name FROM subcategories WHERE status = 1 ORDER BY name ASC")->fetchAll();

$assetImages = [
    'images/ca1.jpg' => 'ca1.jpg',
    'images/ca2.webp' => 'ca2.webp',
    'images/ca3.jpeg' => 'ca3.jpeg',
    'images/ca4.webp' => 'ca4.webp',
    'images/ca5.webp' => 'ca5.webp',
    'images/ca6.webp' => 'ca6.webp',
    'images/ca7.webp' => 'ca7.webp',
    'images/ca8.jpg' => 'ca8.jpg',
    'images/p1.jpeg' => 'p1.jpeg',
    'images/p2.jpeg' => 'p2.jpeg',
    'images/p3.jpeg' => 'p3.jpeg',
    'images/p4.jpeg' => 'p4.jpeg',
    'images/p5.jpg' => 'p5.jpg'
];

$galleryImages = json_decode($product['gallery_images'] ?? '[]', true) ?: [];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="m-0 fw-bold"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Product: <?= e($product['name']) ?></h4>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/product/<?= e($product['slug']) ?>" target="_blank" class="btn btn-outline-info btn-sm">
            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Preview Live
        </a>
        <a href="<?= ADMIN_URL ?>/products.php" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Products
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-circle-info text-primary me-2"></i>Product Information</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Product Name*</label>
                        <input type="text" name="name" class="form-control" data-slug-source="true" value="<?= e($product['name']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">SEO Slug URL</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light small">/product/</span>
                                <input type="text" name="slug" class="form-control" data-slug-target="true" value="<?= e($product['slug']) ?>">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">SKU / Item Code</label>
                            <input type="text" name="sku" class="form-control" value="<?= e($product['sku'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Regular Price (₹)*</label>
                            <input type="number" step="0.01" name="price" class="form-control" value="<?= e($product['price']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Discounted / Old Price (₹)</label>
                            <input type="number" step="0.01" name="sale_price" class="form-control" value="<?= e($product['sale_price'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Available Sizes & Pricing Specifications</label>
                        <textarea name="sizes_pricing" class="form-control" rows="5"><?= e($product['sizes_pricing'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Short Summary / Tagline</label>
                        <input type="text" name="short_description" class="form-control" value="<?= e($product['short_description'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Detailed Description</label>
                        <textarea name="description" class="form-control" rows="6"><?= e($product['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Yoast SEO Card -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold text-primary"><i class="fa-solid fa-chart-line me-2"></i>Yoast SEO Optimization</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">SEO Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" class="form-control" value="<?= e($product['meta_title'] ?? '') ?>">
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted" id="metaTitleCount">0 / 60 characters</small>
                        </div>
                        <div class="seo-counter-bar"><div class="seo-counter-fill" id="metaTitleBar"></div></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">SEO Meta Description</label>
                        <textarea name="meta_description" id="meta_description" class="form-control" rows="3"><?= e($product['meta_description'] ?? '') ?></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted" id="metaDescCount">0 / 160 characters</small>
                        </div>
                        <div class="seo-counter-bar"><div class="seo-counter-fill" id="metaDescBar"></div></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Focus Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="<?= e($product['meta_keywords'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Canonical URL</label>
                        <input type="text" name="canonical_url" class="form-control" value="<?= e($product['canonical_url'] ?? '') ?>">
                    </div>

                    <!-- Google Snippet Preview -->
                    <div class="yoast-preview">
                        <div class="yoast-preview-url">
                            <i class="fa-solid fa-globe text-muted"></i>
                            <?= BASE_URL ?>/product/<span id="yoastPreviewUrl"><?= e($product['slug']) ?></span>
                        </div>
                        <div class="yoast-preview-title" id="yoastPreviewTitle"><?= e($product['meta_title'] ?? ($product['name'] . ' - A2Z Handicraft')) ?></div>
                        <div class="yoast-preview-desc" id="yoastPreviewDesc"><?= e($product['meta_description'] ?? 'Search engine preview of this product description.') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar Controls -->
        <div class="col-lg-4">
            <!-- Classification -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-layer-group text-secondary me-2"></i>Category & Stock</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category*</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subcategory (Optional)</label>
                        <select name="subcategory_id" class="form-select">
                            <option value="">-- No Subcategory --</option>
                            <?php foreach ($subcategories as $sub): ?>
                                <option value="<?= $sub['id'] ?>" <?= ($product['subcategory_id'] == $sub['id']) ? 'selected' : '' ?>><?= e($sub['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <hr>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="in_stock" id="inStock" <?= ($product['in_stock']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="inStock">In Stock</label>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" <?= ($product['is_featured']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="isFeatured">Featured Product</label>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_bestseller" id="isBestseller" <?= ($product['is_bestseller']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="isBestseller">Best Seller</label>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="status" id="status" <?= ($product['status']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="status">Publish Status (Active)</label>
                    </div>
                </div>
            </div>

            <!-- Image Media -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-camera text-secondary me-2"></i>Featured & Gallery Images</span>
                </div>
                <div class="card-body">
                    <label class="form-label fw-semibold">Current Main Image</label>
                    <div class="mb-2">
                        <img src="<?= e(resolve_image_url($product['featured_image'])) ?>" id="productImgPreview" class="rounded border" style="max-height: 120px; width: 100%; object-fit: cover;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Upload Replacement Image:</label>
                        <input type="file" name="featured_image" class="form-control" data-preview-target="productImgPreview" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Or Pick Asset Image:</label>
                        <select name="preset_image" class="form-select form-select-sm">
                            <option value="">-- Keep Existing --</option>
                            <?php foreach ($assetImages as $path => $label): ?>
                                <option value="<?= $path ?>" <?= ($product['featured_image'] === $path) ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <hr>

                    <label class="form-label fw-semibold">Gallery Images (<?= count($galleryImages) ?> attached)</label>
                    <?php if (!empty($galleryImages)): ?>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <?php foreach ($galleryImages as $gImg): ?>
                                <img src="<?= e(resolve_image_url($gImg)) ?>" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <input type="file" name="gallery_files[]" class="form-control form-control-sm mb-2" multiple accept="image/*">
                    <small class="text-muted d-block mb-2">Upload more gallery images</small>

                    <label class="form-label small text-muted">Or Select Additional Presets:</label>
                    <div class="row g-2">
                        <?php foreach (['images/ca1.jpg', 'images/ca2.webp', 'images/ca3.jpeg', 'images/p1.jpeg', 'images/p2.jpeg'] as $imgPreset): ?>
                            <div class="col-4">
                                <label class="border rounded p-1 d-block text-center cursor-pointer bg-light">
                                    <input type="checkbox" name="preset_gallery[]" value="<?= $imgPreset ?>" <?= in_array($imgPreset, $galleryImages) ? 'checked' : '' ?>>
                                    <img src="<?= BASE_URL ?>/<?= $imgPreset ?>" class="img-fluid rounded mt-1" style="height: 40px; object-fit: cover;">
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-a2z btn-lg w-100 py-3 shadow">
                <i class="fa-solid fa-floppy-disk me-2"></i> Update Product
            </button>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
