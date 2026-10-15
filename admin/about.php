<?php
$pageTitle = 'About Us Content Management';
require_once __DIR__ . '/includes/header.php';

global $pdo;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? 'About Us');
        $contentP1 = trim($_POST['content_p1'] ?? '');
        $contentP2 = trim($_POST['content_p2'] ?? '');
        $contentP3 = trim($_POST['content_p3'] ?? '');
        $expYears = trim($_POST['experience_years'] ?? '15+ Years');
        $happyCust = trim($_POST['happy_customers'] ?? '10,000+');
        $prodCount = trim($_POST['products_count'] ?? '500+');
        $craftsmen = trim($_POST['craftsmen_count'] ?? '50+');

        $imagePath = $_POST['existing_image'] ?? 'images/about.jpeg';
        if (!empty($_POST['preset_image'])) {
            $imagePath = $_POST['preset_image'];
        }
        if (!empty($_FILES['image_file']['name'])) {
            $upload = upload_media($_FILES['image_file'], 'about');
            if ($upload['success']) {
                $imagePath = $upload['path'];
            } else {
                $error = 'Image upload failed: ' . $upload['error'];
            }
        }

        if (empty($error)) {
            $stmt = $pdo->prepare("UPDATE about_section SET 
                title = ?, subtitle = ?, image = ?, content_p1 = ?, content_p2 = ?, content_p3 = ?,
                experience_years = ?, happy_customers = ?, products_count = ?, craftsmen_count = ?
                WHERE id = 1");
            $stmt->execute([$title, $subtitle, $imagePath, $contentP1, $contentP2, $contentP3, $expYears, $happyCust, $prodCount, $craftsmen]);

            log_activity('ABOUT_UPDATE', 'Updated About Us section content and statistics');
            set_flash('success', 'About Us page content successfully updated!');
            header('Location: ' . ADMIN_URL . '/about.php');
            exit;
        }
    }
}

$about = get_about_section();
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="m-0 fw-bold"><i class="fa-solid fa-circle-info text-primary me-2"></i>About Us Page Content</h4>
    <a href="<?= BASE_URL ?>/about-us.php" target="_blank" class="btn btn-outline-info btn-sm">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Live Page
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="existing_image" value="<?= e($about['image']) ?>">

    <div class="row">
        <!-- Content Column -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-pen-nib text-primary me-2"></i>Textual Content</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Section Subtitle / Small Badge</label>
                        <input type="text" name="subtitle" class="form-control" value="<?= e($about['subtitle']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Main Heading / Title</label>
                        <input type="text" name="title" class="form-control" value="<?= e($about['title']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Paragraph 1 (Introduction & Heritage)</label>
                        <textarea name="content_p1" class="form-control" rows="4"><?= e($about['content_p1']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Paragraph 2 (Products & Modern Craftsmanship)</label>
                        <textarea name="content_p2" class="form-control" rows="4"><?= e($about['content_p2']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Paragraph 3 (Quality Commitment & Wholesale)</label>
                        <textarea name="content_p3" class="form-control" rows="4"><?= e($about['content_p3']) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Media & Counter Stats Column -->
        <div class="col-lg-4 mb-4">
            <!-- Image Box -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-image text-secondary me-2"></i>About Showcase Image</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <img src="<?= e(resolve_image_url($about['image'])) ?>" id="aboutImgPreview" class="rounded border w-100" style="max-height: 180px; object-fit: cover;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Upload Custom Image:</label>
                        <input type="file" name="image_file" class="form-control" data-preview-target="aboutImgPreview" accept="image/*">
                    </div>

                    <div>
                        <label class="form-label small text-muted">Or Pick Asset Image:</label>
                        <select name="preset_image" class="form-select form-select-sm">
                            <option value="">-- Keep Current --</option>
                            <option value="images/about.jpeg" <?= ($about['image'] === 'images/about.jpeg') ? 'selected' : '' ?>>images/about.jpeg (Default Workshop Photo)</option>
                            <option value="images/banner.png" <?= ($about['image'] === 'images/banner.png') ? 'selected' : '' ?>>images/banner.png</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Stats Counters -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-calculator text-success me-2"></i>Key Counter Metrics</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Years of Experience</label>
                        <input type="text" name="experience_years" class="form-control" value="<?= e($about['experience_years']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Happy Clients / Decorators</label>
                        <input type="text" name="happy_customers" class="form-control" value="<?= e($about['happy_customers']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Product Designs Count</label>
                        <input type="text" name="products_count" class="form-control" value="<?= e($about['products_count']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Master Craftsmen Count</label>
                        <input type="text" name="craftsmen_count" class="form-control" value="<?= e($about['craftsmen_count']) ?>">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-a2z btn-lg w-100 py-3 shadow">
                <i class="fa-solid fa-floppy-disk me-2"></i> Update About Section
            </button>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
