<?php
session_start();
include '../connection/db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$order_id = $_GET['id'] ?? '';
$errorMessage = '';
$successMessage = '';

// Fetch order details
if ($order_id) {
    $orderQuery = $conn->prepare("SELECT * FROM orders WHERE order_id = :order_id");
    $orderQuery->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $orderQuery->execute();
    $order = $orderQuery->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $errorMessage = "Order not found.";
    }
}

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'];

    // Update the status in the database
    $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE order_id = :order_id");
    $stmt->bindParam(':status', $new_status, PDO::PARAM_STR);
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $successMessage = "Status berhasil diperbarui!";
    } else {
        $errorMessage = "Gagal memperbarui status.";
    }
}

// Define status options
$statusOptions = ['Pending', 'In-Process', 'Completed'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Order</title>
    <link rel="stylesheet" href="../css_folder/style.css?v=<?php echo time(); ?>">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Status Pesanan</h2>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?= $errorMessage ?></div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php endif; ?>

        <?php if ($order): ?>
            <form method="post">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control" name="status" id="status" required>
                        <?php foreach ($statusOptions as $option): ?>
                            <option value="<?= $option ?>" <?= $option == $order['status'] ? 'selected' : '' ?>><?= $option ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Perbarui Status</button>
                <a href="orders_admin.php" class="btn btn-secondary ml-2">Kembali ke Daftar Pesanan</a>
            </form>
        <?php else: ?>
            <p>Pesanan tidak ditemukan.</p>
        <?php endif; ?>
    </div>
</body>
</html>
