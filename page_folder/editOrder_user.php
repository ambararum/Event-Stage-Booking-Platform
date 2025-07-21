<?php
session_start();
include '../connection/db.php';

if ($_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$order_id = $_GET['id'] ?? '';
$errorMessage = '';
$successMessage = '';

// Fetch order details
if ($order_id) {
    $orderQuery = $conn->prepare("
        SELECT orders.order_id, orders.status, order_items.stage_id, stages.name AS stage_name, 
               stages.dimensions, stages.price, order_items.quantity, order_items.num_stages 
        FROM orders 
        JOIN order_items ON orders.order_id = order_items.order_id
        JOIN stages ON order_items.stage_id = stages.stage_id
        WHERE orders.order_id = :order_id
    ");
    $orderQuery->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $orderQuery->execute();
    $order = $orderQuery->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $errorMessage = "Order not found.";
    } elseif ($order['status'] !== 'Pending') {
        $errorMessage = "Pesanan tidak dapat diedit lagi karena sudah tidak dalam status 'Pending', jika ingin membatalkannya maka tekan 'Cancel'.";
    }
}

// Update order details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $order && $order['status'] === 'Pending') {
    $stage_id = $_POST['stage_id'];
    $quantity = $_POST['quantity'];
    $num_stages = $_POST['num_stages'];

    // Step 1: Update order items in the database
    $stmt = $conn->prepare("
        UPDATE order_items 
        SET stage_id = :stage_id, quantity = :quantity, num_stages = :num_stages
        WHERE order_id = :order_id
    ");
    $stmt->bindParam(':stage_id', $stage_id, PDO::PARAM_INT);
    $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt->bindParam(':num_stages', $num_stages, PDO::PARAM_INT);
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Step 2: Calculate total price and update it in the orders table
        $totalPriceStmt = $conn->prepare("
            UPDATE orders 
            SET total_price = CalculateTotalPrice(:stage_id, :quantity, :num_stages)
            WHERE order_id = :order_id
        ");
        $totalPriceStmt->bindParam(':stage_id', $stage_id, PDO::PARAM_INT);
        $totalPriceStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $totalPriceStmt->bindParam(':num_stages', $num_stages, PDO::PARAM_INT);
        $totalPriceStmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);

        if ($totalPriceStmt->execute()) {
            $successMessage = "Order updated successfully!";
        } else {
            $errorMessage = "Failed to update total price.";
        }
    } else {
        $errorMessage = "Failed to update order items.";
    }
}

// Fetch available stages for the dropdown
$stages = $conn->query("SELECT stage_id, name, dimensions, price FROM stages")->fetchAll(PDO::FETCH_ASSOC);

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
        <h2>Edit Pesanan Anda</h2>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?= $errorMessage ?></div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php endif; ?>

        <?php if ($order && $order['status'] === 'Pending'): ?>
            <form method="post">
                <div class="form-group">
                    <label for="stage_id">Pilih Jenis Panggung</label>
                    <select class="form-control" name="stage_id" id="stage" required>
                        <?php foreach ($stages as $stage): ?>
                            <option value="<?= $stage['stage_id'] ?>" <?= $stage['stage_id'] == $order['stage_id'] ? 'selected' : '' ?>>
                                <?= $stage['name'] ?> (<?= $stage['dimensions'] ?>) - Rp <?= number_format($stage['price'], 2) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="quantity">Jumlah Hari</label>
                    <input type="number" class="form-control" name="quantity" value="<?= $order['quantity'] ?>" min="1" required>
                </div>
                <div class="form-group">
                    <label for="num_stages">Jumlah Panggung</label>
                    <input type="number" class="form-control" name="num_stages" value="<?= $order['num_stages'] ?>" min="1" required>
                </div>
                <button type="submit" class="btn btn-primary">Perbarui Pesanan</button>
                <a href="orders_user.php" class="btn btn-secondary ml-2">Kembali</a>
            </form>
        <?php elseif (!$errorMessage): ?>
            <p>Pesanan tidak ditemukan atau tidak dalam status 'Pending'.</p>
        <?php endif; ?>
    </div>
</body>
</html>
