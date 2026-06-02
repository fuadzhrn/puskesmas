<?php
session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Detail Pendaftaran';
$adminCurrentPage = 'pendaftaran';

$conn = $mysqli;

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

$allowedStatus = ['menunggu', 'dikonfirmasi', 'selesai', 'dibatalkan'];

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
            return ucfirst((string) $status);
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

function getPendaftaranById($conn, $id)
{
    $stmt = $conn->prepare("
        SELECT 
            p.*,
            pl.nama_poli,
            pl.dokter,
            pl.jadwal,
            pl.jam_operasional
        FROM pendaftaran p
        JOIN poli pl ON p.poli_id = pl.id
        WHERE p.id = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

$data = getPendaftaranById($conn, $id);

if (!$data) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = trim($_POST['status'] ?? '');
    $catatanAdmin = trim($_POST['catatan_admin'] ?? '');

    if (!in_array($status, $allowedStatus, true)) {
        $error = 'Status pendaftaran tidak valid.';
    } else {
        $stmtUpdate = $conn->prepare("
            UPDATE pendaftaran 
            SET status = ?, catatan_admin = ?
            WHERE id = ?
        ");

        $stmtUpdate->bind_param("ssi", $status, $catatanAdmin, $id);

        if ($stmtUpdate->execute()) {
            $success = 'Status pendaftaran berhasil diperbarui.';
            $data = getPendaftaranById($conn, $id);
        } else {
            $error = 'Status pendaftaran gagal diperbarui.';
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="admin-main-content">
    <div class="admin-page-head admin-page-head-row">
        <div>
            <h1>
                <i class="bi bi-eye"></i>
                Detail Pendaftaran
            </h1>
            <p>Lihat detail data pasien dan perbarui status pendaftaran.</p>
        </div>

        <div class="page-actions">
            <a href="index.php" class="btn-admin btn-outline">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>

            <button type="button" onclick="window.print()" class="btn-admin btn-primary">
                <i class="bi bi-printer"></i>
                Cetak Bukti
            </button>
        </div>
    </div>

    <?php if ($success !== ''): ?>
        <div class="alert-success">
            <i class="bi bi-check-circle"></i>
            <span><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert-error">
            <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    <?php endif; ?>

    <section class="admin-card summary-card">
        <div class="summary-item">
            <span class="summary-label">
                <i class="bi bi-upc-scan"></i>
                Kode Pendaftaran
            </span>
            <strong><?= htmlspecialchars($data['kode_daftar'], ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>

        <div class="summary-item">
            <span class="summary-label">
                <i class="bi bi-ticket-perforated"></i>
                Nomor Antrian
            </span>
            <strong>#<?= (int) $data['nomor_antrian']; ?></strong>
        </div>

        <div class="summary-item">
            <span class="summary-label">
                <i class="bi bi-calendar-check"></i>
                Tanggal Kunjungan
            </span>
            <strong><?= date('d M Y', strtotime($data['tanggal_kunjungan'])); ?></strong>
        </div>

        <div class="summary-item">
            <span class="summary-label">
                <i class="bi bi-clock"></i>
                Sesi
            </span>
            <strong><?= htmlspecialchars($data['sesi_waktu'], ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>

        <div class="summary-item">
            <span class="summary-label">
                <i class="bi bi-info-circle"></i>
                Status
            </span>
            <span class="status-badge <?= statusClass($data['status']); ?>">
                <?= htmlspecialchars(formatStatus($data['status']), ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </div>
    </section>

    <section class="detail-grid">
        <div class="admin-card">
            <div class="detail-card-header">
                <h2>
                    <i class="bi bi-person"></i>
                    Data Pasien
                </h2>
            </div>

            <div class="detail-list">
                <div class="detail-item">
                    <span>Nama Pasien</span>
                    <strong><?= htmlspecialchars($data['nama_pasien'], ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>

                <div class="detail-item">
                    <span>NIK</span>
                    <strong><?= htmlspecialchars($data['nik'], ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>

                <div class="detail-item">
                    <span>Jenis Kelamin</span>
                    <strong><?= htmlspecialchars($data['jenis_kelamin'], ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>

                <div class="detail-item">
                    <span>Tanggal Lahir</span>
                    <strong>
                        <?= !empty($data['tanggal_lahir']) ? date('d M Y', strtotime($data['tanggal_lahir'])) : '-'; ?>
                    </strong>
                </div>

                <div class="detail-item">
                    <span>No HP</span>
                    <strong><?= htmlspecialchars($data['no_hp'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>

                <div class="detail-item detail-full">
                    <span>Alamat</span>
                    <strong><?= nl2br(htmlspecialchars($data['alamat'] ?: '-', ENT_QUOTES, 'UTF-8')); ?></strong>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="detail-card-header">
                <h2>
                    <i class="bi bi-hospital"></i>
                    Data Layanan
                </h2>
            </div>

            <div class="detail-list">
                <div class="detail-item">
                    <span>Poli Tujuan</span>
                    <strong><?= htmlspecialchars($data['nama_poli'], ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>

                <div class="detail-item">
                    <span>Dokter</span>
                    <strong><?= htmlspecialchars($data['dokter'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>

                <div class="detail-item">
                    <span>Jadwal Poli</span>
                    <strong><?= htmlspecialchars($data['jadwal'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>

                <div class="detail-item">
                    <span>Jam Operasional</span>
                    <strong><?= htmlspecialchars($data['jam_operasional'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>

                <div class="detail-item">
                    <span>Tanggal Daftar</span>
                    <strong><?= date('d M Y H:i', strtotime($data['created_at'])); ?></strong>
                </div>

                <div class="detail-item">
                    <span>Terakhir Update</span>
                    <strong>
                        <?= !empty($data['updated_at']) ? date('d M Y H:i', strtotime($data['updated_at'])) : '-'; ?>
                    </strong>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-card">
        <div class="detail-card-header">
            <h2>
                <i class="bi bi-pencil-square"></i>
                Update Status Pendaftaran
            </h2>
            <p>Ubah status pendaftaran dan tambahkan catatan untuk pasien jika diperlukan.</p>
        </div>

        <form method="POST" action="" class="admin-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="status">Status Pendaftaran</label>
                    <select name="status" id="status" class="form-control" required>
                        <?php foreach ($allowedStatus as $itemStatus): ?>
                            <option 
                                value="<?= htmlspecialchars($itemStatus, ENT_QUOTES, 'UTF-8'); ?>"
                                <?= $data['status'] === $itemStatus ? 'selected' : ''; ?>
                            >
                                <?= htmlspecialchars(formatStatus($itemStatus), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group detail-full">
                    <label for="catatan_admin">Catatan Admin</label>
                    <textarea 
                        name="catatan_admin" 
                        id="catatan_admin" 
                        class="form-control textarea-control" 
                        rows="5"
                        placeholder="Contoh: Mohon datang 15 menit sebelum jadwal dan membawa KTP/BPJS."
                    ><?= htmlspecialchars($data['catatan_admin'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-admin btn-primary">
                    <i class="bi bi-save"></i>
                    Simpan Perubahan
                </button>

                <a href="index.php" class="btn-admin btn-outline">
                    <i class="bi bi-x-circle"></i>
                    Batal
                </a>
            </div>
        </form>
    </section>
</main>

<?php include '../includes/footer.php'; ?>