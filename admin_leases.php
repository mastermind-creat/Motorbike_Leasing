<?php
session_start();
require_once "includes/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Handle approval
if (isset($_POST['approve'])) {
    $leaseId = $_POST['lease_id'];
    $duration = $_POST['duration'];

    $conn->query("UPDATE leases SET status='Approved', duration=$duration, approved_at=NOW() WHERE id=$leaseId");
}

$leases = $conn->query("
    SELECT l.*, u.name AS customer, b.model AS bike_model 
    FROM leases l 
    JOIN users u ON l.user_id = u.id 
    JOIN bikes b ON l.bike_id = b.id 
    ORDER BY l.lease_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approve Leases - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">Admin Panel</a>
        <a href="admin_logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<div class="container mt-5">
    <h3 class="mb-4 text-center text-dark">Lease Requests</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Bike</th>
                    <th>Lease Date</th>
                    <th>Status</th>
                    <th>Duration (hrs)</th>
                    <th>Approved At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($leases->num_rows > 0): $count = 1; ?>
                    <?php while($lease = $leases->fetch_assoc()): ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td><?= htmlspecialchars($lease['customer']) ?></td>
                            <td><?= htmlspecialchars($lease['bike_model']) ?></td>
                            <td><?= $lease['lease_date'] ?></td>
                            <td>
                                <span class="badge bg-<?= $lease['status'] === 'Approved' ? 'success' : 'warning' ?>">
                                    <?= $lease['status'] ?>
                                </span>
                            </td>
                            <td><?= $lease['duration'] ?? 'N/A' ?></td>
                            <td><?= $lease['approved_at'] ?? 'N/A' ?></td>
                            <td>
                                <?php if ($lease['status'] === 'Pending'): ?>
                                    <form method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="lease_id" value="<?= $lease['id'] ?>">
                                        <input type="number" name="duration" placeholder="Hours" class="form-control form-control-sm" min="1" required>
                                        <button type="submit" name="approve" class="btn btn-success btn-sm">
                                            Approve
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center">No lease requests found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
