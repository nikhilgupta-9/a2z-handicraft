<?php
$pageTitle = 'Categories Management';
require_once __DIR__ . '/includes/header.php';

global $pdo;

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        
        // Check if category has products
        $prodCount = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE category_id = $id")->fetchColumn();
        if ($prodCount > 0) {
            set_flash('danger', "Cannot delete category because it has $prodCount associated products. Please reassign or delete products first.");
        } else {
            $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $img = $stmt->fetchColumn();

            $delStmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $delStmt->execute([$id]);

            delete_uploaded_file($img);
            log_activity('CATEGORY_DELETE', "Deleted category ID: $id");
            set_flash('success', 'Category deleted successfully.');
        }
    } else {
        set_flash('danger', 'Security verification failed.');
    }
    header('Location: ' . ADMIN_URL . '/categories.php');
    exit;
}

// Handle Quick Toggle (Status / Featured)
if (isset($_GET['action']) && in_array($_GET['action'], ['toggle_status', 'toggle_featured']) && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $field = ($_GET['action'] === 'toggle_featured') ? 'is_featured' : 'status';
        
        $pdo->query("UPDATE categories SET $field = 1 - $field WHERE id = $id");
        set_flash('success', 'Category updated successfully.');
    }
    header('Location: ' . ADMIN_URL . '/categories.php');
    exit;
}

// Search filter
$search = trim($_GET['search'] ?? '');
$sql = "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count FROM categories c WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (c.name LIKE ? OR c.slug LIKE ? OR c.description LIKE ?)";
    $term = "%$search%";
    $params = [$term, $term, $term];
}

$sql .= " ORDER BY c.sort_order ASC, c.id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$categories = $stmt->fetchAll();
?>

<!-- Page Header & Action Bar -->
<div class="card mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <form action="" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search categories by name or slug..." value="<?= e($search) ?>" style="max-width: 300px;">
                    <button type="submit" class="btn btn-sm btn-primary-a2z"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <?php if (!empty($search)): ?>
                        <a href="<?= ADMIN_URL ?>/categories.php" class="btn btn-sm btn-light">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="<?= ADMIN_URL ?>/category-add.php" class="btn btn-primary-a2z btn-sm shadow-sm">
                    <i class="fa-solid fa-plus-circle me-1"></i> Add New Category
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Category Listing Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <span class="fw-bold"><i class="fa-solid fa-layer-group text-primary me-2"></i>All Categories (<?= count($categories) ?>)</span>
        <span class="text-muted small">Manage your product collections & SEO metadata</span>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 80px;">Image</th>
                    <th>Category Name & Slug</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Order</th>
                    <th>Featured</th>
                    <th>Status</th>
                    <th style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fs-2 mb-2 d-block"></i>
                            No categories found. Click "Add New Category" above to create one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <img src="<?= e(resolve_image_url($cat['image'])) ?>" class="table-img-thumb" alt="<?= e($cat['name']) ?>">
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= e($cat['name']) ?></div>
                                <div class="small text-muted">
                                    <a href="<?= BASE_URL ?>/category/<?= e($cat['slug']) ?>" target="_blank" class="text-decoration-none text-info">
                                        <i class="fa-solid fa-link me-1"></i>/category/<?= e($cat['slug']) ?>
                                    </a>
                                </div>
                                <?php if (!empty($cat['meta_title'])): ?>
                                    <div class="badge bg-light text-secondary border mt-1" title="SEO Title configured">
                                        <i class="fa-solid fa-check text-success me-1"></i>Yoast SEO
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 260px;">
                                    <?= e($cat['description'] ?? 'No description provided.') ?>
                                </small>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/products.php?category_id=<?= $cat['id'] ?>" class="badge bg-light text-dark border text-decoration-none" title="View products in this category">
                                    <i class="fa-solid fa-boxes-stacked me-1"></i><?= $cat['product_count'] ?> items
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= $cat['sort_order'] ?></span>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/categories.php?action=toggle_featured&id=<?= $cat['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-sm btn-light border p-1" title="Click to toggle featured">
                                    <i class="fa-<?= ($cat['is_featured']) ? 'solid text-warning' : 'regular text-muted' ?> fa-star"></i>
                                </a>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/categories.php?action=toggle_status&id=<?= $cat['id'] ?>&token=<?= csrf_token() ?>" class="badge text-decoration-none <?= ($cat['status'] == 1) ? 'bg-success' : 'bg-secondary' ?>" title="Click to toggle active status">
                                    <?= ($cat['status'] == 1) ? 'Active' : 'Draft' ?>
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= ADMIN_URL ?>/category-edit.php?id=<?= $cat['id'] ?>" class="btn btn-outline-primary" title="Edit Category">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <a href="<?= ADMIN_URL ?>/categories.php?action=delete&id=<?= $cat['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-outline-danger" onclick="return confirmDelete('Are you sure you want to delete this category?');" title="Delete Category">
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
