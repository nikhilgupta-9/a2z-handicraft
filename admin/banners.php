<?php
$pageTitle = 'Home Banners Management';
require_once __DIR__ . '/includes/header.php';

global $pdo;

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

// Handle Status Toggle
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $pdo->query("UPDATE banners SET status = 1 - status WHERE id = $id");
        set_flash('success', 'Banner status updated.');
    }
    header('Location: ' . ADMIN_URL . '/banners.php');
    exit;
}

// Search Filter
$search = trim($_GET['search'] ?? '');
$sql = "SELECT * FROM banners WHERE 1=1";
$params = [];
if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR subtitle LIKE ? OR tagline LIKE ? OR description LIKE ?)";
    $term = "%$search%";
    $params = [$term, $term, $term, $term];
}
$sql .= " ORDER BY sort_order ASC, id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$banners = $stmt->fetchAll();
?>

<!-- Header & Action Bar -->
<div class="card mb-4 shadow-sm">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <form action="" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search banner title or text..." value="<?= e($search) ?>" style="max-width: 280px;">
                    <button type="submit" class="btn btn-sm btn-primary-a2z"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <?php if (!empty($search)): ?>
                        <a href="<?= ADMIN_URL ?>/banners.php" class="btn btn-sm btn-light">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="<?= ADMIN_URL ?>/banner-add.php" class="btn btn-primary-a2z btn-sm shadow-sm">
                    <i class="fa-solid fa-plus-circle me-1"></i> Add New Banner
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Banners Listing Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <span class="fw-bold"><i class="fa-solid fa-images text-primary me-2"></i>Home Hero Banners (<?= count($banners) ?>)</span>
        <span class="text-muted small">Shown in the homepage carousel slider</span>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 140px;">Banner Preview</th>
                    <th>Title & Subtitle</th>
                    <th>Tagline & CTA</th>
                    <th>Rating Text</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($banners)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-images fs-2 mb-2 d-block"></i>
                            No banners found. Click "Add New Banner" above to create one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($banners as $b): ?>
                        <tr>
                            <td>
                                <img src="<?= e(resolve_image_url($b['image'])) ?>" class="rounded border shadow-sm" style="width: 120px; height: 60px; object-fit: cover;" alt="<?= e($b['title']) ?>">
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= e($b['title']) ?></div>
                                <?php if (!empty($b['subtitle'])): ?>
                                    <div class="small text-muted"><?= e($b['subtitle']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($b['tagline'])): ?>
                                    <span class="badge bg-light text-dark border me-1"><?= e($b['tagline']) ?></span>
                                <?php endif; ?>
                                <div class="small text-muted mt-1">
                                    <i class="fa-solid fa-arrow-pointer text-primary me-1"></i><?= e($b['button_text']) ?> &rarr; <code><?= e($b['button_link']) ?></code>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-warning bg-opacity-25 text-dark border border-warning border-opacity-50">
                                    <i class="fa-solid fa-star text-warning me-1"></i><?= e($b['rating_text'] ?? '4.9/5 Review') ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= $b['sort_order'] ?></span>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/banners.php?action=toggle_status&id=<?= $b['id'] ?>&token=<?= csrf_token() ?>" class="badge text-decoration-none <?= ($b['status'] == 1) ? 'bg-success' : 'bg-secondary' ?>" title="Click to toggle status">
                                    <?= ($b['status'] == 1) ? 'Active' : 'Disabled' ?>
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= ADMIN_URL ?>/banner-edit.php?id=<?= $b['id'] ?>" class="btn btn-outline-primary" title="Edit Banner">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <a href="<?= ADMIN_URL ?>/banners.php?action=delete&id=<?= $b['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-outline-danger" onclick="return confirmDelete('Are you sure you want to delete this banner?');" title="Delete Banner">
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
