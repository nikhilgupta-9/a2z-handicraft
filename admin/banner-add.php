<?php
$pageTitle = 'Add New Banner';
require_once __DIR__ . '/includes/header.php';

global $pdo;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
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
            $imagePath = 'images/banner.png';
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
                $stmt = $pdo->prepare("INSERT INTO banners (title, subtitle, tagline, description, image, button_text, button_link, rating_text, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $subtitle, $tagline, $description, $imagePath, $buttonText, $buttonLink, $ratingText, $sortOrder, $status]);
                
                log_activity('BANNER_CREATE', "Added new banner: $title");
                set_flash('success', 'New banner created successfully.');
                header('Location: ' . ADMIN_URL . '/banners.php');
                exit;
            }
        }
    }
}

$presetBanners = [
    'images/banner.png' => 'banner.png (Traditional Yellow Toran Hanging)',
    'images/banner2.png' => 'banner2.png (Wedding & Event Decoration)',
    'images/ca1.jpg' => 'ca1.jpg (RDI Hanging)',
    'images/ca2.webp' => 'ca2.webp (Iron Frame)'
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="m-0 fw-bold"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Add New Home Banner</h4>
        <small class="text-muted">Create a high-impact slide for the homepage carousel</small>
    </div>
    <a href="<?= ADMIN_URL ?>/banners.php" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Banners
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-heading text-primary me-2"></i>Banner Content & Headings</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Banner Main Title*</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Unique Traditional Handmade Decor" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subtitle / Top Label</label>
                        <input type="text" name="subtitle" class="form-control" placeholder="e.g. Best Handcrafted Collection">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Offer Tagline / Badge</label>
                        <input type="text" name="tagline" class="form-control" placeholder="e.g. Flat 30% Off on Bulk Orders">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Short Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief tagline or description to catch customer attention on hero slide..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Call to Action (CTA) Button Text</label>
                            <input type="text" name="button_text" class="form-control" value="Show All Products">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">CTA Button Link URL</label>
                            <input type="text" name="button_link" class="form-control" value="categories.php">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Media & Settings Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-image text-primary me-2"></i>Banner Image</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Choose Existing Project Banner</label>
                        <select name="preset_image" class="form-select">
                            <option value="">-- Or upload custom file below --</option>
                            <?php foreach ($presetBanners as $path => $label): ?>
                                <option value="<?= $path ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Or Upload Custom Banner (PNG / JPG / WebP)</label>
                        <input type="file" name="image_file" class="form-control" accept="image/*" data-preview-target="bannerPreview">
                        <small class="text-muted">Recommended: 1200x500px or 1920x800px</small>
                    </div>

                    <div class="text-center p-2 border rounded bg-light">
                        <img id="bannerPreview" src="<?= BASE_URL ?>/images/banner.png" class="img-fluid rounded shadow-sm" style="max-height: 140px; display: block; margin: 0 auto;" alt="Banner Preview">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-sliders text-primary me-2"></i>Publishing & Order</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rating Text</label>
                        <input type="text" name="rating_text" class="form-control" value="4.9/5 Review (120+ Orders)">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="status" id="bannerStatus" checked>
                        <label class="form-check-label fw-semibold" for="bannerStatus">Active on Homepage</label>
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save New Banner
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
