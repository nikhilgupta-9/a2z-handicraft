<?php
$pageTitle = 'Edit Admin User';
require_once __DIR__ . '/includes/header.php';

global $pdo;

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('danger', 'User account not found.');
    header('Location: ' . ADMIN_URL . '/users.php');
    exit;
}

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

        // Prevent disabling or downgrading own role
        if ($id === (int)$_SESSION['admin_user_id']) {
            $status = 1;
            $role = 'admin';
        }

        if (empty($username) || empty($email) || empty($fullName)) {
            $error = 'Full Name, Username, and Email are required.';
        } elseif (!empty($password) && strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            // Check username & email uniqueness
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $checkStmt->execute([$username, $email, $id]);
            if ($checkStmt->fetch()) {
                $error = 'Username or Email is already registered with another account.';
            } else {
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, phone = ?, role = ?, status = ?, password = ? WHERE id = ?");
                    $stmt->execute([$fullName, $username, $email, $phone, $role, $status, $hash, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, phone = ?, role = ?, status = ? WHERE id = ?");
                    $stmt->execute([$fullName, $username, $email, $phone, $role, $status, $id]);
                }

                log_activity('USER_UPDATE', "Updated user account: $username (ID: $id)");
                set_flash('success', "User account @$username updated successfully.");
                header('Location: ' . ADMIN_URL . '/users.php');
                exit;
            }
        }
    }
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="m-0 fw-bold"><i class="fa-solid fa-user-pen text-primary me-2"></i>Edit Administrator</h4>
        <small class="text-muted">Modify account details for @<?= e($user['username']) ?></small>
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
                            <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Username* (Login ID)</label>
                            <input type="text" name="username" class="form-control" value="<?= e($user['username']) ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email Address*</label>
                            <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?= e($user['phone']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Change Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep existing password">
                        <small class="text-muted">Only fill this if you want to reset this user's password</small>
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
                        <select name="role" class="form-select" <?= ($id === (int)$_SESSION['admin_user_id']) ? 'disabled' : '' ?>>
                            <option value="admin" <?= ($user['role'] === 'admin') ? 'selected' : '' ?>>Admin (Full System Access)</option>
                            <option value="manager" <?= ($user['role'] === 'manager') ? 'selected' : '' ?>>Manager (Catalog, Products & Inquiries)</option>
                            <option value="editor" <?= ($user['role'] === 'editor') ? 'selected' : '' ?>>Editor (Content & Banners Only)</option>
                        </select>
                        <?php if ($id === (int)$_SESSION['admin_user_id']): ?>
                            <small class="text-muted">You cannot change your own administrator role.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="status" id="userStatus" <?= ($user['status'] == 1) ? 'checked' : '' ?> <?= ($id === (int)$_SESSION['admin_user_id']) ? 'disabled' : '' ?>>
                        <label class="form-check-label fw-semibold" for="userStatus">Account Active</label>
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Update User Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
