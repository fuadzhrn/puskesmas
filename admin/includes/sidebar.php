<?php
$adminCurrentPage = $adminCurrentPage ?? '';

$scriptName = $_SERVER['SCRIPT_NAME'];

$isInSubFolder = strpos($scriptName, '/admin/pendaftaran/') !== false 
              || strpos($scriptName, '/admin/poli/') !== false;

$adminBase = $isInSubFolder ? '../' : '';
$rootBase  = $isInSubFolder ? '../../' : '../';
?>

<aside class="admin-sidebar" aria-label="Navigasi admin">
    <div class="admin-sidebar-brand">
        <span class="brand-icon">
            <i class="bi bi-hospital-fill"></i>
        </span>
        <div>
            <strong>Puskesmas Online</strong>
            <small>Panel Admin</small>
        </div>
    </div>

    <nav class="admin-sidebar-nav">
        <a href="<?= $adminBase; ?>dashboard.php" class="<?= $adminCurrentPage === 'dashboard' ? 'is-active' : ''; ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <a href="<?= $adminBase; ?>pendaftaran/index.php" class="<?= $adminCurrentPage === 'pendaftaran' ? 'is-active' : ''; ?>">
            <i class="bi bi-card-checklist"></i>
            <span>Pendaftaran</span>
        </a>

        <a href="<?= $adminBase; ?>poli/index.php" class="<?= $adminCurrentPage === 'poli' ? 'is-active' : ''; ?>">
            <i class="bi bi-building"></i>
            <span>Data Poli</span>
        </a>

        <a href="<?= $rootBase; ?>index.php">
            <i class="bi bi-globe"></i>
            <span>Lihat Website</span>
        </a>
    </nav>

    <a href="<?= $adminBase; ?>logout.php" class="admin-logout-link">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
    </a>
</aside>