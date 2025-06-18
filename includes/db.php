<?php
$host = "localhost";      // Usually localhost
$user = "root";           // Your DB username
$pass = "";               // Your DB password
$dbname = "m_leasing";  // Your DB name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
