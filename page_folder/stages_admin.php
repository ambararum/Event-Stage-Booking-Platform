<?php
session_start();
include '../connection/db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle stage addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $dimensions = $_POST['dimensions'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // Validate input
    if (!empty($name) && !empty($dimensions) && !empty($price) && !empty($stock)) {
        try {
            $stmt = $conn->prepare("INSERT INTO stages (name, dimensions, price, stock) VALUES (?, ?, ?, ?)");
            $stmt->bindParam(1, $name);
            $stmt->bindParam(2, $dimensions);
            $stmt->bindParam(3, $price);
            $stmt->bindParam(4, $stock, PDO::PARAM_INT);
            $stmt->execute();
            header('Location: stages_admin.php'); // Redirect to show updated list
            exit;
        } catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    } else {
        $error_message = "Semua field harus diisi.";
    }
}

// Fetch all stages
$stages = $conn->query("SELECT * FROM stages")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Panggung - Admin</title>
    <link rel="stylesheet" href="../css_folder/style.css?v=<?php echo time(); ?>">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <div class="bg-light" id="sidebar">
            <br>
            <h4 class="text-center">Admin Menu</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="orders_admin.php">Daftar Pesanan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="stages_admin.php">Daftar Panggung</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
        <div class="container mt-5">
            <h2 class="text-center">Daftar Panggung</h2>
            
            <form method="post" class="mb-4">
                <h4>Tambah Panggung</h4>
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="name">Nama</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="dimensions">Dimensi</label>
                    <input type="text" class="form-control" id="dimensions" name="dimensions" required>
                </div>
                <div class="form-group">
                    <label for="price">Harga per Hari</label>
                    <input type="number" class="form-control" id="price" name="price" required>
                </div>
                <div class="form-group">
                    <label for="stock">Stok</label>
                    <input type="number" class="form-control" id="stock" name="stock" required>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>

            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Stage ID</th>
                        <th>Nama</th>
                        <th>Dimensi</th>
                        <th>Harga per Hari</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stages as $stage): ?>
                        <tr>
                            <td><?= htmlspecialchars($stage['stage_id']) ?></td>
                            <td><?= htmlspecialchars($stage['name']) ?></td>
                            <td><?= htmlspecialchars($stage['dimensions']) ?></td>
                            <td>Rp <?= number_format($stage['price'], 2) ?></td>
                            <td><?= htmlspecialchars($stage['stock']) ?></td>
                            <td>
                                <a href="editStage_admin.php?id=<?= htmlspecialchars($stage['stage_id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="deleteStage_admin.php?id=<?= htmlspecialchars($stage['stage_id']) ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus jenis panggung ini?')" class="btn btn-sm btn-danger">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
