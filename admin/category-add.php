<?php
$pageTitle = 'Add New Category';
require_once __DIR__ . '/includes/header.php';

global $pdo;
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

        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 1);
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $status = isset($_POST['status']) ? 1 : 0;

        // Yoast SEO Fields
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $metaKeywords = trim($_POST['meta_keywords'] ?? '');
        $canonicalUrl = trim($_POST['canonical_url'] ?? '');

        if (empty($name)) {
            $error = 'Category name is required.';
        } else {
            // Check slug uniqueness
            $slugCheck = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
            $slugCheck->execute([$slug]);
            if ($slugCheck->fetch()) {
                $slug = $slug . '-' . time();
            }

            // Image handling (upload or existing asset)
            $imagePath = 'images/ca1.jpg';
            if (!empty($_POST['preset_image'])) {
                $imagePath = $_POST['preset_image'];
            }
            if (!empty($_FILES['image_file']['name'])) {
                $upload = upload_media($_FILES['image_file'], 'categories');
                if ($upload['success']) {
                    $imagePath = $upload['path'];
                } else {
                    $error = 'Category image upload failed: ' . $upload['error'];
                }
            }

            if (empty($error)) {
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, image, description, is_featured, sort_order, status, meta_title, meta_description, meta_keywords, canonical_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $imagePath, $description, $isFeatured, $sortOrder, $status, $metaTitle, $metaDescription, $metaKeywords, $canonicalUrl]);
                
                $newId = $pdo->lastInsertId();
                log_activity('CATEGORY_CREATE', "Added category: $name (ID: $newId)");
                set_flash('success', 'New category created successfully.');
                header('Location: ' . ADMIN_URL . '/categories.php');
                exit;
            }
        }
    }
}

$presetImages = [
    'images/ca1.jpg' => 'ca1.jpg (RDI Hanging)',
    'images/ca2.webp' => 'ca2.webp (Iron Frame)',
    'images/ca3.jpeg' => 'ca3.jpeg (Flower Patta)',
    'images/ca4.webp' => 'ca4.webp (Dream Catcher)',
    'images/ca5.webp' => 'ca5.webp (Flower Bunch)',
    'images/ca6.webp' => 'ca6.webp (Net Line)',
    'images/ca7.webp' => 'ca7.webp (Ring Hanging)',
    'images/ca8.jpg' => 'ca8.jpg (Toran & Jumer)'
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="m-0 fw-bold"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Add New Category</h4>
        <small class="text-muted">Create a new product collection with Yoast SEO optimization</small>
    </div>
    <a href="<?= ADMIN_URL ?>/categories.php" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Categories
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-circle-info text-primary me-2"></i>Category Information</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name*</label>
                        <input type="text" name="name" class="form-control" data-slug-source="true" value="<?= e($_POST['name'] ?? '') ?>" required placeholder="e.g. Wedding Event Decor">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">SEO Slug URL</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light small"><?= BASE_URL ?>/category/</span>
                            <input type="text" name="slug" class="form-control" data-slug-target="true" value="<?= e($_POST['slug'] ?? '') ?>" placeholder="wedding-event-decor">
                        </div>
                        <small class="text-muted">Auto-generated from category name. Letters, numbers, and hyphens only.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Brief description of products included in this collection..."><?= e($_POST['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Yoast SEO Box -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <span class="fw-bold text-primary"><i class="fa-solid fa-chart-line me-2"></i>Yoast SEO Optimization</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">SEO Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" class="form-control" value="<?= e($_POST['meta_title'] ?? '') ?>" placeholder="e.g. Handmade Event Decoration Crafts | A2Z Handicraft">
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted" id="metaTitleCount">0 / 60 characters</small>
                        </div>
                        <div class="seo-counter-bar"><div class="seo-counter-fill" id="metaTitleBar"></div></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">SEO Meta Description</label>
                        <textarea name="meta_description" id="meta_description" class="form-control" rows="3" placeholder="Engaging summary for search engines (120-160 characters)..."><?= e($_POST['meta_description'] ?? '') ?></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted" id="metaDescCount">0 / 160 characters</small>
                        </div>
                        <div class="seo-counter-bar"><div class="seo-counter-fill" id="metaDescBar"></div></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Focus Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="<?= e($_POST['meta_keywords'] ?? '') ?>" placeholder="e.g. rdi hanging, wedding decor, wholesale">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Canonical URL (Optional)</label>
                        <input type="text" name="canonical_url" class="form-control" value="<?= e($_POST['canonical_url'] ?? '') ?>" placeholder="<?= BASE_URL ?>/category/...">
                    </div>

                    <!-- Live Google Search Snippet Preview -->
                    <div class="yoast-preview mt-3">
                        <div class="yoast-preview-url">
                            <i class="fa-solid fa-globe text-muted"></i>
                            <?= BASE_URL ?>/category/<span id="yoastPreviewUrl">category-slug</span>
                        </div>
                        <div class="yoast-preview-title" id="yoastPreviewTitle">Category Title - A2Z Handicraft</div>
                        <div class="yoast-preview-desc" id="yoastPreviewDesc">Search engine preview of this category description.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Options Column -->
        <div class="col-lg-4">
            <!-- Image Card -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-image text-secondary me-2"></i>Category Banner / Thumbnail</span>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        <img src="<?= BASE_URL ?>/images/ca1.jpg" id="catPreview" class="rounded border shadow-sm w-100" style="max-height: 180px; object-fit: cover;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Upload Custom Image:</label>
                        <input type="file" name="image_file" class="form-control" data-preview-target="catPreview" accept="image/*">
                    </div>

                    <div>
                        <label class="form-label small text-muted">Or Pick Existing Asset Image:</label>
                        <select name="preset_image" class="form-select form-select-sm">
                            <option value="">-- Choose Existing Asset --</option>
                            <?php foreach ($presetImages as $path => $label): ?>
                                <option value="<?= $path ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Settings Card -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-sliders text-secondary me-2"></i>Display Settings</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sort / Display Order</label>
                        <input type="number" name="sort_order" class="form-control" value="1" min="0">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="catFeatured">
                        <label class="form-check-label fw-semibold" for="catFeatured">Featured on Homepage</label>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="status" id="catStatus" checked>
                        <label class="form-check-label fw-semibold" for="catStatus">Active / Published</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-a2z btn-lg w-100 py-3 shadow">
                <i class="fa-solid fa-floppy-disk me-2"></i> Save Category
            </button>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
