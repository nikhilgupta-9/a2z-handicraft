<?php
$pageTitle = 'Dashboard Overview';
require_once __DIR__ . '/includes/header.php';

global $pdo;

// Fetch Dashboard Metrics
$totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$activeProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status = 1")->fetchColumn();

$totalCategories = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalSubcategories = (int)$pdo->query("SELECT COUNT(*) FROM subcategories")->fetchColumn();

$totalLeads = (int)$pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$newLeads = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE is_read = 0")->fetchColumn();

$totalReviews = (int)$pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$pendingReviews = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();

// Fetch Recent Leads
$recentLeads = $pdo->query("SELECT * FROM leads ORDER BY id DESC LIMIT 5")->fetchAll();

// Fetch Recent Products
$recentProducts = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 5")->fetchAll();

// Fetch Recent Reviews
$recentReviews = $pdo->query("SELECT r.*, p.name as product_name FROM reviews r LEFT JOIN products p ON r.product_id = p.id ORDER BY r.id DESC LIMIT 5")->fetchAll();
?>

<!-- KPI Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-title">Total Products</div>
                <div class="stat-value text-primary"><?= $totalProducts ?></div>
                <small class="text-success"><i class="fa-solid fa-circle-check"></i> <?= $activeProducts ?> Active in store</small>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-title">Categories</div>
                <div class="stat-value text-success"><?= $totalCategories ?></div>
                <small class="text-muted"><i class="fa-solid fa-tags"></i> <?= $totalSubcategories ?> Subcategories</small>
            </div>
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="fa-solid fa-layer-group"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-title">Leads & Inquiries</div>
                <div class="stat-value text-warning"><?= $totalLeads ?></div>
                <small class="<?= ($newLeads > 0) ? 'text-danger fw-bold' : 'text-muted' ?>">
                    <i class="fa-solid fa-bell"></i> <?= $newLeads ?> Unread Leads
                </small>
            </div>
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                <i class="fa-solid fa-envelope-open-text"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-title">Customer Reviews</div>
                <div class="stat-value text-info"><?= $totalReviews ?></div>
                <small class="text-muted"><i class="fa-solid fa-clock"></i> <?= $pendingReviews ?> Pending Moderation</small>
            </div>
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <i class="fa-solid fa-star"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Banner -->
<div class="card mb-4">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
        <div>
            <h6 class="m-0 fw-bold"><i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i>Quick Administration Actions</h6>
            <small class="text-muted">Shortcuts to manage inventory, catalog, and site presentation</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= ADMIN_URL ?>/product-add.php" class="btn btn-sm btn-primary-a2z">
                <i class="fa-solid fa-plus me-1"></i> Add Product
            </a>
            <a href="<?= ADMIN_URL ?>/category-add.php" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-folder-plus me-1"></i> Add Category
            </a>
            <a href="<?= ADMIN_URL ?>/banners.php" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-image me-1"></i> Add Banner
            </a>
            <a href="<?= ADMIN_URL ?>/leads.php" class="btn btn-sm btn-outline-primary">
                <i class="fa-solid fa-inbox me-1"></i> View Inquiries
            </a>
            <a href="<?= ADMIN_URL ?>/settings.php" class="btn btn-sm btn-outline-dark">
                <i class="fa-solid fa-sliders me-1"></i> Site Settings
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Leads -->
    <div class="col-xl-7 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <span><i class="fa-solid fa-envelope text-primary me-2"></i>Recent Contact Leads & Inquiries</span>
                <a href="<?= ADMIN_URL ?>/leads.php" class="btn btn-sm btn-light">View All</a>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentLeads)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No inquiries received yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentLeads as $lead): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= e($lead['name']) ?></div>
                                        <small class="text-muted"><?= e($lead['email']) ?></small>
                                    </td>
                                    <td>
                                        <a href="tel:<?= e($lead['phone']) ?>" class="badge bg-light text-dark text-decoration-none border">
                                            <i class="fa-solid fa-phone me-1"></i><?= e($lead['phone']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 180px;">
                                            <?= e($lead['subject'] ?? 'Website Inquiry') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($lead['is_read'] == 0): ?>
                                            <span class="badge bg-danger">New</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Read</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small class="text-muted"><?= date('M d, H:i', strtotime($lead['created_at'])) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Products -->
    <div class="col-xl-5 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <span><i class="fa-solid fa-box text-success me-2"></i>Recent Products</span>
                <a href="<?= ADMIN_URL ?>/products.php" class="btn btn-sm btn-light">Manage</a>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentProducts)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No products found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentProducts as $prod): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= e(resolve_image_url($prod['featured_image'])) ?>" class="table-img-thumb" alt="Product">
                                            <div>
                                                <div class="fw-bold text-truncate" style="max-width: 140px;"><?= e($prod['name']) ?></div>
                                                <small class="text-muted"><?= e($prod['category_name'] ?? 'General') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="fw-semibold"><?= format_price($prod['price']) ?></span></td>
                                    <td>
                                        <?php if ($prod['in_stock']): ?>
                                            <span class="badge bg-success">In Stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Out of Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= ADMIN_URL ?>/product-edit.php?id=<?= $prod['id'] ?>" class="btn btn-sm btn-light" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
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

<div class="row">
    <!-- Customer Reviews -->
    <div class="col-xl-8 mb-4">
        <div class="card">
            <div class="card-header">
                <span><i class="fa-solid fa-star text-warning me-2"></i>Recent Reviews</span>
                <a href="<?= ADMIN_URL ?>/reviews.php" class="btn btn-sm btn-light">Manage Reviews</a>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Rating</th>
                            <th>Feedback</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentReviews)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No reviews recorded yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentReviews as $rev): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($rev['author_name']) ?></strong>
                                        <?php if (!empty($rev['product_name'])): ?>
                                            <div class="small text-muted">on <em><?= e($rev['product_name']) ?></em></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-warning">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fa-<?= ($i <= $rev['rating']) ? 'solid' : 'regular' ?> fa-star"></i>
                                            <?php endfor; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 320px;">
                                            <?= e($rev['comment']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($rev['status'] === 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php elseif ($rev['status'] === 'rejected'): ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Server & System Environment -->
    <div class="col-xl-4 mb-4">
        <div class="card">
            <div class="card-header">
                <span><i class="fa-solid fa-server text-secondary me-2"></i>System & Environment</span>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Environment</span>
                        <span class="badge bg-primary"><?= e($_ENV['APP_ENV'] ?? 'development') ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">PHP Version</span>
                        <span class="fw-semibold"><?= PHP_VERSION ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Database Server</span>
                        <span class="fw-semibold">MySQL (PDO)</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Max Upload Size</span>
                        <span class="fw-semibold"><?= ini_get('upload_max_filesize') ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Server Time</span>
                        <span class="small text-muted"><?= date('Y-m-d H:i:s') ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
