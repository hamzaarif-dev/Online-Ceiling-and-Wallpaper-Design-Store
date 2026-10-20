<?php
require_once "auth.php";
require_once "../includes/db.php";
include "../includes/header.php";
include "layout.php";

// Fetch basic stats
$stats = [];

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
$stats["total_users"] = $result->fetch_assoc()["count"];

$result = $conn->query("SELECT COUNT(*) as count FROM designs");
$stats["total_designs"] = $result->fetch_assoc()["count"];

$result = $conn->query("SELECT COUNT(*) as count FROM bookings");
$stats["total_bookings"] = $result->fetch_assoc()["count"];

$result = $conn->query(
    "SELECT SUM(total_price) as total FROM bookings WHERE status != 'cancelled'",
);
$stats["total_revenue"] = $result->fetch_assoc()["total"] ?: 0;
?>

<style>
  /* Admin Dashboard Container */
.admin-dashboard-container {
    padding: 0 5%;
    max-width: 1200px;
    margin: 0 auto;
    min-height: 60vh;
}

/* Heading */
.admin-dashboard-heading {
    color: var(--primary-color);
    margin-bottom: 2rem;
}

/* Stats Grid */
.admin-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

/* Stat Card */
.admin-stat-card {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    text-align: center;
}

/* Stat Card Variants */
.admin-stat-card-users {
    border-bottom: 4px solid var(--primary-color);
}

.admin-stat-card-designs {
    border-bottom: 4px solid var(--secondary-color);
}

.admin-stat-card-bookings {
    border-bottom: 4px solid #10b981;
}

.admin-stat-card-revenue {
    border-bottom: 4px solid #8b5cf6;
}

/* Stat Title */
.admin-stat-title {
    color: var(--text-light);
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

/* Stat Value */
.admin-stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-color);
}
</style>

<div class="admin-dashboard-container">
    <h1 class="admin-dashboard-heading">Admin Dashboard</h1>

    <div class="admin-stats-grid">
        <div class="admin-stat-card admin-stat-card-users">
            <h3 class="admin-stat-title">Total Customers</h3>
            <div class="admin-stat-value"><?= number_format(
                $stats["total_users"],
            ) ?></div>
        </div>
        
        <div class="admin-stat-card admin-stat-card-designs">
            <h3 class="admin-stat-title">Total Designs</h3>
            <div class="admin-stat-value"><?= number_format(
                $stats["total_designs"],
            ) ?></div>
        </div>
        
        <div class="admin-stat-card admin-stat-card-bookings">
            <h3 class="admin-stat-title">Total Bookings</h3>
            <div class="admin-stat-value"><?= number_format(
                $stats["total_bookings"],
            ) ?></div>
        </div>
        
        <div class="admin-stat-card admin-stat-card-revenue">
            <h3 class="admin-stat-title">Estimated Revenue</h3>
            <div class="admin-stat-value">Rs <?= number_format(
                $stats["total_revenue"],
                2,
            ) ?></div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
