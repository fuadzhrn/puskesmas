<?php
declare(strict_types=1);

require_once __DIR__ . '/config/koneksi.php';

$pageTitle = 'Cek Pendaftaran - Puskesmas Online';

if (!function_exists('esc')) {
	function esc(mixed $value): string
	{
		return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
	}
}

$keyword = '';
$errors = [];
$hasilPendaftaran = [];
$jumlahData = 0;

$pakaiKolomStatusPoli = false;
$stmtKolom = $mysqli->query("SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'poli' AND COLUMN_NAME = 'status'");
if ($stmtKolom && ($rowKolom = $stmtKolom->fetch_assoc())) {
	$pakaiKolomStatusPoli = ((int) ($rowKolom['total'] ?? 0)) > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$keyword = trim((string) ($_POST['keyword'] ?? ''));

	if ($keyword === '') {
		$errors[] = 'Silakan masukkan kode pendaftaran atau NIK.';
	} else {
		$sql = 'SELECT p.*, pl.nama_poli, pl.dokter, pl.jadwal, pl.jam_operasional
			FROM pendaftaran p
			JOIN poli pl ON p.poli_id = pl.id
			WHERE p.kode_daftar = ? OR p.nik = ?
			ORDER BY p.created_at DESC';

		$stmt = $mysqli->prepare($sql);
		if ($stmt) {
			$stmt->bind_param('ss', $keyword, $keyword);
			$stmt->execute();
			$result = $stmt->get_result();
			if ($result) {
				while ($row = $result->fetch_assoc()) {
					$hasilPendaftaran[] = $row;
				}
				$jumlahData = count($hasilPendaftaran);
			}
			$stmt->close();
		} else {
			$errors[] = 'Sistem belum siap melakukan pencarian data.';
		}
	}
}

function badgeStatus(string $status): array
{
	$statusLower = strtolower(trim($status));
	return match ($statusLower) {
		'dikonfirmasi' => ['label' => 'Dikonfirmasi', 'class' => 'badge-confirmed'],
		'selesai' => ['label' => 'Selesai', 'class' => 'badge-done'],
		'dibatalkan' => ['label' => 'Dibatalkan', 'class' => 'badge-cancelled'],
		default => ['label' => 'Menunggu', 'class' => 'badge-waiting'],
	};
}

function formatTgl(?string $tanggal): string
{
	if (!$tanggal) {
		return '-';
	}

	$timestamp = strtotime($tanggal);
	if ($timestamp === false) {
		return esc($tanggal);
	}

	return date('d M Y', $timestamp);
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero page-hero-check">
	<div class="container page-hero-inner">
		<div>
			<p class="page-hero-kicker"><i class="bi bi-card-checklist"></i> Cek Pendaftaran</p>
			<h1>Masukkan kode pendaftaran atau NIK untuk melihat status pendaftaran Anda.</h1>
			<p class="page-hero-desc">Gunakan kode pendaftaran yang diterima setelah daftar online, atau masukkan NIK pasien untuk melihat riwayat pendaftaran.</p>
		</div>
	</div>
</section>

<section class="section">
	<div class="container check-layout">
		<div class="check-main">
			<div class="search-card card-shell">
				<form method="post" action="cek-pendaftaran.php" class="search-form" novalidate>
					<div class="field-group search-field">
						<label for="keyword">Pencarian</label>
						<div class="search-input-wrap">
							<i class="bi bi-search"></i>
							<input type="text" id="keyword" name="keyword" value="<?= esc($keyword) ?>" placeholder="Masukkan kode pendaftaran atau NIK" autocomplete="off">
						</div>
					</div>
					<button type="submit" class="btn btn-primary search-submit"><i class="bi bi-search"></i> Cek Status</button>
				</form>
			</div>

			<div class="help-mini-card card-shell">
				<div class="help-mini-icon"><i class="bi bi-info-circle"></i></div>
				<div>
					<h2>Informasi Bantuan</h2>
					<p>Kode pendaftaran didapatkan setelah Anda berhasil melakukan pendaftaran online.</p>
				</div>
				<div class="help-mini-actions">
					<a href="pendaftaran.php" class="btn btn-outline"><i class="bi bi-journal-medical"></i> Daftar Online</a>
					<a href="layanan.php" class="btn btn-outline"><i class="bi bi-list-ul"></i> Lihat Layanan</a>
				</div>
			</div>

			<?php if (!empty($errors)): ?>
				<div class="alert-card alert-error">
					<i class="bi bi-exclamation-triangle"></i>
					<div>
						<h2>Terjadi kendala pencarian.</h2>
						<ul>
							<?php foreach ($errors as $error): ?>
								<li><?= esc($error) ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors) && $jumlahData === 0): ?>
				<div class="empty-state">
					<i class="bi bi-search"></i>
					<h3>Data pendaftaran tidak ditemukan.</h3>
					<p>Pastikan kode pendaftaran atau NIK yang dimasukkan sudah benar.</p>
				</div>
			<?php endif; ?>

			<?php if ($jumlahData > 0): ?>
				<div class="result-summary card-shell">
					<p class="section-kicker"><i class="bi bi-check-circle"></i> Hasil Pencarian</p>
					<h2>Ditemukan <?= esc((string) $jumlahData) ?> data pendaftaran</h2>
					<p>Berikut detail status pendaftaran yang berhasil ditemukan.</p>
				</div>

				<div class="result-list">
					<?php foreach ($hasilPendaftaran as $data): ?>
						<?php $badge = badgeStatus((string) ($data['status'] ?? 'menunggu')); ?>
						<article class="result-card card-shell">
							<div class="result-head">
								<div>
									<p class="result-kicker">Kode Pendaftaran</p>
									<h2><?= esc($data['kode_daftar'] ?? '-') ?></h2>
								</div>
								<span class="status-badge <?= esc($badge['class']) ?>"><?= esc($badge['label']) ?></span>
							</div>

							<div class="result-grid">
								<div><span>Nomor Antrian</span><strong>#<?= esc((string) ($data['nomor_antrian'] ?? '-')) ?></strong></div>
								<div><span>Nama Lengkap</span><strong><?= esc($data['nama_lengkap'] ?? $data['nama_pasien'] ?? '-') ?></strong></div>
								<div><span>NIK</span><strong><?= esc($data['nik'] ?? '-') ?></strong></div>
								<div><span>Poli Tujuan</span><strong><?= esc($data['nama_poli'] ?? '-') ?></strong></div>
								<div><span>Dokter</span><strong><?= esc($data['dokter'] ?? '-') ?></strong></div>
								<div><span>Tanggal Kunjungan</span><strong><?= esc(formatTgl($data['tanggal_kunjungan'] ?? null)) ?></strong></div>
								<div><span>Sesi</span><strong><?= esc($data['sesi'] ?? $data['sesi_waktu'] ?? '-') ?></strong></div>
								<div><span>Jenis Jaminan</span><strong><?= esc($data['jenis_jaminan'] ?? '-') ?></strong></div>
								<div><span>Tanggal Pendaftaran</span><strong><?= esc(formatTgl($data['created_at'] ?? null)) ?></strong></div>
							</div>

							<?php if (!empty($data['catatan'])): ?>
								<div class="admin-note-card">
									<p class="section-kicker"><i class="bi bi-chat-left-text"></i> Catatan Admin</p>
									<p><?= esc($data['catatan']) ?></p>
								</div>
							<?php endif; ?>

							<div class="receipt-actions result-actions">
								<button type="button" class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Cetak Bukti</button>
								<a href="pendaftaran.php" class="btn btn-outline"><i class="bi bi-journal-medical"></i> Daftar Lagi</a>
								<a href="index.php" class="btn btn-outline"><i class="bi bi-house"></i> Kembali ke Beranda</a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<aside class="check-side">
			<div class="sidebar-card check-tip-card">
				<h3><i class="bi bi-lightbulb"></i> Tips</h3>
				<p>Jika menggunakan NIK, sistem akan menampilkan semua riwayat pendaftaran pasien yang cocok.</p>
			</div>
			<div class="sidebar-card check-tip-card">
				<h3><i class="bi bi-shield-check"></i> Privasi</h3>
				<p>Informasi pendaftaran ditampilkan hanya untuk membantu pasien memeriksa status kunjungan.</p>
			</div>
		</aside>
	</div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
