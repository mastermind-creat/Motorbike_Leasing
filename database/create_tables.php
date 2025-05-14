<?php
require_once "../includes/db.php";

// Read the SQL file
$sql = file_get_contents('motorbikes.sql');

// Execute the SQL
if ($conn->multi_query($sql)) {
    echo "Motorbikes table created successfully!";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?> 