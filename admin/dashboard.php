<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
	header('Location: login.php');
	exit;
}

echo 'Dashboard admin akan dikembangkan pada tahap berikutnya.';
