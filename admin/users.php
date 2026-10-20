<?php
require_once "auth.php";
require_once "../includes/db.php";
include "../includes/header.php";
include "layout.php";

$result = $conn->query(
    "SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC",
);
$customers = $result->fetch_all(MYSQLI_ASSOC);

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "delete") {
    $id = $_POST["user_id"];
    // Delete user bookings first (if any cascading isn't set up, it's safer)
    $stmt = $conn->prepare("DELETE FROM bookings WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Delete user notifications
    $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Now delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "User deleted successfully.";
        // Refresh users list
        $result = $conn->query("SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC");
        $customers = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $error = "Failed to delete user.";
    }
}
?>

<style>
  /* Admin Users Container */
.admin-users-container {
    padding: 0 5%;
    max-width: 1400px;
    margin: 0 auto;
    min-height: 60vh;
}

/* Heading */
.admin-users-heading {
    color: var(--primary-color);
    margin-bottom: 2rem;
}

/* Table Wrapper */
.admin-users-table-wrapper {
    overflow-x: auto;
}

/* User Name */
.admin-users-name {
    font-weight: 600;
    color: var(--text-color);
}

/* Bookings Count */
.admin-users-bookings {
    font-weight: 600;
    color: var(--primary-color);
}

/* Action forms and links */
.admin-users-edit-btn {
    color: var(--primary-color);
    font-weight: 600;
    margin-right: 10px;
    text-decoration: none;
}
.admin-users-delete-form {
    display: inline;
}
.admin-users-delete-btn {
    background: none;
    border: none;
    color: #dc2626;
    cursor: pointer;
    font-weight: 600;
}
.alert {
padding: 1rem; border-radius: 4px; margin-bottom: 1rem;
}
.alert-success { background-color: #dcfce7; color: #166534; }
.alert-error { background-color: #fee2e2; color: #991b1b; }
</style>

<div class="admin-users-container">
    <h1 class="admin-users-heading">Manage Users</h1>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="admin-users-table-wrapper">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Customer ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Joined Date</th>
                    <th>Total Bookings (Link)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $c): ?>
                    <?php
                    // Get total bookings for this user
                    $b_stmt = $conn->prepare(
                        "SELECT COUNT(*) as count FROM bookings WHERE user_id = ?",
                    );
                    $b_stmt->bind_param("i", $c["id"]);
                    $b_stmt->execute();
                    $b_result = $b_stmt->get_result();
                    $total_bookings = $b_result->fetch_assoc()["count"];
                    ?>
                    <tr>
                        <td>#<?= str_pad($c["id"], 5, "0", STR_PAD_LEFT) ?></td>
                        <td class="admin-users-name"><?= htmlspecialchars(
                            $c["name"],
                        ) ?></td>
                        <td><?= htmlspecialchars($c["email"]) ?></td>
                        <td><?= htmlspecialchars($c["phone"] ?: "N/A") ?></td>
                        <td><?= date(
                            "M j, Y",
                            strtotime($c["created_at"]),
                        ) ?></td>
                        <td>
                            <span class="admin-users-bookings"><?= $total_bookings ?> bookings</span>
                        </td>
                        <td>
                            <a href="edit_user.php?id=<?= $c["id"] ?>" class="admin-users-edit-btn">Edit</a>
                            <form method="POST" class="admin-users-delete-form" onsubmit="return confirm('Are you sure you want to delete this user? This will also delete their bookings.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?= $c["id"] ?>">
                                <button type="submit" class="admin-users-delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
