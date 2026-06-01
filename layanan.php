<?php
declare(strict_types=1);

require_once __DIR__ . '/config/koneksi.php';

$pageTitle = 'Layanan Poli - Puskesmas Online';
$daftarPoli = [];

$punyaKolomStatus = false;
$stmtKolomStatus = $mysqli->prepare("SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'poli' AND COLUMN_NAME = 'status'");
if ($stmtKolomStatus) {
	$stmtKolomStatus->execute();
	$resultKolomStatus = $stmtKolomStatus->get_result();
	if ($resultKolomStatus) {
		$punyaKolomStatus = ((int) ($resultKolomStatus->fetch_assoc()['total'] ?? 0)) > 0;
	}
	$stmtKolomStatus->close();
}

if ($punyaKolomStatus) {
	$stmtPoli = $mysqli->prepare('SELECT id, nama_poli, deskripsi, dokter, jadwal, jam_operasional, status FROM poli WHERE status = ? ORDER BY id ASC');
	if ($stmtPoli) {
		$statusAktif = 'aktif';
		$stmtPoli->bind_param('s', $statusAktif);
		$stmtPoli->execute();
		$resultPoli = $stmtPoli->get_result();
		if ($resultPoli) {
			while ($row = $resultPoli->fetch_assoc()) {
				$daftarPoli[] = $row;
			}
		}
		$stmtPoli->close();
	}
} else {
	$stmtPoli = $mysqli->prepare('SELECT id, nama_poli, deskripsi, dokter, jadwal, jam_operasional, is_active FROM poli WHERE is_active = ? ORDER BY id ASC');
	if ($stmtPoli) {
		$isActive = 1;
		$stmtPoli->bind_param('i', $isActive);
		$stmtPoli->execute();
		$resultPoli = $stmtPoli->get_result();
		if ($resultPoli) {
			while ($row = $resultPoli->fetch_assoc()) {
				$daftarPoli[] = $row;
			}
		}
		$stmtPoli->close();
	}
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero page-hero-layanan">
	<div class="container page-hero-inner">
		<div>
			<p class="page-hero-kicker"><i class="bi bi-heart-pulse"></i> Layanan Poli</p>
			<h1>Pilih layanan poli sesuai kebutuhan Anda dan lakukan pendaftaran secara online.</h1>
			<p class="page-hero-desc">
				Lihat informasi layanan, jadwal dokter, jam operasional, dan daftar langsung ke poli yang Anda butuhkan.
			</p>
		</div>
	</div>
</section>

<section class="section">
	<div class="container">
		<div class="section-head section-head-left">
			<h2>Daftar Poli Aktif</h2>
			<p>Data di bawah menampilkan layanan poli yang sedang aktif di puskesmas.</p>
		</div>

		<div class="service-grid">
			<?php if (!empty($daftarPoli)): ?>
				<?php foreach ($daftarPoli as $poli): ?>
					<article class="service-card service-card-layanan">
						<div class="card-top">
							<span class="card-icon"><i class="bi bi-hospital-fill"></i></span>
							<div>
								<h3><?= htmlspecialchars($poli['nama_poli']) ?></h3>
								<p class="card-subtitle">Dokter: <?= htmlspecialchars($poli['dokter'] ?: '-') ?></p>
							</div>
						</div>

						<p class="service-desc">
							<?= htmlspecialchars($poli['deskripsi'] ?: 'Layanan kesehatan yang tersedia untuk pendaftaran pasien.') ?>
						</p>

						<ul class="service-meta">
							<li><i class="bi bi-calendar-check"></i> Jadwal: <?= htmlspecialchars($poli['jadwal'] ?: '-') ?></li>
							<li><i class="bi bi-clock"></i> Jam operasional: <?= htmlspecialchars($poli['jam_operasional'] ?: '-') ?></li>
							<li><i class="bi bi-badge-ad"></i> Status: <?= htmlspecialchars($poli['status'] ?? ($poli['is_active'] ? 'aktif' : 'nonaktif')) ?></li>
						</ul>

						<a href="pendaftaran.php?poli=<?= (int) $poli['id'] ?>" class="card-link card-link-primary">
							Daftar Sekarang <i class="bi bi-arrow-right"></i>
						</a>
					</article>
				<?php endforeach; ?>
			<?php else: ?>
				<div class="empty-state">
					<i class="bi bi-hospital"></i>
					<h3>Layanan poli belum tersedia.</h3>
					<p>Silakan cek kembali nanti atau hubungi petugas puskesmas untuk informasi layanan aktif.</p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<section class="section section-help">
	<div class="container help-box">
		<div>
			<p class="help-kicker">Masih bingung memilih layanan?</p>
			<h2>Cek pendaftaran atau langsung daftar online sekarang.</h2>
		</div>
		<div class="hero-actions help-actions">
			<a href="cek-pendaftaran.php" class="btn btn-outline"><i class="bi bi-search"></i> Cek Pendaftaran</a>
			<a href="pendaftaran.php" class="btn btn-primary"><i class="bi bi-journal-medical"></i> Daftar Online</a>
		</div>
	</div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
