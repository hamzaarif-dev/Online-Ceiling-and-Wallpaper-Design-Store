<?php
require_once "includes/db.php";
include "includes/header.php";

$category_filter = $_GET["category"] ?? "";
$search_query = $_GET["search"] ?? "";

$sql =
    "SELECT d.*, c.name as category_name FROM designs d JOIN categories c ON d.category_id = c.id WHERE 1=1";
$params = [];

if ($category_filter) {
    // Only accept integer categories to prevent SQLi via $category_filter directly
    if (is_numeric($category_filter)) {
        $sql .= " AND d.category_id = ?";
        $params[] = $category_filter;
    }
}

if ($search_query) {
    $sql .= " AND (d.title LIKE ? OR d.theme LIKE ?)";
    $search_term = "%$search_query%";
    $params[] = $search_term;
    $params[] = $search_term;
}

$sql .= " ORDER BY d.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->get_result();
$designs = $result->fetch_all(MYSQLI_ASSOC);

// Fetch categories for the filter
$cat_result = $conn->query("SELECT * FROM categories");
$categories = $cat_result->fetch_all(MYSQLI_ASSOC);
?>

<style>
   /* Catalog Header */
.catalog-header {
    background: var(--primary-color);
    padding: 4rem 5%;
    color: var(--white);
    text-align: center;
}

.catalog-header h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.catalog-header p {
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}

/* Catalog Content */
.catalog-content {
    padding: 3rem 10%;
}

/* Filter Container */
.catalog-filter-container {
    background: var(--white);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    margin-bottom: 3rem;
}

/* Filter Form */
.catalog-filter-form {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.catalog-filter-input {
    flex: 1;
    min-width: 250px;
}

.catalog-filter-select {
    flex: 1;
    min-width: 200px;
}

/* Grid */
.catalog-grid-wrapper {
    padding: 0;
}

/* Design Description */
.catalog-design-desc {
    color: var(--text-light);
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

/* Button */
.catalog-design-btn {
    width: 100%;
    text-align: center;
}

/* Empty State */
.catalog-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem;
}

.catalog-empty h3 {
    color: var(--text-light);
}
</style>

<div class="catalog-header">
    <h1>Design Catalog</h1>
    <p>Browse through our extensive collection of premium ceiling decorations and modern wallpapers.</p>
</div>

<div class="catalog-content">
    <div class="catalog-filter-container">
        <form method="GET" action="catalog.php" class="catalog-filter-form">
            <div class="catalog-filter-input">
                <input type="text" name="search" class="form-control" placeholder="Search by title or theme..." value="<?= htmlspecialchars(
                    $search_query,
                ) ?>">
            </div>
            <div class="catalog-filter-select">
                <select name="category" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat[
                            "id"
                        ] ?>" <?= $category_filter == $cat["id"]
    ? "selected"
    : "" ?>><?= htmlspecialchars($cat["name"]) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-primary">Filter</button>
            <?php if ($search_query || $category_filter): ?>
                <a href="catalog.php" class="btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="catalog-grid catalog-grid-wrapper">
        <?php foreach ($designs as $design): ?>
            <div class="design-card">
                <?php $imgSrc = !empty($design["image_url"])
                    ? htmlspecialchars($design["image_url"])
                    : "https://images.unsplash.com/photo-1541888031267-33827d7f7aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"; ?>
                <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars(
    $design["title"],
) ?>" class="design-image">
                <div class="design-info">
                    <span class="design-category"><?= htmlspecialchars(
                        $design["category_name"],
                    ) ?></span>
                    <h3 class="design-title"><?= htmlspecialchars(
                        $design["title"],
                    ) ?></h3>
                    <p class="catalog-design-desc"><?= htmlspecialchars(
                        substr($design["description"], 0, 80),
                    ) ?>...</p>
                    <p class="design-price">Rs <?= htmlspecialchars(
                        $design["price_per_sqft"],
                    ) ?> / sq.ft</p>
                    <a href="product.php?id=<?= $design[
                        "id"
                    ] ?>" class="btn-primary catalog-design-btn">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($designs)): ?>
            <div class="catalog-empty">
                <h3>No designs matched your search criteria.</h3>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>
