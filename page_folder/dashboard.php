<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Memeriksa peran pengguna
if ($_SESSION['role'] == 'admin') {
    header("Location: orders_admin.php");
} else {
    header("Location: rental_user.php");
}
?>
