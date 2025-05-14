<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: customer_login.php");
    exit();
}

if (!isset($_GET['bike_id'])) {
    header("Location: dashboard.php");
    exit();
}

$bikeId = $_GET['bike_id'];
$bike = $conn->query("SELECT * FROM motorbikes WHERE id = $bikeId")->fetch_assoc();

if (!$bike || $bike['status'] !== 'available') {
    header("Location: dashboard.php");
    exit();
}

// Handle bike leasing
if (isset($_POST['lease'])) {
    $customerId = $_SESSION['user_id'];
    $bikeId = $bike['id'];
    $startDate = date("Y-m-d");
    $endDate = date("Y-m-d", strtotime("+30 days")); // Default 30 days lease
    $duration = 30; // 30 days
    $totalPrice = $bike['price'] * $duration;

    // Add lease record in the database
    $conn->query("INSERT INTO leases (customer_id, bike_id, start_date, end_date, status, total_price) 
                 VALUES ($customerId, $bikeId, '$startDate', '$endDate', 'pending', $totalPrice)");

    header("Location: dashboard.php?requested=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lease Motorbike</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-danger shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-bicycle me-2"></i>Motorbike Leasing
        </a>
        <div>
            <a href="customer_logout.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="row g-0">
                    <div class="col-md-5">
                        <img src="../<?= htmlspecialchars($bike['image']) ?>" 
                             class="img-fluid rounded-start h-100" 
                             style="object-fit: cover;"
                             alt="<?= htmlspecialchars($bike['brand'] . ' ' . $bike['model']) ?>">
                    </div>
                    <div class="col-md-7">
                        <div class="card-body">
                            <h3 class="card-title text-danger mb-4">
                                <?= htmlspecialchars($bike['brand'] . ' ' . $bike['model']) ?>
                            </h3>
                            
                            <div class="mb-4">
                                <p class="mb-2">
                                    <i class="bi bi-calendar me-2"></i>
                                    <strong>Year:</strong> <?= htmlspecialchars($bike['year']) ?>
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-currency-dollar me-2"></i>
                                    <strong>Price:</strong> Ksh. <?= number_format($bike['price'], 2) ?> per day
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Description:</strong> <?= htmlspecialchars($bike['description']) ?>
                                </p>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                By clicking the lease button, you agree to lease this motorbike for 30 days.
                            </div>

                            <form method="POST">
                                <button type="submit" name="lease" class="btn btn-danger w-100">
                                    <i class="bi bi-hand-thumbs-up me-2"></i>Confirm Lease
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
