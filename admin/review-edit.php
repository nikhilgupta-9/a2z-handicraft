<?php
$pageTitle = 'Edit Customer Review';
require_once __DIR__ . '/includes/header.php';

global $pdo;

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ?");
$stmt->execute([$id]);
$review = $stmt->fetch();

if (!$review) {
    set_flash('danger', 'Review not found.');
    header('Location: ' . ADMIN_URL . '/reviews.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $authorName = trim($_POST['author_name'] ?? '');
        $authorEmail = trim($_POST['author_email'] ?? '');
        $productId = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
        $rating = (int)($_POST['rating'] ?? 5);
        $title = trim($_POST['title'] ?? '');
        $comment = trim($_POST['comment'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['approved', 'pending', 'rejected']) ? $_POST['status'] : 'approved';

        if (empty($authorName) || empty($comment)) {
            $error = 'Reviewer full name and review comments are required.';
        } else {
            $stmt = $pdo->prepare("UPDATE reviews SET 
                product_id = ?, author_name = ?, author_email = ?, rating = ?, title = ?, comment = ?, status = ? 
                WHERE id = ?");
            $stmt->execute([$productId, $authorName, $authorEmail, $rating, $title, $comment, $status, $id]);

            log_activity('REVIEW_UPDATE', "Updated review ID: $id ($authorName)");
            set_flash('success', 'Review updated successfully.');
            header('Location: ' . ADMIN_URL . '/reviews.php');
            exit;
        }
    }
}

$products = $pdo->query("SELECT id, name FROM products ORDER BY name ASC")->fetchAll();
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="m-0 fw-bold"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Review / Moderation</h4>
        <small class="text-muted">Review #<?= $id ?> submitted on <?= date('M d, Y', strtotime($review['created_at'])) ?></small>
    </div>
    <a href="<?= ADMIN_URL ?>/reviews.php" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Reviews
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<form action="" method="POST">
    <?= csrf_field() ?>

    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-comment-dots text-primary me-2"></i>Review Details</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Target Product (Optional)</label>
                        <select name="product_id" class="form-select">
                            <option value="">-- General Website Review --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($review['product_id'] == $p['id']) ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Reviewer Full Name*</label>
                            <input type="text" name="author_name" class="form-control" value="<?= e($review['author_name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Reviewer Email</label>
                            <input type="email" name="author_email" class="form-control" value="<?= e($review['author_email']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Review Headline / Title</label>
                        <input type="text" name="title" class="form-control" value="<?= e($review['title']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Review Comment / Feedback*</label>
                        <textarea name="comment" class="form-control" rows="5" required><?= e($review['comment']) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Options -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-star text-primary me-2"></i>Rating & Moderation</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Star Rating</label>
                        <select name="rating" class="form-select">
                            <option value="5" <?= ($review['rating'] == 5) ? 'selected' : '' ?>>5 Stars - Outstanding</option>
                            <option value="4" <?= ($review['rating'] == 4) ? 'selected' : '' ?>>4 Stars - Very Good</option>
                            <option value="3" <?= ($review['rating'] == 3) ? 'selected' : '' ?>>3 Stars - Average</option>
                            <option value="2" <?= ($review['rating'] == 2) ? 'selected' : '' ?>>2 Stars - Below Expectations</option>
                            <option value="1" <?= ($review['rating'] == 1) ? 'selected' : '' ?>>1 Star - Poor</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Approval Status</label>
                        <select name="status" class="form-select">
                            <option value="approved" <?= ($review['status'] === 'approved') ? 'selected' : '' ?>>Approved (Visible on website)</option>
                            <option value="pending" <?= ($review['status'] === 'pending') ? 'selected' : '' ?>>Pending Review</option>
                            <option value="rejected" <?= ($review['status'] === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Update Review
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
