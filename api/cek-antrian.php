<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/koneksi.php';

$poliId  = (int)   ($_GET['poli_id'] ?? 0);
$tanggal = trim(   ($_GET['tanggal'] ?? ''));
$sesi    = trim(   ($_GET['sesi']    ?? ''));

$sesiValid = ['Pagi', 'Siang', 'Sore'];

if ($poliId <= 0 || $tanggal === '' || !in_array($sesi, $sesiValid, true)) {
    echo json_encode(['success' => false, 'jumlah' => 0, 'antrian' => 1]);
    exit;
}

// Deteksi nama kolom sesi yang dipakai
$kolomSesi = 'sesi_waktu';
$stmtKolom = $mysqli->query("SELECT COUNT(*) AS total FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pendaftaran' AND COLUMN_NAME = 'sesi'");
if ($stmtKolom && (int)($stmtKolom->fetch_assoc()['total'] ?? 0) > 0) {
    $kolomSesi = 'sesi';
}

$stmt = $mysqli->prepare(
    "SELECT COUNT(*) AS total FROM pendaftaran
     WHERE poli_id = ? AND tanggal_kunjungan = ? AND {$kolomSesi} = ?"
);

$jumlah = 0;
if ($stmt) {
    $stmt->bind_param('iss', $poliId, $tanggal, $sesi);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $jumlah = (int)($result->fetch_assoc()['total'] ?? 0);
    }
    $stmt->close();
}

echo json_encode([
    'success' => true,
    'jumlah'  => $jumlah,
    'antrian' => $jumlah + 1,
]);
