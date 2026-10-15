<?php
$pageTitle = 'FAQs Management';
require_once __DIR__ . '/includes/header.php';

global $pdo;

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
        $stmt->execute([$id]);

        log_activity('FAQ_DELETE', "Deleted FAQ ID: $id");
        set_flash('success', 'FAQ deleted successfully.');
    } else {
        set_flash('danger', 'Security verification failed.');
    }
    header('Location: ' . ADMIN_URL . '/faqs.php');
    exit;
}

// Handle Status Toggle
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        $pdo->query("UPDATE faqs SET status = 1 - status WHERE id = $id");
        set_flash('success', 'FAQ status updated.');
    }
    header('Location: ' . ADMIN_URL . '/faqs.php');
    exit;
}

// Filters
$categoryFilter = $_GET['category'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM faqs WHERE 1=1";
$params = [];

if (!empty($categoryFilter)) {
    $sql .= " AND category = ?";
    $params[] = $categoryFilter;
}

if (!empty($search)) {
    $sql .= " AND (question LIKE ? OR answer LIKE ? OR category LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql .= " ORDER BY sort_order ASC, id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$faqs = $stmt->fetchAll();

// Unique categories for filter dropdown
$faqCategories = $pdo->query("SELECT DISTINCT category FROM faqs WHERE category != '' ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- Header & Action Bar -->
<div class="card mb-4 shadow-sm">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-md-7">
                <form action="" method="GET" class="d-flex flex-wrap gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search question or keyword..." value="<?= e($search) ?>" style="max-width: 260px;">
                    <select name="category" class="form-select form-select-sm" style="max-width: 180px;">
                        <option value="">All Categories</option>
                        <?php foreach ($faqCategories as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= ($categoryFilter === $cat) ? 'selected' : '' ?>><?= e($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary-a2z"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <?php if (!empty($search) || !empty($categoryFilter)): ?>
                        <a href="<?= ADMIN_URL ?>/faqs.php" class="btn btn-sm btn-light">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-5 text-md-end">
                <a href="<?= ADMIN_URL ?>/faq-add.php" class="btn btn-primary-a2z btn-sm shadow-sm">
                    <i class="fa-solid fa-plus-circle me-1"></i> Add New FAQ
                </a>
            </div>
        </div>
    </div>
</div>

<!-- FAQs Listing Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <span class="fw-bold"><i class="fa-solid fa-circle-question text-primary me-2"></i>Frequently Asked Questions (<?= count($faqs) ?>)</span>
        <span class="text-muted small">Shown in the help/FAQ accordions</span>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Question</th>
                    <th>Category</th>
                    <th>Answer Excerpt</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($faqs)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-circle-question fs-2 mb-2 d-block"></i>
                            No FAQs found. Click "Add New FAQ" above to create one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($faqs as $i => $f): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= e($f['question']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                    <?= e($f['category'] ?? 'General') ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted d-inline-block text-truncate" style="max-width: 340px;" title="<?= e($f['answer']) ?>">
                                    <?= e($f['answer']) ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= $f['sort_order'] ?></span>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/faqs.php?action=toggle_status&id=<?= $f['id'] ?>&token=<?= csrf_token() ?>" class="badge text-decoration-none <?= ($f['status'] == 1) ? 'bg-success' : 'bg-secondary' ?>" title="Click to toggle status">
                                    <?= ($f['status'] == 1) ? 'Active' : 'Disabled' ?>
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= ADMIN_URL ?>/faq-edit.php?id=<?= $f['id'] ?>" class="btn btn-outline-primary" title="Edit FAQ">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <a href="<?= ADMIN_URL ?>/faqs.php?action=delete&id=<?= $f['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-outline-danger" onclick="return confirmDelete('Are you sure you want to delete this FAQ?');" title="Delete FAQ">
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
