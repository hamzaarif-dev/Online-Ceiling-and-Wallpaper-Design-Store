<?php
require_once "includes/db.php";
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "includes/header.php";

$stmt = $conn->prepare(
    "SELECT b.*, d.title as design_title FROM bookings b JOIN designs d ON b.design_id = d.id WHERE b.user_id = ? ORDER BY b.created_at DESC",
);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
?>

<style>
    /* Orders Container */
.orders-container {
    padding: 4rem 5%;
    max-width: 1400px;
    margin: 0 auto;
    min-height: 60vh;
}

/* Heading */
.orders-heading {
    color: var(--primary-color);
    margin-bottom: 2rem;
}

/* Empty State Card */
.orders-empty-card {
    background: var(--white);
    padding: 3rem;
    text-align: center;
    border-radius: var(--border-radius);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.orders-empty-text {
    color: var(--text-light);
    text-align: center;
    margin-bottom: 2rem;
    font-size: 1.1rem;
}

/* Table Wrapper */
.orders-table-wrapper {
    overflow-x: auto;
}

/* Design Title */
.orders-design-title {
    font-weight: 600;
    color: var(--text-color);
}

/* Status - Paid */
.orders-status-paid {
    color: #166534;
    font-weight: 600;
}

/* Status - Pending */
.orders-status-pending {
    color: #92400e;
    font-weight: 600;
}

/* Print Button */
.orders-btn-print {
    padding: 0.3rem 0.6rem;
    font-size: 0.85rem;
}
</style>

<div class="orders-container">
    <h1 class="orders-heading">My Bookings</h1>

    <?php if (empty($bookings)): ?>
        <div class="orders-empty-card">
            <p class="orders-empty-text">You have not booked any installation services yet.</p>
            <a href="catalog.php" class="btn-primary">Browse Catalog</a>
        </div>
    <?php else: ?>
        <div class="orders-table-wrapper">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Design & Features</th>
                        <th>Scheduled Date</th>
                        <th>Total Cost</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>#<?= str_pad(
                                $b["id"],
                                5,
                                "0",
                                STR_PAD_LEFT,
                            ) ?></td>
                            <td class="orders-design-title">
                                <?= htmlspecialchars($b["design_title"]) ?><br>
                                <span style="font-size:0.85em; color:var(--text-light); font-weight:normal;">Color: <?= htmlspecialchars($b["color_preference"]) ?> &bull; Finish: <?= htmlspecialchars($b["material_finish"]) ?></span>
                            </td>
                            <td><?= date(
                                "M j, Y g:i A",
                                strtotime($b["scheduled_datetime"]),
                            ) ?></td>
                            <td>Rs <?= number_format($b["total_price"], 2) ?></td>
                            <td>
                                <span class="badge badge-<?= $b[
                                    "status"
                                ] ?>"><?= ucfirst($b["status"]) ?></span>
                            </td>
                            <td>
                                <?php if (
                                    $b["payment_status"] === "completed"
                                ): ?>
                                    <span class="orders-status-paid">Paid</span>
                                <?php else: ?>
                                    <span class="orders-status-pending">Pending (COD)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="invoice.php?id=<?= $b[
                                    "id"
                                ] ?>" target="_blank" class="btn-secondary orders-btn-print">Print Invoice</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
