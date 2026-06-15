<?php
declare(strict_types=1);

require_once __DIR__ . '/config/koneksi.php';

$pageTitle = 'Pendaftaran Online - Puskesmas Online';

if (!function_exists('esc')) {
	function esc(mixed $nilai): string
	{
		return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
	}
}

if (!function_exists('kolomAda')) {
	function kolomAda(mysqli $mysqli, string $tabel, string $kolom): bool
	{
		static $cache = [];
		$key = $tabel . '.' . $kolom;
		if (array_key_exists($key, $cache)) {
			return $cache[$key];
		}

		$stmt = $mysqli->prepare('SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
		if (!$stmt) {
			return $cache[$key] = false;
		}

		$stmt->bind_param('ss', $tabel, $kolom);
		$stmt->execute();
		$result = $stmt->get_result();
		$total = $result ? (int) ($result->fetch_assoc()['total'] ?? 0) : 0;
		$stmt->close();

		return $cache[$key] = $total > 0;
	}
}

if (!function_exists('ambilPoliAktif')) {
	function ambilPoliAktif(mysqli $mysqli): array
	{
		$daftarPoli = [];
		$pakaiKolomStatus = kolomAda($mysqli, 'poli', 'status');

		if ($pakaiKolomStatus) {
			$stmt = $mysqli->prepare('SELECT id, nama_poli, deskripsi, dokter, jadwal, jam_operasional, status FROM poli WHERE status = ? ORDER BY id ASC');
			if ($stmt) {
				$statusAktif = 'aktif';
				$stmt->bind_param('s', $statusAktif);
				$stmt->execute();
				$result = $stmt->get_result();
				if ($result) {
					while ($row = $result->fetch_assoc()) {
						$daftarPoli[] = $row;
					}
				}
				$stmt->close();
			}
		} else {
			$stmt = $mysqli->prepare('SELECT id, nama_poli, deskripsi, dokter, jadwal, jam_operasional, is_active FROM poli WHERE is_active = ? ORDER BY id ASC');
			if ($stmt) {
				$isActive = 1;
				$stmt->bind_param('i', $isActive);
				$stmt->execute();
				$result = $stmt->get_result();
				if ($result) {
					while ($row = $result->fetch_assoc()) {
						$row['status'] = 'aktif';
						$daftarPoli[] = $row;
					}
				}
				$stmt->close();
			}
		}

		return $daftarPoli;
	}
}

if (!function_exists('pilihKolom')) {
	function pilihKolom(mysqli $mysqli, string $utama, ?string $cadangan = null): ?string
	{
		if (kolomAda($mysqli, 'pendaftaran', $utama)) {
			return $utama;
		}

		if ($cadangan !== null && kolomAda($mysqli, 'pendaftaran', $cadangan)) {
			return $cadangan;
		}

		return null;
	}
}

function bindDynamicParams(mysqli_stmt $stmt, string $types, array &$values): bool
{
	$params = [$types];
	foreach ($values as &$value) {
		$params[] = &$value;
	}

	return call_user_func_array([$stmt, 'bind_param'], $params);
}

$daftarPoli = ambilPoliAktif($mysqli);
$selectedPoliId = (int) ($_GET['poli'] ?? 0);
$errors = [];
$success = false;
$bukti = [];

$namaLengkap = '';
$nik = '';
$tanggalLahir = '';
$jenisKelamin = '';
$noTelepon = '';
$alamat = '';
$jenisJaminan = 'Umum';
$noJaminan = '';
$tanggalKunjungan = date('Y-m-d');
$sesi = '';
$catatan = '';
$statusDisplay = '';

$selectedPoli = null;
if (!empty($daftarPoli)) {
	$daftarPoliMap = [];
	foreach ($daftarPoli as $poli) {
		$daftarPoliMap[(int) $poli['id']] = $poli;
	}

	if ($selectedPoliId > 0 && isset($daftarPoliMap[$selectedPoliId])) {
		$selectedPoli = $daftarPoliMap[$selectedPoliId];
	} else {
		$selectedPoli = $daftarPoli[0];
		$selectedPoliId = (int) $selectedPoli['id'];
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$selectedPoliId = (int) ($_POST['poli_id'] ?? 0);
	$namaLengkap = bersihkan($_POST['nama_lengkap'] ?? '');
	$nik = bersihkan($_POST['nik'] ?? '');
	$tanggalLahir = bersihkan($_POST['tanggal_lahir'] ?? '');
	$jenisKelamin = bersihkan($_POST['jenis_kelamin'] ?? '');
	$noTelepon = bersihkan($_POST['no_telepon'] ?? '');
	$alamat = bersihkan($_POST['alamat'] ?? '');
	$jenisJaminan = bersihkan($_POST['jenis_jaminan'] ?? 'Umum') ?: 'Umum';
	$noJaminan = bersihkan($_POST['no_jaminan'] ?? '');
	$tanggalKunjungan = bersihkan($_POST['tanggal_kunjungan'] ?? '');
	$sesi = bersihkan($_POST['sesi'] ?? '');
	$catatan = bersihkan($_POST['catatan'] ?? '');

	$daftarPoliMap = [];
	foreach ($daftarPoli as $poli) {
		$daftarPoliMap[(int) $poli['id']] = $poli;
	}

	if ($namaLengkap === '') {
		$errors[] = 'Nama lengkap wajib diisi.';
	}

	if ($nik === '' || !preg_match('/^\d{16}$/', $nik)) {
		$errors[] = 'NIK harus 16 digit angka.';
	}

	if ($tanggalLahir === '' || !DateTime::createFromFormat('Y-m-d', $tanggalLahir)) {
		$errors[] = 'Tanggal lahir wajib diisi dengan format yang benar.';
	}

	if (!in_array($jenisKelamin, ['Laki-laki', 'Perempuan'], true)) {
		$errors[] = 'Jenis kelamin wajib dipilih.';
	}

	if ($noTelepon === '' || !preg_match('/^\d{10,}$/', $noTelepon)) {
		$errors[] = 'Nomor telepon hanya boleh angka dan minimal 10 digit.';
	}

	if ($alamat === '') {
		$errors[] = 'Alamat wajib diisi.';
	}

	if ($selectedPoliId <= 0 || !isset($daftarPoliMap[$selectedPoliId])) {
		$errors[] = 'Poli tujuan wajib dipilih.';
	} else {
		$selectedPoli = $daftarPoliMap[$selectedPoliId];
	}

	$today = new DateTimeImmutable('today');
	$tanggalKunjunganObj = DateTimeImmutable::createFromFormat('Y-m-d', $tanggalKunjungan) ?: false;
	if (!$tanggalKunjunganObj) {
		$errors[] = 'Tanggal kunjungan wajib dipilih.';
	} elseif ($tanggalKunjunganObj < $today) {
		$errors[] = 'Tanggal kunjungan tidak boleh sebelum hari ini.';
	}

	if (!in_array($sesi, ['Pagi', 'Siang', 'Sore'], true)) {
		$errors[] = 'Sesi kunjungan wajib dipilih.';
	}

	if (!in_array($jenisJaminan, ['Umum', 'BPJS', 'KIS', 'Asuransi'], true)) {
		$errors[] = 'Jenis jaminan tidak valid.';
	}

	if ($jenisJaminan !== 'Umum' && $noJaminan === '') {
		$errors[] = 'Nomor jaminan sebaiknya diisi jika jenis jaminan bukan Umum.';
	}

	if (empty($errors) && $selectedPoli) {
		$kolomSesi = kolomAda($mysqli, 'pendaftaran', 'sesi') ? 'sesi' : 'sesi_waktu';
		$stmtHitung = $mysqli->prepare("SELECT COUNT(*) AS total FROM pendaftaran WHERE poli_id = ? AND tanggal_kunjungan = ? AND {$kolomSesi} = ?");
		$nomorAntrian = 1;

		if ($stmtHitung) {
			$stmtHitung->bind_param('iss', $selectedPoliId, $tanggalKunjungan, $sesi);
			$stmtHitung->execute();
			$resultHitung = $stmtHitung->get_result();
			if ($resultHitung) {
				$nomorAntrian = ((int) ($resultHitung->fetch_assoc()['total'] ?? 0)) + 1;
			}
			$stmtHitung->close();
		}

		$kodeDaftar = generateKode($mysqli);
		$statusSimpan = 'menunggu';

		$kolomModern = [
			'kode_daftar' => $kodeDaftar,
			'nama_lengkap' => $namaLengkap,
			'nama_pasien' => $namaLengkap,
			'nik' => $nik,
			'tanggal_lahir' => $tanggalLahir,
			'jenis_kelamin' => $jenisKelamin,
			'no_telepon' => $noTelepon,
			'no_hp' => $noTelepon,
			'alamat' => $alamat,
			'jenis_jaminan' => $jenisJaminan,
			'no_jaminan' => $noJaminan,
			'poli_id' => $selectedPoliId,
			'tanggal_kunjungan' => $tanggalKunjungan,
			'sesi' => $sesi,
			'sesi_waktu' => $sesi,
			'nomor_antrian' => $nomorAntrian,
			'status' => $statusSimpan,
			'catatan' => $catatan,
			'catatan_admin' => $catatan,
		];

		$insertColumns = [];
		$insertValues = [];
		$insertTypes = '';

		$pilihanKolom = [
			['utama' => 'kode_daftar'],
			['utama' => 'nama_lengkap', 'cadangan' => 'nama_pasien'],
			['utama' => 'nik'],
			['utama' => 'tanggal_lahir'],
			['utama' => 'jenis_kelamin'],
			['utama' => 'no_telepon', 'cadangan' => 'no_hp'],
			['utama' => 'alamat'],
			['utama' => 'jenis_jaminan'],
			['utama' => 'no_jaminan'],
			['utama' => 'poli_id'],
			['utama' => 'tanggal_kunjungan'],
			['utama' => 'sesi', 'cadangan' => 'sesi_waktu'],
			['utama' => 'nomor_antrian'],
			['utama' => 'status'],
			['utama' => 'catatan', 'cadangan' => 'catatan_admin'],
		];

		foreach ($pilihanKolom as $item) {
			$kolom = pilihKolom($mysqli, $item['utama'], $item['cadangan'] ?? null);
			if ($kolom !== null) {
				$insertColumns[] = $kolom;
				$insertValues[] = $kolomModern[$kolom] ?? ($kolomModern[$item['utama']] ?? '');
				$insertTypes .= in_array($kolom, ['poli_id', 'nomor_antrian'], true) ? 'i' : 's';
			}
		}

		if ($insertColumns) {
			$sqlInsert = 'INSERT INTO pendaftaran (' . implode(', ', $insertColumns) . ') VALUES (' . implode(', ', array_fill(0, count($insertColumns), '?')) . ')';
			$stmtInsert = $mysqli->prepare($sqlInsert);

			if ($stmtInsert) {
				if (bindDynamicParams($stmtInsert, $insertTypes, $insertValues) && $stmtInsert->execute()) {
					$success = true;
					$statusDisplay = 'Menunggu';
					$bukti = [
						'kode_daftar' => $kodeDaftar,
						'nomor_antrian' => $nomorAntrian,
						'nama_lengkap' => $namaLengkap,
						'nik' => $nik,
						'poli' => $selectedPoli['nama_poli'] ?? '-',
						'tanggal_kunjungan' => $tanggalKunjungan,
						'sesi' => $sesi,
						'status' => $statusDisplay,
					];
				} else {
					$errors[] = 'Pendaftaran gagal disimpan. Silakan coba lagi.';
				}

				$stmtInsert->close();
			} else {
				$errors[] = 'Sistem belum siap menyimpan data pendaftaran.';
			}
		} else {
			$errors[] = 'Struktur tabel pendaftaran belum sesuai.';
		}
	}
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero page-hero-registration">
	<div class="container page-hero-inner">
		<div>
			<p class="page-hero-kicker"><i class="bi bi-clipboard-check"></i> Pendaftaran Online</p>
			<h1>Isi data pendaftaran dengan benar untuk mendapatkan nomor antrian.</h1>
			<p class="page-hero-desc">
				Pilih poli aktif, lengkapi identitas pasien, lalu kirim pendaftaran secara online dengan alur yang ringkas dan mudah diikuti.
			</p>
		</div>
	</div>
</section>

<section class="section">
	<div class="container registration-layout">
		<div class="registration-main">
			<div class="hotline-card">
				<div class="hotline-icon"><i class="bi bi-whatsapp"></i></div>
				<div>
					<h2>Informasi Hotline</h2>
					<p>Jika mengalami kendala, pasien dapat menghubungi admin puskesmas untuk bantuan pendaftaran.</p>
				</div>
				<a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer" class="btn btn-outline"><i class="bi bi-telephone"></i> Hubungi Admin</a>
			</div>

			<?php if ($success): ?>
				<div class="alert-card alert-success">
					<i class="bi bi-check-circle"></i>
					<div>
						<h2>Pendaftaran berhasil disimpan.</h2>
						<p>Silakan simpan atau cetak bukti pendaftaran berikut.</p>
					</div>
				</div>

				<div class="receipt-card" id="buktiPendaftaran">
					<div class="receipt-head">
						<div>
							<p class="receipt-kicker">Bukti Pendaftaran</p>
							<h2><?= esc($bukti['kode_daftar']) ?></h2>
						</div>
						<span class="receipt-badge"><?= esc($bukti['status']) ?></span>
					</div>
					<div class="receipt-grid">
						<div><span>Kode Daftar</span><strong><?= esc($bukti['kode_daftar']) ?></strong></div>
						<div><span>Nomor Antrian</span><strong>#<?= esc((string) $bukti['nomor_antrian']) ?></strong></div>
						<div><span>Nama Lengkap</span><strong><?= esc($bukti['nama_lengkap']) ?></strong></div>
						<div><span>NIK</span><strong><?= esc($bukti['nik']) ?></strong></div>
						<div><span>Poli Tujuan</span><strong><?= esc($bukti['poli']) ?></strong></div>
						<div><span>Tanggal Kunjungan</span><strong><?= esc($bukti['tanggal_kunjungan']) ?></strong></div>
						<div><span>Sesi</span><strong><?= esc($bukti['sesi']) ?></strong></div>
						<div><span>Status</span><strong><?= esc($bukti['status']) ?></strong></div>
					</div>
					<div class="receipt-actions">
						<button type="button" class="btn btn-primary js-print-receipt"><i class="bi bi-printer"></i> Cetak Bukti</button>
						<a href="cek-pendaftaran.php" class="btn btn-outline"><i class="bi bi-search"></i> Cek Status</a>
						<a href="index.php" class="btn btn-outline"><i class="bi bi-house"></i> Kembali ke Beranda</a>
					</div>
				</div>
			<?php endif; ?>

			<?php if (!empty($errors)): ?>
				<div class="alert-card alert-error">
					<i class="bi bi-exclamation-triangle"></i>
					<div>
						<h2>Mohon periksa kembali isian Anda.</h2>
						<ul>
							<?php foreach ($errors as $error): ?>
								<li><?= esc($error) ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			<?php endif; ?>

			<?php if (!empty($daftarPoli)): ?>
				<form class="registration-form card-shell" method="post" action="pendaftaran.php" data-registration-form data-base-url="<?= esc($baseUrl ?? '') ?>" novalidate>
					<div class="form-progress">
						<span class="step-dot is-active" data-step-dot>1</span>
						<span class="step-line"></span>
						<span class="step-dot" data-step-dot>2</span>
						<span class="step-line"></span>
						<span class="step-dot" data-step-dot>3</span>
						<span class="step-line"></span>
						<span class="step-dot" data-step-dot>4</span>
						<span class="step-line"></span>
						<span class="step-dot" data-step-dot>5</span>
					</div>

					<div class="step-panels">
						<section class="step-panel is-active" data-step-panel>
							<div class="step-panel-head">
								<h3><i class="bi bi-hospital"></i> Pilih Poli</h3>
								<p>Pilih poli aktif yang sesuai dengan kebutuhan layanan Anda.</p>
							</div>
							<div class="field-group">
								<label for="poli_id">Poli Tujuan</label>
								<select id="poli_id" name="poli_id" required data-poli-select>
									<option value="">-- Pilih Poli --</option>
									<?php foreach ($daftarPoli as $poli): ?>
										<option
											value="<?= (int) $poli['id'] ?>"
											<?= ((int) $poli['id'] === (int) ($selectedPoliId ?: 0)) ? 'selected' : '' ?>
											data-dokter="<?= esc($poli['dokter'] ?? '-') ?>"
											data-jadwal="<?= esc($poli['jadwal'] ?? '-') ?>"
											data-jam="<?= esc($poli['jam_operasional'] ?? '-') ?>"
											data-deskripsi="<?= esc($poli['deskripsi'] ?? '') ?>"
										>
											<?= esc($poli['nama_poli']) ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="poli-preview" data-poli-preview>
								<strong data-preview-title><?= esc($selectedPoli['nama_poli'] ?? 'Pilih poli aktif terlebih dahulu') ?></strong>
								<p data-preview-desc><?= esc($selectedPoli['deskripsi'] ?? 'Informasi poli akan muncul setelah Anda memilih layanan.') ?></p>
								<div class="preview-meta">
									<span><i class="bi bi-person-badge"></i> <b>Dokter:</b> <em data-preview-doctor><?= esc($selectedPoli['dokter'] ?? '-') ?></em></span>
									<span><i class="bi bi-calendar-check"></i> <b>Jadwal:</b> <em data-preview-schedule><?= esc($selectedPoli['jadwal'] ?? '-') ?></em></span>
									<span><i class="bi bi-clock"></i> <b>Jam:</b> <em data-preview-hours><?= esc($selectedPoli['jam_operasional'] ?? '-') ?></em></span>
								</div>
							</div>
							<div class="step-actions step-actions-single">
								<button type="button" class="btn btn-primary" data-step-next>Lanjut</button>
							</div>
						</section>

						<section class="step-panel" data-step-panel>
							<div class="step-panel-head">
								<h3><i class="bi bi-person"></i> Identitas Pasien</h3>
								<p>Lengkapi data pasien dengan benar sesuai identitas resmi.</p>
							</div>
							<div class="form-grid form-grid-two">
								<div class="field-group">
									<label for="nama_lengkap">Nama Lengkap</label>
									<input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= esc($namaLengkap) ?>" required>
								</div>
								<div class="field-group">
									<label for="nik">NIK</label>
									<input type="text" id="nik" name="nik" value="<?= esc($nik) ?>" inputmode="numeric" maxlength="16" placeholder="16 digit angka" required>
								</div>
								<div class="field-group">
									<label for="tanggal_lahir">Tanggal Lahir</label>
									<input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?= esc($tanggalLahir) ?>" required>
								</div>
								<div class="field-group">
									<label for="jenis_kelamin">Jenis Kelamin</label>
									<select id="jenis_kelamin" name="jenis_kelamin" required>
										<option value="">-- Pilih --</option>
										<option value="Laki-laki" <?= $jenisKelamin === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
										<option value="Perempuan" <?= $jenisKelamin === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
									</select>
								</div>
								<div class="field-group">
									<label for="no_telepon">Nomor Telepon</label>
									<input type="text" id="no_telepon" name="no_telepon" value="<?= esc($noTelepon) ?>" inputmode="numeric" placeholder="Minimal 10 digit" required>
								</div>
								<div class="field-group field-group-full">
									<label for="alamat">Alamat</label>
									<textarea id="alamat" name="alamat" rows="4" required><?= esc($alamat) ?></textarea>
								</div>
							</div>
							<div class="step-actions">
								<button type="button" class="btn btn-outline" data-step-prev>Kembali</button>
								<button type="button" class="btn btn-primary" data-step-next>Lanjut</button>
							</div>
						</section>

						<section class="step-panel" data-step-panel>
							<div class="step-panel-head">
								<h3><i class="bi bi-shield-check"></i> Data Jaminan</h3>
								<p>Isi informasi jaminan pasien bila ada.</p>
							</div>
							<div class="form-grid form-grid-two">
								<div class="field-group">
									<label for="jenis_jaminan">Jenis Jaminan</label>
									<select id="jenis_jaminan" name="jenis_jaminan" data-jaminan-select>
										<option value="Umum" <?= $jenisJaminan === 'Umum' ? 'selected' : '' ?>>Umum</option>
										<option value="BPJS" <?= $jenisJaminan === 'BPJS' ? 'selected' : '' ?>>BPJS</option>
										<option value="KIS" <?= $jenisJaminan === 'KIS' ? 'selected' : '' ?>>KIS</option>
										<option value="Asuransi" <?= $jenisJaminan === 'Asuransi' ? 'selected' : '' ?>>Asuransi</option>
									</select>
								</div>
								<div class="field-group" data-no-jaminan-wrap>
									<label for="no_jaminan">Nomor Jaminan</label>
									<input type="text" id="no_jaminan" name="no_jaminan" value="<?= esc($noJaminan) ?>" placeholder="Opsional untuk Umum">
								</div>
							</div>
							<div class="step-actions">
								<button type="button" class="btn btn-outline" data-step-prev>Kembali</button>
								<button type="button" class="btn btn-primary" data-step-next>Lanjut</button>
							</div>
						</section>

						<section class="step-panel" data-step-panel>
							<div class="step-panel-head">
								<h3><i class="bi bi-calendar-check"></i> Jadwal Kunjungan</h3>
								<p>Tentukan tanggal kunjungan dan sesi yang tersedia.</p>
							</div>
							<div class="form-grid form-grid-two">
								<div class="field-group">
									<label for="tanggal_kunjungan">Tanggal Kunjungan</label>
									<input type="date" id="tanggal_kunjungan" name="tanggal_kunjungan" value="<?= esc($tanggalKunjungan) ?>" min="<?= esc(date('Y-m-d')) ?>" required>
								</div>
								<div class="field-group">
									<label for="sesi">Sesi</label>
									<select id="sesi" name="sesi" required>
										<option value="">-- Pilih Sesi --</option>
										<option value="Pagi" <?= $sesi === 'Pagi' ? 'selected' : '' ?>>Pagi (08.00 – 11.00)</option>
										<option value="Siang" <?= $sesi === 'Siang' ? 'selected' : '' ?>>Siang (11.00 – 14.00)</option>
										<option value="Sore" <?= $sesi === 'Sore' ? 'selected' : '' ?>>Sore (14.00 – 16.00)</option>
									</select>
								</div>
							</div>

							<div id="infoAntrian" class="info-antrian" hidden></div>

							<div class="step-actions">
								<button type="button" class="btn btn-outline" data-step-prev>Kembali</button>
								<button type="button" class="btn btn-primary" data-step-next>Lanjut</button>
							</div>
						</section>

						<section class="step-panel" data-step-panel>
							<div class="step-panel-head">
								<h3><i class="bi bi-check-circle"></i> Konfirmasi</h3>
								<p>Periksa kembali data yang sudah Anda isi sebelum mengirim pendaftaran.</p>
							</div>
							<div class="confirmation-box">
								<p><strong>Nama:</strong> <span data-confirm-nama>-</span></p>
								<p><strong>Poli:</strong> <span data-confirm-poli>-</span></p>
								<p><strong>Tanggal Kunjungan:</strong> <span data-confirm-tanggal>-</span></p>
								<p><strong>Sesi:</strong> <span data-confirm-sesi>-</span></p>
							</div>
							<div class="step-actions">
								<button type="button" class="btn btn-outline" data-step-prev>Kembali</button>
								<button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Kirim Pendaftaran</button>
							</div>
						</section>
					</div>
				</form>
			<?php else: ?>
				<div class="empty-state form-empty">
					<i class="bi bi-hospital"></i>
					<h3>Layanan poli belum tersedia.</h3>
					<p>Silakan tambahkan poli aktif terlebih dahulu melalui panel admin.</p>
				</div>
			<?php endif; ?>
		</div>

		<aside class="registration-side">
			<div class="sidebar-card">
				<h3><i class="bi bi-list-check"></i> Alur Singkat</h3>
				<ul class="sidebar-steps">
					<li><span>1</span> Pilih poli aktif</li>
					<li><span>2</span> Isi identitas pasien</li>
					<li><span>3</span> Tentukan jadwal kunjungan</li>
					<li><span>4</span> Kirim dan simpan bukti</li>
				</ul>
			</div>

			<div class="sidebar-card">
				<h3><i class="bi bi-info-circle"></i> Catatan Penting</h3>
				<p>Pastikan NIK dan nomor telepon diisi dengan benar agar pendaftaran mudah diverifikasi.</p>
			</div>
		</aside>
	</div>
</section>

<script src="<?= esc(($baseUrl ?? '') . '/assets/js/pendaftaran.js') ?>"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
