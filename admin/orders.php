<?php
require_once "auth.php";
require_once "../includes/db.php";
require_once "../includes/send_mail.php";

$success = "";

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action"]) &&
    $_POST["action"] === "update_status"
) {
    $booking_id = $_POST["booking_id"];
    $new_status = $_POST["status"];

    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);
    if ($stmt->execute()) {
        $success = "Booking status updated securely.";

        // Fetch user info to send notification
        $u_stmt = $conn->prepare(
            "SELECT b.*, u.email, u.name, d.title FROM bookings b JOIN users u ON b.user_id = u.id JOIN designs d ON b.design_id = d.id WHERE b.id = ?",
        );
        $u_stmt->bind_param("i", $booking_id);
        $u_stmt->execute();
        $u_result = $u_stmt->get_result();
        $booking = $u_result->fetch_assoc();

        if ($booking) {
            $subject = "Order Status Update - DecorSlot";
            
            $status_color = "#0b5ed7"; // Default blue
            if ($new_status === "approved" || $new_status === "completed") $status_color = "#198754"; // Green
            elseif ($new_status === "cancelled") $status_color = "#dc3545"; // Red
            elseif ($new_status === "dispatched") $status_color = "#fd7e14"; // Orange

            $body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; margin: 0; padding: 0; }
                    .email-container { max-width: 600px; margin: 20px auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
                    .email-header { background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; padding: 40px 20px; text-align: center; }
                    .email-header h1 { margin: 0; font-size: 28px; }
                    .email-body { padding: 40px 30px; }
                    .greeting { font-size: 18px; color: #333; margin-bottom: 20px; }
                    .status-badge { display: inline-block; padding: 8px 16px; border-radius: 20px; color: white; font-weight: bold; background: {$status_color}; }
                    .details-container { background: #f8f9fa; border-left: 4px solid #0d6efd; padding: 20px; margin: 20px 0; border-radius: 4px; }
                    .details-table { width: 100%; border-collapse: collapse; }
                    .details-table td { padding: 8px 0; border-bottom: 1px solid #eee; }
                    .details-table tr:last-child td { border-bottom: none; }
                    .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #dee2e6; }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <div class='email-header'>
                        <h1>📦 Order Update</h1>
                    </div>
                    <div class='email-body'>
                        <p class='greeting'>Hello <strong>" . htmlspecialchars($booking['name']) . "</strong>,</p>
                        
                        <p>There is an update regarding your recent booking with DecorSlot.</p>
                        
                        <p style='text-align: center; margin: 30px 0;'>
                            <span class='status-badge'>Status changed to: " . strtoupper($new_status) . "</span>
                        </p>
                        
                        <div class='details-container'>
                            <h3 style='margin-top: 0; color: #0d6efd;'>Order Details</h3>
                            <table class='details-table'>
                                <tr>
                                    <td><strong>Service:</strong></td>
                                    <td style='text-align: right;'>" . htmlspecialchars($booking['title']) . "</td>
                                </tr>
                                <tr>
                                    <td><strong>Order ID:</strong></td>
                                    <td style='text-align: right;'>#" . str_pad($booking['id'], 5, '0', STR_PAD_LEFT) . "</td>
                                </tr>
                            </table>
                        </div>
                        
                        <p>You can track further details from your DecorSlot dashboard.</p>
                        
                        <p style='margin-top: 20px;'>Best regards,<br><strong>DecorSlot Team</strong></p>
                    </div>
                    <div class='footer'>
                        <p style='margin: 5px 0;'><strong>DecorSlot Service</strong></p>
                        <p style='margin: 5px 0;'>© " . date('Y') . " All Rights Reserved</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            sendEmail($booking["email"], $subject, $body);

            // Insert notification for the user dashboard
            $notif_title = "Order Updated";
            $notif_msg =
                "Your booking for " .
                $booking["title"] .
                " has been updated to " .
                ucfirst($new_status) .
                ".";
            $n_stmt = $conn->prepare(
                "INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)",
            );
            $n_stmt->bind_param("iss", $booking["user_id"], $notif_title, $notif_msg);
            $n_stmt->execute();
        }
    }
}

include "../includes/header.php";
include "layout.php";

$result = $conn->query(
    "SELECT b.*, u.name as customer_name, u.email as customer_email, u.phone, d.title as design_title FROM bookings b JOIN users u ON b.user_id = u.id JOIN designs d ON b.design_id = d.id ORDER BY b.created_at DESC",
);
$bookings = $result->fetch_all(MYSQLI_ASSOC);
?>

<style>
   /* Admin Orders Container */
.admin-orders-container {
    padding: 0 5%;
    max-width: 1400px;
    margin: 0 auto;
    min-height: 60vh;
}

/* Heading */
.admin-orders-heading {
    color: var(--primary-color);
    margin-bottom: 2rem;
}

/* Table Wrapper */
.admin-orders-table-wrapper {
    overflow-x: auto;
}

/* Text Styles */
.admin-orders-bold {
    font-weight: 600;
    color: var(--text-color);
}

.admin-orders-light {
    font-size: 0.85rem;
    color: var(--text-light);
}

.admin-orders-bold-primary {
    font-weight: 600;
    color: var(--primary-color);
}

/* Form */
.admin-orders-form {
    display: flex;
    gap: 0.5rem;
}

/* Select */
.admin-orders-select {
    padding: 0.4rem;
    font-size: 0.85rem;
}
</style>

<div class="admin-orders-container">
    <h1 class="admin-orders-heading">Manage Orders</h1>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="admin-orders-table-wrapper">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Info</th>
                    <th>Design & Features</th>
                    <th>Date & Time</th>
                    <th>Total / Payment</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td>#<?= str_pad($b["id"], 5, "0", STR_PAD_LEFT) ?></td>
                        <td>
                            <div class="admin-orders-bold"><?= htmlspecialchars(
                                $b["customer_name"],
                            ) ?></div>
                            <div class="admin-orders-light"><?= htmlspecialchars(
                                $b["customer_email"],
                            ) ?></div>
                            <div class="admin-orders-light"><?= htmlspecialchars(
                                $b["phone"],
                            ) ?></div>
                        </td>
                        <td>
                            <div style="font-weight: 600;"><?= htmlspecialchars(
                                $b["design_title"],
                            ) ?></div>
                            <div class="admin-orders-light"><?= htmlspecialchars(
                                $b["dimensions_sqft"],
                            ) ?> sq.ft</div>
                            <div class="admin-orders-light" style="margin-top:2px;">Color: <?= htmlspecialchars($b["color_preference"]) ?> &bull; Finish: <?= htmlspecialchars($b["material_finish"]) ?></div>
                        </td>
                        <td><?= date(
                            "M j, Y g:i A",
                            strtotime($b["scheduled_datetime"]),
                        ) ?></td>
                        <td>
                            <div class="admin-orders-bold-primary">Rs <?= number_format(
                                $b["total_price"],
                                2,
                            ) ?></div>
                            <div class="admin-orders-light"><?= strtoupper(
                                $b["payment_method"],
                            ) ?> (<?= $b["payment_status"] ?>)</div>
                        </td>
                        <td><span class="badge badge-<?= $b[
                            "status"
                        ] ?>"><?= ucfirst($b["status"]) ?></span></td>
                        <td>
                            <form method="POST" class="admin-orders-form">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="booking_id" value="<?= $b[
                                    "id"
                                ] ?>">
                                <select name="status" class="form-control admin-orders-select" onchange="this.form.submit()">
                                    <option value="pending" <?= $b["status"] ===
                                    "pending"
                                        ? "selected"
                                        : "" ?>>Pending</option>
                                    <option value="approved" <?= $b[
                                        "status"
                                    ] === "approved"
                                        ? "selected"
                                        : "" ?>>Approved</option>
                                    <option value="dispatched" <?= $b[
                                        "status"
                                    ] === "dispatched"
                                        ? "selected"
                                        : "" ?>>Dispatched</option>
                                    <option value="completed" <?= $b[
                                        "status"
                                    ] === "completed"
                                        ? "selected"
                                        : "" ?>>Completed</option>
                                    <option value="cancelled" <?= $b[
                                        "status"
                                    ] === "cancelled"
                                        ? "selected"
                                        : "" ?>>Cancelled</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
