<?php
$pageTitle = 'Edit Subcategory';
require_once __DIR__ . '/includes/header.php';

global $pdo;

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM subcategories WHERE id = ?");
$stmt->execute([$id]);
$subcategory = $stmt->fetch();

if (!$subcategory) {
    set_flash('danger', 'Subcategory not found.');
    header('Location: ' . ADMIN_URL . '/subcategories.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        if (empty($slug)) {
            $slug = slugify($name);
        } else {
            $slug = slugify($slug);
        }

        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        // Yoast SEO Fields
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $metaKeywords = trim($_POST['meta_keywords'] ?? '');
        $canonicalUrl = trim($_POST['canonical_url'] ?? '');

        if (empty($categoryId) || empty($name)) {
            $error = 'Parent Category and Subcategory Name are required.';
        } else {
            // Check slug uniqueness
            $slugCheck = $pdo->prepare("SELECT id FROM subcategories WHERE slug = ? AND id != ?");
            $slugCheck->execute([$slug, $id]);
            if ($slugCheck->fetch()) {
                $slug = $slug . '-' . time();
            }

            // Image handling (upload or existing asset)
            $imagePath = $subcategory['image'];
            if (!empty($_POST['preset_image'])) {
                $imagePath = $_POST['preset_image'];
            }
            if (!empty($_FILES['image_file']['name'])) {
                $upload = upload_media($_FILES['image_file'], 'subcategories');
                if ($upload['success']) {
                    $imagePath = $upload['path'];
                } else {
                    $error = 'Subcategory image upload failed: ' . $upload['error'];
                }
            }

            if (empty($error)) {
                $stmt = $pdo->prepare("UPDATE subcategories SET 
                    category_id = ?, name = ?, slug = ?, image = ?, description = ?, 
                    sort_order = ?, status = ?, meta_title = ?, meta_description = ?, 
                    meta_keywords = ?, canonical_url = ? 
                    WHERE id = ?");
                $stmt->execute([$categoryId, $name, $slug, $imagePath, $description, $sortOrder, $status, $metaTitle, $metaDescription, $metaKeywords, $canonicalUrl, $id]);

                log_activity('SUBCATEGORY_UPDATE', "Updated subcategory: $name (ID: $id)");
                set_flash('success', 'Subcategory updated successfully.');
                header('Location: ' . ADMIN_URL . '/subcategories.php');
                exit;
            }
        }
    }
}

// Fetch categories for parent select dropdown
$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name ASC")->fetchAll();

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
        <h4 class="m-0 fw-bold"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Subcategory: <?= e($subcategory['name']) ?></h4>
        <small class="text-muted">Modify subcategory details, parent association, and SEO metadata</small>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/subcategory/<?= e($subcategory['slug']) ?>" target="_blank" class="btn btn-outline-info btn-sm">
            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Preview Live
        </a>
        <a href="<?= ADMIN_URL ?>/subcategories.php" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Subcategories
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
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-circle-info text-primary me-2"></i>Subcategory Details</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Parent Category*</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select Parent Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($subcategory['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subcategory Name*</label>
                        <input type="text" name="name" class="form-control" data-slug-source="true" value="<?= e($subcategory['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">SEO Slug URL</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light small"><?= BASE_URL ?>/subcategory/</span>
                            <input type="text" name="slug" class="form-control" data-slug-target="true" value="<?= e($subcategory['slug']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subcategory Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= e($subcategory['description'] ?? '') ?></textarea>
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
                        <input type="text" name="meta_title" id="meta_title" class="form-control" value="<?= e($subcategory['meta_title'] ?? '') ?>">
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted" id="metaTitleCount">0 / 60 characters</small>
                        </div>
                        <div class="seo-counter-bar"><div class="seo-counter-fill" id="metaTitleBar"></div></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">SEO Meta Description</label>
                        <textarea name="meta_description" id="meta_description" class="form-control" rows="3"><?= e($subcategory['meta_description'] ?? '') ?></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted" id="metaDescCount">0 / 160 characters</small>
                        </div>
                        <div class="seo-counter-bar"><div class="seo-counter-fill" id="metaDescBar"></div></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Focus Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="<?= e($subcategory['meta_keywords'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Canonical URL</label>
                        <input type="text" name="canonical_url" class="form-control" value="<?= e($subcategory['canonical_url'] ?? '') ?>">
                    </div>

                    <!-- Live Google Search Snippet Preview -->
                    <div class="yoast-preview mt-3">
                        <div class="yoast-preview-url">
                            <i class="fa-solid fa-globe text-muted"></i>
                            <?= BASE_URL ?>/subcategory/<span id="yoastPreviewUrl"><?= e($subcategory['slug']) ?></span>
                        </div>
                        <div class="yoast-preview-title" id="yoastPreviewTitle"><?= e($subcategory['meta_title'] ?? ($subcategory['name'] . ' - A2Z Handicraft')) ?></div>
                        <div class="yoast-preview-desc" id="yoastPreviewDesc"><?= e($subcategory['meta_description'] ?? 'Explore subcategories.') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Options Column -->
        <div class="col-lg-4">
            <!-- Image Card -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-image text-secondary me-2"></i>Subcategory Image</span>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        <img src="<?= e(resolve_image_url($subcategory['image'] ?? 'images/ca1.jpg')) ?>" id="subPreview" class="rounded border shadow-sm w-100" style="max-height: 180px; object-fit: cover;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Upload Replacement Image:</label>
                        <input type="file" name="image_file" class="form-control" data-preview-target="subPreview" accept="image/*">
                    </div>

                    <div>
                        <label class="form-label small text-muted">Or Pick Existing Asset:</label>
                        <select name="preset_image" class="form-select form-select-sm">
                            <option value="">-- Keep Current --</option>
                            <?php foreach ($presetImages as $path => $label): ?>
                                <option value="<?= $path ?>" <?= ($subcategory['image'] === $path) ? 'selected' : '' ?>><?= $label ?></option>
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
                        <label class="form-label fw-semibold">Display Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= e($subcategory['sort_order']) ?>" min="0">
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="status" id="subStatus" <?= ($subcategory['status'] == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="subStatus">Active / Published</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-a2z btn-lg w-100 py-3 shadow">
                <i class="fa-solid fa-floppy-disk me-2"></i> Update Subcategory
            </button>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
