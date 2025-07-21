<?php
session_start();
include '../connection/db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    echo "Order ID tidak ditemukan!";
    exit;
}

// Use PDO to fetch order details
$query = "SELECT orders.order_id, users.name AS user_name, users.email, orders.order_date, stages.name AS stage_name, stages.price,
                 order_items.quantity, order_items.num_stages, orders.total_price, orders.status
          FROM orders
          JOIN users ON orders.user_id = users.user_id
          JOIN order_items ON orders.order_id = order_items.order_id
          JOIN stages ON order_items.stage_id = stages.stage_id
          WHERE orders.order_id = :order_id";

$stmt = $conn->prepare($query);
$stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "Pesanan tidak ditemukan!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detail Pesanan</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Detail Pesanan</h2>
        <table class="table">
            <tr><th>Order ID:</th><td><?= htmlspecialchars($order['order_id']) ?></td></tr>
            <tr><th>Nama Pemesan:</th><td><?= htmlspecialchars($order['user_name']) ?></td></tr>
            <tr><th>Email Pemesan:</th><td><?= htmlspecialchars($order['email']) ?></td></tr>
            <tr><th>Tanggal:</th><td><?= htmlspecialchars($order['order_date']) ?></td></tr>
            <tr><th>Jenis Panggung:</th><td><?= htmlspecialchars($order['stage_name']) ?></td></tr>
            <tr><th>Harga Sewa/Hari:</th><td><?= htmlspecialchars($order['price']) ?></td></tr>
            <tr><th>Jumlah Hari:</th><td><?= htmlspecialchars($order['quantity']) ?></td></tr>
            <tr><th>Jumlah Panggung:</th><td><?= htmlspecialchars($order['num_stages']) ?></td></tr>
            <tr><th>Total:</th><td>Rp <?= number_format($order['total_price'], 2) ?></td></tr>
            <tr><th>Status:</th><td><?= htmlspecialchars($order['status']) ?></td></tr>
        </table>
        <a href="orders_admin.php" class="btn btn-primary">Kembali</a>
    </div>
</body>
</html>
