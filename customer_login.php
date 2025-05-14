<?php
session_start();
require_once "includes/db.php";

$message = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if the email exists
    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: customer_dashboard.php");
            exit();
        } else {
            $message = '<div class="alert alert-danger">Incorrect password.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Email not found.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Login</title>
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
    <h3 class="text-danger text-center mb-4">Customer Login</h3>
    <?= $message ?>

    <form method="POST" class="bg-white p-4 shadow rounded-4">
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" name="login" class="btn btn-danger w-100">Login</button>
    </form>

    <div class="mt-3 text-center">
        <p>Don't have an account? <a href="customer_register.php" class="text-danger">Register here</a></p>
    </div>
</div>

</body>
</html>
