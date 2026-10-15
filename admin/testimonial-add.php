<?php
$pageTitle = 'Add New Testimonial';
require_once __DIR__ . '/includes/header.php';

global $pdo;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $designation = trim($_POST['designation'] ?? '');
        $rating = (int)($_POST['rating'] ?? 5);
        $content = trim($_POST['content'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        if (empty($name) || empty($content)) {
            $error = 'Client name and testimonial content are required.';
        } else {
            $imagePath = 'images/icon-google.svg';
            if (!empty($_POST['preset_image'])) {
                $imagePath = $_POST['preset_image'];
            }
            if (!empty($_FILES['image_file']['name'])) {
                $upload = upload_media($_FILES['image_file'], 'testimonials');
                if ($upload['success']) {
                    $imagePath = $upload['path'];
                } else {
                    $error = 'Client photo upload failed: ' . $upload['error'];
                }
            }

            if (empty($error)) {
                $stmt = $pdo->prepare("INSERT INTO testimonials (name, designation, rating, content, image, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $designation, $rating, $content, $imagePath, $sortOrder, $status]);

                log_activity('TESTIMONIAL_CREATE', "Added testimonial from $name");
                set_flash('success', 'New testimonial added successfully.');
                header('Location: ' . ADMIN_URL . '/testimonials.php');
                exit;
            }
        }
    }
}

$presetAvatars = [
    'images/icon-google.svg' => 'icon-google.svg (Google Review Verified)',
    'images/icon-mail.svg' => 'icon-mail.svg (Direct Customer)',
    'images/icon-phone.svg' => 'icon-phone.svg (Phone Client)'
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="m-0 fw-bold"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Add New Testimonial</h4>
        <small class="text-muted">Record client review and feedback to build trust with buyers</small>
    </div>
    <a href="<?= ADMIN_URL ?>/testimonials.php" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Testimonials
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-user-pen text-primary me-2"></i>Client Details & Review</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Client / Customer Name*</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Radhika Sharma" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Designation / City</label>
                            <input type="text" name="designation" class="form-control" placeholder="e.g. Event Decorator, Jaipur">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Star Rating (1 to 5 Stars)</label>
                        <select name="rating" class="form-select">
                            <option value="5" selected>5 Stars - Exceptional / Highly Recommended</option>
                            <option value="4">4 Stars - Very Good</option>
                            <option value="3">3 Stars - Average</option>
                            <option value="2">2 Stars - Fair</option>
                            <option value="1">1 Star - Poor</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Testimonial Quote / Feedback*</label>
                        <textarea name="content" class="form-control" rows="5" placeholder="Share what the customer said about our handicraft items, timely delivery, and handcrafted quality..." required></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Options -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-image text-primary me-2"></i>Client Avatar / Icon</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Icon / Badge</label>
                        <select name="preset_image" class="form-select">
                            <?php foreach ($presetAvatars as $path => $label): ?>
                                <option value="<?= $path ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Or Upload Customer Photo</label>
                        <input type="file" name="image_file" class="form-control" accept="image/*" data-preview-target="avatarPreview">
                        <small class="text-muted">Square ratio recommended (e.g. 150x150)</small>
                    </div>

                    <div class="text-center p-3 border rounded bg-light">
                        <img id="avatarPreview" src="<?= BASE_URL ?>/images/icon-google.svg" class="rounded-circle border shadow-sm" style="width: 70px; height: 70px; object-fit: cover;" alt="Avatar Preview">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-sliders text-primary me-2"></i>Display Settings</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Display Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                        <small class="text-muted">Lower numbers show up first</small>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="status" id="testStatus" checked>
                        <label class="form-check-label fw-semibold" for="testStatus">Visible on Website</label>
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Testimonial
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
