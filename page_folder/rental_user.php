<?php
session_start();
include '../connection/db.php';

if ($_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stage_id = $_POST['stage_id'];
    $quantity = $_POST['quantity'];
    $num_stages = $_POST['num_stages'];
    $user_id = $_SESSION['user_id'];

    try {
        // Use a prepared statement for executing stored procedure
        $stmt = $conn->prepare("CALL create_order(?, ?, ?, ?)");
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $stage_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $quantity, PDO::PARAM_INT);
        $stmt->bindParam(4, $num_stages, PDO::PARAM_INT);
        
        // Attempt to execute the statement
        $stmt->execute();
        $successMessage = "Pesanan berhasil dibuat!";
    } catch (PDOException $e) {
        // Handle the out-of-stock error
        if ($e->getMessage() === 'Sorry, Out of Stock') {
            $errorMessage = $e->getMessage();
        } else {
            // Handle other potential errors
            $errorMessage = "An error occurred: " . $e->getMessage();
        }
    }
}

// Fetch all stages
$stages = $conn->query("SELECT * FROM stages")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rental Panggung</title>
    <link rel="stylesheet" href="../css_folder/style.css?v=<?php echo time(); ?>">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <div class="sidebar-custom bg-light" id="sidebar">
            <br>
            <h4 class="text-center"> Customer Menu </h4>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="rental_user.php">Buat Pesanan</a></li>
                <li class="nav-item"><a class="nav-link" href="orders_user.php">Pesanan Anda</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
        
        <div class="container mt-5">
            <div class="card p-4 shadow-sm">
                <h2 class="text-center">Pilih Panggung untuk Disewa</h2>
                
                <?php if ($successMessage): ?>
                    <div class="alert alert-success text-center"><?= htmlspecialchars($successMessage) ?></div>
                <?php endif; ?>
                
                <?php if ($errorMessage): ?>
                    <div class="alert alert-danger text-center"><?= htmlspecialchars($errorMessage) ?></div>
                <?php endif; ?>

                <form action="rental_user.php" method="post">
                    <div class="form-group">
                        <label for="stage">Pilih Panggung</label>
                        <select class="form-control" name="stage_id" id="stage" required>
                            <?php foreach ($stages as $stage): ?>
                                <option value="<?= htmlspecialchars($stage['stage_id']) ?>">
                                    <?= htmlspecialchars($stage['name']) ?> (<?= htmlspecialchars($stage['dimensions']) ?>) - Rp <?= number_format($stage['price'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Jumlah Hari</label>
                        <input type="number" class="form-control" name="quantity" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="num_stages">Jumlah Panggung</label>
                        <input type="number" class="form-control" name="num_stages" min="1" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Lanjut</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
