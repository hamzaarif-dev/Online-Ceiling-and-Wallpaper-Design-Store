<?php
require_once "auth.php";
require_once "../includes/db.php";

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    if ($_POST["action"] === "add") {
        $title = trim($_POST["title"]);
        $category_id = $_POST["category_id"];
        $price = $_POST["price_per_sqft"];
        $theme = trim($_POST["theme"]);
        $image_url = "";
        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] === UPLOAD_ERR_OK
        ) {
            $uploadDir = __DIR__ . "/../assets/uploads/designs/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = time() . "_" . basename($_FILES["image"]["name"]);
            if (
                move_uploaded_file(
                    $_FILES["image"]["tmp_name"],
                    $uploadDir . $filename,
                )
            ) {
                $image_url = "assets/uploads/designs/" . $filename;
            }
        }

        $stmt = $conn->prepare(
            "INSERT INTO designs (category_id, title, description, price_per_sqft, theme, image_url) VALUES (?, ?, ?, ?, ?, ?)",
        );
        $stmt->bind_param("isssss", $category_id, $title, $description, $price, $theme, $image_url);
        if ($stmt->execute()) {
            $success = "Design added successfully.";
        } else {
            $error = "Failed to add design.";
        }
    } elseif ($_POST["action"] === "delete") {
        $id = $_POST["design_id"];
        $stmt = $conn->prepare("DELETE FROM designs WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $success = "Design deleted successfully.";
    }
}

include "../includes/header.php";
include "layout.php";

$cat_result = $conn->query("SELECT * FROM categories");
$categories = $cat_result->fetch_all(MYSQLI_ASSOC);

$result = $conn->query(
    "SELECT d.*, c.name as category_name FROM designs d LEFT JOIN categories c ON d.category_id = c.id ORDER BY d.created_at DESC",
);
$designs = $result->fetch_all(MYSQLI_ASSOC);
?>

<style>
   /* Admin Designs Container */
.admin-designs-container {
    padding: 0 5%;
    max-width: 1400px;
    margin: 0 auto;
    min-height: 60vh;
}

/* Header */
.admin-designs-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

/* Heading */
.admin-designs-heading {
    color: var(--primary-color);
}

/* Form Container */
.admin-designs-form-container {
    display: none;
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
}

/* Form Heading */
.admin-designs-form-heading {
    margin-bottom: 1.5rem;
}

/* Form Grid */
.admin-designs-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

/* Full Width Field */
.admin-designs-form-full {
    grid-column: 1 / -1;
}

/* Table Wrapper */
.admin-designs-table-wrapper {
    overflow-x: auto;
}

/* Image */
.admin-designs-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}

/* No Image Text */
.admin-designs-img-na {
    font-size: 0.8rem;
    color: #94a3b8;
}

/* Title */
.admin-designs-title {
    font-weight: 600;
}

/* Delete Form */
.admin-designs-delete-form {
    display: inline;
}

/* Delete Button */
.admin-designs-delete-btn {
    background: none;
    border: none;
    color: #dc2626;
    cursor: pointer;
    font-weight: 600;
}
</style>

<div class="admin-designs-container">
    <div class="admin-designs-header">
        <h1 class="admin-designs-heading">Manage Catalog</h1>
        <button onclick="document.getElementById('addForm').style.display='block'" class="btn-primary">Add New Design</button>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div id="addForm" class="admin-designs-form-container">
        <h3 class="admin-designs-form-heading">Add New Design</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="admin-designs-form-grid">
                <div class="form-group">
                    <label>Design Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" class="form-control" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat[
                                "id"
                            ] ?>"><?= htmlspecialchars($cat["name"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price Per Sq.Ft ($) *</label>
                    <input type="number" step="0.01" name="price_per_sqft" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Theme</label>
                    <input type="text" name="theme" class="form-control">
                </div>
                <div class="form-group admin-designs-form-full">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>
            </div>
            <button type="submit" class="btn-primary mt-3">Save Design</button>
            <button type="button" class="btn-secondary" onclick="document.getElementById('addForm').style.display='none'">Cancel</button>
        </form>
    </div>

    <div class="admin-designs-table-wrapper">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Theme</th>
                    <th>Price/Sq.Ft</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($designs as $d): ?>
                    <tr>
                        <td>
                            <?php if (!empty($d["image_url"])): ?>
                                <img src="../<?= htmlspecialchars(
                                    $d["image_url"],
                                ) ?>" alt="design" class="admin-designs-img">
                            <?php else: ?>
                                <span class="admin-designs-img-na">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td class="admin-designs-title"><?= htmlspecialchars(
                            $d["title"],
                        ) ?></td>
                        <td><?= htmlspecialchars($d["category_name"]) ?></td>
                        <td><?= htmlspecialchars($d["theme"]) ?></td>
                        <td>Rs <?= number_format($d["price_per_sqft"], 2) ?></td>
                        <td>
                            <a href="edit_design.php?id=<?= $d["id"] ?>" class="admin-designs-edit-btn" style="color: var(--primary-color); font-weight: 600; margin-right: 10px; text-decoration: none;">Edit</a>
                            <form method="POST" class="admin-designs-delete-form" onsubmit="return confirm('Are you sure you want to delete this design?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="design_id" value="<?= $d[
                                    "id"
                                ] ?>">
                                <button type="submit" class="admin-designs-delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
