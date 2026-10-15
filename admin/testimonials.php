<?php
$pageTitle = 'Testimonials Management';
require_once __DIR__ . '/includes/header.php';

global $pdo;
$error = '';

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$id]);

        log_activity('TESTIMONIAL_DELETE', "Deleted testimonial ID: $id");
        set_flash('success', 'Testimonial deleted successfully.');
    } else {
        set_flash('danger', 'Security verification failed.');
    }
    header('Location: ' . ADMIN_URL . '/testimonials.php');
    exit;
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $testId = !empty($_POST['test_id']) ? (int)$_POST['test_id'] : null;
        $name = trim($_POST['name'] ?? '');
        $designation = trim($_POST['designation'] ?? '');
        $rating = (int)($_POST['rating'] ?? 5);
        $content = trim($_POST['content'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        if (empty($name) || empty($content)) {
            $error = 'Client name and testimonial content are required.';
        } else {
            $imagePath = $_POST['existing_image'] ?? 'images/icon-google.svg';
            if (!empty($_POST['preset_image'])) {
                $imagePath = $_POST['preset_image'];
            }
            if (!empty($_FILES['image_file']['name'])) {
                $upload = upload_media($_FILES['image_file'], 'testimonials');
                if ($upload['success']) {
                    $imagePath = $upload['path'];
                }
            }

            if ($testId) {
                $stmt = $pdo->prepare("UPDATE testimonials SET name = ?, designation = ?, rating = ?, content = ?, image = ?, sort_order = ?, status = ? WHERE id = ?");
                $stmt->execute([$name, $designation, $rating, $content, $imagePath, $sortOrder, $status, $testId]);
                log_activity('TESTIMONIAL_UPDATE', "Updated testimonial ID: $testId");
                set_flash('success', 'Testimonial updated successfully.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO testimonials (name, designation, rating, content, image, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $designation, $rating, $content, $imagePath, $sortOrder, $status]);
                log_activity('TESTIMONIAL_CREATE', "Added testimonial from $name");
                set_flash('success', 'New testimonial added successfully.');
            }
            header('Location: ' . ADMIN_URL . '/testimonials.php');
            exit;
        }
    }
}

$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY sort_order ASC, id ASC")->fetchAll();

$editItem = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
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
                <span><i class="fa-solid <?= $editItem ? 'fa-pen-to-square text-warning' : 'fa-plus text-primary' ?> me-2"></i><?= $editItem ? 'Edit Testimonial' : 'Add Testimonial' ?></span>
                <?php if ($editItem): ?>
                    <a href="<?= ADMIN_URL ?>/testimonials.php" class="btn btn-sm btn-outline-secondary">Cancel</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <?php if ($editItem): ?>
                        <input type="hidden" name="test_id" value="<?= $editItem['id'] ?>">
                        <input type="hidden" name="existing_image" value="<?= e($editItem['image']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Client / Customer Name*</label>
                        <input type="text" name="name" class="form-control" value="<?= e($editItem['name'] ?? '') ?>" required placeholder="e.g. Pooja Sharma">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Designation / Location</label>
                        <input type="text" name="designation" class="form-control" value="<?= e($editItem['designation'] ?? '') ?>" placeholder="e.g. Wedding Planner, Delhi">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rating (Stars)</label>
                        <select name="rating" class="form-select">
                            <option value="5" <?= (($editItem['rating'] ?? 5) == 5) ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ (5 Stars)</option>
                            <option value="4" <?= (($editItem['rating'] ?? 5) == 4) ? 'selected' : '' ?>>⭐⭐⭐⭐ (4 Stars)</option>
                            <option value="3" <?= (($editItem['rating'] ?? 5) == 3) ? 'selected' : '' ?>>⭐⭐⭐ (3 Stars)</option>
                            <option value="2" <?= (($editItem['rating'] ?? 5) == 2) ? 'selected' : '' ?>>⭐⭐ (2 Stars)</option>
                            <option value="1" <?= (($editItem['rating'] ?? 5) == 1) ? 'selected' : '' ?>>⭐ (1 Star)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Review / Testimonial Text*</label>
                        <textarea name="content" class="form-control" rows="4" required placeholder="What did the client say about the handicrafts, quality, or delivery?"><?= e($editItem['content'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Client Avatar / Source Icon</label>
                        <div class="mb-2">
                            <img src="<?= e(resolve_image_url($editItem['image'] ?? 'images/icon-google.svg')) ?>" id="testiImgPreview" class="border rounded p-1" style="max-height: 50px;">
                        </div>
                        <input type="file" name="image_file" class="form-control form-control-sm mb-2" data-preview-target="testiImgPreview" accept="image/*">
                        <select name="preset_image" class="form-select form-select-sm">
                            <option value="">-- Choose Existing Icon --</option>
                            <option value="images/icon-google.svg" <?= (($editItem['image'] ?? '') === 'images/icon-google.svg') ? 'selected' : '' ?>>Google Review Icon</option>
                            <option value="images/dummy.png" <?= (($editItem['image'] ?? '') === 'images/dummy.png') ? 'selected' : '' ?>>Default Avatar</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= e($editItem['sort_order'] ?? 1) ?>" min="0">
                        </div>
                        <div class="col-6 mb-3 pt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status" id="testiStatus" <?= (!isset($editItem) || $editItem['status'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="testiStatus">Active</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> <?= $editItem ? 'Update Testimonial' : 'Save Testimonial' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Table Column -->
    <div class="col-lg-7 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <span><i class="fa-solid fa-quote-left text-primary me-2"></i>Client Testimonials (<?= count($testimonials) ?>)</span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Rating</th>
                            <th>Feedback</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($testimonials)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No testimonials found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($testimonials as $t): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= e(resolve_image_url($t['image'])) ?>" class="rounded-circle border" style="width: 38px; height: 38px; object-fit: cover;">
                                            <div>
                                                <div class="fw-bold"><?= e($t['name']) ?></div>
                                                <small class="text-muted"><?= e($t['designation']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-warning">
                                            <?= str_repeat('★', $t['rating']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-truncate d-inline-block" style="max-width: 250px;">
                                            <?= e($t['content']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($t['status'] == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Hidden</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= ADMIN_URL ?>/testimonials.php?edit=<?= $t['id'] ?>" class="btn btn-light" title="Edit">
                                                <i class="fa-solid fa-pen text-primary"></i>
                                            </a>
                                            <a href="<?= ADMIN_URL ?>/testimonials.php?action=delete&id=<?= $t['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-light" onclick="return confirmDelete('Delete this testimonial?');" title="Delete">
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
