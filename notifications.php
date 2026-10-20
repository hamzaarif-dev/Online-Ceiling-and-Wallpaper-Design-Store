<?php
require_once "includes/db.php";
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Mark all as read when user visits the page
$u_stmt = $conn->prepare(
    "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0",
);
$u_stmt->bind_param("i", $_SESSION["user_id"]);
$u_stmt->execute();

include "includes/header.php";

// Fetch notifications
$stmt = $conn->prepare(
    "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC",
);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
?>

<style>
    /* Notification Container */
.notif-container {
    padding: 4rem 5%;
    max-width: 800px;
    margin: 0 auto;
    min-height: 60vh;
}

/* Heading */
.notif-heading {
    color: var(--primary-color);
    margin-bottom: 2rem;
}

/* Empty State Card */
.notif-empty-card {
    background: var(--white);
    padding: 3rem;
    text-align: center;
    border-radius: var(--border-radius);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.notif-empty-text {
    color: var(--text-light);
    font-size: 1.1rem;
    margin-bottom: 0;
}

/* Notification List */
.notif-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Notification Card */
.notif-card {
    background: var(--white);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.notif-card-read {
    opacity: 0.8;
}

.notif-card-unread {
    border-left: 4px solid var(--primary-color);
}

/* Header Row */
.notif-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    align-items: center;
}

/* Title */
.notif-title {
    color: var(--secondary-color);
    margin: 0;
    font-size: 1.2rem;
}

/* Time */
.notif-time {
    font-size: 0.85rem;
    color: var(--text-light);
}

/* Message */
.notif-msg {
    color: var(--text-color);
    margin: 0;
    line-height: 1.6;
}
</style>

<div class="notif-container">
    <h1 class="notif-heading">Notifications</h1>

    <?php if (empty($notifications)): ?>
        <div class="notif-empty-card">
            <p class="notif-empty-text">You have no notifications yet.</p>
        </div>
    <?php else: ?>
        <div class="notif-list">
            <?php foreach ($notifications as $n): ?>
                <?php $read_class = $n["is_read"]
                    ? "notif-card-read"
                    : "notif-card-unread"; ?>
                <div class="notif-card <?= $read_class ?>">
                    <div class="notif-header">
                        <h3 class="notif-title"><?= htmlspecialchars(
                            $n["title"],
                        ) ?></h3>
                        <span class="notif-time"><?= date(
                            "M j, Y g:i A",
                            strtotime($n["created_at"]),
                        ) ?></span>
                    </div>
                    <p class="notif-msg"><?= htmlspecialchars(
                        $n["message"],
                    ) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
