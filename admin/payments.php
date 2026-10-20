<?php
require_once "auth.php";
require_once "../includes/db.php";
require_once "../includes/send_mail.php";

$success = "";

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action"]) &&
    $_POST["action"] === "update_payment_status"
) {
    $booking_id = $_POST["booking_id"];
    $new_status = $_POST["payment_status"];

    $stmt = $conn->prepare("UPDATE bookings SET payment_status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);
    if ($stmt->execute()) {
        $success = "Payment status updated successfully.";

        // Fetch user info to send notification
        $u_stmt = $conn->prepare(
            "SELECT b.*, u.email, u.name, d.title FROM bookings b JOIN users u ON b.user_id = u.id JOIN designs d ON b.design_id = d.id WHERE b.id = ?",
        );
        $u_stmt->bind_param("i", $booking_id);
        $u_stmt->execute();
        $u_result = $u_stmt->get_result();
        $booking = $u_result->fetch_assoc();

        if ($booking) {
            $subject = "Payment Status Update - DecorSlot";
            
            $status_color = "#0b5ed7"; // Default blue
            if ($new_status === "completed") $status_color = "#198754"; // Green
            elseif ($new_status === "pending") $status_color = "#fd7e14"; // Orange

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
                        <h1>💳 Payment Update</h1>
                    </div>
                    <div class='email-body'>
                        <p class='greeting'>Hello <strong>" . htmlspecialchars($booking['name']) . "</strong>,</p>
                        
                        <p>There is an update regarding the payment for your recent booking with DecorSlot.</p>
                        
                        <p style='text-align: center; margin: 30px 0;'>
                            <span class='status-badge'>Payment Status: " . strtoupper($new_status) . "</span>
                        </p>
                        
                        <div class='details-container'>
                            <h3 style='margin-top: 0; color: #0d6efd;'>Payment Details</h3>
                            <table class='details-table'>
                                <tr>
                                    <td><strong>Service:</strong></td>
                                    <td style='text-align: right;'>" . htmlspecialchars($booking['title']) . "</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Due:</strong></td>
                                    <td style='text-align: right;'>$" . number_format($booking['total_price'], 2) . "</td>
                                </tr>
                                <tr>
                                    <td><strong>Order ID:</strong></td>
                                    <td style='text-align: right;'>#" . str_pad($booking['id'], 5, '0', STR_PAD_LEFT) . "</td>
                                </tr>
                            </table>
                        </div>
                        
                        <p>Thank you for choosing us! You can track further details from your DecorSlot dashboard.</p>
                        
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
            $notif_title = "Payment Updated";
            $notif_msg =
                "The payment status for your booking (" .
                $booking["title"] .
                ") has been updated to " .
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
    "SELECT b.*, u.name as customer_name, u.email as customer_email, u.phone, d.title as design_title FROM bookings b JOIN users u ON b.user_id = u.id JOIN designs d ON b.design_id = d.id ORDER BY b.created_at DESC"
);
$bookings = $result->fetch_all(MYSQLI_ASSOC);
?>

<style>
.admin-payments-container {
    padding: 0 5%;
    max-width: 1400px;
    margin: 0 auto;
    min-height: 60vh;
}
.admin-payments-heading {
    color: var(--primary-color);
    margin-bottom: 2rem;
}
.admin-payments-table-wrapper {
    overflow-x: auto;
}
.admin-payments-bold {
    font-weight: 600;
    color: var(--text-color);
}
.admin-payments-light {
    font-size: 0.85rem;
    color: var(--text-light);
}
.admin-payments-bold-primary {
    font-weight: 600;
    color: var(--primary-color);
}
.admin-payments-form {
    display: flex;
    gap: 0.5rem;
}
.admin-payments-select {
    padding: 0.4rem;
    font-size: 0.85rem;
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
</style>

<div class="admin-payments-container">
    <h1 class="admin-payments-heading">Manage Payments</h1>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="admin-payments-table-wrapper">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Info</th>
                    <th>Design & Features</th>
                    <th>Total Price</th>
                    <th>Payment Method</th>
                    <th>Payment Status (Action)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td>#<?= str_pad($b["id"], 5, "0", STR_PAD_LEFT) ?></td>
                        <td>
                            <div class="admin-payments-bold"><?= htmlspecialchars($b["customer_name"]) ?></div>
                            <div class="admin-payments-light"><?= htmlspecialchars($b["customer_email"]) ?></div>
                        </td>
                        <td>
                            <div style="font-weight: 600;"><?= htmlspecialchars($b["design_title"]) ?></div>
                            <div class="admin-payments-light" style="margin-top:2px;">Color: <?= htmlspecialchars($b["color_preference"]) ?> &bull; Finish: <?= htmlspecialchars($b["material_finish"]) ?></div>
                        </td>
                        <td>
                            <div class="admin-payments-bold-primary">Rs <?= number_format($b["total_price"], 2) ?></div>
                        </td>
                        <td>
                            <?= strtoupper($b["payment_method"]) ?>
                        </td>
                        <td>
                            <form method="POST" class="admin-payments-form">
                                <input type="hidden" name="action" value="update_payment_status">
                                <input type="hidden" name="booking_id" value="<?= $b["id"] ?>">
                                <select name="payment_status" class="form-control admin-payments-select" onchange="this.form.submit()">
                                    <option value="pending" <?= $b["payment_status"] === "pending" ? "selected" : "" ?>>Pending</option>
                                    <option value="completed" <?= $b["payment_status"] === "completed" ? "selected" : "" ?>>Completed</option>
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
