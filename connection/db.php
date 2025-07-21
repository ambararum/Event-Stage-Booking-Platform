<?php
// db.php

$host = 'localhost';
$dbname = 'rental_db';
$username = 'root';
$password = '';

try {
    // Membuat instance PDO dan mengatur mode error menjadi exception
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Mencatat error ke file log dan menampilkan pesan umum
    error_log("Database connection failed: " . $e->getMessage(), 3, __DIR__ . '/error_log.log');
    die("Koneksi gagal. Silakan coba lagi nanti.");
}
