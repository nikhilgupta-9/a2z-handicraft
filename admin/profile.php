<?php
$pageTitle = 'My Profile & Security';
require_once __DIR__ . '/includes/header.php';

global $pdo;
$error = '';
$user = current_admin_user();
$userId = (int)$_SESSION['admin_user_id'];

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $actionType = $_POST['action_type'] ?? '';

        if ($actionType === 'update_profile') {
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            if (empty($fullName) || empty($email)) {
                $error = 'Full Name and Email Address are required.';
            } else {
                // Verify email uniqueness
                $emailCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $emailCheck->execute([$email, $userId]);
                if ($emailCheck->fetch()) {
                    $error = 'Email address is already in use by another account.';
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
                    $stmt->execute([$fullName, $email, $phone, $userId]);

                    $_SESSION['admin_full_name'] = $fullName;
                    log_activity('PROFILE_UPDATE', 'Updated personal profile details');
                    set_flash('success', 'Profile information updated successfully.');
                    header('Location: ' . ADMIN_URL . '/profile.php');
                    exit;
                }
            }
        } elseif ($actionType === 'change_password') {
            $currentPass = $_POST['current_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';

            // Verify current password from DB
            $passStmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $passStmt->execute([$userId]);
            $hash = $passStmt->fetchColumn();

            if (!password_verify($currentPass, $hash)) {
                $error = 'Current password does not match records.';
            } elseif (strlen($newPass) < 6) {
                $error = 'New password must be at least 6 characters long.';
            } elseif ($newPass !== $confirmPass) {
                $error = 'New password and confirmation do not match.';
            } else {
                $newHash = password_hash($newPass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$newHash, $userId]);

                log_activity('PASSWORD_CHANGE', 'Password successfully changed');
                set_flash('success', 'Your password has been changed securely.');
                header('Location: ' . ADMIN_URL . '/profile.php');
                exit;
            }
        }
    }
}
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<div class="row">
    <!-- Profile Info -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <span class="fw-bold"><i class="fa-solid fa-user-circle text-primary me-2"></i>Edit Profile Information</span>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action_type" value="update_profile">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Login Username</label>
                        <input type="text" class="form-control bg-light" value="<?= e($user['username']) ?>" disabled>
                        <small class="text-muted">Username is locked for account security.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role Authority</label>
                        <input type="text" class="form-control bg-light" value="<?= strtoupper(e($user['role'])) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name*</label>
                        <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address*</label>
                        <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Contact Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
                    </div>

                    <button type="submit" class="btn btn-primary-a2z w-100 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Update Profile Details
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Password Change -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <span class="fw-bold text-danger"><i class="fa-solid fa-lock me-2"></i>Security & Change Password</span>
            </div>
            <div class="card-body">
                <form action="" method="POST" autocomplete="off">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action_type" value="change_password">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Password*</label>
                        <input type="password" name="current_password" class="form-control" required placeholder="Enter existing password">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password*</label>
                        <input type="password" name="new_password" class="form-control" required placeholder="Minimum 6 characters">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirm New Password*</label>
                        <input type="password" name="confirm_password" class="form-control" required placeholder="Re-type new password">
                    </div>

                    <button type="submit" class="btn btn-outline-danger w-100 py-2">
                        <i class="fa-solid fa-key me-1"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
