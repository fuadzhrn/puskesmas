<?php
session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Tambah Poli';
$adminCurrentPage = 'poli';

$conn = $mysqli;

$success = '';
$error = '';

$namaPoli = '';
$deskripsi = '';
$dokter = '';
$jadwal = '';
$jamOperasional = '';
$isActive = '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaPoli = trim($_POST['nama_poli'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
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
            INSERT INTO poli 
                (nama_poli, deskripsi, dokter, jadwal, jam_operasional, is_active)
            VALUES 
                (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sssssi",
            $namaPoli,
            $deskripsi,
            $dokter,
            $jadwal,
            $jamOperasional,
            $isActiveInt
        );

        if ($stmt->execute()) {
            $success = 'Data poli berhasil ditambahkan.';

            $namaPoli = '';
            $deskripsi = '';
            $dokter = '';
            $jadwal = '';
            $jamOperasional = '';
            $isActive = '1';
        } else {
            $error = 'Data poli gagal ditambahkan.';
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
                <i class="bi bi-plus-circle"></i>
                Tambah Poli
            </h1>
            <p>Tambahkan layanan poli baru yang akan ditampilkan pada halaman user.</p>
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

    <section class="admin-card">
        <div class="detail-card-header">
            <h2>
                <i class="bi bi-hospital"></i>
                Form Tambah Poli
            </h2>
            <p>Isi data layanan poli dengan lengkap dan benar.</p>
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
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-admin btn-primary">
                    <i class="bi bi-save"></i>
                    Simpan Poli
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