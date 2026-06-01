<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
	header('Location: ../login.php');
	exit;
}

echo 'Halaman data poli admin akan dikembangkan pada tahap berikutnya.';
