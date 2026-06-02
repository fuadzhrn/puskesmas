<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$adminPageTitle = $pageTitle ?? ($adminPageTitle ?? 'Admin Puskesmas Online');
$adminBodyClass = $adminBodyClass ?? 'admin-dashboard-page';

$adminNama = trim((string) ($_SESSION['admin_nama'] ?? ''));
if ($adminNama === '') {
    $adminNama = 'Admin';
}

$scriptName = $_SERVER['SCRIPT_NAME'];

$isInAdminSubFolder = strpos($scriptName, '/admin/pendaftaran/') !== false
                   || strpos($scriptName, '/admin/poli/') !== false;

$assetPath = $isInAdminSubFolder ? '../../assets/css/admin.css' : '../assets/css/admin.css';
$logoutPath = $isInAdminSubFolder ? '../logout.php' : 'logout.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminPageTitle, ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="<?= htmlspecialchars($assetPath, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="<?= htmlspecialchars($adminBodyClass, ENT_QUOTES, 'UTF-8') ?>">
<div class="admin-wrapper admin-layout">
    <header class="admin-topbar">
        <div class="admin-brand">
            <span class="admin-brand-logo" aria-hidden="true">
                <i class="bi bi-hospital-fill"></i>
            </span>
            <div class="admin-brand-text">
                <strong>Admin Puskesmas</strong>
                <small>Sistem Informasi Pendaftaran Online</small>
            </div>
        </div>

        <div class="admin-user">
            <span class="admin-user-name">
                <?= htmlspecialchars($adminNama, ENT_QUOTES, 'UTF-8') ?>
            </span>

            <a href="<?= htmlspecialchars($logoutPath, ENT_QUOTES, 'UTF-8') ?>" class="admin-logout-link">
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                <span>Logout</span>
            </a>
        </div>
    </header>