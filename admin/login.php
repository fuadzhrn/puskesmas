<?php
session_start();

if (isset($_SESSION['admin_id'])) {
	header('Location: dashboard.php');
	exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login Admin</title>
	<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
	<h1>Login Admin</h1>
	<p>Halaman login admin akan dikembangkan pada tahap berikutnya.</p>
</body>
</html>
