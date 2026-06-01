<?php
declare(strict_types=1);

require_once __DIR__ . '/config/koneksi.php';

$pageTitle = 'Tentang Puskesmas - Puskesmas Online';

$profil = [
	'nama_puskesmas' => 'Puskesmas Sehat Bersama',
	'alamat' => 'Jl. Sehat Selalu No. 10, Kota Anda',
	'no_telepon' => '(021) 1234 5678',
	'email' => 'info@puskesmas.local',
	'jam_operasional' => 'Senin - Jumat, 08.00 - 16.00',
	'deskripsi' => 'Puskesmas Sehat Bersama adalah fasilitas pelayanan kesehatan primer yang berfokus pada pelayanan yang cepat, ramah, terjangkau, dan profesional untuk masyarakat.',
	'versi' => '',
	'visi' => 'Menjadi puskesmas yang unggul dalam pelayanan kesehatan dasar, mudah diakses, dan berorientasi pada kepuasan masyarakat.',
	'misi' => 'Memberikan pelayanan kesehatan yang berkualitas, ramah, cepat, dan tepat sasaran kepada seluruh masyarakat.',
	'google_maps' => '',
];

$stmtProfil = $mysqli->query('SELECT * FROM profil_puskesmas ORDER BY id ASC LIMIT 1');
if ($stmtProfil && $stmtProfil->num_rows > 0) {
	$profilDb = $stmtProfil->fetch_assoc();
	$profil = array_merge($profil, array_filter($profilDb, static fn($value) => $value !== null && $value !== ''));
}

$timDokter = [];

$pakaiKolomStatus = false;
$stmtKolom = $mysqli->query("SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'poli' AND COLUMN_NAME = 'status'");
if ($stmtKolom && ($rowKolom = $stmtKolom->fetch_assoc())) {
	$pakaiKolomStatus = ((int) ($rowKolom['total'] ?? 0)) > 0;
}

if ($pakaiKolomStatus) {
	$stmtPoli = $mysqli->query("SELECT id, nama_poli, dokter, jadwal, jam_operasional FROM poli WHERE status = 'aktif' ORDER BY id ASC");
} else {
	$stmtPoli = $mysqli->query('SELECT id, nama_poli, dokter, jadwal, jam_operasional FROM poli WHERE is_active = 1 ORDER BY id ASC');
}

if ($stmtPoli && $stmtPoli->num_rows > 0) {
	while ($row = $stmtPoli->fetch_assoc()) {
		$timDokter[] = $row;
	}
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero page-hero-about">
	<div class="container page-hero-inner about-hero-inner">
		<div>
			<p class="page-hero-kicker"><i class="bi bi-building-heart"></i> Tentang Puskesmas</p>
			<h1>Mengenal lebih dekat layanan dan informasi Puskesmas Online.</h1>
			<p class="page-hero-desc">
				Halaman ini menampilkan profil, visi dan misi, informasi umum, serta tim dokter yang bertugas pada poli aktif.
			</p>
		</div>
	</div>
</section>

<section class="section">
	<div class="container about-profile-grid">
		<div class="about-text-card card-shell">
			<p class="section-kicker"><i class="bi bi-hospital"></i> Profil Puskesmas</p>
			<h2><?= htmlspecialchars($profil['nama_puskesmas'] ?? 'Puskesmas Sehat Bersama') ?></h2>
			<p class="about-desc"><?= htmlspecialchars($profil['deskripsi'] ?? 'Puskesmas ini menyediakan layanan kesehatan dasar dengan pendekatan yang humanis dan profesional.') ?></p>
			<div class="about-quick-info">
				<div><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($profil['alamat'] ?? '-') ?></div>
				<div><i class="bi bi-telephone"></i> <?= htmlspecialchars($profil['no_telepon'] ?? '-') ?></div>
				<div><i class="bi bi-envelope"></i> <?= htmlspecialchars($profil['email'] ?? '-') ?></div>
				<div><i class="bi bi-clock"></i> <?= htmlspecialchars($profil['jam_operasional'] ?? '-') ?></div>
			</div>
		</div>

		<div class="about-image-card">
			<img src="https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=1200&q=80" alt="Ilustrasi layanan kesehatan puskesmas">
			<div class="about-image-caption">
				<i class="bi bi-heart-pulse"></i>
				<span>Layanan kesehatan yang ramah, cepat, dan terjangkau.</span>
			</div>
		</div>
	</div>
</section>

<section class="section section-alt">
	<div class="container about-vision-grid">
		<div class="vision-card card-shell">
			<p class="section-kicker"><i class="bi bi-eye"></i> Visi</p>
			<p><?= htmlspecialchars($profil['visi'] ?? 'Menjadi puskesmas yang unggul dalam pelayanan kesehatan dasar dan mudah diakses masyarakat.') ?></p>
		</div>

		<div class="mission-card card-shell">
			<p class="section-kicker"><i class="bi bi-bullseye"></i> Misi</p>
			<ul>
				<?php
				$misiUtama = trim((string) ($profil['misi'] ?? 'Memberikan pelayanan kesehatan yang berkualitas, ramah, cepat, dan tepat sasaran kepada seluruh masyarakat.'));
				$misiItems = preg_split('/\r\n|\r|\n|\./', $misiUtama, -1, PREG_SPLIT_NO_EMPTY) ?: [];
				if (empty($misiItems)) {
					$misiItems = [$misiUtama];
				}
				foreach ($misiItems as $item):
					$item = trim($item);
					if ($item === '') {
						continue;
					}
				?>
					<li><i class="bi bi-check-circle"></i> <?= htmlspecialchars($item) ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>

<section class="section">
	<div class="container">
		<div class="section-head section-head-left">
			<h2>Informasi Umum Puskesmas</h2>
			<p>Ringkasan kontak dan jam layanan yang dapat membantu masyarakat mendapatkan informasi dengan cepat.</p>
		</div>

		<div class="info-cards about-info-grid">
			<article class="info-card">
				<i class="bi bi-geo-alt"></i>
				<h3>Alamat</h3>
				<p><?= htmlspecialchars($profil['alamat'] ?? '-') ?></p>
			</article>
			<article class="info-card">
				<i class="bi bi-telephone"></i>
				<h3>Nomor Telepon</h3>
				<p><?= htmlspecialchars($profil['no_telepon'] ?? '-') ?></p>
			</article>
			<article class="info-card">
				<i class="bi bi-envelope"></i>
				<h3>Email</h3>
				<p><?= htmlspecialchars($profil['email'] ?? '-') ?></p>
			</article>
			<article class="info-card">
				<i class="bi bi-clock"></i>
				<h3>Jam Operasional</h3>
				<p><?= htmlspecialchars($profil['jam_operasional'] ?? '-') ?></p>
			</article>
		</div>
	</div>
</section>

<section class="section section-alt">
	<div class="container">
		<div class="section-head section-head-left">
			<h2>Tim Dokter Poli Aktif</h2>
			<p>Data diambil dari poli aktif yang tersedia di database.</p>
		</div>

		<div class="doctor-grid">
			<?php if (!empty($timDokter)): ?>
				<?php foreach ($timDokter as $dokter): ?>
					<article class="doctor-card card-shell">
						<div class="doctor-avatar"><i class="bi bi-person-badge"></i></div>
						<h3><?= htmlspecialchars($dokter['dokter'] ?: 'Dokter Belum Diisi') ?></h3>
						<p class="doctor-poli"><i class="bi bi-hospital"></i> <?= htmlspecialchars($dokter['nama_poli']) ?></p>
						<ul class="doctor-meta">
							<li><i class="bi bi-calendar-check"></i> <?= htmlspecialchars($dokter['jadwal'] ?: '-') ?></li>
							<li><i class="bi bi-clock"></i> <?= htmlspecialchars($dokter['jam_operasional'] ?: '-') ?></li>
						</ul>
					</article>
				<?php endforeach; ?>
			<?php else: ?>
				<div class="empty-state">
					<i class="bi bi-person-badge"></i>
					<h3>Data tim dokter belum tersedia.</h3>
					<p>Silakan lengkapi data poli aktif agar nama dokter dapat ditampilkan di halaman ini.</p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<section class="section about-cta-section">
	<div class="container help-box about-cta-box">
		<div>
			<p class="help-kicker">Ingin melakukan pendaftaran?</p>
			<h2>Daftar online sekarang atau lihat layanan poli yang tersedia.</h2>
			<p class="hero-desc">Gunakan pendaftaran online agar kunjungan Anda lebih teratur dan mudah dipantau.</p>
		</div>
		<div class="hero-actions help-actions">
			<a href="pendaftaran.php" class="btn btn-primary"><i class="bi bi-journal-medical"></i> Daftar Online</a>
			<a href="layanan.php" class="btn btn-outline"><i class="bi bi-list-ul"></i> Lihat Layanan</a>
		</div>
	</div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
