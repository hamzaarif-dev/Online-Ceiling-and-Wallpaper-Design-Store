<?php
require_once "includes/db.php";
include "includes/header.php";

// Fetch some featured designs for the homepage
$result = $conn->query("SELECT d.*, c.name as category_name FROM designs d JOIN
categories c ON d.category_id = c.id ORDER BY d.created_at DESC LIMIT 6");
$featured_designs = $result->fetch_all(MYSQLI_ASSOC);
?>

<style>
  /* Hero Join Button */
.index-hero-join {
    margin-left: 1rem;
    color: #fff !important;
    border-color: #fff;
}

/* Trending Section */
.index-trending-section {
    padding: 4rem 5%;
    text-align: center;
}

.index-trending-h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: var(--primary-color);
}

.index-trending-p {
    color: var(--text-light);
    max-width: 600px;
    margin: 0 auto 3rem;
}

/* View Details Button */
.index-view-details {
    width: 100%;
    text-align: center;
}

/* Catalog Link Container */
.index-catalog-link-container {
    margin-top: 2rem;
}

/* Why Section */
.index-why-section {
    background-color: var(--white);
    padding: 5rem 5%;
}

/* Why Header */
.index-why-header {
    text-align: center;
    margin-bottom: 4rem;
}

.index-why-h2 {
    font-size: 2.5rem;
    color: var(--primary-color);
}

/* Why Grid */
.index-why-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    text-align: center;
}

/* Why Card Title */
.index-why-h3 {
    color: var(--secondary-color);
    margin-bottom: 1rem;
    font-size: 1.5rem;
}
</style>

<section class="hero">
  <div class="hero-content">
    <h1>Transform Your Space with <span>Premium Decor</span></h1>
    <p>
      Discover our exclusive catalog of luxury wallpapers, modern false
      ceilings, and premium PVC panels. Book installation services seamlessly
      with our expert professionals.
    </p>
    <a href="catalog.php" class="btn-primary">Explore Catalog</a>
    <a href="register.php" class="btn-secondary index-hero-join">Join Now</a>
  </div>
</section>

<section class="index-trending-section">
  <h2 class="index-trending-h2">Trending Designs</h2>
  <p class="index-trending-p">
    Explore our most requested styles that bring elegance to any room.
  </p>

  <div class="catalog-grid">
    <?php foreach ($featured_designs as $design): ?>
    <div class="design-card">
      <!-- Using a placeholder pattern if real images are not available -->
      <?php $imgSrc = !empty($design["image_url"])
          ? htmlspecialchars($design["image_url"])
          : "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"; ?>
      <img
        src="<?= $imgSrc ?>"
        alt="<?= htmlspecialchars($design["title"]) ?>"
        class="design-image"
      />
      <div class="design-info">
        <span class="design-category"
          ><?= htmlspecialchars($design["category_name"]) ?></span
        >
        <h3 class="design-title"><?= htmlspecialchars($design["title"]) ?></h3>
        <p class="design-price">
          Rs <?= htmlspecialchars($design["price_per_sqft"]) ?> / sq.ft
        </p>
        <a
          href="product.php?id=<?= $design["id"] ?>"
          class="btn-primary index-view-details"
          >View Details</a
        >
      </div>
    </div>
    <?php endforeach; ?> <?php if (empty($featured_designs)): ?>
    <p>No designs available yet. Admin will add some soon.</p>
    <?php endif; ?>
  </div>

  <div class="index-catalog-link-container">
    <a href="catalog.php" class="btn-secondary">View Full Catalog</a>
  </div>
</section>

<section class="index-why-section">
  <div class="index-why-header">
    <h2 class="index-why-h2">Why Choose DecorSlot?</h2>
  </div>
  <div class="index-why-grid">
    <div>
      <h3 class="index-why-h3">Premium Quality</h3>
      <p>
        We source only the highest grade materials for lasting beauty and
        durability.
      </p>
    </div>
    <div>
      <h3 class="index-why-h3">Expert Installation</h3>
      <p>
        Our certified professionals ensure a flawless finish for every project.
      </p>
    </div>
    <div>
      <h3 class="index-why-h3">Seamless Booking</h3>
      <p>
        Book your service online, upload dimensions, and track your order
        effortlessly.
      </p>
    </div>
  </div>
</section>

<?php include "includes/footer.php"; ?>
