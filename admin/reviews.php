<?php
$pageTitle = 'Product Reviews Moderation';
require_once __DIR__ . '/includes/header.php';

global $pdo;

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

// Filters
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT r.*, p.name as product_name 
        FROM reviews r 
        LEFT JOIN products p ON r.product_id = p.id 
        WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $sql .= " AND r.status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $sql .= " AND (r.author_name LIKE ? OR r.author_email LIKE ? OR r.comment LIKE ? OR r.title LIKE ? OR p.name LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql .= " ORDER BY r.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reviews = $stmt->fetchAll();

// Counts for filter pills
$totalReviews = (int)$pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$approvedCount = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'approved'")->fetchColumn();
$pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();
$rejectedCount = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'rejected'")->fetchColumn();
?>

<!-- Header & Action Bar -->
<div class="card mb-4 shadow-sm">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-md-7">
                <form action="" method="GET" class="d-flex flex-wrap gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search reviewer, product, or comment..." value="<?= e($search) ?>" style="max-width: 260px;">
                    <select name="status" class="form-select form-select-sm" style="max-width: 160px;">
                        <option value="">All Statuses</option>
                        <option value="approved" <?= ($statusFilter === 'approved') ? 'selected' : '' ?>>Approved</option>
                        <option value="pending" <?= ($statusFilter === 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="rejected" <?= ($statusFilter === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary-a2z"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <?php if (!empty($search) || !empty($statusFilter)): ?>
                        <a href="<?= ADMIN_URL ?>/reviews.php" class="btn btn-sm btn-light">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-5 text-md-end">
                <a href="<?= ADMIN_URL ?>/review-add.php" class="btn btn-primary-a2z btn-sm shadow-sm">
                    <i class="fa-solid fa-plus-circle me-1"></i> Add Customer Review
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Status Tabs -->
<div class="d-flex gap-2 mb-3">
    <a href="<?= ADMIN_URL ?>/reviews.php" class="btn btn-sm <?= empty($statusFilter) ? 'btn-dark' : 'btn-outline-secondary' ?>">
        All (<?= $totalReviews ?>)
    </a>
    <a href="<?= ADMIN_URL ?>/reviews.php?status=approved" class="btn btn-sm <?= ($statusFilter === 'approved') ? 'btn-success' : 'btn-outline-success' ?>">
        Approved (<?= $approvedCount ?>)
    </a>
    <a href="<?= ADMIN_URL ?>/reviews.php?status=pending" class="btn btn-sm <?= ($statusFilter === 'pending') ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' ?>">
        Pending (<?= $pendingCount ?>)
    </a>
    <a href="<?= ADMIN_URL ?>/reviews.php?status=rejected" class="btn btn-sm <?= ($statusFilter === 'rejected') ? 'btn-danger' : 'btn-outline-danger' ?>">
        Rejected (<?= $rejectedCount ?>)
    </a>
</div>

<!-- Reviews Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <span class="fw-bold"><i class="fa-solid fa-star-half-stroke text-primary me-2"></i>Product & Customer Reviews (<?= count($reviews) ?>)</span>
        <span class="text-muted small">Moderate before showing publicly on product pages</span>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Reviewer</th>
                    <th>Product</th>
                    <th>Rating</th>
                    <th>Review Title & Comment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reviews)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-star-half-stroke fs-2 mb-2 d-block"></i>
                            No reviews found matching your filter criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reviews as $r): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?= e($r['author_name']) ?></div>
                                <small class="text-muted"><?= e($r['author_email'] ?? 'No email') ?></small>
                            </td>
                            <td>
                                <?php if (!empty($r['product_name'])): ?>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fa-solid fa-box text-primary me-1"></i><?= e($r['product_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border">General Review</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-warning small text-nowrap">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa-<?= ($i <= $r['rating']) ? 'solid' : 'regular' ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($r['title'])): ?>
                                    <div class="fw-semibold text-dark small"><?= e($r['title']) ?></div>
                                <?php endif; ?>
                                <small class="text-muted d-inline-block text-truncate" style="max-width: 280px;" title="<?= e($r['comment']) ?>">
                                    <?= e($r['comment']) ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php elseif ($r['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted text-nowrap"><?= date('M d, Y', strtotime($r['created_at'])) ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= ADMIN_URL ?>/review-edit.php?id=<?= $r['id'] ?>" class="btn btn-outline-primary" title="Edit Review">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <?php if ($r['status'] !== 'approved'): ?>
                                        <a href="<?= ADMIN_URL ?>/reviews.php?action=status&id=<?= $r['id'] ?>&val=approved&token=<?= csrf_token() ?>" class="btn btn-outline-success" title="Approve">
                                            <i class="fa-solid fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($r['status'] !== 'rejected'): ?>
                                        <a href="<?= ADMIN_URL ?>/reviews.php?action=status&id=<?= $r['id'] ?>&val=rejected&token=<?= csrf_token() ?>" class="btn btn-outline-warning" title="Reject">
                                            <i class="fa-solid fa-ban"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= ADMIN_URL ?>/reviews.php?action=delete&id=<?= $r['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-outline-danger" onclick="return confirmDelete('Delete this review permanently?');" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
