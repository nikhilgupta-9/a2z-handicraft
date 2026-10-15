<?php
$pageTitle = 'Edit FAQ';
require_once __DIR__ . '/includes/header.php';

global $pdo;

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
$stmt->execute([$id]);
$faq = $stmt->fetch();

if (!$faq) {
    set_flash('danger', 'FAQ not found.');
    header('Location: ' . ADMIN_URL . '/faqs.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        if (empty($question) || empty($answer)) {
            $error = 'Question and Answer are required fields.';
        } else {
            $stmt = $pdo->prepare("UPDATE faqs SET question = ?, answer = ?, category = ?, sort_order = ?, status = ? WHERE id = ?");
            $stmt->execute([$question, $answer, $category, $sortOrder, $status, $id]);

            log_activity('FAQ_UPDATE', "Updated FAQ ID: $id");
            set_flash('success', 'FAQ updated successfully.');
            header('Location: ' . ADMIN_URL . '/faqs.php');
            exit;
        }
    }
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="m-0 fw-bold"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit FAQ</h4>
        <small class="text-muted">Modify question, explanation or category for FAQ #<?= $id ?></small>
    </div>
    <a href="<?= ADMIN_URL ?>/faqs.php" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to FAQs
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<form action="" method="POST">
    <?= csrf_field() ?>

    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-circle-question text-primary me-2"></i>Question & Answer Content</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Question*</label>
                        <input type="text" name="question" class="form-control" value="<?= e($faq['question']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Topic / Category</label>
                        <input type="text" name="category" class="form-control" list="categoryOptions" value="<?= e($faq['category'] ?? 'General') ?>">
                        <datalist id="categoryOptions">
                            <option value="Orders & Booking">
                            <option value="Customization">
                            <option value="Shipping & Delivery">
                            <option value="Bulk Wholesale">
                            <option value="Payments">
                            <option value="General">
                        </datalist>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Detailed Answer*</label>
                        <textarea name="answer" class="form-control" rows="6" required><?= e($faq['answer']) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Settings -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-sliders text-primary me-2"></i>Display Settings</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= $faq['sort_order'] ?>">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="status" id="faqStatus" <?= ($faq['status'] == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="faqStatus">Active & Visible</label>
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Update FAQ
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
