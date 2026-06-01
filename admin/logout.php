<?php
session_start();

unset($_SESSION['admin_id'], $_SESSION['admin_nama'], $_SESSION['admin_username']);
session_unset();
session_destroy();

header('Location: login.php');
exit;
