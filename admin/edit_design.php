<?php
require_once "auth.php";
require_once "../includes/db.php";

$success = "";
$error = "";

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: designs.php");
    exit();
}

$id = $_GET["id"];

// Fetch design details
$stmt = $conn->prepare("SELECT * FROM designs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$design = $result->fetch_assoc();

if (!$design) {
    header("Location: designs.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "edit") {
    $title = trim($_POST["title"]);
    $category_id = $_POST["category_id"];
    $price = $_POST["price_per_sqft"];
    $theme = trim($_POST["theme"]);
    $description = trim($_POST["description"]);

    $image_url = $design["image_url"]; // Default to old image

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . "/../assets/uploads/designs/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $uploadDir . $filename)) {
            $image_url = "assets/uploads/designs/" . $filename;
        }
    }

    $update_stmt = $conn->prepare(
        "UPDATE designs SET category_id = ?, title = ?, description = ?, price_per_sqft = ?, theme = ?, image_url = ? WHERE id = ?"
    );
    $update_stmt->bind_param("isssssi", $category_id, $title, $description, $price, $theme, $image_url, $id);
    if ($update_stmt->execute()) {
        $success = "Design updated successfully.";
        // Refresh design data
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $design = $result->fetch_assoc();
    } else {
        $error = "Failed to update design.";
    }
}

// Fetch categories for the dropdown
$cat_result = $conn->query("SELECT * FROM categories");
$categories = $cat_result->fetch_all(MYSQLI_ASSOC);

include "../includes/header.php";
include "layout.php";
?>

<style>
.admin-designs-container {
    padding: 0 5%;
    max-width: 1200px;
    margin: 0 auto;
    min-height: 60vh;
}
.admin-designs-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}
.admin-designs-heading {
    color: var(--primary-color);
}
.admin-designs-form-container {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
}
.admin-designs-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}
.admin-designs-form-full {
    grid-column: 1 / -1;
}
.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}
.alert-success {
    background-color: #dcfce7;
    color: #166534;
}
.alert-error {
    background-color: #fee2e2;
    color: #991b1b;
}
.current-image {
    max-width: 100px;
    border-radius: 4px;
    margin-top: 10px;
    display: block;
}
</style>

<div class="admin-designs-container">
    <div class="admin-designs-header">
        <h1 class="admin-designs-heading">Edit Design</h1>
        <a href="designs.php" class="btn-secondary" style="text-decoration: none;">Back to Catalog</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="admin-designs-form-container">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <div class="admin-designs-form-grid">
                <div class="form-group">
                    <label>Design Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <?php if (!empty($design['image_url'])): ?>
                        <img src="../<?= htmlspecialchars($design['image_url']) ?>" alt="Current Image" class="current-image">
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($design['title']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" class="form-control" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat["id"] ?>" <?= $cat["id"] == $design["category_id"] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat["name"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price Per Sq.Ft ($) *</label>
                    <input type="number" step="0.01" name="price_per_sqft" class="form-control" value="<?= htmlspecialchars($design['price_per_sqft']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Theme</label>
                    <input type="text" name="theme" class="form-control" value="<?= htmlspecialchars($design['theme']) ?>">
                </div>
                <div class="form-group admin-designs-form-full">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($design['description'] ?? "") ?></textarea>
                </div>
            </div>
            <button type="submit" class="btn-primary mt-3">Update Design</button>
        </form>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
