<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
	header('Location: ../login.php');
	exit;
}

echo 'Form tambah poli akan dikembangkan pada tahap berikutnya.';
