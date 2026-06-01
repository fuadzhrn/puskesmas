<?php
$adminCurrentPage = $adminCurrentPage ?? '';
?>
<aside class="admin-sidebar" aria-label="Navigasi admin">
	<div class="admin-sidebar-brand">
		<span class="brand-icon"><i class="bi bi-hospital-fill"></i></span>
		<div>
			<strong>Puskesmas Online</strong>
			<small>Panel Admin</small>
		</div>
	</div>

	<nav class="admin-sidebar-nav">
		<a href="dashboard.php" class="<?= $adminCurrentPage === 'dashboard' ? 'is-active' : '' ?>">
			<i class="bi bi-speedometer2"></i> Dashboard
		</a>
		<a href="pendaftaran/index.php" class="<?= $adminCurrentPage === 'pendaftaran' ? 'is-active' : '' ?>">
			<i class="bi bi-card-checklist"></i> Pendaftaran
		</a>
		<a href="poli/index.php" class="<?= $adminCurrentPage === 'poli' ? 'is-active' : '' ?>">
			<i class="bi bi-building"></i> Data Poli
		</a>
	</nav>

	<a href="logout.php" class="admin-logout-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
</aside>
