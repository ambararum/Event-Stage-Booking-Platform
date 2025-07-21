<?php
session_start();
include '../connection/db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Check if ID is provided
if (isset($_GET['id'])) {
    $stage_id = $_GET['id'];

    // Delete the stage from the database using a prepared statement
    $stmt = $conn->prepare("DELETE FROM stages WHERE stage_id = :stage_id");
    $stmt->bindParam(':stage_id', $stage_id, PDO::PARAM_INT);
    $stmt->execute();
}

// Redirect back to stages.php after deletion
header('Location: stages_admin.php');
exit;
?>
