<?php
session_start();
require_once "../includes/db.php";
$page_title = "Dashboard";

include 'includes/layout.php';
?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body text-center">
                <i class="bi bi-plus-circle-fill text-success display-4"></i>
                <h5 class="mt-2">Add New Motorbike</h5>
                <a href="add_bike.php" class="btn btn-outline-success btn-sm mt-2">Go</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body text-center">
                <i class="bi bi-check2-square text-primary display-4"></i>
                <h5 class="mt-2">Approve Lease Requests</h5>
                <a href="approve_leases.php" class="btn btn-outline-primary btn-sm mt-2">Go</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body text-center">
                <i class="bi bi-bicycle text-dark display-4"></i>
                <h5 class="mt-2">View All Motorbikes</h5>
                <a href="manage_bikes.php" class="btn btn-outline-dark btn-sm mt-2">Go</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle sidebar on mobile
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('d-none');
    });
</script>
</body>
</html>
