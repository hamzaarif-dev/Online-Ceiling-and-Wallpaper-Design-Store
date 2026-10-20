<?php
require_once "includes/db.php";

$id = $_GET["id"] ?? null;

if (!$id || !is_numeric($id)) {
    header("Location: catalog.php");
    exit();
}

$stmt = $conn->prepare(
    "SELECT d.*, c.name as category_name FROM designs d JOIN categories c ON d.category_id = c.id WHERE d.id = ?",
);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$design = $result->fetch_assoc();

if (!$design) {
    header("Location: catalog.php");
    exit();
}

include "includes/header.php";
?>

<style>
   /* Product Container */
.product-container {
    padding: 4rem 5%;
    max-width: 1200px;
    margin: 0 auto;
}

/* Product Grid */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 4rem;
}

/* Product Image */
.product-image {
    width: 100%;
    border-radius: var(--border-radius);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

/* Product Title */
.product-title {
    font-size: 2.5rem;
    color: var(--text-color);
    margin: 0.5rem 0 1rem;
}

/* Product Description */
.product-desc {
    font-size: 1.1rem;
    color: var(--text-light);
    margin-bottom: 2rem;
}

/* Booking Card */
.product-book-card {
    background: #f8fafc;
    padding: 2rem;
    border-radius: var(--border-radius);
    border: 1px solid #e2e8f0;
}

.product-book-h3 {
    margin-bottom: 1.5rem;
    color: var(--primary-color);
}

/* Price */
.product-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--secondary-color);
    margin-bottom: 1.5rem;
}

.product-price-sqft {
    font-size: 1rem;
    font-weight: 400;
    color: var(--text-light);
}

/* Total Section */
.product-total-container {
    margin: 2rem 0;
    padding-top: 1.5rem;
    border-top: 1px dashed #cbd5e1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.product-total-label {
    font-size: 1.2rem;
    font-weight: 600;
}

.product-total-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary-color);
}

/* Buttons */
.product-submit-btn {
    width: 100%;
    font-size: 1.1rem;
    padding: 1rem;
}

/* Login Alert */
.product-alert-login {
    text-align: center;
}

.product-login-btn {
    display: block;
    text-align: center;
}
</style>

<div class="product-container">
    <div class="product-grid">
        
        <!-- Image Section -->
        <div>
            <?php $imgSrc = !empty($design["image_url"])
                ? htmlspecialchars($design["image_url"])
                : "https://images.unsplash.com/photo-1541888031267-33827d7f7aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"; ?>
            <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars(
    $design["title"],
) ?>" class="product-image">
        </div>

        <!-- Details & Booking Form -->
        <div>
            <span class="design-category"><?= htmlspecialchars(
                $design["category_name"],
            ) ?> &bull; <?= htmlspecialchars($design["theme"]) ?></span>
            <h1 class="product-title"><?= htmlspecialchars(
                $design["title"],
            ) ?></h1>
            <p class="product-desc"><?= nl2br(
                htmlspecialchars($design["description"]),
            ) ?></p>
            
            <div class="product-book-card">
                <h3 class="product-book-h3">Calculate Estimate & Book</h3>
                <p class="product-price">
                    Rs <?= number_format(
                        $design["price_per_sqft"],
                        2,
                    ) ?> <span class="product-price-sqft">per sq.ft</span>
                </p>

                <form method="POST" action="checkout.php">
                    <input type="hidden" name="design_id" value="<?= $design[
                        "id"
                    ] ?>">
                    
                    <div class="form-group">
                        <label for="dimensions_sqft">Room Dimensions (Total Sq.Ft) *</label>
                        <input type="number" step="0.01" id="dimensions_sqft" name="dimensions_sqft" class="form-control" required min="10" placeholder="e.g. 150">
                    </div>
                    
                    <div class="form-group">
                        <label for="scheduled_datetime">Preferred Scheduled Date & Time *</label>
                        <input type="datetime-local" id="scheduled_datetime" name="scheduled_datetime" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="color_preference">Color Preference</label>
                        <select id="color_preference" name="color_preference" class="form-control">
                            <option value="Default">Default</option>
                            <option value="Pure White">Pure White</option>
                            <option value="Midnight Black">Midnight Black</option>
                            <option value="Metallic Gold">Metallic Gold</option>
                            <option value="Brushed Silver">Brushed Silver</option>
                            <option value="Custom">Custom (Specify to installers)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="material_finish">Material Finish</label>
                        <select id="material_finish" name="material_finish" class="form-control">
                            <option value="Default">Default</option>
                            <option value="Matte">Matte</option>
                            <option value="High Gloss">High Gloss</option>
                            <option value="Textured">Textured</option>
                            <option value="Wooden Grain">Wooden Grain</option>
                        </select>
                    </div>

                    <div class="product-total-container">
                        <span class="product-total-label">Estimated Total:</span>
                        <span class="product-total-value" id="estimated_total">Rs 0.00</span>
                    </div>

                    <?php if (isset($_SESSION["user_id"])): ?>
                        <button type="submit" class="btn-primary product-submit-btn">Proceed to Checkout</button>
                    <?php else: ?>
                        <div class="alert alert-error product-alert-login">You must be logged in to book a service.</div>
                        <a href="login.php" class="btn-secondary product-login-btn">Login to Book</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    const pricePerSqFt = <?= $design["price_per_sqft"] ?>;
    const dimensionsInput = document.getElementById('dimensions_sqft');
    const totalDisplay = document.getElementById('estimated_total');

    dimensionsInput.addEventListener('input', function() {
        const sqft = parseFloat(this.value) || 0;
        const total = sqft * pricePerSqFt;
        totalDisplay.textContent = 'Rs ' + total.toFixed(2);
    });

    // Set min datetime to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setMinutes(tomorrow.getMinutes() - tomorrow.getTimezoneOffset());
    document.getElementById('scheduled_datetime').min = tomorrow.toISOString().slice(0, 16);
</script>

<?php include "includes/footer.php"; ?>
