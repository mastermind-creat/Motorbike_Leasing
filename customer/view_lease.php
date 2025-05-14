<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: customer_login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$leaseId = (int)$_GET['id'];
$userId = $_SESSION['user_id'];

// Fetch lease details with motorbike and customer information
$lease = $conn->query("
    SELECT l.*, 
           m.brand, m.model, m.image, m.price as daily_price,
           u.name as customer_name, u.email as customer_email
    FROM leases l
    JOIN motorbikes m ON l.bike_id = m.id
    JOIN users u ON l.customer_id = u.id
    WHERE l.id = $leaseId AND l.customer_id = $userId
")->fetch_assoc();

if (!$lease) {
    header("Location: dashboard.php");
    exit();
}

// Calculate penalties if any
$today = new DateTime();
$endDate = new DateTime($lease['end_date']);
$penalty = 0;
$daysOverdue = 0;

if ($lease['status'] === 'approved' && $today > $endDate) {
    $daysOverdue = $today->diff($endDate)->days;
    $penalty = $daysOverdue * 100; // Ksh. 100 per day
}

// Handle return submission
if (isset($_POST['return']) && $lease['status'] === 'approved') {
    $returnDate = date('Y-m-d');
    $totalPenalty = $penalty;
    
    // Update lease record
    $conn->query("UPDATE leases SET 
                 status = 'returned',
                 return_date = '$returnDate',
                 penalty_amount = $totalPenalty
                 WHERE id = $leaseId");
    
    // Update motorbike status
    $conn->query("UPDATE motorbikes SET status = 'available' WHERE id = {$lease['bike_id']}");
    
    header("Location: dashboard.php?returned=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lease Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-danger shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-bicycle me-2"></i>Motorbike Leasing
        </a>
        <div>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="text-danger mb-0">Lease Details</h4>
                        <span class="badge bg-<?= $lease['status'] === 'approved' ? 'success' : 
                            ($lease['status'] === 'pending' ? 'warning' : 
                            ($lease['status'] === 'returned' ? 'info' : 'danger')) ?>">
                            <?= ucfirst($lease['status']) ?>
                        </span>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <img src="../<?= htmlspecialchars($lease['image']) ?>" 
                                 class="img-fluid rounded" 
                                 alt="<?= htmlspecialchars($lease['brand'] . ' ' . $lease['model']) ?>">
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3"><?= htmlspecialchars($lease['brand'] . ' ' . $lease['model']) ?></h5>
                            <p class="mb-2">
                                <i class="bi bi-calendar me-2"></i>
                                <strong>Start Date:</strong> <?= date('M d, Y', strtotime($lease['start_date'])) ?>
                            </p>
                            <p class="mb-2">
                                <i class="bi bi-calendar me-2"></i>
                                <strong>End Date:</strong> <?= date('M d, Y', strtotime($lease['end_date'])) ?>
                            </p>
                            <p class="mb-2">
                                <i class="bi bi-currency-dollar me-2"></i>
                                <strong>Daily Rate:</strong> Ksh. <?= number_format($lease['daily_price'], 2) ?>
                            </p>
                            <p class="mb-2">
                                <i class="bi bi-currency-dollar me-2"></i>
                                <strong>Total Price:</strong> Ksh. <?= number_format($lease['total_price'], 2) ?>
                            </p>
                        </div>
                    </div>

                    <?php if ($lease['status'] === 'approved'): ?>
                        <?php if ($penalty > 0): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Overdue Notice:</strong> This motorbike is overdue by <?= $daysOverdue ?> days.
                                <br>
                                <strong>Penalty Amount:</strong> Ksh. <?= number_format($penalty, 2) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="mt-4">
                            <button type="submit" name="return" class="btn btn-danger w-100" 
                                    onclick="return confirm('Are you sure you want to return this motorbike?')">
                                <i class="bi bi-arrow-return-left me-2"></i>Return Motorbike
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($lease['status'] === 'returned'): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Returned on:</strong> <?= date('M d, Y', strtotime($lease['return_date'])) ?>
                            <?php if ($lease['penalty_amount'] > 0): ?>
                                <br>
                                <strong>Penalty Paid:</strong> Ksh. <?= number_format($lease['penalty_amount'], 2) ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 