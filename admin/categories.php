<?php
$pageTitle = 'Categories Management';
require_once __DIR__ . '/includes/header.php';

global $pdo;
$error = '';

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        
        // Check if category has products
        $prodCount = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE category_id = $id")->fetchColumn();
        if ($prodCount > 0) {
            set_flash('danger', "Cannot delete category because it has $prodCount associated products. Reassign or delete products first.");
        } else {
            $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $img = $stmt->fetchColumn();

            $delStmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $delStmt->execute([$id]);

            delete_uploaded_file($img);
            log_activity('CATEGORY_DELETE', "Deleted category ID: $id");
            set_flash('success', 'Category deleted successfully.');
        }
    } else {
        set_flash('danger', 'Security verification failed.');
    }
    header('Location: ' . ADMIN_URL . '/categories.php');
    exit;
}

// Handle Add / Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $catId = !empty($_POST['cat_id']) ? (int)$_POST['cat_id'] : null;
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        if (empty($slug)) {
            $slug = slugify($name);
        } else {
            $slug = slugify($slug);
        }

        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
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
            $slugCheck = $pdo->prepare("SELECT id FROM categories WHERE slug = ? " . ($catId ? "AND id != $catId" : ""));
            $slugCheck->execute([$slug]);
            if ($slugCheck->fetch()) {
                $slug = $slug . '-' . time();
            }

            // Image handling (upload or existing asset)
            $imagePath = $_POST['existing_image'] ?? 'images/ca1.jpg';
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
                if ($catId) {
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, image = ?, description = ?, is_featured = ?, sort_order = ?, status = ?, meta_title = ?, meta_description = ?, meta_keywords = ?, canonical_url = ? WHERE id = ?");
                    $stmt->execute([$name, $slug, $imagePath, $description, $isFeatured, $sortOrder, $status, $metaTitle, $metaDescription, $metaKeywords, $canonicalUrl, $catId]);
                    log_activity('CATEGORY_UPDATE', "Updated category: $name (ID: $catId)");
                    set_flash('success', 'Category updated successfully.');
                } else {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, image, description, is_featured, sort_order, status, meta_title, meta_description, meta_keywords, canonical_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $slug, $imagePath, $description, $isFeatured, $sortOrder, $status, $metaTitle, $metaDescription, $metaKeywords, $canonicalUrl]);
                    log_activity('CATEGORY_CREATE', "Added category: $name");
                    set_flash('success', 'New category created successfully.');
                }
                header('Location: ' . ADMIN_URL . '/categories.php');
                exit;
            }
        }
    }
}

// Fetch all categories with product count
$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count FROM categories c ORDER BY c.sort_order ASC, c.id ASC")->fetchAll();

// Edit Category Selection
$editCat = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$editId]);
    $editCat = $stmt->fetch();
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

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<div class="row">
    <!-- Form Column -->
    <div class="col-lg-5 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <span><i class="fa-solid <?= $editCat ? 'fa-pen-to-square text-warning' : 'fa-plus text-primary' ?> me-2"></i><?= $editCat ? 'Edit Category' : 'Add New Category' ?></span>
                <?php if ($editCat): ?>
                    <a href="<?= ADMIN_URL ?>/categories.php" class="btn btn-sm btn-outline-secondary">Cancel</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <?php if ($editCat): ?>
                        <input type="hidden" name="cat_id" value="<?= $editCat['id'] ?>">
                        <input type="hidden" name="existing_image" value="<?= e($editCat['image']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name*</label>
                        <input type="text" name="name" class="form-control" data-slug-source="true" value="<?= e($editCat['name'] ?? '') ?>" required placeholder="e.g. RDI Hanging">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">SEO Slug URL</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light small">/category/</span>
                            <input type="text" name="slug" class="form-control" data-slug-target="true" value="<?= e($editCat['slug'] ?? '') ?>" placeholder="rdi-hanging">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief category description..."><?= e($editCat['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Category Image -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Image</label>
                        <div class="mb-2">
                            <img src="<?= e(resolve_image_url($editCat['image'] ?? 'images/ca1.jpg')) ?>" id="catPreview" class="table-img-thumb" style="width: 80px; height: 80px;">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small text-muted">Upload Custom Image:</label>
                            <input type="file" name="image_file" class="form-control" data-preview-target="catPreview" accept="image/*">
                        </div>

                        <div>
                            <label class="form-label small text-muted">Or Choose Existing Asset Image:</label>
                            <select name="preset_image" class="form-select form-select-sm">
                                <option value="">-- Choose Existing Asset --</option>
                                <?php foreach ($presetImages as $path => $label): ?>
                                    <option value="<?= $path ?>" <?= (($editCat['image'] ?? '') === $path) ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Display Order</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= e($editCat['sort_order'] ?? 1) ?>" min="0">
                        </div>
                        <div class="col-6 mb-3 pt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="catFeatured" <?= (!empty($editCat['is_featured'])) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="catFeatured">Featured</label>
                            </div>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="status" id="catStatus" <?= (!isset($editCat) || $editCat['status'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="catStatus">Active</label>
                            </div>
                        </div>
                    </div>

                    <!-- Yoast SEO Box -->
                    <div class="yoast-card">
                        <h6 class="fw-bold mb-2 text-primary"><i class="fa-solid fa-chart-line me-1"></i> Yoast SEO Settings</h6>
                        <small class="text-muted d-block mb-3">Optimize category for Google and social sharing</small>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">SEO Meta Title</label>
                            <input type="text" name="meta_title" id="meta_title" class="form-control form-control-sm" value="<?= e($editCat['meta_title'] ?? '') ?>" placeholder="e.g. RDI Hanging Decor | A2Z Handicraft">
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted" id="metaTitleCount">0 / 60</small>
                            </div>
                            <div class="seo-counter-bar"><div class="seo-counter-fill" id="metaTitleBar"></div></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">SEO Meta Description</label>
                            <textarea name="meta_description" id="meta_description" class="form-control form-control-sm" rows="2" placeholder="Write an engaging 120-160 character description..."><?= e($editCat['meta_description'] ?? '') ?></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted" id="metaDescCount">0 / 160</small>
                            </div>
                            <div class="seo-counter-bar"><div class="seo-counter-fill" id="metaDescBar"></div></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Meta Keywords</label>
                            <input type="text" name="meta_keywords" class="form-control form-control-sm" value="<?= e($editCat['meta_keywords'] ?? '') ?>" placeholder="e.g. rdi hanging, wedding decor, event">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Canonical URL (Optional)</label>
                            <input type="text" name="canonical_url" class="form-control form-control-sm" value="<?= e($editCat['canonical_url'] ?? '') ?>" placeholder="<?= BASE_URL ?>/category/...">
                        </div>

                        <!-- Snippet Preview -->
                        <div class="yoast-preview">
                            <div class="yoast-preview-url">
                                <i class="fa-solid fa-globe text-muted"></i>
                                <?= BASE_URL ?>/category/<span id="yoastPreviewUrl"><?= e($editCat['slug'] ?? 'category-slug') ?></span>
                            </div>
                            <div class="yoast-preview-title" id="yoastPreviewTitle"><?= e($editCat['meta_title'] ?? ($editCat['name'] ?? 'Category Title - A2Z Handicraft')) ?></div>
                            <div class="yoast-preview-desc" id="yoastPreviewDesc"><?= e($editCat['meta_description'] ?? 'Explore our premium handcrafted categories.') ?></div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2 mt-3">
                        <i class="fa-solid fa-floppy-disk me-1"></i> <?= $editCat ? 'Update Category' : 'Save Category' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Table Column -->
    <div class="col-lg-7 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <span><i class="fa-solid fa-layer-group text-primary me-2"></i>All Categories (<?= count($categories) ?>)</span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Category</th>
                            <th>Products</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No categories available. Add one using the form.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td>
                                        <img src="<?= e(resolve_image_url($cat['image'])) ?>" class="table-img-thumb" alt="<?= e($cat['name']) ?>">
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= e($cat['name']) ?></div>
                                        <small class="text-muted"><code>/category/<?= e($cat['slug']) ?></code></small>
                                        <?php if ($cat['is_featured']): ?>
                                            <span class="badge bg-warning text-dark ms-1">Featured</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= $cat['product_count'] ?> products</span>
                                    </td>
                                    <td><?= $cat['sort_order'] ?></td>
                                    <td>
                                        <?php if ($cat['status'] == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= ADMIN_URL ?>/categories.php?edit=<?= $cat['id'] ?>" class="btn btn-light" title="Edit">
                                                <i class="fa-solid fa-pen text-primary"></i>
                                            </a>
                                            <a href="<?= ADMIN_URL ?>/categories.php?action=delete&id=<?= $cat['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-light" onclick="return confirmDelete('Are you sure you want to delete this category?');" title="Delete">
                                                <i class="fa-solid fa-trash text-danger"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
