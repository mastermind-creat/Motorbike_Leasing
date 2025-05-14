<?php
require_once "../includes/db.php";

// Add penalty_amount column to leases table
$sql = "ALTER TABLE leases 
        ADD COLUMN penalty_amount DECIMAL(10,2) DEFAULT 0.00 AFTER total_price";

if ($conn->query($sql)) {
    echo "Successfully added penalty_amount column to leases table.";
} else {
    echo "Error adding penalty_amount column: " . $conn->error;
}

$conn->close();
?> 