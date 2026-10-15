<?php
require_once dirname(__DIR__) . '/config/config.php';

if (is_admin_logged_in()) {
    log_activity('LOGOUT', 'Admin logged out');
}

// Clear all session variables
$_SESSION = [];

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start fresh session to pass flash message
session_start();
session_regenerate_id(true);
set_flash('info', 'You have been safely logged out.');

header('Location: ' . ADMIN_URL . '/login.php');
exit;
