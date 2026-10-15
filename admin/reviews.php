<?php
$pageTitle = 'Product Reviews Moderation';
require_once __DIR__ . '/includes/header.php';

global $pdo;
$error = '';

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$id]);

        log_activity('REVIEW_DELETE', "Deleted review ID: $id");
        set_flash('success', 'Review deleted successfully.');
    } else {
        set_flash('danger', 'Security verification failed.');
    }
    header('Location: ' . ADMIN_URL . '/reviews.php');
    exit;
}

// Handle Status Change
if (isset($_GET['action']) && $_GET['action'] === 'status' && isset($_GET['id']) && isset($_GET['val'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $val = clean_input($_GET['val']);
        if (in_array($val, ['approved', 'pending', 'rejected'])) {
            $stmt = $pdo->prepare("UPDATE reviews SET status = ? WHERE id = ?");
            $stmt->execute([$val, $id]);
            set_flash('success', "Review marked as $val.");
        }
    }
    header('Location: ' . ADMIN_URL . '/reviews.php');
    exit;
}

// Handle Add / Edit Review POST
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
        $status = $_POST['status'] ?? 'approved';

        if (empty($authorName) || empty($comment)) {
            $error = 'Reviewer name and comment are required.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO reviews (product_id, author_name, author_email, rating, title, comment, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$productId, $authorName, $authorEmail, $rating, $title, $comment, $status]);
            log_activity('REVIEW_CREATE', "Added review from $authorName");
            set_flash('success', 'Review recorded successfully.');
            header('Location: ' . ADMIN_URL . '/reviews.php');
            exit;
        }
    }
}

// Fetch Reviews with product name
$reviews = $pdo->query("SELECT r.*, p.name as product_name FROM reviews r LEFT JOIN products p ON r.product_id = p.id ORDER BY r.id DESC")->fetchAll();
$products = $pdo->query("SELECT id, name FROM products ORDER BY name ASC")->fetchAll();
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<div class="row">
    <!-- Form Column -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <span class="fw-bold"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Add Customer Review</span>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Product (Optional)</label>
                        <select name="product_id" class="form-select">
                            <option value="">-- General Website Review --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reviewer Full Name*</label>
                        <input type="text" name="author_name" class="form-control" required placeholder="e.g. Aman Verma">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reviewer Email (Optional)</label>
                        <input type="email" name="author_email" class="form-control" placeholder="reviewer@example.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rating</label>
                        <select name="rating" class="form-select">
                            <option value="5">⭐⭐⭐⭐⭐ (5 Stars)</option>
                            <option value="4">⭐⭐⭐⭐ (4 Stars)</option>
                            <option value="3">⭐⭐⭐ (3 Stars)</option>
                            <option value="2">⭐⭐ (2 Stars)</option>
                            <option value="1">⭐ (1 Star)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Review Headline / Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Outstanding Quality">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Review Comment*</label>
                        <textarea name="comment" class="form-control" rows="3" required placeholder="Customer review remarks..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Moderation Status</label>
                        <select name="status" class="form-select">
                            <option value="approved">Approved & Published</option>
                            <option value="pending">Pending Moderation</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Review
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Table Column -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <span class="fw-bold"><i class="fa-solid fa-star-half-stroke text-primary me-2"></i>Customer Reviews (<?= count($reviews) ?>)</span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Reviewer</th>
                            <th>Target Item</th>
                            <th>Rating & Feedback</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No reviews recorded yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $rev): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= e($rev['author_name']) ?></div>
                                        <small class="text-muted"><?= e($rev['author_email'] ?? 'No email') ?></small>
                                    </td>
                                    <td>
                                        <small class="badge bg-light text-dark border"><?= e($rev['product_name'] ?? 'General Review') ?></small>
                                    </td>
                                    <td>
                                        <div class="text-warning small mb-1">
                                            <?= str_repeat('★', $rev['rating']) ?>
                                        </div>
                                        <?php if (!empty($rev['title'])): ?>
                                            <div class="fw-semibold small"><?= e($rev['title']) ?></div>
                                        <?php endif; ?>
                                        <div class="small text-muted text-truncate" style="max-width: 250px;">
                                            <?= e($rev['comment']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 px-2" type="button" data-bs-toggle="dropdown">
                                                <?php if ($rev['status'] === 'approved'): ?>
                                                    <span class="text-success fw-bold">Approved</span>
                                                <?php elseif ($rev['status'] === 'rejected'): ?>
                                                    <span class="text-danger fw-bold">Rejected</span>
                                                <?php else: ?>
                                                    <span class="text-warning text-dark fw-bold">Pending</span>
                                                <?php endif; ?>
                                            </button>
                                            <ul class="dropdown-menu shadow">
                                                <li><a class="dropdown-item text-success" href="<?= ADMIN_URL ?>/reviews.php?action=status&val=approved&id=<?= $rev['id'] ?>&token=<?= csrf_token() ?>">Approve</a></li>
                                                <li><a class="dropdown-item text-warning" href="<?= ADMIN_URL ?>/reviews.php?action=status&val=pending&id=<?= $rev['id'] ?>&token=<?= csrf_token() ?>">Pending</a></li>
                                                <li><a class="dropdown-item text-danger" href="<?= ADMIN_URL ?>/reviews.php?action=status&val=rejected&id=<?= $rev['id'] ?>&token=<?= csrf_token() ?>">Reject</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="<?= ADMIN_URL ?>/reviews.php?action=delete&id=<?= $rev['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-sm btn-light" onclick="return confirmDelete('Delete this review?');" title="Delete">
                                            <i class="fa-solid fa-trash text-danger"></i>
                                        </a>
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
