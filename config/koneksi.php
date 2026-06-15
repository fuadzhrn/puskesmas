<?php
// Konfigurasi koneksi database MySQL untuk aplikasi.
declare(strict_types=1);

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'puskesmas_online';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_errno) {
	die('Koneksi database gagal: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');
date_default_timezone_set('Asia/Jakarta');

if (!function_exists('bersihkan')) {
	function bersihkan(?string $nilai): string
	{
		return trim((string) $nilai);
	}
}

if (!function_exists('cekStatusBuka')) {
	function cekStatusBuka(string $jadwal, string $jamOp): bool
	{
		$hariMap = [
			'senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4,
			'jumat' => 5, 'sabtu' => 6, 'minggu' => 7,
		];

		$now      = new DateTime('now');
		$hariIni  = (int) $now->format('N'); // 1=Senin … 7=Minggu
		$jamNow   = $now->format('H:i');

		// Deteksi hari buka dari field jadwal (contoh: "Senin - Jumat")
		$jadwalLower = strtolower($jadwal);
		$hariDibuka  = false;

		if (preg_match('/(\w+)\s*[-–]\s*(\w+)/', $jadwalLower, $m)) {
			$awal  = $hariMap[$m[1]] ?? null;
			$akhir = $hariMap[$m[2]] ?? null;
			if ($awal && $akhir) {
				$hariDibuka = ($hariIni >= $awal && $hariIni <= $akhir);
			}
		} else {
			foreach ($hariMap as $nama => $num) {
				if (str_contains($jadwalLower, $nama) && $hariIni === $num) {
					$hariDibuka = true;
					break;
				}
			}
		}

		if (!$hariDibuka) {
			return false;
		}

		// Deteksi jam buka dari field jam_operasional (contoh: "08.00 - 12.00")
		$jamOp = str_replace('.', ':', $jamOp);
		if (preg_match('/(\d{1,2}:\d{2})\s*[-–]\s*(\d{1,2}:\d{2})/', $jamOp, $m)) {
			return ($jamNow >= $m[1] && $jamNow <= $m[2]);
		}

		return false;
	}
}

if (!function_exists('generateKode')) {
	function generateKode(mysqli $mysqli): string
	{
		$tanggal = date('Ymd');
		$prefix = 'PKM-' . $tanggal . '-';
		$total = 0;

		$stmt = $mysqli->prepare('SELECT COUNT(*) AS total FROM pendaftaran WHERE kode_daftar LIKE ?');
		if ($stmt) {
			$like = $prefix . '%';
			$stmt->bind_param('s', $like);
			$stmt->execute();
			$result = $stmt->get_result();
			if ($result) {
				$total = (int) ($result->fetch_assoc()['total'] ?? 0);
			}
			$stmt->close();
		}

		return $prefix . str_pad((string) ($total + 1), 4, '0', STR_PAD_LEFT);
	}
}