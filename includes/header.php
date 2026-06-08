<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

$pageTitle = $pageTitle ?? 'Sistem Informasi Pendaftaran Online Puskesmas';
$baseUrl = $baseUrl ?? rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($baseUrl === '/' || $baseUrl === '.') {
	$baseUrl = '';
}

$currentPage = basename($_SERVER['PHP_SELF']);

function navClass(string $page, string $current): string {
	return $page === $current ? 'nav-active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= htmlspecialchars($pageTitle) ?></title>
	<meta name="description" content="Sistem informasi pendaftaran online puskesmas yang modern, mudah, dan cepat.">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWix+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkR4j8Tx9vR9DqQ1fVYQh8fYfJ6VQ9fNfYkg==" crossorigin="anonymous" referrerpolicy="no-referrer">
	<link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/style.css">
</head>
<body>
<header class="site-header">
	<div class="container nav-wrap">
		<a href="<?= htmlspecialchars($baseUrl) ?>/index.php" class="brand">
			<span class="brand-icon" aria-hidden="true"><i class="bi bi-hospital-fill"></i></span>
			<span class="brand-text">Puskesmas Online</span>
		</a>

		<button class="menu-toggle" id="menuToggle" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="siteNav">
			<span class="hamburger-bar"></span>
			<span class="hamburger-bar"></span>
			<span class="hamburger-bar"></span>
		</button>

		<nav class="site-nav" id="siteNav" aria-hidden="true">
			<a href="<?= htmlspecialchars($baseUrl) ?>/index.php" class="<?= navClass('index.php', $currentPage) ?>">
				<i class="bi bi-house"></i> Beranda
			</a>
			<a href="<?= htmlspecialchars($baseUrl) ?>/tentang.php" class="<?= navClass('tentang.php', $currentPage) ?>">
				<i class="bi bi-info-circle"></i> Tentang
			</a>
			<a href="<?= htmlspecialchars($baseUrl) ?>/layanan.php" class="<?= navClass('layanan.php', $currentPage) ?>">
				<i class="bi bi-heart-pulse"></i> Layanan
			</a>
			<a href="<?= htmlspecialchars($baseUrl) ?>/cek-pendaftaran.php" class="<?= navClass('cek-pendaftaran.php', $currentPage) ?>">
				<i class="bi bi-card-checklist"></i> Cek Pendaftaran
			</a>
			<a href="<?= htmlspecialchars($baseUrl) ?>/pendaftaran.php" class="btn-nav <?= navClass('pendaftaran.php', $currentPage) ?>">
				<i class="bi bi-journal-medical"></i> Daftar Online
			</a>
		</nav>
	</div>
</header>

<main>
