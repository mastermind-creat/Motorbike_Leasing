<?php
session_start();
require_once "../includes/db.php";
$page_title = "Manage Motorbikes";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

// Fetch all bikes
$bikes = $conn->query("SELECT * FROM motorbikes ORDER BY created_at DESC");

include 'includes/layout.php';
?>

<!-- Add SweetAlert2 CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (isset($_SESSION['success'])): ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?= $_SESSION['success'] ?>',
                    timer: 3000,
                    showConfirmButton: false
                });
            </script>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '<?= $_SESSION['error'] ?>',
                    timer: 3000,
                    showConfirmButton: false
                });
            </script>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if ($bikes->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Year</th>
                            <th>Price/Day</th>
                            <th>Status</th>
                            <th>Added On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = $bikes->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <img src="../<?= htmlspecialchars($row['image']) ?>" 
                                         alt="<?= htmlspecialchars($row['brand'] . ' ' . $row['model']) ?>"
                                         class="rounded" style="width: 80px; height: 60px; object-fit: cover;">
                                </td>
                                <td><?= htmlspecialchars($row['brand']) ?></td>
                                <td><?= htmlspecialchars($row['model']) ?></td>
                                <td><?= htmlspecialchars($row['year']) ?></td>
                                <td>Ksh. <?= number_format($row['price'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['status'] == 'available' ? 'success' : 
                                        ($row['status'] == 'leased' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td><?= date("M d, Y", strtotime($row['created_at'])) ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="edit_bike.php?id=<?= $row['id'] ?>" 
                                           class="btn btn-sm btn-primary" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete(<?= $row['id'] ?>)"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i>No motorbikes found in the database.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `delete_bike.php?id=${id}`;
        }
    });
}
</script>

</body>
</html>
