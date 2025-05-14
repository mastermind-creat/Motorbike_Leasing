<?php
session_start();
require_once "../includes/db.php";
$page_title = "Reports";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

// Get date range from request or default to current month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Fetch total revenue
$revenue = $conn->query("
    SELECT 
        SUM(total_price) as total_revenue,
        COALESCE(SUM(penalty_amount), 0) as total_penalties
    FROM leases 
    WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
")->fetch_assoc();

// Fetch lease statistics
$leases = $conn->query("
    SELECT 
        COUNT(*) as total_leases,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_leases,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_leases,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_leases,
        SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_leases
    FROM leases 
    WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
")->fetch_assoc();

// Fetch most leased motorbikes
$popular_bikes = $conn->query("
    SELECT 
        m.brand,
        m.model,
        COUNT(l.id) as lease_count,
        SUM(l.total_price) as total_revenue
    FROM motorbikes m
    LEFT JOIN leases l ON m.id = l.bike_id
    WHERE l.created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
    GROUP BY m.id
    ORDER BY lease_count DESC
    LIMIT 5
");

// Fetch recent leases
$recent_leases = $conn->query("
    SELECT 
        l.*,
        m.brand,
        m.model,
        u.name as customer_name
    FROM leases l
    JOIN motorbikes m ON l.bike_id = m.id
    JOIN users u ON l.customer_id = u.id
    WHERE l.created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
    ORDER BY l.created_at DESC
    LIMIT 10
");

include 'includes/layout.php';
?>

<style>
    @media print {
        .no-print {
            display: none !important;
        }
        .card {
            border: 1px solid #ddd !important;
            break-inside: avoid;
        }
        .table {
            border-collapse: collapse !important;
        }
        .table td, .table th {
            border: 1px solid #ddd !important;
        }
        .badge {
            border: 1px solid #000 !important;
        }
        .container-fluid {
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .sidebar {
            display: none !important;
        }
        .top-navbar {
            display: none !important;
        }
        body {
            background: white !important;
        }
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
        }
    }
    .print-header {
        display: none;
    }
</style>

<div class="print-header">
    <h2>Motorbike Leasing System - Reports</h2>
    <p>Period: <?= date('F d, Y', strtotime($start_date)) ?> to <?= date('F d, Y', strtotime($end_date)) ?></p>
    <p>Generated on: <?= date('F d, Y H:i:s') ?></p>
    <hr>
</div>

<div class="card shadow-sm mb-4 no-print">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" value="<?= $end_date ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search me-2"></i>Generate Report
                </button>
                <button type="button" class="btn btn-success" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>Print Report
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <!-- Revenue Summary -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-primary mb-4">
                    <i class="bi bi-currency-dollar me-2"></i>Revenue Summary
                </h5>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Total Revenue</h6>
                            <h4 class="mb-0">Ksh. <?= number_format($revenue['total_revenue'] ?? 0, 2) ?></h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Total Penalties</h6>
                            <h4 class="mb-0">Ksh. <?= number_format($revenue['total_penalties'] ?? 0, 2) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lease Statistics -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-primary mb-4">
                    <i class="bi bi-graph-up me-2"></i>Lease Statistics
                </h5>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Total Leases</h6>
                            <h4 class="mb-0"><?= $leases['total_leases'] ?? 0 ?></h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Approved Leases</h6>
                            <h4 class="mb-0"><?= $leases['approved_leases'] ?? 0 ?></h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Pending Leases</h6>
                            <h4 class="mb-0"><?= $leases['pending_leases'] ?? 0 ?></h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Returned Leases</h6>
                            <h4 class="mb-0"><?= $leases['returned_leases'] ?? 0 ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Leased Motorbikes -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-primary mb-4">
                    <i class="bi bi-trophy me-2"></i>Most Leased Motorbikes
                </h5>
                <?php if ($popular_bikes->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Motorbike</th>
                                    <th>Leases</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($bike = $popular_bikes->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($bike['brand'] . ' ' . $bike['model']) ?></td>
                                        <td><?= $bike['lease_count'] ?></td>
                                        <td>Ksh. <?= number_format($bike['total_revenue'], 2) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>No lease data available for the selected period.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Leases -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-primary mb-4">
                    <i class="bi bi-clock-history me-2"></i>Recent Leases
                </h5>
                <?php if ($recent_leases->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer</th>
                                    <th>Motorbike</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($lease = $recent_leases->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($lease['customer_name']) ?></td>
                                        <td><?= htmlspecialchars($lease['brand'] . ' ' . $lease['model']) ?></td>
                                        <td>Ksh. <?= number_format($lease['total_price'], 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $lease['status'] === 'approved' ? 'success' : 
                                                ($lease['status'] === 'pending' ? 'warning' : 
                                                ($lease['status'] === 'returned' ? 'info' : 'danger')) ?>">
                                                <?= ucfirst($lease['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>No recent leases found for the selected period.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 