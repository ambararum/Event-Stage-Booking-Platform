<?php
session_start();
include '../connection/db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Location: stages_admin.php');
    exit;
}

$stage_id = $_GET['id'];

// Fetch the stage data from the database
$stmt = $conn->prepare("SELECT * FROM stages WHERE stage_id = :stage_id");
$stmt->bindParam(':stage_id', $stage_id, PDO::PARAM_INT);
$stmt->execute();
$stage = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update stage data
    $name = $_POST['name'];
    $dimensions = $_POST['dimensions'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $stmt = $conn->prepare("UPDATE stages SET name = :name, dimensions = :dimensions, price = :price, stock = :stock WHERE stage_id = :stage_id");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':dimensions', $dimensions);
    $stmt->bindParam(':price', $price, PDO::PARAM_STR);
    $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
    $stmt->bindParam(':stage_id', $stage_id, PDO::PARAM_INT);
    $stmt->execute();

    // Redirect back to stages_admin.php after editing
    header('Location: stages_admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Panggung - Admin</title>
    <link rel="stylesheet" href="../css_folder/style.css?v=<?php echo time(); ?>">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Edit Panggung</h2>

        <form method="post">
            <div class="form-group">
                <label for="name">Nama</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($stage['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="dimensions">Dimensi</label>
                <input type="text" class="form-control" id="dimensions" name="dimensions" value="<?= htmlspecialchars($stage['dimensions']) ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Harga per Hari</label>
                <input type="number" class="form-control" id="price" name="price" value="<?= htmlspecialchars($stage['price']) ?>" required>
            </div>
            <div class="form-group">
                <label for="stock">Stok</label>
                <input type="number" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars($stage['stock']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="stages_admin.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>
</html>
