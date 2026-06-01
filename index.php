<?php
declare(strict_types=1);

require_once __DIR__ . '/config/koneksi.php';

$pageTitle = 'Beranda - Puskesmas Online';

$profil = null;
$daftarPoli = [];
$limitPoli = 6;

$stmtProfil = $mysqli->prepare('SELECT nama_puskesmas, alamat, deskripsi_singkat FROM profil_puskesmas LIMIT 1');
if ($stmtProfil) {
	$stmtProfil->execute();
	$resultProfil = $stmtProfil->get_result();
	$profil = $resultProfil ? $resultProfil->fetch_assoc() : null;
	$stmtProfil->close();
}


$stmtKolomStatus = $mysqli->prepare("SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'poli' AND COLUMN_NAME = 'status'");
$punyaKolomStatus = false;
if ($stmtKolomStatus) {
	$stmtKolomStatus->execute();
	$resultKolomStatus = $stmtKolomStatus->get_result();
	if ($resultKolomStatus) {
		$punyaKolomStatus = ((int) ($resultKolomStatus->fetch_assoc()['total'] ?? 0)) > 0;
	}
	$stmtKolomStatus->close();
}

if ($punyaKolomStatus) {
	$stmtPoli = $mysqli->prepare('SELECT id, nama_poli, deskripsi, dokter, jadwal, jam_operasional FROM poli WHERE status = ? ORDER BY nama_poli ASC LIMIT ?');
	if ($stmtPoli) {
		$statusAktif = 'aktif';
		$stmtPoli->bind_param('si', $statusAktif, $limitPoli);
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
	$stmtPoli = $mysqli->prepare('SELECT id, nama_poli, deskripsi, dokter, jadwal, jam_operasional FROM poli WHERE is_active = ? ORDER BY nama_poli ASC LIMIT ?');
	if ($stmtPoli) {
		$isActive = 1;
		$stmtPoli->bind_param('ii', $isActive, $limitPoli);
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

<section class="hero-section">
	<div class="container hero-grid">
		<div>
			<p class="hero-tag">Layanan Kesehatan Terintegrasi</p>
			<h1>Daftar Berobat ke Puskesmas Lebih Cepat Tanpa Antre Panjang</h1>
			<p class="hero-desc">
				Sistem informasi pendaftaran online untuk memudahkan masyarakat melakukan pendaftaran,
				memilih poli tujuan, dan memantau status kunjungan secara real-time.
			</p>
			<div class="hero-actions">
				<a href="pendaftaran.php" class="btn btn-primary"><i class="bi bi-journal-medical"></i> Daftar Online</a>
				<a href="cek-pendaftaran.php" class="btn btn-outline"><i class="bi bi-search"></i> Cek Pendaftaran</a>
			</div>
		</div>
		<div class="hero-image-wrap">
			<img src="https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?auto=format&fit=crop&w=1200&q=80" alt="Layanan kesehatan puskesmas" class="hero-image">
		</div>
	</div>
</section>

<section class="section">
	<div class="container info-cards">
		<article class="info-card">
			<i class="bi bi-hospital"></i>
			<h3><?= htmlspecialchars($profil['nama_puskesmas'] ?? 'Puskesmas Sehat Bersama') ?></h3>
			<p><?= htmlspecialchars($profil['deskripsi_singkat'] ?? 'Pusat layanan kesehatan primer dengan tenaga medis profesional dan ramah.') ?></p>
		</article>
		<article class="info-card">
			<i class="bi bi-geo-alt"></i>
			<h3>Alamat</h3>
			<p><?= htmlspecialchars($profil['alamat'] ?? 'Jl. Sehat Selalu No. 10, Kota Anda') ?></p>
		</article>
		<article class="info-card">
			<i class="bi bi-telephone"></i>
			<h3>Hotline Pendaftaran</h3>
			<p>Hubungi layanan bantuan kami di (021) 1234 5678 untuk informasi lebih lanjut.</p>
		</article>
	</div>
</section>

<section class="section section-alt">
	<div class="container">
		<div class="section-head">
			<h2>Alur Pendaftaran Online</h2>
			<p>Proses sederhana untuk mempercepat kunjungan Anda ke puskesmas.</p>
		</div>
		<div class="flow-grid">
			<article class="flow-item">
				<div class="flow-head"><span>1</span><i class="bi bi-ui-checks-grid"></i></div>
				<h3>Pilih Poli</h3>
				<p>Tentukan poli tujuan sesuai kebutuhan layanan.</p>
			</article>
			<article class="flow-item">
				<div class="flow-head"><span>2</span><i class="bi bi-person-vcard"></i></div>
				<h3>Isi Data Diri</h3>
				<p>Lengkapi identitas pasien dengan benar dan lengkap.</p>
			</article>
			<article class="flow-item">
				<div class="flow-head"><span>3</span><i class="bi bi-calendar2-week"></i></div>
				<h3>Pilih Jadwal</h3>
				<p>Tentukan tanggal kunjungan dan sesi waktu yang tersedia.</p>
			</article>
			<article class="flow-item">
				<div class="flow-head"><span>4</span><i class="bi bi-check2-circle"></i></div>
				<h3>Konfirmasi</h3>
				<p>Dapatkan kode daftar dan nomor antrian secara otomatis.</p>
			</article>
		</div>
	</div>
</section>

<section class="section">
	<div class="container">
		<div class="section-head">
			<h2>Ringkasan Layanan Poli</h2>
			<p>Berikut beberapa layanan poli aktif yang tersedia saat ini.</p>
		</div>

		<div class="service-grid">
			<?php if (!empty($daftarPoli)): ?>
				<?php foreach ($daftarPoli as $poli): ?>
					<article class="service-card">
						<div class="card-top">
							<span class="card-icon"><i class="bi bi-hospital"></i></span>
							<div>
								<h3><?= htmlspecialchars($poli['nama_poli']) ?></h3>
								<p class="card-subtitle">Dokter: <?= htmlspecialchars($poli['dokter'] ?: '-') ?></p>
							</div>
						</div>
						<p><?= htmlspecialchars($poli['deskripsi'] ? mb_strimwidth($poli['deskripsi'], 0, 120, '…', 'UTF-8') : 'Layanan kesehatan umum untuk pasien sesuai jadwal dokter.') ?></p>
						<ul class="service-meta">
							<li><i class="bi bi-calendar-event"></i> <?= htmlspecialchars($poli['jadwal'] ?: '-') ?></li>
							<li><i class="bi bi-clock"></i> <?= htmlspecialchars($poli['jam_operasional'] ?: '-') ?></li>
						</ul>
						<a href="pendaftaran.php?poli=<?= (int) $poli['id'] ?>" class="card-link">Daftar Sekarang <i class="bi bi-arrow-right"></i></a>
					</article>
				<?php endforeach; ?>
			<?php else: ?>
				<article class="service-card">
					<h3>Layanan poli belum tersedia.</h3>
					<p>Silakan tambahkan data poli aktif melalui panel admin agar layanan tampil di halaman ini.</p>
					<a href="admin/login.php" class="card-link">Masuk Admin <i class="bi bi-arrow-right"></i></a>
				</article>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
