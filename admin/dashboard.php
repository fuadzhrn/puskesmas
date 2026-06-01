<?php
session_start();

require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function getTotal(mysqli $conn, string $query): int
{
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return 0;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $total = 0;
    if ($result) {
        $total = (int) ($result->fetch_assoc()['total'] ?? 0);
    }

    $stmt->close();
    return $total;
}

$adminNama = esc((string) ($_SESSION['admin_nama'] ?? 'Admin'));

$totalPendaftaran = getTotal($mysqli, 'SELECT COUNT(*) AS total FROM pendaftaran');
$pendaftaranHariIni = getTotal($mysqli, 'SELECT COUNT(*) AS total FROM pendaftaran WHERE DATE(created_at) = CURDATE()');
$totalMenunggu = getTotal($mysqli, "SELECT COUNT(*) AS total FROM pendaftaran WHERE LOWER(status) = 'menunggu'");
$totalDikonfirmasi = getTotal($mysqli, "SELECT COUNT(*) AS total FROM pendaftaran WHERE LOWER(status) = 'dikonfirmasi'");
$totalSelesai = getTotal($mysqli, "SELECT COUNT(*) AS total FROM pendaftaran WHERE LOWER(status) = 'selesai'");
$totalDibatalkan = getTotal($mysqli, "SELECT COUNT(*) AS total FROM pendaftaran WHERE LOWER(status) = 'dibatalkan'");

$adaStatusPoli = getTotal(
    $mysqli,
    "SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'poli' AND COLUMN_NAME = 'status'"
) > 0;

$totalPoliAktif = $adaStatusPoli
    ? getTotal($mysqli, "SELECT COUNT(*) AS total FROM poli WHERE status = 'aktif'")
    : getTotal($mysqli, 'SELECT COUNT(*) AS total FROM poli WHERE is_active = 1');

$cards = [
    [
        'icon' => 'bi bi-clipboard-data',
        'title' => 'Total Pendaftaran',
        'value' => $totalPendaftaran,
        'desc' => 'Seluruh data pendaftaran yang tersimpan.',
        'class' => 'card-total',
    ],
    [
        'icon' => 'bi bi-calendar-check',
        'title' => 'Pendaftaran Hari Ini',
        'value' => $pendaftaranHariIni,
        'desc' => 'Pendaftaran yang masuk pada hari ini.',
        'class' => 'card-today',
    ],
    [
        'icon' => 'bi bi-hourglass-split',
        'title' => 'Status Menunggu',
        'value' => $totalMenunggu,
        'desc' => 'Pendaftaran yang belum diproses lanjut.',
        'class' => 'card-waiting',
    ],
    [
        'icon' => 'bi bi-check-circle',
        'title' => 'Status Dikonfirmasi',
        'value' => $totalDikonfirmasi,
        'desc' => 'Pendaftaran yang telah dikonfirmasi admin.',
        'class' => 'card-confirmed',
    ],
    [
        'icon' => 'bi bi-patch-check',
        'title' => 'Status Selesai',
        'value' => $totalSelesai,
        'desc' => 'Pendaftaran yang sudah selesai dilayani.',
        'class' => 'card-completed',
    ],
    [
        'icon' => 'bi bi-x-circle',
        'title' => 'Status Dibatalkan',
        'value' => $totalDibatalkan,
        'desc' => 'Pendaftaran yang dibatalkan.',
        'class' => 'card-cancelled',
    ],
    [
        'icon' => 'bi bi-hospital',
        'title' => 'Total Poli Aktif',
        'value' => $totalPoliAktif,
        'desc' => 'Poli aktif yang bisa dipilih pasien.',
        'class' => 'card-poli',
    ],
];

$adminPageTitle = 'Dashboard Admin - Puskesmas Online';
$adminBodyClass = 'admin-dashboard-page';
$adminCurrentPage = 'dashboard';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<main class="admin-main-content">
    <section class="admin-page-head">
        <h1>Dashboard Admin</h1>
        <p>Selamat datang, <?= $adminNama ?>. Kelola data pendaftaran puskesmas dengan mudah.</p>
    </section>

    <section class="admin-stats-grid">
        <?php foreach ($cards as $card): ?>
            <article class="admin-stat-card <?= esc($card['class']) ?>">
                <div class="stat-icon" aria-hidden="true">
                    <i class="<?= esc($card['icon']) ?>"></i>
                </div>
                <div>
                    <p class="stat-title"><?= esc($card['title']) ?></p>
                    <p class="stat-value"><?= number_format((int) $card['value']) ?></p>
                    <p class="stat-desc"><?= esc($card['desc']) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="admin-quick-actions">
        <div class="quick-actions-head">
            <h2>Aksi Cepat</h2>
            <p>Gunakan menu cepat berikut untuk mengelola data utama aplikasi.</p>
        </div>
        <div class="quick-actions-grid">
            <a href="pendaftaran/index.php" class="quick-action-btn">
                <i class="bi bi-card-checklist"></i>
                Lihat Data Pendaftaran
            </a>
            <a href="poli/index.php" class="quick-action-btn">
                <i class="bi bi-hospital"></i>
                Kelola Data Poli
            </a>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
