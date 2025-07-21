<?php
session_start();
include '../connection/db.php';

// Check user role
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$order_id = $_GET['id'] ?? '';

if ($order_id) {
    // Check if the order status is 'Completed'
    $statusQuery = $conn->prepare("SELECT status FROM orders WHERE order_id = :order_id");
    $statusQuery->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $statusQuery->execute();
    $orderStatus = $statusQuery->fetch(PDO::FETCH_ASSOC);

    if (!$orderStatus) {
        echo "Pesanan tidak ditemukan!.";
    } elseif ($orderStatus['status'] === 'Completed') {
        echo "Pesanan yang telah selesai tidak dapat dibatalkan.";
    } else {
        try {
            // Begin a transaction
            $conn->beginTransaction();

            // Delete associated order items
            $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = :order_id");
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $stmt->execute();

            // Delete the order itself
            $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = :order_id");
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $stmt->execute();

            // Commit the transaction
            $conn->commit();

            // Redirect based on user role
            if ($_SESSION['role'] === 'admin') {
                header("Location: orders_admin.php?message=Order deleted successfully");
            } else {
                header("Location: orders_user.php?message=Order canceled successfully");
            }
        } catch (PDOException $e) {
            // Roll back transaction on error
            $conn->rollBack();
            echo "Error deleting order: " . $e->getMessage();
        }
    }
} else {
    echo "No order ID provided.";
}
?>
