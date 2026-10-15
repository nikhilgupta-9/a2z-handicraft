<?php
$pageTitle = 'FAQs Management';
require_once __DIR__ . '/includes/header.php';

global $pdo;
$error = '';

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

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $faqId = !empty($_POST['faq_id']) ? (int)$_POST['faq_id'] : null;
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        if (empty($question) || empty($answer)) {
            $error = 'Question and Answer are required.';
        } else {
            if ($faqId) {
                $stmt = $pdo->prepare("UPDATE faqs SET question = ?, answer = ?, category = ?, sort_order = ?, status = ? WHERE id = ?");
                $stmt->execute([$question, $answer, $category, $sortOrder, $status, $faqId]);
                log_activity('FAQ_UPDATE', "Updated FAQ ID: $faqId");
                set_flash('success', 'FAQ updated successfully.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO faqs (question, answer, category, sort_order, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$question, $answer, $category, $sortOrder, $status]);
                log_activity('FAQ_CREATE', "Added FAQ: $question");
                set_flash('success', 'New FAQ added successfully.');
            }
            header('Location: ' . ADMIN_URL . '/faqs.php');
            exit;
        }
    }
}

$faqs = $pdo->query("SELECT * FROM faqs ORDER BY sort_order ASC, id ASC")->fetchAll();

$editItem = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
}
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<div class="row">
    <!-- Form Column -->
    <div class="col-lg-5 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <span><i class="fa-solid <?= $editItem ? 'fa-pen-to-square text-warning' : 'fa-plus text-primary' ?> me-2"></i><?= $editItem ? 'Edit FAQ' : 'Add FAQ' ?></span>
                <?php if ($editItem): ?>
                    <a href="<?= ADMIN_URL ?>/faqs.php" class="btn btn-sm btn-outline-secondary">Cancel</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <?= csrf_field() ?>
                    <?php if ($editItem): ?>
                        <input type="hidden" name="faq_id" value="<?= $editItem['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Question*</label>
                        <input type="text" name="question" class="form-control" value="<?= e($editItem['question'] ?? '') ?>" required placeholder="e.g. Do you accept bulk wholesale orders?">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category / Topic</label>
                        <input type="text" name="category" class="form-control" value="<?= e($editItem['category'] ?? 'General') ?>" placeholder="e.g. Orders, Customization, Shipping">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Answer Explanation*</label>
                        <textarea name="answer" class="form-control" rows="4" required placeholder="Write clear answer for buyers and decorators..."><?= e($editItem['answer'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Display Order</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= e($editItem['sort_order'] ?? 1) ?>" min="0">
                        </div>
                        <div class="col-6 mb-3 pt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status" id="faqStatus" <?= (!isset($editItem) || $editItem['status'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="faqStatus">Active</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> <?= $editItem ? 'Update FAQ' : 'Save FAQ' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Table Column -->
    <div class="col-lg-7 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <span><i class="fa-solid fa-circle-question text-primary me-2"></i>Frequently Asked Questions (<?= count($faqs) ?>)</span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Question & Answer</th>
                            <th>Topic</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($faqs)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No FAQs recorded.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($faqs as $f): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?= e($f['question']) ?></div>
                                        <small class="text-muted text-truncate d-inline-block" style="max-width: 320px;"><?= e($f['answer']) ?></small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= e($f['category']) ?></span></td>
                                    <td><?= $f['sort_order'] ?></td>
                                    <td>
                                        <?php if ($f['status'] == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Hidden</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= ADMIN_URL ?>/faqs.php?edit=<?= $f['id'] ?>" class="btn btn-light" title="Edit">
                                                <i class="fa-solid fa-pen text-primary"></i>
                                            </a>
                                            <a href="<?= ADMIN_URL ?>/faqs.php?action=delete&id=<?= $f['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-light" onclick="return confirmDelete('Delete this FAQ?');" title="Delete">
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
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
