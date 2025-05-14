<?php
session_start();
require_once "../includes/db.php";
$page_title = "Edit Motorbike";

if (!isset($_GET['id'])) {
    header("Location: manage_bikes.php");
    exit();
}

$bike_id = (int)$_GET['id'];

// Fetch bike details
$stmt = $conn->prepare("SELECT * FROM motorbikes WHERE id = ?");
$stmt->bind_param("i", $bike_id);
$stmt->execute();
$result = $stmt->get_result();
$bike = $result->fetch_assoc();

if (!$bike) {
    $_SESSION['error'] = "Motorbike not found!";
    header("Location: manage_bikes.php");
    exit();
}

if (isset($_POST['submit'])) {
    $model = trim($_POST['model']);
    $brand = trim($_POST['brand']);
    $year = trim($_POST['year']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    // Handle image upload
    $image = $bike['image']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/bikes/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Delete old image
        if ($bike['image'] && file_exists("../" . $bike['image'])) {
            unlink("../" . $bike['image']);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = 'uploads/bikes/' . $new_filename;
        }
    }

    $query = $conn->prepare("UPDATE motorbikes SET model = ?, brand = ?, year = ?, price = ?, description = ?, image = ?, status = ? WHERE id = ?");
    $query->bind_param("sssdsssi", $model, $brand, $year, $price, $description, $image, $status, $bike_id);

    if ($query->execute()) {
        $_SESSION['success'] = "Motorbike updated successfully!";
        header("Location: manage_bikes.php");
        exit();
    } else {
        $error = "Error updating motorbike: " . $conn->error;
    }
}

include 'includes/layout.php';
?>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Brand</label>
                    <input type="text" class="form-control" name="brand" value="<?= htmlspecialchars($bike['brand']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Model</label>
                    <input type="text" class="form-control" name="model" value="<?= htmlspecialchars($bike['model']) ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Year</label>
                    <input type="number" class="form-control" name="year" min="1900" max="<?= date('Y') ?>" value="<?= htmlspecialchars($bike['year']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Price per Day ($)</label>
                    <input type="number" class="form-control" name="price" min="0" step="0.01" value="<?= htmlspecialchars($bike['price']) ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="4" required><?= htmlspecialchars($bike['description']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status" required>
                    <option value="available" <?= $bike['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="leased" <?= $bike['status'] == 'leased' ? 'selected' : '' ?>>Leased</option>
                    <option value="maintenance" <?= $bike['status'] == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Current Image</label>
                <div class="mb-2">
                    <img src="../<?= htmlspecialchars($bike['image']) ?>" 
                         alt="<?= htmlspecialchars($bike['brand'] . ' ' . $bike['model']) ?>"
                         class="rounded" style="max-width: 200px;">
                </div>
                <label class="form-label">Change Image (Optional)</label>
                <input type="file" class="form-control" name="image" accept="image/*">
                <small class="text-muted">Leave empty to keep current image</small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Update Motorbike
                </button>
                <a href="manage_bikes.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html> 