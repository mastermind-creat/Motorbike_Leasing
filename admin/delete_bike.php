<?php
session_start();
require_once "../includes/db.php";

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

// Check if bike ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid motorbike ID!";
    header("Location: manage_bikes.php");
    exit();
}

$bikeId = (int)$_GET['id'];

// Start transaction
$conn->begin_transaction();

try {
    // Check if bike exists and is not currently leased
    $stmt = $conn->prepare("SELECT image, status FROM motorbikes WHERE id = ?");
    $stmt->bind_param("i", $bikeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $bike = $result->fetch_assoc();

    if (!$bike) {
        throw new Exception("Motorbike not found!");
    }

    if ($bike['status'] === 'leased') {
        throw new Exception("Cannot delete a motorbike that is currently leased!");
    }

    // Check if bike has any lease history
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM leases WHERE bike_id = ?");
    $stmt->bind_param("i", $bikeId);
    $stmt->execute();
    $leaseCount = $stmt->get_result()->fetch_assoc()['count'];

    if ($leaseCount > 0) {
        throw new Exception("Cannot delete a motorbike with lease history!");
    }

    // Delete the image file if it exists
    if ($bike['image']) {
        $imagePath = "../" . $bike['image'];
        if (file_exists($imagePath)) {
            if (!unlink($imagePath)) {
                throw new Exception("Error deleting motorbike image!");
            }
        }
    }

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM motorbikes WHERE id = ?");
    $stmt->bind_param("i", $bikeId);
    
    if (!$stmt->execute()) {
        throw new Exception("Error deleting motorbike from database!");
    }

    // If everything is successful, commit the transaction
    $conn->commit();
    $_SESSION['success'] = "Motorbike deleted successfully!";

} catch (Exception $e) {
    // If there's an error, rollback the transaction
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
}

// Redirect back to manage bikes page
header("Location: manage_bikes.php");
exit();
?> 