<?php
require_once "includes/db.php";
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$booking_id = $_GET["id"] ?? null;
if (!$booking_id || !is_numeric($booking_id)) {
    die("Invalid Order ID.");
}

// Fetch booking data
$stmt = $conn->prepare("SELECT b.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone, u.address as customer_address, d.title as design_title, d.price_per_sqft 
                       FROM bookings b 
                       JOIN users u ON b.user_id = u.id 
                       JOIN designs d ON b.design_id = d.id 
                       WHERE b.id = ? AND (b.user_id = ? OR ? = 'admin')");
$stmt->bind_param("sss", $booking_id, $_SESSION["user_id"], $_SESSION["role"]);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();

if (!$invoice) {
    die("Access denied or invoice not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= str_pad($invoice["id"], 5, "0", STR_PAD_LEFT) ?></title>
    <style>
     /* Base Styles */
body {
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    background: #f8fafc;
    padding: 2rem;
    color: #333;
}

/* Container */
.invoice-container {
    background: #fff;
    max-width: 800px;
    margin: 0 auto;
    padding: 3rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

/* Header */
.header {
    display: flex;
    justify-content: space-between;
    border-bottom: 2px solid #0f4c81;
    padding-bottom: 1rem;
    margin-bottom: 2rem;
}

/* Logo */
.logo {
    font-size: 2rem;
    font-weight: bold;
    color: #0f4c81;
}

.logo span {
    color: #d4af37;
}

/* Invoice Details */
.invoice-details {
    text-align: right;
}

.invoice-title {
    margin: 0;
    color: #64748b;
}

/* Billing Section */
.bill-to {
    margin-bottom: 2rem;
}

/* Table */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2rem;
}

.table th,
.table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.table th {
    background: #f1f5f9;
    color: #0f4c81;
}

/* Amount Alignment */
.invoice-amount-th,
.invoice-amount-td {
    text-align: right;
}

/* Total Row */
.total-row {
    font-size: 1.5rem;
    font-weight: bold;
    color: #0f4c81;
}

.invoice-total-label {
    text-align: right;
    padding-top: 2rem;
    font-weight: bold;
}

.invoice-total-val {
    text-align: right;
    padding-top: 2rem;
}

/* Button */
.btn-print {
    display: block;
    width: 100px;
    margin: 2rem auto;
    text-align: center;
    padding: 0.8rem;
    background: #0f4c81;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
    cursor: pointer;
    border: none;
}

/* Footer */
.invoice-footer {
    margin-top: 4rem;
    text-align: center;
    color: #64748b;
    font-size: 0.9rem;
}

/* Print Styles */
@media print {
    body {
        background: #fff;
        padding: 0;
    }

    .invoice-container {
        box-shadow: none;
        border: none;
        padding: 0;
    }

    .btn-print {
        display: none;
    }
}
    </style>
</head>
<body>

<div class="invoice-container">
    <div class="header">
        <div class="logo">Decor<span>Slot</span></div>
        <div class="invoice-details">
            <h2 class="invoice-title">INVOICE</h2>
            <p><strong>Order #:</strong> <?= str_pad(
                $invoice["id"],
                5,
                "0",
                STR_PAD_LEFT,
            ) ?></p>
            <p><strong>Date:</strong> <?= date(
                "F j, Y",
                strtotime($invoice["created_at"]),
            ) ?></p>
            <p><strong>Status:</strong> <?= ucfirst(
                $invoice["payment_status"],
            ) ?></p>
        </div>
    </div>

    <div class="bill-to">
        <h3>Bill To:</h3>
        <p><strong>Name:</strong> <?= htmlspecialchars(
            $invoice["customer_name"],
        ) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars(
            $invoice["customer_email"],
        ) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars(
            $invoice["customer_phone"],
        ) ?></p>
        <p><strong>Address:</strong> <?= nl2br(
            htmlspecialchars($invoice["customer_address"]),
        ) ?></p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Service Description</th>
                <th>Rate (per sq.ft)</th>
                <th>Dimensions</th>
                <th class="invoice-amount-th">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= htmlspecialchars(
                    $invoice["design_title"],
                ) ?><br><small>Scheduled: <?= date(
    "M j, Y g:i A",
    strtotime($invoice["scheduled_datetime"]),
) ?></small></td>
                <td>Rs <?= number_format($invoice["price_per_sqft"], 2) ?></td>
                <td><?= htmlspecialchars(
                    $invoice["dimensions_sqft"],
                ) ?> sq.ft</td>
                <td class="invoice-amount-td">Rs <?= number_format(
                    $invoice["total_price"],
                    2,
                ) ?></td>
            </tr>
            <tr>
                <td colspan="3" class="invoice-total-label">Grand Total:</td>
                <td class="total-row invoice-total-val">Rs <?= number_format(
                    $invoice["total_price"],
                    2,
                ) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="invoice-footer">
        <p>Thank you for choosing DecorSlot for your interior needs.</p>
        <p>If you have any questions about this invoice, please contact booking@decorslot.com</p>
    </div>
</div>

<button class="btn-print" onclick="window.print()">Print Invoice</button>

</body>
</html>
