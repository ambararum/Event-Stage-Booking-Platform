<?php
session_start();
include '../connection/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'customer';

    // Cek apakah nama sudah ada di database
    $checkNameQuery = $conn->prepare("SELECT * FROM users WHERE name = :name");
    $checkNameQuery->bindParam(':name', $name);
    $checkNameQuery->execute();

    // Cek apakah email sudah ada di database
    $checkEmailQuery = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $checkEmailQuery->bindParam(':email', $email);
    $checkEmailQuery->execute();

    // Validasi nama
    if ($checkNameQuery->rowCount() > 0) {
        $error = "Nama sudah digunakan. Silakan pilih nama lain.";
    } 
    // Validasi email
    elseif ($checkEmailQuery->rowCount() > 0) {
        $error = "Email sudah digunakan. Silakan pilih email lain.";
    } 
    else {
        // Prepare the SQL statement to prevent SQL injection
        $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role', $role);
        
        // Execute the statement
        if ($stmt->execute()) {
            header('Location: login.php?success=1');
            exit;
        } else {
            $error = "Error: " . $stmt->errorInfo()[2];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Rental Panggung</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card p-4 mx-auto" style="max-width: 400px;">
            <h2 class="text-center">Register</h2>
            <?php if (isset($error)) echo "<p class='text-danger'>$error</p>"; ?>
            <form action="register.php" method="post">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
        </div>
    </div>
</body>
</html>
