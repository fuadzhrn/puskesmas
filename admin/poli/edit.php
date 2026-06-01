<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
	header('Location: ../login.php');
	exit;
}

echo 'Form edit poli akan dikembangkan pada tahap berikutnya.';
