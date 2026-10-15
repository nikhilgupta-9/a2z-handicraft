<?php
$pageTitle = 'Testimonials Management';
require_once __DIR__ . '/includes/header.php';

global $pdo;

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT image FROM testimonials WHERE id = ?");
        $stmt->execute([$id]);
        $img = $stmt->fetchColumn();

        $delStmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
        $delStmt->execute([$id]);

        delete_uploaded_file($img);
        log_activity('TESTIMONIAL_DELETE', "Deleted testimonial ID: $id");
        set_flash('success', 'Testimonial deleted successfully.');
    } else {
        set_flash('danger', 'Security verification failed.');
    }
    header('Location: ' . ADMIN_URL . '/testimonials.php');
    exit;
}

// Handle Status Toggle
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $pdo->query("UPDATE testimonials SET status = 1 - status WHERE id = $id");
        set_flash('success', 'Testimonial status updated.');
    }
    header('Location: ' . ADMIN_URL . '/testimonials.php');
    exit;
}

// Search Filter
$search = trim($_GET['search'] ?? '');
$sql = "SELECT * FROM testimonials WHERE 1=1";
$params = [];
if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR designation LIKE ? OR content LIKE ?)";
    $term = "%$search%";
    $params = [$term, $term, $term];
}
$sql .= " ORDER BY sort_order ASC, id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$testimonials = $stmt->fetchAll();
?>

<!-- Header & Action Bar -->
<div class="card mb-4 shadow-sm">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <form action="" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search client name, company or text..." value="<?= e($search) ?>" style="max-width: 280px;">
                    <button type="submit" class="btn btn-sm btn-primary-a2z"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <?php if (!empty($search)): ?>
                        <a href="<?= ADMIN_URL ?>/testimonials.php" class="btn btn-sm btn-light">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="<?= ADMIN_URL ?>/testimonial-add.php" class="btn btn-primary-a2z btn-sm shadow-sm">
                    <i class="fa-solid fa-plus-circle me-1"></i> Add New Testimonial
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Testimonials Listing Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <span class="fw-bold"><i class="fa-solid fa-quote-left text-primary me-2"></i>Customer & Decorator Testimonials (<?= count($testimonials) ?>)</span>
        <span class="text-muted small">Displayed on homepage and reviews section</span>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 70px;">Client</th>
                    <th>Name & Designation</th>
                    <th>Rating</th>
                    <th>Testimonial Quote</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($testimonials)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-quote-left fs-2 mb-2 d-block"></i>
                            No testimonials found. Click "Add New Testimonial" above to add one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($testimonials as $t): ?>
                        <tr>
                            <td>
                                <img src="<?= e(resolve_image_url($t['image'])) ?>" class="rounded-circle border" style="width: 45px; height: 45px; object-fit: cover;" alt="<?= e($t['name']) ?>">
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= e($t['name']) ?></div>
                                <div class="small text-muted"><?= e($t['designation'] ?? 'Customer') ?></div>
                            </td>
                            <td>
                                <div class="text-warning small">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa-<?= ($i <= $t['rating']) ? 'solid' : 'regular' ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted d-inline-block text-truncate" style="max-width: 320px;" title="<?= e($t['content']) ?>">
                                    "<?= e($t['content']) ?>"
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= $t['sort_order'] ?></span>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/testimonials.php?action=toggle_status&id=<?= $t['id'] ?>&token=<?= csrf_token() ?>" class="badge text-decoration-none <?= ($t['status'] == 1) ? 'bg-success' : 'bg-secondary' ?>" title="Click to toggle status">
                                    <?= ($t['status'] == 1) ? 'Active' : 'Hidden' ?>
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= ADMIN_URL ?>/testimonial-edit.php?id=<?= $t['id'] ?>" class="btn btn-outline-primary" title="Edit Testimonial">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <a href="<?= ADMIN_URL ?>/testimonials.php?action=delete&id=<?= $t['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-outline-danger" onclick="return confirmDelete('Are you sure you want to delete this testimonial?');" title="Delete Testimonial">
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
