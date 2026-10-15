<?php
$pageTitle = 'Admin Users Management';
require_once __DIR__ . '/includes/header.php';

global $pdo;

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

// Handle Status Toggle
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $id = (int)$_GET['id'];
        if ($id === (int)$_SESSION['admin_user_id']) {
            set_flash('danger', 'You cannot disable your own active account.');
        } else {
            $pdo->query("UPDATE users SET status = 1 - status WHERE id = $id");
            set_flash('success', 'User status updated.');
        }
    }
    header('Location: ' . ADMIN_URL . '/users.php');
    exit;
}

// Search & Role Filter
$search = trim($_GET['search'] ?? '');
$roleFilter = $_GET['role'] ?? '';

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($roleFilter)) {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
}

if (!empty($search)) {
    $sql .= " AND (full_name LIKE ? OR username LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $term = "%$search%";
    $params = array_merge($params, [$term, $term, $term, $term]);
}

$sql .= " ORDER BY id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<!-- Header & Action Bar -->
<div class="card mb-4 shadow-sm">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-md-7">
                <form action="" method="GET" class="d-flex flex-wrap gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, username, email..." value="<?= e($search) ?>" style="max-width: 250px;">
                    <select name="role" class="form-select form-select-sm" style="max-width: 160px;">
                        <option value="">All Roles</option>
                        <option value="admin" <?= ($roleFilter === 'admin') ? 'selected' : '' ?>>Admin</option>
                        <option value="manager" <?= ($roleFilter === 'manager') ? 'selected' : '' ?>>Manager</option>
                        <option value="editor" <?= ($roleFilter === 'editor') ? 'selected' : '' ?>>Editor</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary-a2z"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <?php if (!empty($search) || !empty($roleFilter)): ?>
                        <a href="<?= ADMIN_URL ?>/users.php" class="btn btn-sm btn-light">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-5 text-md-end">
                <a href="<?= ADMIN_URL ?>/user-add.php" class="btn btn-primary-a2z btn-sm shadow-sm">
                    <i class="fa-solid fa-user-plus me-1"></i> Add New Admin User
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Users Listing Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <span class="fw-bold"><i class="fa-solid fa-users-gear text-primary me-2"></i>Administrative Team & Staff (<?= count($users) ?>)</span>
        <span class="text-muted small">Team members with secure login access</span>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>User & Login ID</th>
                    <th>Contact Info</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Active</th>
                    <th style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-users fs-2 mb-2 d-block"></i>
                            No users found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= e($u['full_name']) ?></div>
                                <small class="text-muted"><code>@<?= e($u['username']) ?></code></small>
                                <?php if ($u['id'] === (int)$_SESSION['admin_user_id']): ?>
                                    <span class="badge bg-info bg-opacity-25 text-dark border border-info ms-1">You</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><small class="text-dark fw-medium"><i class="fa-regular fa-envelope me-1 text-primary"></i><?= e($u['email']) ?></small></div>
                                <?php if (!empty($u['phone'])): ?>
                                    <div><small class="text-muted"><i class="fa-solid fa-phone me-1 text-secondary"></i><?= e($u['phone']) ?></small></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $roleBadge = 'bg-primary';
                                if ($u['role'] === 'manager') $roleBadge = 'bg-info text-dark';
                                if ($u['role'] === 'editor') $roleBadge = 'bg-secondary';
                                ?>
                                <span class="badge <?= $roleBadge ?>"><?= strtoupper($u['role']) ?></span>
                            </td>
                            <td>
                                <?php if ($u['id'] === (int)$_SESSION['admin_user_id']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <a href="<?= ADMIN_URL ?>/users.php?action=toggle_status&id=<?= $u['id'] ?>&token=<?= csrf_token() ?>" class="badge text-decoration-none <?= ($u['status'] == 1) ? 'bg-success' : 'bg-danger' ?>" title="Click to toggle status">
                                        <?= ($u['status'] == 1) ? 'Active' : 'Disabled' ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted text-nowrap">
                                    <?= $u['last_login_at'] ? date('M d, H:i', strtotime($u['last_login_at'])) : 'Never' ?>
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= ADMIN_URL ?>/user-edit.php?id=<?= $u['id'] ?>" class="btn btn-outline-primary" title="Edit User">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <?php if ($u['id'] !== (int)$_SESSION['admin_user_id']): ?>
                                        <a href="<?= ADMIN_URL ?>/users.php?action=delete&id=<?= $u['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-outline-danger" onclick="return confirmDelete('Are you sure you want to delete this user?');" title="Delete User">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
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
