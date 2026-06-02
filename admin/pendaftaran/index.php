<?php
session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Data Pendaftaran';
$adminCurrentPage = 'pendaftaran';

$conn = $mysqli;

$q = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
$poli = trim($_GET['poli'] ?? '');

$allowedStatus = ['menunggu', 'dikonfirmasi', 'selesai', 'dibatalkan'];

$where = [];
$params = [];
$types = '';

$sql = "
    SELECT 
        p.id,
        p.kode_daftar,
        p.nama_pasien,
        p.nik,
        p.no_hp,
        p.tanggal_kunjungan,
        p.sesi_waktu,
        p.nomor_antrian,
        p.status,
        p.created_at,
        pl.nama_poli
    FROM pendaftaran p
    JOIN poli pl ON p.poli_id = pl.id
";

if ($q !== '') {
    $where[] = "(p.kode_daftar LIKE ? OR p.nama_pasien LIKE ? OR p.nik LIKE ?)";
    $search = '%' . $q . '%';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= 'sss';
}

if ($status !== '' && in_array($status, $allowedStatus, true)) {
    $where[] = "p.status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($poli !== '') {
    $poliId = (int) $poli;
    if ($poliId > 0) {
        $where[] = "p.poli_id = ?";
        $params[] = $poliId;
        $types .= 'i';
    }
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$poliResult = mysqli_query($conn, "SELECT id, nama_poli FROM poli ORDER BY nama_poli ASC");

function formatStatus($status)
{
    switch ($status) {
        case 'menunggu':
            return 'Menunggu';
        case 'dikonfirmasi':
            return 'Dikonfirmasi';
        case 'selesai':
            return 'Selesai';
        case 'dibatalkan':
            return 'Dibatalkan';
        default:
            return ucfirst($status);
    }
}

function statusClass($status)
{
    switch ($status) {
        case 'menunggu':
            return 'status-menunggu';
        case 'dikonfirmasi':
            return 'status-dikonfirmasi';
        case 'selesai':
            return 'status-selesai';
        case 'dibatalkan':
            return 'status-dibatalkan';
        default:
            return '';
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="admin-main-content">
    <div class="admin-page-head">
        <h1>
            <i class="bi bi-clipboard-data"></i>
            Data Pendaftaran
        </h1>
        <p>Kelola dan pantau data pendaftaran pasien puskesmas.</p>
    </div>

    <section class="admin-card">
        <form method="GET" action="" class="filter-form">
            <div class="filter-grid">
                <div class="form-group">
                    <label for="q">Pencarian</label>
                    <div class="input-wrapper">
                        <i class="bi bi-search"></i>
                        <input 
                            type="text" 
                            id="q" 
                            name="q" 
                            value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="Cari kode daftar, nama, atau NIK..."
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">Semua Status</option>
                        <?php foreach ($allowedStatus as $itemStatus): ?>
                            <option 
                                value="<?= htmlspecialchars($itemStatus, ENT_QUOTES, 'UTF-8'); ?>"
                                <?= $status === $itemStatus ? 'selected' : ''; ?>
                            >
                                <?= htmlspecialchars(formatStatus($itemStatus), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="poli">Poli</label>
                    <select name="poli" id="poli" class="form-control">
                        <option value="">Semua Poli</option>
                        <?php if ($poliResult && mysqli_num_rows($poliResult) > 0): ?>
                            <?php while ($poliData = mysqli_fetch_assoc($poliResult)): ?>
                                <option 
                                    value="<?= (int) $poliData['id']; ?>"
                                    <?= ((string) $poli === (string) $poliData['id']) ? 'selected' : ''; ?>
                                >
                                    <?= htmlspecialchars($poliData['nama_poli'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-admin btn-primary">
                        <i class="bi bi-funnel"></i>
                        Filter
                    </button>

                    <a href="index.php" class="btn-admin btn-outline">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </section>

    <section class="admin-card">
        <div class="admin-card-header">
            <div>
                <h2>Daftar Pendaftaran Pasien</h2>
                <p>Data terbaru ditampilkan paling atas.</p>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Daftar</th>
                        <th>Nama Pasien</th>
                        <th>NIK</th>
                        <th>No HP</th>
                        <th>Poli</th>
                        <th>Tanggal Kunjungan</th>
                        <th>Sesi</th>
                        <th>No. Antrian</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($row['kode_daftar'], ENT_QUOTES, 'UTF-8'); ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_pasien'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nik'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['no_hp'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_poli'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?= date('d M Y', strtotime($row['tanggal_kunjungan'])); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['sesi_waktu'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <span class="queue-number">
                                        <?= (int) $row['nomor_antrian']; ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge <?= statusClass($row['status']); ?>">
                                        <?= htmlspecialchars(formatStatus($row['status']), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>

                                <td>
                                    <a 
                                        href="detail.php?id=<?= (int) $row['id']; ?>" 
                                        class="btn-admin btn-small btn-outline"
                                    >
                                        <i class="bi bi-eye"></i>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <h3>Data pendaftaran belum tersedia.</h3>
                                    <p>Belum ada data pendaftaran yang sesuai dengan pencarian atau filter.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>