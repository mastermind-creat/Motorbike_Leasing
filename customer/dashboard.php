<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: customer_login.php");
    exit();
}

// Fetch available motorbikes (available status)
$bikes = $conn->query("SELECT * FROM motorbikes WHERE status = 'available' ORDER BY created_at DESC");

// Fetch customer profile
$user_id = $_SESSION['user_id'];
$profile = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Fetch customer's leasing history
$leases = $conn->query("SELECT l.*, m.model, m.image 
                       FROM leases l 
                       JOIN motorbikes m ON l.bike_id = m.id 
                       WHERE l.customer_id = $user_id 
                       ORDER BY l.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .bike-card {
            transition: transform 0.2s;
        }
        .bike-card:hover {
            transform: translateY(-5px);
        }
        .bike-image {
            height: 200px;
            object-fit: cover;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .nav-pills .nav-link.active {
            background-color: #dc3545;
        }
        .nav-pills .nav-link {
            color: #dc3545;
        }
        .nav-pills .nav-link:hover {
            background-color: #ffebee;
        }
    </style>
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
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-person-circle display-4 text-danger"></i>
                        <h5 class="mt-2"><?= htmlspecialchars($profile['name']) ?></h5>
                        <p class="text-muted small"><?= htmlspecialchars($profile['email']) ?></p>
                    </div>
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#available-bikes">
                            <i class="bi bi-bicycle me-2"></i>Available Motorbikes
                        </button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#leasing-history">
                            <i class="bi bi-clock-history me-2"></i>Leasing History
                        </button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#profile">
                            <i class="bi bi-person me-2"></i>My Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Available Motorbikes Tab -->
                <div class="tab-pane fade show active" id="available-bikes">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="text-danger">
                            <i class="bi bi-bicycle me-2"></i>Available Motorbikes
                        </h4>
                    </div>

                    <?php if ($bikes->num_rows > 0): ?>
                        <div class="row g-4">
                            <?php while ($row = $bikes->fetch_assoc()): ?>
                                <div class="col-md-4">
                                    <div class="card bike-card shadow h-100">
                                        <div class="position-relative">
                                            <img src="../<?= htmlspecialchars($row['image']) ?>" 
                                                 class="card-img-top bike-image" 
                                                 alt="<?= htmlspecialchars($row['brand'] . ' ' . $row['model']) ?>">
                                            <span class="badge bg-success status-badge">Available</span>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title text-danger mb-3">
                                                <?= htmlspecialchars($row['brand'] . ' ' . $row['model']) ?>
                                            </h5>
                                            <div class="mb-3">
                                                <p class="card-text mb-2">
                                                    <i class="bi bi-calendar me-2"></i>
                                                    <strong>Year:</strong> <?= htmlspecialchars($row['year']) ?>
                                                </p>
                                                <p class="card-text mb-2">
                                                    <i class="bi bi-currency-dollar me-2"></i>
                                                    <strong>Price:</strong> Ksh. <?= number_format($row['price'], 2) ?> per day
                                                </p>
                                                <p class="card-text mb-2">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    <strong>Description:</strong> <?= htmlspecialchars($row['description']) ?>
                                                </p>
                                            </div>
                                            <a href="lease_bike.php?bike_id=<?= $row['id'] ?>" 
                                               class="btn btn-danger w-100">
                                                <i class="bi bi-hand-thumbs-up me-2"></i>Lease This Bike
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center py-4">
                            <i class="bi bi-info-circle me-2"></i>
                            No motorbikes available for lease at the moment.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Leasing History Tab -->
                <div class="tab-pane fade" id="leasing-history">
                    <h4 class="text-danger mb-4">
                        <i class="bi bi-clock-history me-2"></i>Leasing History
                    </h4>
                    
                    <?php if ($leases->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Motorbike</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($lease = $leases->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="../<?= htmlspecialchars($lease['image']) ?>" 
                                                         class="rounded" 
                                                         width="50" 
                                                         alt="<?= htmlspecialchars($lease['model']) ?>">
                                                    <span class="ms-2"><?= htmlspecialchars($lease['model']) ?></span>
                                                </div>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($lease['start_date'])) ?></td>
                                            <td><?= date('M d, Y', strtotime($lease['end_date'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $lease['status'] === 'Active' ? 'success' : 'secondary' ?>">
                                                    <?= htmlspecialchars($lease['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view_lease.php?id=<?= $lease['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center py-4">
                            <i class="bi bi-info-circle me-2"></i>
                            You haven't leased any motorbikes yet.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Profile Tab -->
                <div class="tab-pane fade" id="profile">
                    <h4 class="text-danger mb-4">
                        <i class="bi bi-person me-2"></i>My Profile
                    </h4>
                    
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form action="update_profile.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="name" 
                                           value="<?= htmlspecialchars($profile['name']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?= htmlspecialchars($profile['email']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="password" 
                                           placeholder="Leave blank to keep current password">
                                </div>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-save me-2"></i>Update Profile
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
