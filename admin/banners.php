<?php
$pageTitle = 'Home Banners Management';
require_once __DIR__ . '/includes/header.php';

global $pdo;
$error = '';

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT image FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $img = $stmt->fetchColumn();

        $delStmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
        $delStmt->execute([$id]);

        delete_uploaded_file($img);
        log_activity('BANNER_DELETE', "Deleted banner ID: $id");
        set_flash('success', 'Banner deleted successfully.');
    } else {
        set_flash('danger', 'Security verification failed.');
    }
    header('Location: ' . ADMIN_URL . '/banners.php');
    exit;
}

// Handle Add / Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $bannerId = !empty($_POST['banner_id']) ? (int)$_POST['banner_id'] : null;
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $tagline = trim($_POST['tagline'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $buttonText = trim($_POST['button_text'] ?? 'Show All Products');
        $buttonLink = trim($_POST['button_link'] ?? 'categories.php');
        $ratingText = trim($_POST['rating_text'] ?? '4.9/5 Review');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        if (empty($title)) {
            $error = 'Banner title is required.';
        } else {
            // Determine image
            $imagePath = $_POST['existing_image'] ?? 'images/banner.png';
            if (!empty($_POST['preset_image'])) {
                $imagePath = $_POST['preset_image'];
            }
            if (!empty($_FILES['image_file']['name'])) {
                $upload = upload_media($_FILES['image_file'], 'banners');
                if ($upload['success']) {
                    $imagePath = $upload['path'];
                } else {
                    $error = 'Banner image upload failed: ' . $upload['error'];
                }
            }

            if (empty($error)) {
                if ($bannerId) {
                    $stmt = $pdo->prepare("UPDATE banners SET title = ?, subtitle = ?, tagline = ?, description = ?, image = ?, button_text = ?, button_link = ?, rating_text = ?, sort_order = ?, status = ? WHERE id = ?");
                    $stmt->execute([$title, $subtitle, $tagline, $description, $imagePath, $buttonText, $buttonLink, $ratingText, $sortOrder, $status, $bannerId]);
                    log_activity('BANNER_UPDATE', "Updated banner: $title (ID: $bannerId)");
                    set_flash('success', 'Banner updated successfully.');
                } else {
                    $stmt = $pdo->prepare("INSERT INTO banners (title, subtitle, tagline, description, image, button_text, button_link, rating_text, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $subtitle, $tagline, $description, $imagePath, $buttonText, $buttonLink, $ratingText, $sortOrder, $status]);
                    log_activity('BANNER_CREATE', "Added new banner: $title");
                    set_flash('success', 'New banner created successfully.');
                }
                header('Location: ' . ADMIN_URL . '/banners.php');
                exit;
            }
        }
    }
}

// Fetch Banners
$banners = $pdo->query("SELECT * FROM banners ORDER BY sort_order ASC, id ASC")->fetchAll();

// Edit Banner Selection
$editBanner = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
    $stmt->execute([$editId]);
    $editBanner = $stmt->fetch();
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
                <span><i class="fa-solid <?= $editBanner ? 'fa-pen-to-square text-warning' : 'fa-plus text-primary' ?> me-2"></i><?= $editBanner ? 'Edit Banner' : 'Add New Banner' ?></span>
                <?php if ($editBanner): ?>
                    <a href="<?= ADMIN_URL ?>/banners.php" class="btn btn-sm btn-outline-secondary">Cancel</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <?php if ($editBanner): ?>
                        <input type="hidden" name="banner_id" value="<?= $editBanner['id'] ?>">
                        <input type="hidden" name="existing_image" value="<?= e($editBanner['image']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Banner Heading / Title*</label>
                        <input type="text" name="title" class="form-control" value="<?= e($editBanner['title'] ?? '') ?>" required placeholder="e.g. Elevate Your Style With Handmade Fashion">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subtitle / Brand Badge</label>
                        <input type="text" name="subtitle" class="form-control" value="<?= e($editBanner['subtitle'] ?? 'A2Z HANDICRAFT') ?>" placeholder="e.g. A2Z HANDICRAFT">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Key Highlights / Tagline</label>
                        <input type="text" name="tagline" class="form-control" value="<?= e($editBanner['tagline'] ?? 'Premium Quality Materials | Affordable Collection') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description Paragraph</label>
                        <textarea name="description" class="form-control" rows="3"><?= e($editBanner['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Button Text</label>
                            <input type="text" name="button_text" class="form-control" value="<?= e($editBanner['button_text'] ?? 'Show All Products') ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Button Link</label>
                            <input type="text" name="button_link" class="form-control" value="<?= e($editBanner['button_link'] ?? 'categories.php') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rating Text</label>
                        <input type="text" name="rating_text" class="form-control" value="<?= e($editBanner['rating_text'] ?? '4.9/5 Review') ?>">
                    </div>

                    <!-- Banner Image Options -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Banner Background Image</label>
                        <?php if ($editBanner): ?>
                            <div class="mb-2">
                                <img src="<?= e(resolve_image_url($editBanner['image'])) ?>" id="bannerPreview" class="rounded border" style="max-height: 90px; width: 100%; object-fit: cover;">
                            </div>
                        <?php else: ?>
                            <div class="mb-2">
                                <img src="<?= BASE_URL ?>/images/banner.png" id="bannerPreview" class="rounded border" style="max-height: 90px; width: 100%; object-fit: cover;">
                            </div>
                        <?php endif; ?>

                        <div class="mb-2">
                            <label class="form-label small text-muted">Upload New Image File:</label>
                            <input type="file" name="image_file" class="form-control" data-preview-target="bannerPreview" accept="image/*">
                        </div>

                        <div>
                            <label class="form-label small text-muted">Or Select from Existing Asset Images:</label>
                            <select name="preset_image" class="form-select form-select-sm">
                                <option value="">-- Choose Existing Asset --</option>
                                <option value="images/banner.png" <?= (($editBanner['image'] ?? '') === 'images/banner.png') ? 'selected' : '' ?>>images/banner.png (Hero Banner)</option>
                                <option value="images/testimonial-bg-image.jpg" <?= (($editBanner['image'] ?? '') === 'images/testimonial-bg-image.jpg') ? 'selected' : '' ?>>images/testimonial-bg-image.jpg</option>
                                <option value="images/about.jpeg" <?= (($editBanner['image'] ?? '') === 'images/about.jpeg') ? 'selected' : '' ?>>images/about.jpeg</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Display Order</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= e($editBanner['sort_order'] ?? 1) ?>" min="0">
                        </div>
                        <div class="col-6 mb-3 d-flex align-items-center">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="status" id="bannerStatus" <?= (!isset($editBanner) || $editBanner['status'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="bannerStatus">Active Status</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> <?= $editBanner ? 'Update Banner' : 'Create Banner' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Table Column -->
    <div class="col-lg-7 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <span><i class="fa-solid fa-images text-primary me-2"></i>Existing Home Banners (<?= count($banners) ?>)</span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Details</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($banners)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No banners found. Add one on the left.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($banners as $b): ?>
                                <tr>
                                    <td>
                                        <img src="<?= e(resolve_image_url($b['image'])) ?>" class="rounded border" style="width: 80px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= e($b['title']) ?></div>
                                        <small class="text-muted"><?= e($b['subtitle']) ?> | Link: <em><?= e($b['button_link']) ?></em></small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= $b['sort_order'] ?></span></td>
                                    <td>
                                        <?php if ($b['status'] == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= ADMIN_URL ?>/banners.php?edit=<?= $b['id'] ?>" class="btn btn-light" title="Edit">
                                                <i class="fa-solid fa-pen text-primary"></i>
                                            </a>
                                            <a href="<?= ADMIN_URL ?>/banners.php?action=delete&id=<?= $b['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-light" onclick="return confirmDelete('Delete this banner?');" title="Delete">
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
