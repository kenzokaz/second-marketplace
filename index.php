<?php
include("includes/db.php");
include("includes/header.php");
?>

<section class="hero-section">
    <div class="hero-overlay"></div>
    <div class="container hero-content text-center">
        <span class="hero-eyebrow">Your Local Marketplace</span>
        <h1 class="hero-title">Buy & Sell<br><span class="hero-accent">Second-Hand</span> Items</h1>
        <p class="hero-sub">Electronics, clothing, collectibles, vinyl, books & more - all in one place.</p>

        <form action="products.php" method="GET" class="hero-search-form">
            <div class="hero-search-wrap">
                <span class="hero-search-icon">🔍</span>
                <input
                    type="text"
                    name="search"
                    class="hero-search-input"
                    placeholder="Search for anything - iPhone, vinyl, jacket..."
                    autocomplete="off"
                >
                <button type="submit" class="hero-search-btn">Search</button>
            </div>
            <div class="hero-search-hints">
                <span>Popular:</span>
                <a href="products.php?category=Electronics">Electronics</a>
                <a href="products.php?category=Clothing">Clothing</a>
                <a href="products.php?category=Books">Books</a>
                <a href="products.php?category=Vinyl">Vinyl</a>
            </div>
        </form>

        <div class="hero-actions">
            <a href="products.php" class="btn btn-outline-light btn-hero">Browse All</a>
            <a href="register.php" class="btn btn-outline-light btn-hero">Start Selling</a>
        </div>

        <div class="hero-stats">
            <div class="stat-item">
                <span class="stat-number">10+</span>
                <span class="stat-label">Listings</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number">2+</span>
                <span class="stat-label">Sellers</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number">5+</span>
                <span class="stat-label">Categories</span>
            </div>
        </div>
    </div>
</section>

<!-- Catergories -->
<section class="category-strip">
    <div class="container">
        <div class="category-scroll">
            <a href="products.php?category=Electronics"    class="category-pill">Electronics</a>
            <a href="products.php?category=Clothing"       class="category-pill">Clothing</a>
            <a href="products.php?category=Books"          class="category-pill">Books</a>
            <a href="products.php?category=Vinyl"          class="category-pill">Vinyl</a>
            <a href="products.php?category=Collectibles"   class="category-pill">Collectibles</a>
            <a href="products.php?category=Furniture"      class="category-pill">Furniture</a>
            <a href="products.php"                         class="category-pill category-pill--all">View All →</a>
        </div>
    </div>
</section>

<!--  productson main pahe -->
<section class="featured-section">
    <div class="container">
        <div class="section-header">
            <div>
                <p class="section-eyebrow">For you</p>
                <h2 class="section-title">Featured Listings</h2>
            </div>
            <a href="products.php" class="btn btn-outline-dark btn-sm view-all-btn">View All Products →</a>
        </div>

        <div class="row g-4">
            <?php
            $sql    = "SELECT * FROM products WHERE stock > 0 ORDER BY id DESC LIMIT 4";
            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()):
                $image    = !empty($row['image_url']) ? htmlspecialchars($row['image_url']) : 'https://placehold.co/400x260/e9ecef/6c757d?text=No+Image';
                $name     = htmlspecialchars($row['name']);
                $desc     = htmlspecialchars($row['description']);
                $price    = number_format($row['price'], 2);
                $category = htmlspecialchars($row['category'] ?? 'General');
                $cond     = htmlspecialchars($row['condition_of_product'] ?? 'Used');
                $id       = (int) $row['id'];

                $conditionClass = match(strtolower($cond)) {
                    'new'      => 'badge-new',
                    'like new' => 'badge-likenew',
                    'good'     => 'badge-good',
                    default    => 'badge-used'
                };
            ?>
            <div class="col-sm-6 col-lg-3">
                <div class="product-card">
                    <div class="product-img-wrap">
                        <img src="<?= $image ?>" alt="<?= $name ?>" class="product-img">
                        <span class="product-condition <?= $conditionClass ?>"><?= $cond ?></span>
                    </div>
                    <div class="product-body">
                        <span class="product-category"><?= $category ?></span>
                        <h5 class="product-name"><?= $name ?></h5>
                        <p class="product-desc"><?= $desc ?></p>
                        <div class="product-footer">
                            <span class="product-price">$<?= $price ?></span>
                            <a href="product.php?id=<?= $id ?>" class="btn-view">View →</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Footer / lower section content place -->
<section class="why-section">
    <div class="container">
        <h2 class="section-title text-center mb-5">Why Use Our Marketplace?</h2>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="why-card">
                    <div class="why-icon">🔒</div>
                    <h5>Safe &amp; Secure</h5>
                    <p>Every account is verified. Your data stays protected with encrypted passwords and secure sessions.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="why-card">
                    <div class="why-icon">💸</div>
                    <h5>No Listing Fees</h5>
                    <p>Post your items for free. We believe everyone should be able to sell without barriers.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="why-card">
                    <div class="why-icon">♻️</div>
                    <h5>Sustainable Shopping</h5>
                    <p>Give items a second life. Every second-hand purchase reduces waste and helps the planet.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include("includes/footer.php"); ?>