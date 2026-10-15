<?php
$pageTitle = 'Subcategories Management';
require_once __DIR__ . '/includes/header.php';

global $pdo;

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        
        // Check if subcategory has products
        $prodCount = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE subcategory_id = $id")->fetchColumn();
        if ($prodCount > 0) {
            set_flash('danger', "Cannot delete subcategory because it has $prodCount associated products. Please reassign or delete products first.");
        } else {
            $stmt = $pdo->prepare("SELECT image FROM subcategories WHERE id = ?");
            $stmt->execute([$id]);
            $img = $stmt->fetchColumn();

            $delStmt = $pdo->prepare("DELETE FROM subcategories WHERE id = ?");
            $delStmt->execute([$id]);

            delete_uploaded_file($img);
            log_activity('SUBCATEGORY_DELETE', "Deleted subcategory ID: $id");
            set_flash('success', 'Subcategory deleted successfully.');
        }
    } else {
        set_flash('danger', 'Security verification failed.');
    }
    header('Location: ' . ADMIN_URL . '/subcategories.php');
    exit;
}

// Handle Quick Toggle (Status)
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $pdo->query("UPDATE subcategories SET status = 1 - status WHERE id = $id");
        set_flash('success', 'Subcategory status updated.');
    }
    header('Location: ' . ADMIN_URL . '/subcategories.php');
    exit;
}

// Filters
$search = trim($_GET['search'] ?? '');
$categoryId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;

$sql = "SELECT s.*, c.name as category_name, (SELECT COUNT(*) FROM products p WHERE p.subcategory_id = s.id) as product_count 
        FROM subcategories s 
        LEFT JOIN categories c ON s.category_id = c.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (s.name LIKE ? OR s.slug LIKE ? OR s.description LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if (!empty($categoryId)) {
    $sql .= " AND s.category_id = ?";
    $params[] = $categoryId;
}

$sql .= " ORDER BY c.name ASC, s.sort_order ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$subcategories = $stmt->fetchAll();

// Categories for filter dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
?>

<!-- Page Header & Action Bar -->
<div class="card mb-4 shadow-sm">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-md-7">
                <form action="" method="GET" class="d-flex flex-wrap gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search subcategory or slug..." value="<?= e($search) ?>" style="max-width: 250px;">
                    <select name="category_id" class="form-select form-select-sm" style="max-width: 220px;">
                        <option value="">All Parent Categories</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($categoryId == $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary-a2z"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <?php if (!empty($search) || !empty($categoryId)): ?>
                        <a href="<?= ADMIN_URL ?>/subcategories.php" class="btn btn-sm btn-light">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-5 text-md-end">
                <a href="<?= ADMIN_URL ?>/subcategory-add.php" class="btn btn-primary-a2z btn-sm shadow-sm">
                    <i class="fa-solid fa-plus-circle me-1"></i> Add New Subcategory
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Subcategories Listing Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <span class="fw-bold"><i class="fa-solid fa-tags text-primary me-2"></i>All Subcategories (<?= count($subcategories) ?>)</span>
        <span class="text-muted small">Sub-classifications of product categories</span>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 70px;">Image</th>
                    <th>Subcategory Name & URL</th>
                    <th>Parent Category</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subcategories)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-tags fs-2 mb-2 d-block"></i>
                            No subcategories found. Click "Add New Subcategory" above to create one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subcategories as $s): ?>
                        <tr>
                            <td>
                                <?php if (!empty($s['image'])): ?>
                                    <img src="<?= e(resolve_image_url($s['image'])) ?>" class="table-img-thumb" alt="<?= e($s['name']) ?>">
                                <?php else: ?>
                                    <div class="bg-light border rounded d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                        <i class="fa-solid fa-tag"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= e($s['name']) ?></div>
                                <div class="small text-muted">
                                    <a href="<?= BASE_URL ?>/subcategory/<?= e($s['slug']) ?>" target="_blank" class="text-decoration-none text-info">
                                        <i class="fa-solid fa-link me-1"></i>/subcategory/<?= e($s['slug']) ?>
                                    </a>
                                </div>
                                <?php if (!empty($s['meta_title'])): ?>
                                    <div class="badge bg-light text-secondary border mt-1" title="SEO Title configured">
                                        <i class="fa-solid fa-check text-success me-1"></i>Yoast SEO
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                    <i class="fa-solid fa-folder me-1"></i><?= e($s['category_name'] ?? 'Unassigned') ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 240px;">
                                    <?= e($s['description'] ?? 'No description.') ?>
                                </small>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/products.php?subcategory_id=<?= $s['id'] ?>" class="badge bg-light text-dark border text-decoration-none" title="View products in this subcategory">
                                    <i class="fa-solid fa-boxes-stacked me-1"></i><?= $s['product_count'] ?> items
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= $s['sort_order'] ?></span>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/subcategories.php?action=toggle_status&id=<?= $s['id'] ?>&token=<?= csrf_token() ?>" class="badge text-decoration-none <?= ($s['status'] == 1) ? 'bg-success' : 'bg-secondary' ?>" title="Click to toggle status">
                                    <?= ($s['status'] == 1) ? 'Active' : 'Draft' ?>
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= ADMIN_URL ?>/subcategory-edit.php?id=<?= $s['id'] ?>" class="btn btn-outline-primary" title="Edit Subcategory">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <a href="<?= ADMIN_URL ?>/subcategories.php?action=delete&id=<?= $s['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-outline-danger" onclick="return confirmDelete('Are you sure you want to delete this subcategory?');" title="Delete Subcategory">
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
