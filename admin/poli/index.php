<?php
session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Data Poli';
$adminCurrentPage = 'poli';

$conn = $mysqli;

$sql = "
    SELECT 
        id,
        nama_poli,
        deskripsi,
        dokter,
        jadwal,
        jam_operasional,
        is_active,
        created_at,
        updated_at
    FROM poli
    ORDER BY id ASC
";

$result = $conn->query($sql);

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
                <i class="bi bi-hospital"></i>
                Data Poli
            </h1>
            <p>Kelola data layanan poli yang tersedia di puskesmas.</p>
        </div>

        <div class="page-actions">
            <a href="tambah.php" class="btn-admin btn-primary">
                <i class="bi bi-plus-circle"></i>
                Tambah Poli
            </a>
        </div>
    </div>

    <section class="admin-card">
        <div class="admin-card-header">
            <div>
                <h2>Daftar Layanan Poli</h2>
                <p>Data poli aktif akan tampil di halaman layanan user.</p>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Poli</th>
                        <th>Dokter</th>
                        <th>Jadwal</th>
                        <th>Jam Operasional</th>
                        <th>Status</th>
                        <th>Dibuat</th>
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
                                        <?= htmlspecialchars($row['nama_poli'], ENT_QUOTES, 'UTF-8'); ?>
                                    </strong>

                                    <?php if (!empty($row['deskripsi'])): ?>
                                        <br>
                                        <small class="table-muted">
                                            <?= htmlspecialchars(mb_strimwidth($row['deskripsi'], 0, 70, '...'), ENT_QUOTES, 'UTF-8'); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['dokter'] ?: '-', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['jadwal'] ?: '-', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['jam_operasional'] ?: '-', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <span class="status-badge <?= statusPoliClass($row['is_active']); ?>">
                                        <?= htmlspecialchars(formatStatusPoli($row['is_active']), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>

                                <td>
                                    <?= !empty($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : '-'; ?>
                                </td>

                                <td>
                                    <a 
                                        href="edit.php?id=<?= (int) $row['id']; ?>" 
                                        class="btn-admin btn-small btn-outline"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="bi bi-hospital"></i>
                                    <h3>Data poli belum tersedia.</h3>
                                    <p>Silakan tambahkan layanan poli terlebih dahulu.</p>

                                    <a href="tambah.php" class="btn-admin btn-primary" style="margin-top: 14px;">
                                        <i class="bi bi-plus-circle"></i>
                                        Tambah Poli
                                    </a>
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