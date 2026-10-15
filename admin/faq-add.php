<?php
$pageTitle = 'Add New FAQ';
require_once __DIR__ . '/includes/header.php';

global $pdo;
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
            $stmt = $pdo->prepare("INSERT INTO faqs (question, answer, category, sort_order, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$question, $answer, $category, $sortOrder, $status]);

            log_activity('FAQ_CREATE', "Added FAQ: $question");
            set_flash('success', 'New FAQ added successfully.');
            header('Location: ' . ADMIN_URL . '/faqs.php');
            exit;
        }
    }
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="m-0 fw-bold"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Add New FAQ</h4>
        <small class="text-muted">Create a question and answer for buyers, event planners and wholesale decorators</small>
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
                        <input type="text" name="question" class="form-control" placeholder="e.g. Do you accept customized bulk orders for weddings?" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Topic / Category</label>
                        <input type="text" name="category" class="form-control" list="categoryOptions" placeholder="e.g. Orders, Customization, Shipping, Bulk Inquiry" value="General">
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
                        <textarea name="answer" class="form-control" rows="6" placeholder="Provide a helpful, complete answer for customers..." required></textarea>
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
                        <input type="number" name="sort_order" class="form-control" value="0">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="status" id="faqStatus" checked>
                        <label class="form-check-label fw-semibold" for="faqStatus">Active & Visible</label>
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save New FAQ
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
