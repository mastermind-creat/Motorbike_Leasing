<?php
session_start();
require_once "../includes/db.php";
$page_title = "Add New Motorbike";

if (isset($_POST['submit'])) {
    $model = trim($_POST['model']);
    $brand = trim($_POST['brand']);
    $year = trim($_POST['year']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    $status = 'available';

    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/bikes/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = 'uploads/bikes/' . $new_filename;
        }
    }

    $query = $conn->prepare("INSERT INTO motorbikes (model, brand, year, price, description, image, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $query->bind_param("sssdsss", $model, $brand, $year, $price, $description, $image, $status);

    if ($query->execute()) {
        $success = "Motorbike added successfully!";
    } else {
        $error = "Error adding motorbike: " . $conn->error;
    }
}

include 'includes/layout.php';
?>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Brand</label>
                    <input type="text" class="form-control" name="brand" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Model</label>
                    <input type="text" class="form-control" name="model" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Year</label>
                    <input type="number" class="form-control" name="year" min="1900" max="<?= date('Y') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Price per Day (Ksh.)</label>
                    <input type="number" class="form-control" name="price" min="0" step="0.01" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Image</label>
                <input type="file" class="form-control" name="image" accept="image/*" required>
            </div>

            <button type="submit" name="submit" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Add Motorbike
            </button>
        </form>
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
