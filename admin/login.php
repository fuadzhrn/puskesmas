<?php
session_start();

require_once __DIR__ . '/../config/koneksi.php';

if (isset($_SESSION['admin_id'])) {
	header('Location: dashboard.php');
	exit;
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim((string) ($_POST['username'] ?? ''));
	$password = (string) ($_POST['password'] ?? '');

	if ($username === '') {
		$errors[] = 'Username wajib diisi.';
	}

	if ($password === '') {
		$errors[] = 'Password wajib diisi.';
	}

	if (empty($errors)) {
		$kolomNama = 'nama';
		$cekKolom = $mysqli->query("SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'nama'");
		if ($cekKolom) {
			$rowKolom = $cekKolom->fetch_assoc();
			if (((int) ($rowKolom['total'] ?? 0)) === 0) {
				$kolomNama = 'nama_lengkap';
			}
		}

		$sql = "SELECT id, {$kolomNama} AS nama, username, password FROM users WHERE username = ? LIMIT 1";
		$stmt = $mysqli->prepare($sql);
		if ($stmt) {
			$stmt->bind_param('s', $username);
			$stmt->execute();
			$result = $stmt->get_result();
			$user = $result ? $result->fetch_assoc() : null;
			$stmt->close();

			if ($user && password_verify($password, (string) $user['password'])) {
				session_regenerate_id(true);
				$_SESSION['admin_id'] = (int) $user['id'];
				$_SESSION['admin_nama'] = (string) ($user['nama'] ?? 'Admin');
				$_SESSION['admin_username'] = (string) ($user['username'] ?? $username);
				header('Location: dashboard.php');
				exit;
			}

			$errors[] = 'Username atau password salah.';
		} else {
			$errors[] = 'Sistem login sedang bermasalah. Silakan coba lagi.';
		}
	}
}

function esc(string $value): string
{
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login Admin</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-login-page">
	<main class="login-container">
		<section class="login-card" aria-label="Form login admin">
			<div class="login-logo" aria-hidden="true">
				<i class="bi bi-hospital-fill"></i>
			</div>
			<h1 class="login-title">Login Admin</h1>
			<p class="login-subtitle">Masuk untuk mengelola data pendaftaran puskesmas.</p>

			<?php if (!empty($errors)): ?>
				<div class="alert-error" role="alert">
					<?php foreach ($errors as $error): ?>
						<p><?= esc($error) ?></p>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<form method="post" action="login.php" novalidate>
				<div class="form-group">
					<label for="username">Username</label>
					<div class="input-wrapper">
						<i class="bi bi-person"></i>
						<input type="text" id="username" name="username" value="<?= esc($username) ?>" autocomplete="username" placeholder="Masukkan username">
					</div>
				</div>

				<div class="form-group">
					<label for="password">Password</label>
					<div class="input-wrapper">
						<i class="bi bi-lock"></i>
						<input type="password" id="password" name="password" autocomplete="current-password" placeholder="Masukkan password">
					</div>
				</div>

				<button type="submit" class="btn-login">Masuk</button>
			</form>

			<a href="../index.php" class="back-link">Kembali ke Beranda</a>
		</section>
	</main>
</body>
</html>
