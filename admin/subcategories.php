<?php
$pageTitle = 'Subcategories Management';
require_once __DIR__ . '/includes/header.php';

global $pdo;
$error = '';

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $delStmt = $pdo->prepare("DELETE FROM subcategories WHERE id = ?");
        $delStmt->execute([$id]);

        log_activity('SUBCATEGORY_DELETE', "Deleted subcategory ID: $id");
        set_flash('success', 'Subcategory deleted successfully.');
    } else {
        set_flash('danger', 'Security verification failed.');
    }
    header('Location: ' . ADMIN_URL . '/subcategories.php');
    exit;
}

// Handle Add / Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $subId = !empty($_POST['sub_id']) ? (int)$_POST['sub_id'] : null;
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

        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $metaKeywords = trim($_POST['meta_keywords'] ?? '');
        $canonicalUrl = trim($_POST['canonical_url'] ?? '');

        if (empty($categoryId) || empty($name)) {
            $error = 'Parent Category and Subcategory Name are required.';
        } else {
            // Check slug uniqueness
            $slugCheck = $pdo->prepare("SELECT id FROM subcategories WHERE slug = ? " . ($subId ? "AND id != $subId" : ""));
            $slugCheck->execute([$slug]);
            if ($slugCheck->fetch()) {
                $slug = $slug . '-' . time();
            }

            $imagePath = $_POST['existing_image'] ?? null;
            if (!empty($_POST['preset_image'])) {
                $imagePath = $_POST['preset_image'];
            }
            if (!empty($_FILES['image_file']['name'])) {
                $upload = upload_media($_FILES['image_file'], 'subcategories');
                if ($upload['success']) {
                    $imagePath = $upload['path'];
                }
            }

            if (empty($error)) {
                if ($subId) {
                    $stmt = $pdo->prepare("UPDATE subcategories SET category_id = ?, name = ?, slug = ?, image = ?, description = ?, sort_order = ?, status = ?, meta_title = ?, meta_description = ?, meta_keywords = ?, canonical_url = ? WHERE id = ?");
                    $stmt->execute([$categoryId, $name, $slug, $imagePath, $description, $sortOrder, $status, $metaTitle, $metaDescription, $metaKeywords, $canonicalUrl, $subId]);
                    log_activity('SUBCATEGORY_UPDATE', "Updated subcategory: $name (ID: $subId)");
                    set_flash('success', 'Subcategory updated successfully.');
                } else {
                    $stmt = $pdo->prepare("INSERT INTO subcategories (category_id, name, slug, image, description, sort_order, status, meta_title, meta_description, meta_keywords, canonical_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$categoryId, $name, $slug, $imagePath, $description, $sortOrder, $status, $metaTitle, $metaDescription, $metaKeywords, $canonicalUrl]);
                    log_activity('SUBCATEGORY_CREATE', "Added subcategory: $name");
                    set_flash('success', 'New subcategory created successfully.');
                }
                header('Location: ' . ADMIN_URL . '/subcategories.php');
                exit;
            }
        }
    }
}

// Fetch all categories for select
$allCategories = $pdo->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name ASC")->fetchAll();

// Fetch all subcategories
$subcategories = $pdo->query("SELECT s.*, c.name as category_name, (SELECT COUNT(*) FROM products p WHERE p.subcategory_id = s.id) as product_count FROM subcategories s LEFT JOIN categories c ON s.category_id = c.id ORDER BY c.name ASC, s.sort_order ASC")->fetchAll();

// Edit Subcategory Selection
$editSub = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM subcategories WHERE id = ?");
    $stmt->execute([$editId]);
    $editSub = $stmt->fetch();
}
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<div class="row">
    <!-- Form Column -->
    <div class="col-lg-5 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <span><i class="fa-solid <?= $editSub ? 'fa-pen-to-square text-warning' : 'fa-plus text-primary' ?> me-2"></i><?= $editSub ? 'Edit Subcategory' : 'Add Subcategory' ?></span>
                <?php if ($editSub): ?>
                    <a href="<?= ADMIN_URL ?>/subcategories.php" class="btn btn-sm btn-outline-secondary">Cancel</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <?php if ($editSub): ?>
                        <input type="hidden" name="sub_id" value="<?= $editSub['id'] ?>">
                        <input type="hidden" name="existing_image" value="<?= e($editSub['image']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Parent Category*</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select Parent Category --</option>
                            <?php foreach ($allCategories as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= (($editSub['category_id'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subcategory Name*</label>
                        <input type="text" name="name" class="form-control" data-slug-source="true" value="<?= e($editSub['name'] ?? '') ?>" required placeholder="e.g. Kite Hanging">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">SEO Slug URL</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light small">/subcategory/</span>
                            <input type="text" name="slug" class="form-control" data-slug-target="true" value="<?= e($editSub['slug'] ?? '') ?>" placeholder="kite-hanging">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?= e($editSub['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Display Order</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= e($editSub['sort_order'] ?? 1) ?>" min="0">
                        </div>
                        <div class="col-6 mb-3 pt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status" id="subStatus" <?= (!isset($editSub) || $editSub['status'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="subStatus">Active Status</label>
                            </div>
                        </div>
                    </div>

                    <!-- Yoast SEO Box -->
                    <div class="yoast-card">
                        <h6 class="fw-bold mb-2 text-primary"><i class="fa-solid fa-chart-line me-1"></i> Yoast SEO Settings</h6>
                        
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Meta Title</label>
                            <input type="text" name="meta_title" id="meta_title" class="form-control form-control-sm" value="<?= e($editSub['meta_title'] ?? '') ?>" placeholder="Subcategory Meta Title">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Meta Description</label>
                            <textarea name="meta_description" id="meta_description" class="form-control form-control-sm" rows="2" placeholder="Meta description..."><?= e($editSub['meta_description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Meta Keywords</label>
                            <input type="text" name="meta_keywords" class="form-control form-control-sm" value="<?= e($editSub['meta_keywords'] ?? '') ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2 mt-3">
                        <i class="fa-solid fa-floppy-disk me-1"></i> <?= $editSub ? 'Update Subcategory' : 'Save Subcategory' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Table Column -->
    <div class="col-lg-7 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <span><i class="fa-solid fa-tags text-primary me-2"></i>Subcategories (<?= count($subcategories) ?>)</span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Subcategory</th>
                            <th>Parent Category</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subcategories)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No subcategories defined yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($subcategories as $s): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= e($s['name']) ?></div>
                                        <small class="text-muted"><code>/subcategory/<?= e($s['slug']) ?></code></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= e($s['category_name'] ?? 'None') ?></span>
                                    </td>
                                    <td><?= $s['product_count'] ?> products</td>
                                    <td>
                                        <?php if ($s['status'] == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= ADMIN_URL ?>/subcategories.php?edit=<?= $s['id'] ?>" class="btn btn-light" title="Edit">
                                                <i class="fa-solid fa-pen text-primary"></i>
                                            </a>
                                            <a href="<?= ADMIN_URL ?>/subcategories.php?action=delete&id=<?= $s['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-light" onclick="return confirmDelete('Delete this subcategory?');" title="Delete">
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
