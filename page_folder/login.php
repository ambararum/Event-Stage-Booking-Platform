<?php
session_start();
include '../connection/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user data
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Check password
        if (($user['role'] === 'admin' && hash('sha256', $password) === $user['password']) || 
            ($user['role'] === 'customer' && password_verify($password, $user['password']))) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            header('Location: ' . ($user['role'] === 'admin' ? 'orders_admin.php' : 'rental_user.php'));
            exit;
        } else {
            $error = "Invalid login credentials.";
        }
    } else {
        $error = "Invalid login credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Rental Panggung</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card p-4 mx-auto" style="max-width: 400px;">
            <h2 class="text-center">Login</h2>
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <p class="alert alert-success">Registration successful! You can now log in.</p>
            <?php endif; ?>
            <?php if (isset($error)) echo "<p class='text-danger'>$error</p>"; ?>
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <p>Belum punya akun? <a href="register.php">Register di sini</a></p>
        </div>
    </div>
</body>
</html>
