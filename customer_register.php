<?php
session_start();
require_once "includes/db.php";

$message = "";

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    // Check if the email is already registered
    $emailCheck = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($emailCheck->num_rows > 0) {
        $message = '<div class="alert alert-danger">Email already registered.</div>';
    } else {
        $query = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $query->bind_param("sss", $username, $email, $password);

        if ($query->execute()) {
            $message = '<div class="alert alert-success">Registration successful. <a href="customer_login.php">Login here</a></div>';
        } else {
            $message = '<div class="alert alert-danger">Registration failed. Try again.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-danger shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Motorbike Leasing</a>
    </div>
</nav>

<div class="container mt-5">
    <h3 class="text-danger text-center mb-4">Customer Registration</h3>
    <?= $message ?>

    <form method="POST" class="bg-white p-4 shadow rounded-4">
        <div class="mb-3">
            <label for="username" class="form-label">Full Name</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" name="register" class="btn btn-danger w-100">Register</button>
    </form>

    <div class="mt-3 text-center">
        <p>Already have an account? <a href="customer_login.php" class="text-danger">Login here</a></p>
    </div>
</div>

</body>
</html>
