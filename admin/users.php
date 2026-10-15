<?php
$pageTitle = 'Admin Users Management';
require_once __DIR__ . '/includes/header.php';

global $pdo;
$error = '';

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        
        // Cannot delete self
        if ($id === (int)$_SESSION['admin_user_id']) {
            set_flash('danger', 'You cannot delete your own active administrator account.');
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            log_activity('USER_DELETE', "Deleted user account ID: $id");
            set_flash('success', 'User account deleted successfully.');
        }
    } else {
        set_flash('danger', 'Security verification failed.');
    }
    header('Location: ' . ADMIN_URL . '/users.php');
    exit;
}

// Handle Add / Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $userId = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = in_array($_POST['role'] ?? '', ['admin', 'manager', 'editor']) ? $_POST['role'] : 'admin';
        $status = isset($_POST['status']) ? 1 : 0;
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($email) || empty($fullName)) {
            $error = 'Full Name, Username, and Email are required.';
        } elseif (!$userId && empty($password)) {
            $error = 'Password is required when creating a new user.';
        } elseif (!empty($password) && strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            // Check username & email uniqueness
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) " . ($userId ? "AND id != $userId" : ""));
            $checkStmt->execute([$username, $email]);
            if ($checkStmt->fetch()) {
                $error = 'Username or Email is already registered with another account.';
            } else {
                if ($userId) {
                    if (!empty($password)) {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, phone = ?, role = ?, status = ?, password = ? WHERE id = ?");
                        $stmt->execute([$fullName, $username, $email, $phone, $role, $status, $hash, $userId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, phone = ?, role = ?, status = ? WHERE id = ?");
                        $stmt->execute([$fullName, $username, $email, $phone, $role, $status, $userId]);
                    }
                    log_activity('USER_UPDATE', "Updated user account: $username (ID: $userId)");
                    set_flash('success', 'User account updated successfully.');
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, phone, role, status, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$fullName, $username, $email, $phone, $role, $status, $hash]);
                    log_activity('USER_CREATE', "Created user account: $username");
                    set_flash('success', 'New user account created successfully.');
                }
                header('Location: ' . ADMIN_URL . '/users.php');
                exit;
            }
        }
    }
}

// Fetch users
$users = $pdo->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();

$editUser = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$editId]);
    $editUser = $stmt->fetch();
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
                <span><i class="fa-solid <?= $editUser ? 'fa-user-pen text-warning' : 'fa-user-plus text-primary' ?> me-2"></i><?= $editUser ? 'Edit User Account' : 'Add New Admin User' ?></span>
                <?php if ($editUser): ?>
                    <a href="<?= ADMIN_URL ?>/users.php" class="btn btn-sm btn-outline-secondary">Cancel</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form action="" method="POST" autocomplete="off">
                    <?= csrf_field() ?>
                    <?php if ($editUser): ?>
                        <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name*</label>
                        <input type="text" name="full_name" class="form-control" value="<?= e($editUser['full_name'] ?? '') ?>" required placeholder="e.g. Nikhil Gupta">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username* (Login ID)</label>
                        <input type="text" name="username" class="form-control" value="<?= e($editUser['username'] ?? '') ?>" required placeholder="e.g. nikhil_admin">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address*</label>
                        <input type="email" name="email" class="form-control" value="<?= e($editUser['email'] ?? '') ?>" required placeholder="admin@example.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?= e($editUser['phone'] ?? '') ?>" placeholder="+91 9876543210">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role Permission</label>
                        <select name="role" class="form-select">
                            <option value="admin" <?= (($editUser['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin (Full Permissions)</option>
                            <option value="manager" <?= (($editUser['role'] ?? '') === 'manager') ? 'selected' : '' ?>>Manager (Catalog & Inquiries)</option>
                            <option value="editor" <?= (($editUser['role'] ?? '') === 'editor') ? 'selected' : '' ?>>Editor (Content Only)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold"><?= $editUser ? 'Change Password (Leave blank to keep current)' : 'Password*' ?></label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" <?= $editUser ? '' : 'required' ?>>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="status" id="userStatus" <?= (!isset($editUser) || $editUser['status'] == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="userStatus">Account Active</label>
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> <?= $editUser ? 'Update Account' : 'Create User' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Table Column -->
    <div class="col-lg-7 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <span><i class="fa-solid fa-users-gear text-primary me-2"></i>Registered Administrative Users (<?= count($users) ?>)</span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Contact</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= e($u['full_name']) ?></div>
                                    <small class="text-muted">@<?= e($u['username']) ?></small>
                                </td>
                                <td>
                                    <div><small class="text-muted"><?= e($u['email']) ?></small></div>
                                    <?php if (!empty($u['phone'])): ?>
                                        <small class="text-muted"><?= e($u['phone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= strtoupper($u['role']) ?></span>
                                </td>
                                <td>
                                    <?php if ($u['status'] == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Disabled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?= $u['last_login_at'] ? date('M d, H:i', strtotime($u['last_login_at'])) : 'Never' ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= ADMIN_URL ?>/users.php?edit=<?= $u['id'] ?>" class="btn btn-light" title="Edit">
                                            <i class="fa-solid fa-pen text-primary"></i>
                                        </a>
                                        <?php if ($u['id'] !== (int)$_SESSION['admin_user_id']): ?>
                                            <a href="<?= ADMIN_URL ?>/users.php?action=delete&id=<?= $u['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-light" onclick="return confirmDelete('Delete this user?');" title="Delete">
                                                <i class="fa-solid fa-trash text-danger"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
