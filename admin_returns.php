<?php
session_start();
require_once "includes/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Handle return
if (isset($_POST['return'])) {
    $leaseId = $_POST['lease_id'];

    // Get lease details
    $lease = $conn->query("SELECT * FROM leases WHERE id = $leaseId")->fetch_assoc();
    $approvedAt = new DateTime($lease['approved_at']);
    $now = new DateTime();
    $expected = $lease['duration'];
    $bikeId = $lease['bike_id'];

    $interval = $approvedAt->diff($now);
    $actualHours = ceil(($interval->days * 24) + ($interval->h) + ($interval->i / 60));

    $extra = max(0, $actualHours - $expected);
    $extraCharge = $extra * 50;

    // Update lease
    $conn->query("
        UPDATE leases 
        SET returned_at = NOW(), status = 'Returned' 
        WHERE id = $leaseId
    ");

    // Update bike status
    $conn->query("UPDATE bikes SET status = 'Idle' WHERE id = $bikeId");

    $_SESSION['return_success'] = "Bike returned. Extra charge: Ksh $extraCharge for $extra extra hour(s).";
}

$activeLeases = $conn->query("
    SELECT l.*, u.name AS customer, b.model AS bike_model 
    FROM leases l 
    JOIN users u ON l.user_id = u.id 
    JOIN bikes b ON l.bike_id = b.id 
    WHERE l.status = 'Approved'
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Return Bikes - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">Admin Panel</a>
        <a href="admin_logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<div class="container mt-5">
    <h3 class="mb-4 text-center text-dark">Return Leased Bikes</h3>

    <?php if (isset($_SESSION['return_success'])): ?>
        <div class="alert alert-info"><?= $_SESSION['return_success']; unset($_SESSION['return_success']); ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered bg-white">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Bike</th>
                    <th>Lease Date</th>
                    <th>Approved At</th>
                    <th>Expected Duration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($activeLeases->num_rows > 0): $count = 1; ?>
                    <?php while($lease = $activeLeases->fetch_assoc()): ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td><?= htmlspecialchars($lease['customer']) ?></td>
                            <td><?= htmlspecialchars($lease['bike_model']) ?></td>
                            <td><?= $lease['lease_date'] ?></td>
                            <td><?= $lease['approved_at'] ?></td>
                            <td><?= $lease['duration'] ?> hrs</td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="lease_id" value="<?= $lease['id'] ?>">
                                    <button type="submit" name="return" class="btn btn-danger btn-sm">
                                        Mark as Returned
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">No active leases found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
