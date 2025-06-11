<?php
require_once __DIR__ . "/../includes/db.php";

// Add penalty_amount column to leases table
$sql = "ALTER TABLE leases 
        ADD COLUMN penalty_amount DECIMAL(10,2) DEFAULT 0.00 AFTER total_price";

if ($conn->query($sql)) {
    echo "Successfully added penalty_amount column to leases table.\n";
} else {
    echo "Error adding penalty_amount column: " . $conn->error . "\n";
}

// Add return_date column to leases table
$sql = "ALTER TABLE leases 
        ADD COLUMN return_date DATE NULL AFTER end_date";

if ($conn->query($sql)) {
    echo "Successfully added return_date column to leases table.\n";
} else {
    echo "Error adding return_date column: " . $conn->error . "\n";
}

$conn->close();
?> 