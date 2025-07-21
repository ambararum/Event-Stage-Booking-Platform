<?php 
session_start();
include '../connection/db.php';
if ($_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

// Fetch the user’s orders based on their user ID
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM orders WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Anda</title>
    <link rel="stylesheet" href="../css_folder/style.css?v=<?php echo time(); ?>">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <div class="bg-light" id="sidebar">
            <br>
            <h4 class="text-center">Customer Menu</h4>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="rental_user.php">Buat Pesanan</a></li>
                <li class="nav-item"><a class="nav-link" href="orders_user.php">Pesanan Anda</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="container mt-5">
            <h2 class="text-center">Pesanan Anda</h2>
            <br>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Tanggal</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order) : ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['order_date']) ?></td>
                            <td>Rp <?= number_format($order['total_price'], 2) ?></td>
                            <td><?= htmlspecialchars($order['status']) ?></td>
                            <td>
                                <a href="editOrder_user.php?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-edit">Edit</a>
                                <a href="delete_order.php?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-cancel" onclick="return confirm('Membatalkan pesanan berarti menghapusnya, apakah Anda ingin melanjutkan?')">Cancel</a>
                                <a href="orderDetail_user.php?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-info">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
