<?php
/**
 * Application Configuration & Initialization
 * A2Z Handicraft
 */

// Define Root Directory
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Require DB connection & Environment
require_once ROOT_PATH . '/config/db.php';

// Session Security Settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    session_set_cookie_params([
        'lifetime' => 86400 * 7, // 7 days
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// Determine Base URL dynamically or from .env
$envAppUrl = $_ENV['APP_URL'] ?? '';
if (!empty($envAppUrl)) {
    $baseUrl = rtrim($envAppUrl, '/');
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = $protocol . $host . '/a2z-handicraft';
}

if (!defined('BASE_URL')) {
    define('BASE_URL', $baseUrl);
}

if (!defined('ADMIN_URL')) {
    define('ADMIN_URL', BASE_URL . '/admin');
}

if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', ROOT_PATH . '/uploads');
}

if (!defined('UPLOADS_URL')) {
    define('UPLOADS_URL', BASE_URL . '/uploads');
}

// Environment Error Handling
$appEnv = $_ENV['APP_ENV'] ?? 'development';
if ($appEnv === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Include Main Utility Functions
require_once ROOT_PATH . '/util/function.php';
