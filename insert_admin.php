<?php
require_once "includes/db.php";

// Function to check existing admins
function checkExistingAdmins() {
    global $conn;
    $query = "SELECT username, email, name FROM users WHERE role = 'admin'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        echo "<div style='margin: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;'>";
        echo "<h4 style='color: #dc3545;'>Existing Admin Users:</h4>";
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #e9ecef;'><th style='padding: 8px; text-align: left;'>Username</th><th style='padding: 8px; text-align: left;'>Email</th><th style='padding: 8px; text-align: left;'>Name</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr style='border-bottom: 1px solid #dee2e6;'>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($row['name']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        return true;
    }
    return false;
}

// Set admin details with different values
$username = "superadmin";  // Changed from 'admin'
$name = "Faith Ojino";
$email = "admin@motor.com";  // Changed from 'admin@motor.com'
$password = "faith";  // Changed from 'admin123'
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$role = "admin";

// Check if admin already exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
$check->bind_param("ss", $email, $username);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "<h4 style='color:red;text-align:center;'>Admin with this email or username already exists.</h4>";
    checkExistingAdmins();
    echo "<p style='text-align:center;'><a href='../admin_login.php'>Go to Login Page</a></p>";
} else {
    // Insert new admin
    $stmt = $conn->prepare("INSERT INTO users (username, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $name, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        echo "<h4 style='color:green;text-align:center;'>Admin successfully added!</h4>";
        echo "<p style='text-align:center;'>Login Details:<br>";
        echo "Username: <strong>$username</strong><br>";
        echo "Email: <strong>$email</strong><br>";
        echo "Password: <strong>$password</strong></p>";
        echo "<p style='text-align:center;'><a href='../admin_login.php'>Go to Login Page</a></p>";
        
        // Show all admin users after successful insertion
        checkExistingAdmins();
    } else {
        echo "<h4 style='color:red;text-align:center;'>Error adding admin: " . $stmt->error . "</h4>";
    }
}

$conn->close();
?> 