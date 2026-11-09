<?php
/**
 * Header Component
 * Drop2Earn - Smart Waste Management
 */
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Drop2Earn');
}
$current_page_title = isset($page_title) ? sanitize($page_title) . ' | Drop2Earn' : 'Drop2Earn | Bank Sampah Digital & Pickup Directory';

// Detect flash messages from session or local variable scope
$body_toast_success = '';
$body_toast_error = '';
$body_toast_warning = '';
$body_toast_info = '';

if (!empty($_SESSION['flash_success'])) {
    $body_toast_success = sanitize($_SESSION['flash_success']);
    unset($_SESSION['flash_success']);
} elseif (!empty($flash_success)) {
    $body_toast_success = sanitize($flash_success);
}

if (!empty($_SESSION['flash_error'])) {
    $body_toast_error = sanitize($_SESSION['flash_error']);
    unset($_SESSION['flash_error']);
} elseif (!empty($flash_error)) {
    $body_toast_error = sanitize($flash_error);
}

if (!empty($_SESSION['flash_warning'])) {
    $body_toast_warning = sanitize($_SESSION['flash_warning']);
    unset($_SESSION['flash_warning']);
} elseif (!empty($flash_warning)) {
    $body_toast_warning = sanitize($flash_warning);
}

if (!empty($_SESSION['flash_info'])) {
    $body_toast_info = sanitize($_SESSION['flash_info']);
    unset($_SESSION['flash_info']);
} elseif (!empty($flash_info)) {
    $body_toast_info = sanitize($flash_info);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $current_page_title ?></title>
    
    <!-- Google Font: Plus Jakarta Sans (Modern Flat 2.0 & Clean Startup) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="d-flex flex-column min-vh-100" 
      data-toast-success="<?= $body_toast_success ?>" 
      data-toast-error="<?= $body_toast_error ?>"
      data-toast-warning="<?= $body_toast_warning ?>"
      data-toast-info="<?= $body_toast_info ?>">
