<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base_path = strpos($_SERVER["SCRIPT_NAME"], "/admin/") !== false ? "../" : "";

$unread_notifications_count = 0;
if (isset($_SESSION["user_id"]) && isset($conn)) {
    try {
        $n_stmt = $conn->prepare(
            "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
        );
        $n_stmt->bind_param("i", $_SESSION["user_id"]);
        $n_stmt->execute();
        $n_result = $n_stmt->get_result();
        if ($n_row = $n_result->fetch_assoc()) {
            $unread_notifications_count = $n_row['count'];
        }
    } catch (Exception $e) {
        // Handle gracefully if table doesn't exist yet during transitions
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DecorSlot</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css?v=<?= time() ?>">
    <style>
       /* Navigation Notifications */
.nav-notifications {
    position: relative;
    display: inline-flex;
    align-items: center;
}

/* Notifications Count Badge */
.nav-notifications-count {
    background-color: #ef4444;
    color: white;
    font-size: 0.7rem;
    font-weight: bold;
    padding: 0.1rem 0.4rem;
    border-radius: 999px;
    margin-left: 0.3rem;
    line-height: 1;
}
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="<?= $base_path ?>index.php">Decor<span>Slot</span></a>
        </div>
        <ul class="nav-links">
            <li><a href="<?= $base_path ?>index.php">Home</a></li>
            <li><a href="<?= $base_path ?>catalog.php">Catalog</a></li>
            <?php if (isset($_SESSION["user_id"])): ?>
                <li><a href="<?= $base_path ?>orders.php">My Bookings</a></li>
                <li>
                    <a href="<?= $base_path ?>notifications.php" class="nav-notifications">
                        Notifications
                        <?php if ($unread_notifications_count > 0): ?>
                            <span class="nav-notifications-count"><?= $unread_notifications_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if ($_SESSION["role"] === "admin"): ?>
                    <li><a href="<?= $base_path ?>admin/index.php">Admin Panel</a></li>
                <?php endif; ?>
                <li style="color: var(--primary-color); font-weight: 600; display: flex; align-items: center; margin-right: 0.5rem;">
                    Hi, <?= htmlspecialchars($_SESSION["user_name"] ?? "User") ?>
                </li>
                <li><a href="<?= $base_path ?>logout.php" class="btn-secondary">Logout</a></li>
            <?php else: ?>
                <li><a href="<?= $base_path ?>login.php">Login</a></li>
                <li><a href="<?= $base_path ?>register.php" class="btn-primary">Register</a></li>
            <?php endif; ?>
        </ul>
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
    <main class="main-content">
