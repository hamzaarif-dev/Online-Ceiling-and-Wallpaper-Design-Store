<?php
require_once "auth.php";
require_once "../includes/db.php";
include "../includes/header.php";
include "layout.php";

// Prepare basic reporting data
$report_type = $_GET["type"] ?? "sales";

if ($report_type === "sales") {
    // Group sales by month and year
    $result = $conn->query("SELECT DATE_FORMAT(scheduled_datetime, '%Y-%m') as month, SUM(total_price) as revenue, COUNT(*) as volume 
                         FROM bookings WHERE status != 'cancelled' 
                         GROUP BY month ORDER BY month DESC");
    $report_data = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // Design Popularity report
    $result = $conn->query("SELECT d.title, COUNT(b.id) as total_bookings, SUM(b.total_price) as generated_revenue 
                         FROM designs d LEFT JOIN bookings b ON d.id = b.design_id 
                         GROUP BY d.id ORDER BY total_bookings DESC");
    $report_data = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<style>
    /* Admin Reports Container */
.admin-reports-container {
    padding: 0 5%;
    max-width: 1400px;
    margin: 0 auto;
    min-height: 60vh;
}

/* Header */
.admin-reports-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

/* Heading */
.admin-reports-heading {
    color: var(--primary-color);
}

/* Navigation */
.admin-reports-nav {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

/* Print Button */
.admin-reports-btn-print {
    margin-left: auto;
}

/* Card */
.admin-reports-card {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

/* Title */
.admin-reports-title {
    margin-bottom: 1.5rem;
}

/* Table */
.admin-reports-table {
    margin-top: 0;
}

/* Empty State */
.admin-reports-empty {
    text-align: center;
}

/* Text Styles */
.admin-reports-bold {
    font-weight: 600;
}

.admin-reports-val {
    color: var(--primary-color);
    font-weight: 600;
}
</style>

<div class="admin-reports-container">
    <div class="admin-reports-header">
        <h1 class="admin-reports-heading">Analytics & Reports</h1>
    </div>
    
    <div class="admin-reports-nav">
        <a href="reports.php?type=sales" class="<?= $report_type === "sales"
            ? "btn-primary"
            : "btn-secondary" ?>">Monthly Sales</a>
        <a href="reports.php?type=designs" class="<?= $report_type === "designs"
            ? "btn-primary"
            : "btn-secondary" ?>">Design Popularity</a>
        <button onclick="window.print()" class="btn-secondary admin-reports-btn-print">🖨️ Print Report</button>
    </div>

    <div class="admin-reports-card">
        
        <?php if ($report_type === "sales"): ?>
            <h3 class="admin-reports-title">Monthly Sales Performance</h3>
            <table class="dashboard-table admin-reports-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Bookings Volume</th>
                        <th>Generated Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($report_data)): ?>
                        <tr><td colspan="3" class="admin-reports-empty">No data available.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($report_data as $row): ?>
                        <tr>
                            <td class="admin-reports-bold"><?= date(
                                "F Y",
                                strtotime($row["month"] . "-01"),
                            ) ?></td>
                            <td><?= $row["volume"] ?></td>
                            <td class="admin-reports-val">Rs <?= number_format(
                                $row["revenue"],
                                2,
                            ) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ($report_type === "designs"): ?>
            <h3 class="admin-reports-title">Catalog Performance</h3>
            <table class="dashboard-table admin-reports-table">
                <thead>
                    <tr>
                        <th>Design Title</th>
                        <th>Times Booked</th>
                        <th>Total Yield</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($report_data)): ?>
                        <tr><td colspan="3" class="admin-reports-empty">No data available.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($report_data as $row): ?>
                        <tr>
                            <td class="admin-reports-bold"><?= htmlspecialchars(
                                $row["title"],
                            ) ?></td>
                            <td><?= $row["total_bookings"] ?></td>
                            <td class="admin-reports-val">Rs <?= number_format(
                                $row["generated_revenue"] ?: 0,
                                2,
                            ) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
    </div>
</div>

<?php include "../includes/footer.php"; ?>
