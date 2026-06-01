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
