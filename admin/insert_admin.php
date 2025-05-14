<?php
require_once "includes/db.php";

// Set admin details
$name = "System Admin";
$email = "customer@motor.com";
$password = "faith"; // You can change this
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$role = "admin";

// Check if admin already exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ? AND role = 'customer'");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "<h4 style='color:red;text-align:center;'>Admin already exists.</h4>";
} else {
    // Insert new admin
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        echo "<h4 style='color:green;text-align:center;'>Admin successfully added!</h4>";
        echo "<p style='text-align:center;'>Email: <strong>$email</strong><br>Password: <strong>$password</strong></p>";
    } else {
        echo "<h4 style='color:red;text-align:center;'>Error adding admin: " . $stmt->error . "</h4>";
    }
}
?>
