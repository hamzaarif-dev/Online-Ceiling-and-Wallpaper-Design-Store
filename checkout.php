<?php
require_once "includes/db.php";
require_once "includes/send_mail.php";
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" && !isset($_GET["success"])) {
    header("Location: catalog.php");
    exit();
}

if (isset($_GET["success"])) {
    include "includes/header.php"; ?>
    <style>
       /* Checkout Success Container */
.checkout-success-container {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem;
}

/* Success Icon */
.checkout-success-icon {
    width: 80px;
    height: 80px;
    background: #dcfce7;
    color: #166534;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    font-size: 2rem;
}

/* Title */
.checkout-success-title {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

/* Description */
.checkout-success-desc {
    color: var(--text-light);
    margin-bottom: 2rem;
}
    </style>
    <div class="checkout-success-container">
        <div>
            <div class="checkout-success-icon">
                ✓
            </div>
            <h1 class="checkout-success-title">Booking Confirmed!</h1>
            <p class="checkout-success-desc">Your installation service has been successfully booked. You will receive an email confirmation shortly.</p>
            <a href="orders.php" class="btn-primary">View My Bookings</a>
        </div>
    </div>
    <?php
    include "includes/footer.php";
    exit();

}

$design_id = $_POST["design_id"];
$dimensions = $_POST["dimensions_sqft"];
$scheduled_datetime = $_POST["scheduled_datetime"];
$color_preference = $_POST["color_preference"] ?? 'Default';
$material_finish = $_POST["material_finish"] ?? 'Default';

// Fetch design details to calculate total securely on the backend
$stmt = $conn->prepare("SELECT * FROM designs WHERE id = ?");
$stmt->bind_param("i", $design_id);
$stmt->execute();
$result = $stmt->get_result();
$design = $result->fetch_assoc();

if (!$design) {
    die("Invalid design selected.");
}

$total_price = $dimensions * $design["price_per_sqft"];
$error = "";

if (isset($_POST["confirm_booking"])) {
    $payment_method = $_POST["payment_method"];

    // In a real scenario, integrate Stripe/PayPal here for 'online'
    $payment_status = $payment_method === "online" ? "completed" : "pending";

    $stmt = $conn->prepare(
        "INSERT INTO bookings (user_id, design_id, dimensions_sqft, total_price, scheduled_datetime, status, payment_status, payment_method, color_preference, material_finish) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?)",
    );
    $stmt->bind_param("sssssssss", $_SESSION["user_id"], $design_id, $dimensions, $total_price, $scheduled_datetime, $payment_status, $payment_method, $color_preference, $material_finish);
    if ($stmt->execute()) {
        // Insert notification
        $notif_title = "Order Booked Successfully";
        $notif_msg =
            "You have booked an order for {$design["title"]}. Dimensions: {$dimensions} sq.ft, Total: Rs " .
            number_format($total_price, 2) .
            ". Scheduled on: " .
            date("F j, Y, g:i a", strtotime($scheduled_datetime)) .
            ".";
        $notif_stmt = $conn->prepare(
            "INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)",
        );
        $notif_stmt->bind_param("iss", $_SESSION["user_id"], $notif_title, $notif_msg);
        $notif_stmt->execute();

        // Fetch user email
        $u_stmt = $conn->prepare("SELECT email, name FROM users WHERE id = ?");
        $u_stmt->bind_param("i", $_SESSION["user_id"]);
        $u_stmt->execute();
        $u_result = $u_stmt->get_result();
        $user = $u_result->fetch_assoc();

        // Send Email Confirmation
        $subject = "Booking Confirmation - DecorSlot";
        $scheduled_formatted = date(
            "F j, Y, g:i a",
            strtotime($scheduled_datetime),
        );
        $formatted_price = number_format($total_price, 2);

        $body =
            "
        <html>
        <head>
            <style>
                body { 
                font-family: Arial, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                background: #f4f4f4; 
                margin: 0; 
                padding: 0; 
                
                }
                .email-container { 
                max-width: 600px; 
                margin: 20px auto; 
                background: white; 
                border-radius: 10px; 
                overflow: hidden; 
                box-shadow: 0 0 20px rgba(0,0,0,0.1); 

                }
                .email-header { 
                
                background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); 
                color: white; 
                padding: 40px 20px; 
                text-align: center; 
                
                }
                .email-header h1 {
                
                margin: 0; 
                font-size: 28px; 
                
                }
                .email-body { 
                padding: 40px 30px; 
                }
                .greeting { 
                font-size: 18px; 
                color: #333; 
                margin-bottom: 20px; 
                }

                .details-container { 
                background: #f8f9fa; 
                border-left: 4px solid #0d6efd; 
                padding: 20px; 
                margin: 20px 0; 
                border-radius: 4px; 
                }

                .details-table { 
                width: 100%; 
                border-collapse: collapse; 
                }

                .details-table td { 
                padding: 8px 0; 
                border-bottom: 1px solid #eee; 
                
                }
                
                .details-table tr:last-child td { 
                border-bottom: none; 
                
                }
                .footer { 
                background: #f8f9fa; 
                padding: 20px; 
                text-align: center; 
                font-size: 12px; color: #666; border-top: 1px solid #dee2e6; 
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h1>📅 Booking Confirmed!</h1>
                </div>
                <div class='email-body'>
                    <p class='greeting'>Hello <strong>" .
            htmlspecialchars($user["name"]) .
            "</strong>,</p>
                    
                    <p>Thank you for choosing DecorSlot! We have successfully received your booking request.</p>
                    
                    <div class='details-container'>
                        <h3 style='margin-top: 0; color: #0d6efd;'>Booking Summary</h3>
                        <table class='details-table'>
                            <tr>
                                <td><strong>Service:</strong></td>
                                <td style='text-align: right;'>" .
            htmlspecialchars($design["title"]) .
            "</td>
                            </tr>
                            <tr>
                                <td><strong>Features:</strong></td>
                                <td style='text-align: right;'>" .
            htmlspecialchars($color_preference) .
            " Color, " .
            htmlspecialchars($material_finish) .
            " Finish</td>
                            </tr>
                            <tr>
                                <td><strong>Dimensions:</strong></td>
                                <td style='text-align: right;'>" .
            htmlspecialchars($dimensions) .
            " sq.ft</td>
                            </tr>
                            <tr>
                                <td><strong>Scheduled Date:</strong></td>
                                <td style='text-align: right;'>" .
            $scheduled_formatted .
            "</td>
                            </tr>
                            <tr>
                                <td><strong>Payment Method:</strong></td>
                                <td style='text-align: right;'>" .
            ucfirst(htmlspecialchars($payment_method)) .
            "</td>
                            </tr>
                            <tr>
                                <td style='padding-top: 15px;'><strong>Total Price:</strong></td>
                                <td style='padding-top: 15px; text-align: right; font-size: 18px; font-weight: bold; color: #0d6efd;'>$" .
            $formatted_price .
            "</td>
                            </tr>
                        </table>
                    </div>
                    
                    <p>Our team will review your booking and assign an expert for your installation. You can track your order status live from your DecorSlot dashboard.</p>
                    
                    <p style='margin-top: 20px;'>Best regards,<br><strong>DecorSlot Team</strong></p>
                </div>
                <div class='footer'>
                    <p style='margin: 5px 0;'><strong>DecorSlot Service</strong></p>
                    <p style='margin: 5px 0;'>© " .
            date("Y") .
            " All Rights Reserved</p>
                    <p style='margin: 5px 0;'>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        sendEmail($user["email"], $subject, $body);

        header("Location: checkout.php?success=1");
        exit();
    } else {
        $error = "Failed to process booking.";
    }
}

include "includes/header.php";
?>

<style>
    .checkout-container { padding: 4rem 5%; max-width: 800px; margin: 0 auto; }
    .checkout-heading { color: var(--primary-color); margin-bottom: 2rem; text-align: center; }
    .checkout-summary-card { background: var(--white); padding: 2.5rem; border-radius: var(--border-radius); box-shadow: 0 10px 40px rgba(0,0,0,0.05); }
    .checkout-summary-heading { margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem; }
    .checkout-table { width: 100%; margin-bottom: 2rem; border-collapse: collapse; }
    .checkout-table-td { padding: 1rem 0; border-bottom: 1px solid #f1f5f9; }
    .checkout-table-td-right { padding: 1rem 0; border-bottom: 1px solid #f1f5f9; text-align: right; }
    .checkout-table-total-td { padding: 1.5rem 0; font-size: 1.25rem; }
    .checkout-table-total-val { padding: 1.5rem 0; font-size: 1.5rem; font-weight: 700; color: var(--primary-color); text-align: right; }
    .checkout-payment-heading { margin-bottom: 1rem; }
    .checkout-payment-flex { display: flex; gap: 1rem; margin-bottom: 2rem; }
    .checkout-payment-label { flex: 1; border: 1px solid #cbd5e1; padding: 1rem; border-radius: 8px; cursor: pointer; text-align: center; }
    .checkout-payment-title { margin-top: 0.5rem; font-weight: 600; }
    .checkout-payment-desc { font-size: 0.85rem; color: var(--text-light); }
    .checkout-submit-btn { width: 100%; font-size: 1.1rem; padding: 1.2rem; }
</style>

<div class="checkout-container">
    <h1 class="checkout-heading">Checkout</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="checkout-summary-card">
        <h3 class="checkout-summary-heading">Order Summary</h3>
        
        <table class="checkout-table">
            <tr>
                <td class="checkout-table-td"><strong>Service</strong></td>
                <td class="checkout-table-td-right"><?= htmlspecialchars(
                    $design["title"],
                ) ?></td>
            </tr>
            <tr>
                <td class="checkout-table-td"><strong>Features</strong></td>
                <td class="checkout-table-td-right">Color: <?= htmlspecialchars($color_preference) ?> &bull; Finish: <?= htmlspecialchars($material_finish) ?></td>
            </tr>
            <tr>
                <td class="checkout-table-td"><strong>Dimensions</strong></td>
                <td class="checkout-table-td-right"><?= htmlspecialchars(
                    $dimensions,
                ) ?> sq.ft</td>
            </tr>
            <tr>
                <td class="checkout-table-td"><strong>Scheduled Time</strong></td>
                <td class="checkout-table-td-right"><?= date(
                    "F j, Y, g:i a",
                    strtotime($scheduled_datetime),
                ) ?></td>
            </tr>
            <tr>
                <td class="checkout-table-total-td"><strong>Total Due</strong></td>
                <td class="checkout-table-total-val">Rs <?= number_format(
                    $total_price,
                    2,
                ) ?></td>
            </tr>
        </table>

        <form method="POST" action="checkout.php">
            <input type="hidden" name="design_id" value="<?= $design_id ?>">
            <input type="hidden" name="dimensions_sqft" value="<?= $dimensions ?>">
            <input type="hidden" name="scheduled_datetime" value="<?= $scheduled_datetime ?>">
            <input type="hidden" name="color_preference" value="<?= htmlspecialchars($color_preference) ?>">
            <input type="hidden" name="material_finish" value="<?= htmlspecialchars($material_finish) ?>">
            <input type="hidden" name="confirm_booking" value="1">
            
            <h3 class="checkout-payment-heading">Payment Method</h3>
            <div class="checkout-payment-flex">
                <label class="checkout-payment-label">
                    <input type="radio" name="payment_method" value="online" checked>
                    <div class="checkout-payment-title">Pay Online Now</div>
                    <div class="checkout-payment-desc">(Online Payment Gateway)</div>
                </label>
                <label class="checkout-payment-label">
                    <input type="radio" name="payment_method" value="cod">
                    <div class="checkout-payment-title">Cash on Delivery</div>
                    <div class="checkout-payment-desc">(Pay strictly after installation)</div>
                </label>
            </div>
            
            <button type="submit" class="btn-primary checkout-submit-btn">Confirm Booking & Pay</button>
        </form>
    </div>
</div>

<?php include "includes/footer.php"; ?>
