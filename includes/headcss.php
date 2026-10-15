<?php
$siteFavicon = resolve_image_url(get_setting('site_favicon', 'images/logo.png'));
?>
<link rel="shortcut icon" type="image/x-icon" href="<?= e($siteFavicon) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com/">
<link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&amp;family=Roboto:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?= BASE_URL ?>/css/slicknav.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/swiper-bundle.min.css">
<link href="<?= BASE_URL ?>/css/all.min.css" rel="stylesheet" media="screen">
<link href="<?= BASE_URL ?>/css/animate.css" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/magnific-popup.css">
<link href="<?= BASE_URL ?>/css/custom.css" rel="stylesheet" media="screen">
