<?php
session_start();
include '../connection/db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'order_date'; 
$orderDirection = $_GET['orderDirection'] ?? 'asc'; 

// Prepare sorting
if (isset($_GET['sort'])) {
    if ($_GET['sort'] === 'name_asc') {
        $sort = 'name';
        $orderDirection = 'asc';
    } elseif ($_GET['sort'] === 'name_desc') {
        $sort = 'name';
        $orderDirection = 'desc';
    }
}

// Dynamic query with placeholders
$query = "SELECT orders.order_id, users.name, orders.order_date, orders.total_price, orders.status 
          FROM orders
          JOIN users ON orders.user_id = users.user_id 
          WHERE users.name LIKE :search 
          ORDER BY " . ($sort === 'name' ? "users.name $orderDirection" : "orders.order_date $orderDirection");

$stmt = $conn->prepare($query);
$stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pesanan - Admin</title>
    <link rel="stylesheet" href="../css_folder/style.css?v=<?php echo time(); ?>">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <div class="bg-light" id="sidebar">
            <br>
            <h4 class="text-center">Admin Menu</h4>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="orders_admin.php">Daftar Pesanan</a></li>
                <li class="nav-item"><a class="nav-link" href="stages_admin.php">Daftar Panggung</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="container mt-5">
            <h2 class="text-center">Daftar Pesanan</h2>
            <br>
            <form method="get" class="form-inline justify-content-center mb-4">
                <input type="text" class="form-control mr-2" name="search" placeholder="Cari berdasarkan Nama" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary mr-2">Cari</button>
                <button type="submit" name="sort" value="name_asc" class="btn btn-secondary mr-2">Urutkan Nama (A-Z)</button>
                <button type="submit" name="sort" value="name_desc" class="btn btn-secondary">Urutkan Nama (Z-A)</button>
            </form>
            
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Nama Pemesan</th>
                        <th>Tanggal</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['name']) ?></td>
                            <td><?= htmlspecialchars($order['order_date']) ?></td>
                            <td>Rp <?= number_format($order['total_price'], 2) ?></td>
                            <td><?= htmlspecialchars($order['status']) ?></td>
                            <td>
                                <a href="editOrder_admin.php?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-edit">Edit</a>
                                <a href="delete_order.php?id=<?= $order['order_id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus pesanan ini?')" class="btn btn-sm btn-cancel">Hapus</a>
                                <a href="orderDetail_admin.php?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-info">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
