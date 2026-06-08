<?php
session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Edit Poli';
$adminCurrentPage = 'poli';

$conn = $mysqli;

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

function getPoliById($conn, $id)
{
    $stmt = $conn->prepare("
        SELECT
            id,
            nama_poli,
            deskripsi,
            kondisi_ditangani,
            dokter,
            jadwal,
            jam_operasional,
            is_active,
            created_at,
            updated_at
        FROM poli
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

$poli = getPoliById($conn, $id);

if (!$poli) {
    header('Location: index.php');
    exit;
}

$namaPoli = $poli['nama_poli'];
$deskripsi = $poli['deskripsi'];
$kondisiDitangani = $poli['kondisi_ditangani'] ?? '';
$dokter = $poli['dokter'];
$jadwal = $poli['jadwal'];
$jamOperasional = $poli['jam_operasional'];
$isActive = (string) $poli['is_active'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaPoli = trim($_POST['nama_poli'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $kondisiDitangani = trim($_POST['kondisi_ditangani'] ?? '');
    $dokter = trim($_POST['dokter'] ?? '');
    $jadwal = trim($_POST['jadwal'] ?? '');
    $jamOperasional = trim($_POST['jam_operasional'] ?? '');
    $isActive = trim($_POST['is_active'] ?? '1');

    if ($namaPoli === '') {
        $error = 'Nama poli wajib diisi.';
    } elseif ($deskripsi === '') {
        $error = 'Deskripsi wajib diisi.';
    } elseif ($dokter === '') {
        $error = 'Nama dokter wajib diisi.';
    } elseif ($jadwal === '') {
        $error = 'Jadwal wajib diisi.';
    } elseif ($jamOperasional === '') {
        $error = 'Jam operasional wajib diisi.';
    } elseif (!in_array($isActive, ['0', '1'], true)) {
        $error = 'Status poli tidak valid.';
    } else {
        $isActiveInt = (int) $isActive;

        $stmt = $conn->prepare("
            UPDATE poli
            SET
                nama_poli = ?,
                deskripsi = ?,
                kondisi_ditangani = ?,
                dokter = ?,
                jadwal = ?,
                jam_operasional = ?,
                is_active = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "ssssssii",
            $namaPoli,
            $deskripsi,
            $kondisiDitangani,
            $dokter,
            $jadwal,
            $jamOperasional,
            $isActiveInt,
            $id
        );

        if ($stmt->execute()) {
            $success = 'Data poli berhasil diperbarui.';
            $poli = getPoliById($conn, $id);
            $namaPoli = $poli['nama_poli'];
            $deskripsi = $poli['deskripsi'];
            $kondisiDitangani = $poli['kondisi_ditangani'] ?? '';
            $dokter = $poli['dokter'];
            $jadwal = $poli['jadwal'];
            $jamOperasional = $poli['jam_operasional'];
            $isActive = (string) $poli['is_active'];
        } else {
            $error = 'Data poli gagal diperbarui.';
        }
    }
}

function formatStatusPoli($isActive)
{
    return (int) $isActive === 1 ? 'Aktif' : 'Nonaktif';
}

function statusPoliClass($isActive)
{
    return (int) $isActive === 1 ? 'status-aktif' : 'status-nonaktif';
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="admin-main-content">
    <div class="admin-page-head admin-page-head-row">
        <div>
            <h1>
                <i class="bi bi-pencil-square"></i>
                Edit Poli
            </h1>
            <p>Perbarui informasi layanan poli dan status ketersediaannya.</p>
        </div>

        <div class="page-actions">
            <a href="index.php" class="btn-admin btn-outline">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>
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
                <i class="bi bi-hospital"></i>
                Nama Poli
            </span>
            <strong><?= htmlspecialchars($poli['nama_poli'], ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>

        <div class="summary-item">
            <span class="summary-label">
                <i class="bi bi-info-circle"></i>
                Status Saat Ini
            </span>
            <span class="status-badge <?= statusPoliClass($poli['is_active']); ?>">
                <?= htmlspecialchars(formatStatusPoli($poli['is_active']), ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </div>

        <div class="summary-item">
            <span class="summary-label">
                <i class="bi bi-calendar-check"></i>
                Dibuat
            </span>
            <strong>
                <?= !empty($poli['created_at']) ? date('d M Y', strtotime($poli['created_at'])) : '-'; ?>
            </strong>
        </div>

        <div class="summary-item">
            <span class="summary-label">
                <i class="bi bi-arrow-repeat"></i>
                Terakhir Update
            </span>
            <strong>
                <?= !empty($poli['updated_at']) ? date('d M Y H:i', strtotime($poli['updated_at'])) : '-'; ?>
            </strong>
        </div>
    </section>

    <section class="admin-card">
        <div class="detail-card-header">
            <h2>
                <i class="bi bi-hospital"></i>
                Form Edit Poli
            </h2>
            <p>Ubah data layanan poli sesuai kebutuhan.</p>
        </div>

        <form method="POST" action="" class="admin-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nama_poli">Nama Poli</label>
                    <input
                        type="text"
                        id="nama_poli"
                        name="nama_poli"
                        class="form-control"
                        value="<?= htmlspecialchars($namaPoli, ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="Contoh: Poli Umum"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="dokter">Nama Dokter</label>
                    <input
                        type="text"
                        id="dokter"
                        name="dokter"
                        class="form-control"
                        value="<?= htmlspecialchars($dokter, ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="Contoh: dr. Ahmad Pratama"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="jadwal">Jadwal</label>
                    <input
                        type="text"
                        id="jadwal"
                        name="jadwal"
                        class="form-control"
                        value="<?= htmlspecialchars($jadwal, ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="Contoh: Senin - Sabtu"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="jam_operasional">Jam Operasional</label>
                    <input
                        type="text"
                        id="jam_operasional"
                        name="jam_operasional"
                        class="form-control"
                        value="<?= htmlspecialchars($jamOperasional, ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="Contoh: 08.00 - 12.00"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="is_active">Status Poli</label>
                    <select name="is_active" id="is_active" class="form-control" required>
                        <option value="1" <?= $isActive === '1' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?= $isActive === '0' ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>

                <div class="form-group detail-full">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea
                        id="deskripsi"
                        name="deskripsi"
                        class="form-control textarea-control"
                        rows="5"
                        placeholder="Tuliskan deskripsi singkat layanan poli..."
                        required
                    ><?= htmlspecialchars($deskripsi, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div class="form-group detail-full">
                    <label for="kondisi_ditangani">Kondisi yang Ditangani</label>
                    <input
                        type="text"
                        id="kondisi_ditangani"
                        name="kondisi_ditangani"
                        class="form-control"
                        value="<?= htmlspecialchars($kondisiDitangani, ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="Contoh: Demam, Batuk, Pilek, Sakit Kepala"
                    >
                    <small class="form-hint">Pisahkan setiap kondisi dengan tanda koma. Akan ditampilkan sebagai tag di halaman layanan.</small>
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