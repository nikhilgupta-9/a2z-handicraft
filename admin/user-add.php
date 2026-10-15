<?php
$pageTitle = 'Add New Admin User';
require_once __DIR__ . '/includes/header.php';

global $pdo;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = in_array($_POST['role'] ?? '', ['admin', 'manager', 'editor']) ? $_POST['role'] : 'admin';
        $status = isset($_POST['status']) ? 1 : 0;
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($email) || empty($fullName)) {
            $error = 'Full Name, Username, and Email are required.';
        } elseif (empty($password)) {
            $error = 'Password is required when creating a new user account.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            // Check username & email uniqueness
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $checkStmt->execute([$username, $email]);
            if ($checkStmt->fetch()) {
                $error = 'Username or Email is already registered with another account.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, phone, role, status, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$fullName, $username, $email, $phone, $role, $status, $hash]);

                $newId = $pdo->lastInsertId();
                log_activity('USER_CREATE', "Created user account: $username (ID: $newId)");
                set_flash('success', "New user @$username created successfully.");
                header('Location: ' . ADMIN_URL . '/users.php');
                exit;
            }
        }
    }
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="m-0 fw-bold"><i class="fa-solid fa-user-plus text-primary me-2"></i>Add New Administrator</h4>
        <small class="text-muted">Create a new login account with role-based permissions</small>
    </div>
    <a href="<?= ADMIN_URL ?>/users.php" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Users
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<form action="" method="POST" autocomplete="off">
    <?= csrf_field() ?>

    <div class="row">
        <!-- Account Info -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-id-card text-primary me-2"></i>Personal & Login Details</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Full Name*</label>
                            <input type="text" name="full_name" class="form-control" placeholder="e.g. Nikhil Gupta" value="<?= e($_POST['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Username* (Login ID)</label>
                            <input type="text" name="username" class="form-control" placeholder="e.g. nikhil_admin" value="<?= e($_POST['username'] ?? '') ?>" required>
                            <small class="text-muted">Used to sign in to the admin panel</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email Address*</label>
                            <input type="email" name="email" class="form-control" placeholder="admin@example.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 9876543210" value="<?= e($_POST['phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Account Password*</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters strong password" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role & Permissions -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <span class="fw-bold"><i class="fa-solid fa-shield-halved text-primary me-2"></i>Role & Access Control</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role Level</label>
                        <select name="role" class="form-select">
                            <option value="admin" selected>Admin (Full System Access)</option>
                            <option value="manager">Manager (Catalog, Products & Inquiries)</option>
                            <option value="editor">Editor (Content & Banners Only)</option>
                        </select>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="status" id="userStatus" checked>
                        <label class="form-check-label fw-semibold" for="userStatus">Account Active</label>
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2">
                        <i class="fa-solid fa-user-plus me-1"></i> Create User Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
