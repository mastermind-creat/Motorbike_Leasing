<?php
session_start();

// Store the role before destroying session
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect based on role
if ($role === 'admin') {
    header("Location: admin_login.php");
} else {
    header("Location: index.php");
}
exit(); 