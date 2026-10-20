<?php
require_once "auth.php";
require_once "../includes/db.php";

$success = "";
$error = "";

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: users.php");
    exit();
}

$id = $_GET["id"];

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: users.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "edit") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);

    // Check if email already exists for another user
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check_stmt->bind_param("si", $email, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->fetch_assoc()) {
        $error = "Email is already taken by another user.";
    } else {
        $update_stmt = $conn->prepare(
            "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?"
        );
        $update_stmt->bind_param("sssi", $name, $email, $phone, $id);
        if ($update_stmt->execute()) {
            $success = "User updated successfully.";
            // Refresh user data
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error = "Failed to update user.";
        }
    }
}

include "../includes/header.php";
include "layout.php";
?>

<style>
.admin-users-container {
    padding: 0 5%;
    max-width: 1200px;
    margin: 0 auto;
    min-height: 60vh;
}
.admin-users-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}
.admin-users-heading {
    color: var(--primary-color);
}
.admin-users-form-container {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
}
.admin-users-form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    max-width: 600px;
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
</style>

<div class="admin-users-container">
    <div class="admin-users-header">
        <h1 class="admin-users-heading">Edit User</h1>
        <a href="users.php" class="btn-secondary" style="text-decoration: none;">Back to Manage Users</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="admin-users-form-container">
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <div class="admin-users-form-grid">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? "") ?>">
                </div>
            </div>
            <button type="submit" class="btn-primary mt-3">Update User</button>
        </form>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
