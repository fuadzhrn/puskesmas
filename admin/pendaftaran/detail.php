<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
	header('Location: ../login.php');
	exit;
}

echo 'Halaman detail pendaftaran admin akan dikembangkan pada tahap berikutnya.';
