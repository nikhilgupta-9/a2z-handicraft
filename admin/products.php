<?php
$pageTitle = 'Products Management';
require_once __DIR__ . '/includes/header.php';

global $pdo;

// Handle Product Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT featured_image, gallery_images FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $prod = $stmt->fetch();

        if ($prod) {
            delete_uploaded_file($prod['featured_image']);
            if (!empty($prod['gallery_images'])) {
                $gallery = json_decode($prod['gallery_images'], true) ?: [];
                foreach ($gallery as $img) {
                    delete_uploaded_file($img);
                }
            }
        }

        $delStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $delStmt->execute([$id]);

        log_activity('PRODUCT_DELETE', "Deleted product ID: $id");
        set_flash('success', 'Product removed successfully.');
    } else {
        set_flash('danger', 'Security token invalid.');
    }
    header('Location: ' . ADMIN_URL . '/products.php');
    exit;
}

// Handle Quick Toggle (In-Stock / Status / Featured)
if (isset($_GET['action']) && in_array($_GET['action'], ['toggle_stock', 'toggle_status', 'toggle_featured']) && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $field = ($_GET['action'] === 'toggle_stock') ? 'in_stock' : (($_GET['action'] === 'toggle_featured') ? 'is_featured' : 'status');
        
        $pdo->query("UPDATE products SET $field = 1 - $field WHERE id = $id");
        set_flash('success', 'Product state updated.');
    }
    header('Location: ' . ADMIN_URL . '/products.php');
    exit;
}

// Filter variables
$search = request_string($_GET, 'search');
$categoryId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;
$subcategoryId = !empty($_GET['subcategory_id']) ? (int)$_GET['subcategory_id'] : null;

$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($categoryId)) {
    $query .= " AND p.category_id = ?";
    $params[] = $categoryId;
}

if (!empty($subcategoryId)) {
    $query .= " AND p.subcategory_id = ?";
    $params[] = $subcategoryId;
}

$query .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Categories for filter dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
?>

<div class="card mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <form action="" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name or SKU..." value="<?= e($search) ?>">
                    <select name="category_id" class="form-select form-select-sm" style="max-width: 200px;">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($categoryId == $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary-a2z"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <?php if (!empty($subcategoryId)): ?>
                        <input type="hidden" name="subcategory_id" value="<?= $subcategoryId ?>">
                    <?php endif; ?>
                    <?php if (!empty($search) || !empty($categoryId) || !empty($subcategoryId)): ?>
                        <a href="<?= ADMIN_URL ?>/products.php" class="btn btn-sm btn-light">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="<?= ADMIN_URL ?>/product-add.php" class="btn btn-primary-a2z btn-sm">
                    <i class="fa-solid fa-plus me-1"></i> Add New Product
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <span><i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Product Catalog (<?= count($products) ?> items)</span>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Item Details</th>
                    <th>Category</th>
                    <th>Pricing</th>
                    <th>Stock</th>
                    <th>Featured</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No products found matching criteria.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= e(resolve_image_url($p['featured_image'])) ?>" class="table-img-thumb" alt="Product">
                                    <div>
                                        <div class="fw-bold"><?= e($p['name']) ?></div>
                                        <small class="text-muted">SKU: <code><?= e($p['sku'] ?? 'N/A') ?></code></small>
                                        <div class="small"><a href="<?= BASE_URL ?>/product/<?= e($p['slug']) ?>" target="_blank" class="text-decoration-none text-info"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>/product/<?= e($p['slug']) ?></a></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= e($p['category_name'] ?? 'Unassigned') ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-success"><?= format_price($p['price']) ?></div>
                                <?php if (!empty($p['sale_price'])): ?>
                                    <small class="text-muted text-decoration-line-through"><?= format_price($p['sale_price']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/products.php?action=toggle_stock&id=<?= $p['id'] ?>&token=<?= csrf_token() ?>" class="badge text-decoration-none <?= ($p['in_stock']) ? 'bg-success' : 'bg-danger' ?>" title="Click to toggle stock status">
                                    <?= ($p['in_stock']) ? 'In Stock' : 'Out of Stock' ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/products.php?action=toggle_featured&id=<?= $p['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-sm btn-light border p-1" title="Toggle Featured">
                                    <i class="fa-<?= ($p['is_featured']) ? 'solid text-warning' : 'regular text-muted' ?> fa-star"></i>
                                </a>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/products.php?action=toggle_status&id=<?= $p['id'] ?>&token=<?= csrf_token() ?>" class="badge text-decoration-none <?= ($p['status']) ? 'bg-success' : 'bg-secondary' ?>" title="Click to toggle active status">
                                    <?= ($p['status']) ? 'Active' : 'Draft' ?>
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= ADMIN_URL ?>/product-edit.php?id=<?= $p['id'] ?>" class="btn btn-light" title="Edit">
                                        <i class="fa-solid fa-pen text-primary"></i>
                                    </a>
                                    <a href="<?= ADMIN_URL ?>/products.php?action=delete&id=<?= $p['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-light" onclick="return confirmDelete('Are you sure you want to delete this product?');" title="Delete">
                                        <i class="fa-solid fa-trash text-danger"></i>
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
