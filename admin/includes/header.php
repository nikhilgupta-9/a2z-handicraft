<?php
// Buffer page output so action handlers that run after this include can still redirect
ob_start();

require_once dirname(__DIR__, 2) . '/config/config.php';
require_admin_auth();

$currentUser = current_admin_user();
$siteName = get_setting('site_name', 'A2Z Handicraft');
$siteLogo = resolve_image_url(get_setting('site_logo', 'images/logo.png'));
$siteFavicon = resolve_image_url(get_setting('site_favicon', 'images/logo.png'));

// Counts for navigation badges
global $pdo;
$unreadLeadsCount = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE is_read = 0")->fetchColumn();
$pendingReviewsCount = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>Admin - <?= e($siteName) ?></title>
    <link rel="shortcut icon" href="<?= e($siteFavicon) ?>">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<?php require_once __DIR__ . '/sidebar.php'; ?>

<div class="admin-main">
    <?php require_once __DIR__ . '/navbar.php'; ?>
    <div class="admin-content">
        <?= render_flash() ?>
