<?php
$pageTitle = 'Add New Inquiry / Lead';
require_once __DIR__ . '/includes/header.php';

global $pdo;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? 'Manual / Phone Inquiry');
        $message = trim($_POST['message'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['new', 'contacted', 'resolved', 'spam']) ? $_POST['status'] : 'new';

        if (empty($name) || empty($message)) {
            $error = 'Customer name and message/requirement details are required.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, subject, message, status, is_read, ip_address) VALUES (?, ?, ?, ?, ?, ?, 1, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message, $status, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

            log_activity('LEAD_CREATE', "Manually logged lead from $name");
            set_flash('success', 'Customer inquiry recorded successfully.');
            header('Location: ' . ADMIN_URL . '/leads.php');
            exit;
        }
    }
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="m-0 fw-bold"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Add Customer Inquiry / Lead</h4>
        <small class="text-muted">Record walk-in, phone, or offline wholesale inquiry</small>
    </div>
    <a href="<?= ADMIN_URL ?>/leads.php" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Leads
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
                    <span class="fw-bold"><i class="fa-solid fa-envelope-open-text text-primary me-2"></i>Customer Inquiry Details</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Customer Full Name*</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Ramesh Patel" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Phone Number / WhatsApp</label>
                            <input type="text" name="phone" class="form-control" placeholder="e.g. +91 9876543210">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="customer@example.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Subject / Purpose</label>
                            <input type="text" name="subject" class="form-control" value="Wedding Decoration Wholesale Requirement">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Inquiry Notes / Customer Message*</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Specify products requested, quantities, delivery deadline, budget..." required></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Status -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-sliders text-primary me-2"></i>Lead Status</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Status</label>
                        <select name="status" class="form-select">
                            <option value="new" selected>New Inquiry</option>
                            <option value="contacted">Contacted / Follow-up</option>
                            <option value="resolved">Resolved / Order Placed</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Customer Lead
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
