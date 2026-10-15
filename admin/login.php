<?php
require_once dirname(__DIR__) . '/config/config.php';

// Redirect if already logged in
if (is_admin_logged_in()) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

$siteName = get_setting('site_name', 'A2Z Handicraft');
$siteLogo = resolve_image_url(get_setting('site_logo', 'images/logo.png'));
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } elseif (!check_login_attempts($username)) {
            $error = 'Too many failed login attempts detected. For security, access is temporarily locked for 15 minutes.';
        } else {
            // Prepared statement query by username or email
            global $pdo;
            $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) LIMIT 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ((int)$user['status'] !== 1) {
                    $error = 'This account has been deactivated. Please contact the administrator.';
                } else {
                    // Successful Authentication
                    clear_login_attempts($username);
                    
                    // Session Fixation Prevention
                    session_regenerate_id(true);

                    $_SESSION['admin_user_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_full_name'] = $user['full_name'];
                    $_SESSION['admin_role'] = $user['role'];
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_login_time'] = time();

                    // Update last login timestamp & IP
                    $ip = get_client_ip();
                    $updateStmt = $pdo->prepare("UPDATE users SET last_login_at = NOW(), last_login_ip = ?, failed_attempts = 0 WHERE id = ?");
                    $updateStmt->execute([$ip, $user['id']]);

                    // Audit Log
                    log_activity('LOGIN_SUCCESS', 'Successful administrative authentication', $user['id']);

                    set_flash('success', 'Welcome back, ' . htmlspecialchars($user['full_name']) . '!');
                    header('Location: ' . ADMIN_URL . '/index.php');
                    exit;
                }
            } else {
                // Failed login attempt
                record_login_attempt($username);
                log_activity('LOGIN_FAILED', "Failed attempt for: $username");
                $error = 'Invalid username or password. Please verify credentials.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?= e($siteName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e242b 0%, #2c343f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .login-header {
            background: #8c532b;
            padding: 30px 20px;
            text-align: center;
            color: #ffffff;
        }
        .login-header img {
            max-height: 55px;
            background: #ffffff;
            padding: 5px 12px;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        .login-body {
            padding: 35px 30px;
        }
        .form-control {
            height: 48px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
        }
        .form-control:focus {
            border-color: #8c532b;
            box-shadow: 0 0 0 3px rgba(140, 83, 43, 0.15);
        }
        .btn-login {
            background: #8c532b;
            border-color: #8c532b;
            color: #ffffff;
            height: 48px;
            font-weight: 600;
            border-radius: 8px;
            width: 100%;
            transition: all 0.2s;
        }
        .btn-login:hover {
            background: #734321;
            border-color: #734321;
            color: #ffffff;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <img src="<?= e($siteLogo) ?>" alt="<?= e($siteName) ?>">
        <h4 class="m-0 fw-bold">Admin Portal</h4>
        <small class="opacity-75">Secure Administrative Sign In</small>
    </div>
    
    <div class="login-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <div><?= e($error) ?></div>
            </div>
        <?php endif; ?>

        <form action="" method="POST" autocomplete="off">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="username" class="form-label fw-semibold">Username or Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-user text-muted"></i></span>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Enter admin username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter secure password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-login mb-3">
                <i class="fa-solid fa-shield-halved me-1"></i> Sign In to Dashboard
            </button>

            <div class="text-center">
                <a href="<?= BASE_URL ?>/index.php" class="text-decoration-none text-muted small">
                    <i class="fa-solid fa-arrow-left me-1"></i> Return to Website
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
