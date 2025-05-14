<?php
session_start();
require_once "../includes/db.php";
$page_title = "Approve Leases";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

// Handle approval
if (isset($_GET['approve_id'])) {
    $leaseId = (int)$_GET['approve_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get lease details
        $stmt = $conn->prepare("SELECT * FROM leases WHERE id = ?");
        $stmt->bind_param("i", $leaseId);
        $stmt->execute();
        $lease = $stmt->get_result()->fetch_assoc();
        
        if ($lease) {
            // Update lease status
            $stmt = $conn->prepare("UPDATE leases SET status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $leaseId);
            $stmt->execute();
            
            // Update bike status
            $stmt = $conn->prepare("UPDATE motorbikes SET status = 'leased' WHERE id = ?");
            $stmt->bind_param("i", $lease['bike_id']);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['success'] = "Lease request approved successfully!";
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error approving lease request!";
    }
    
    header("Location: approve_leases.php");
    exit();
}

// Handle rejection
if (isset($_GET['reject_id'])) {
    $leaseId = (int)$_GET['reject_id'];
    
    $stmt = $conn->prepare("UPDATE leases SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $leaseId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Lease request rejected successfully!";
    } else {
        $_SESSION['error'] = "Error rejecting lease request!";
    }
    
    header("Location: approve_leases.php");
    exit();
}

// Fetch pending leases with user and bike details
$leases = $conn->query("
    SELECT l.*, 
           u.name as customer_name, 
           m.brand, m.model, m.image,
           DATEDIFF(l.end_date, l.start_date) as duration_days
    FROM leases l
    JOIN users u ON l.customer_id = u.id
    JOIN motorbikes m ON l.bike_id = m.id
    WHERE l.status = 'pending'
    ORDER BY l.created_at DESC
");

include 'includes/layout.php';
?>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if ($leases->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Motorbike</th>
                            <th>Duration</th>
                            <th>Total Price</th>
                            <th>Requested On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = $leases->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="../<?= htmlspecialchars($row['image']) ?>" 
                                             alt="<?= htmlspecialchars($row['brand'] . ' ' . $row['model']) ?>"
                                             class="rounded me-2" style="width: 50px; height: 40px; object-fit: cover;">
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($row['brand'] . ' ' . $row['model']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?= $row['duration_days'] ?> days<br>
                                    <small class="text-muted">
                                        <?= date('M d, Y', strtotime($row['start_date'])) ?> to 
                                        <?= date('M d, Y', strtotime($row['end_date'])) ?>
                                    </small>
                                </td>
                                <td>Ksh. <?= number_format($row['total_price'], 2) ?></td>
                                <td><?= date('M d, Y H:i', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="approve_leases.php?approve_id=<?= $row['id'] ?>" 
                                           class="btn btn-sm btn-success"
                                           onclick="return confirm('Are you sure you want to approve this lease request?')">
                                            <i class="bi bi-check-circle me-1"></i>Approve
                                        </a>
                                        <a href="approve_leases.php?reject_id=<?= $row['id'] ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to reject this lease request?')">
                                            <i class="bi bi-x-circle me-1"></i>Reject
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i>No pending lease requests found.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmApprove(id) {
    Swal.fire({
        title: 'Approve Lease Request?',
        text: "This will mark the motorbike as leased.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, approve it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `approve_leases.php?approve_id=${id}`;
        }
    });
}

function confirmReject(id) {
    Swal.fire({
        title: 'Reject Lease Request?',
        text: "This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, reject it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `approve_leases.php?reject_id=${id}`;
        }
    });
}
</script>

</body>
</html>
