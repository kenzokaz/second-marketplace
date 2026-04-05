<?php
include("includes/db.php");
include("includes/header.php");

$search     = trim($_GET['search']   ?? '');
$category   = trim($_GET['category'] ?? '');
$condition  = trim($_GET['condition'] ?? '');
$sort       = $_GET['sort'] ?? 'newest';

$where   = [];
$params  = [];
$types   = '';

if ($search !== '') {
    $where[]  = "(name LIKE ? OR description LIKE ?)";
    $like     = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}
if ($category !== '') {
    $where[]  = "category = ?";
    $params[] = $category;
    $types   .= 's';
}
if ($condition !== '') {
    $where[]  = "condition_of_product = ?";
    $params[] = $condition;
    $types   .= 's';
}

$whereSQL = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$orderSQL = match($sort) {
    'price_asc'  => "ORDER BY price ASC",
    'price_desc' => "ORDER BY price DESC",
    'name'       => "ORDER BY name ASC",
    default      => "ORDER BY id DESC",
};

$sql  = "SELECT * FROM products $whereSQL $orderSQL";
$stmt = $conn->prepare($sql);

if (count($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$cats = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category");
?>

<div class="browse-page">

  <!-- Page Header -->
  <div class="browse-hero">
    <div class="container">
      <h1 class="browse-title">Browse Listings</h1>
      <p class="browse-sub">
          <?php echo $result->num_rows; ?> item<?php echo $result->num_rows !== 1 ? 's' : ''; ?> available
      </p>
    </div>
  </div>

  <div class="container browse-body">

    <!-- Searcr Bar -->
    <form method="GET" class="filter-bar">
      <div class="filter-search">
        <span class="filter-search-icon">🔍</span>
        <input
          type="text"
          name="search"
          class="filter-input"
          placeholder="Search products..."
          value="<?php echo htmlspecialchars($search); ?>"
        >
      </div>

      <select name="category" class="filter-select">
        <option value="">All Categories</option>
        <?php while ($cat = $cats->fetch_assoc()): ?>
          <option value="<?php echo htmlspecialchars($cat['category']); ?>"
            <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($cat['category']); ?>
          </option>
        <?php endwhile; ?>
      </select>

      <select name="condition" class="filter-select">
        <option value="">Any Condition</option>
        <?php
        $conditions = ['New', 'Like New', 'Good', 'Used'];
        foreach ($conditions as $c):
        ?>
          <option value="<?php echo $c; ?>" <?php echo $condition === $c ? 'selected' : ''; ?>>
            <?php echo $c; ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select name="sort" class="filter-select">
        <option value="newest"     <?php echo $sort === 'newest'     ? 'selected' : ''; ?>>Newest First</option>
        <option value="price_asc"  <?php echo $sort === 'price_asc'  ? 'selected' : ''; ?>>Price: Low → High</option>
        <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High → Low</option>
        <option value="name"       <?php echo $sort === 'name'       ? 'selected' : ''; ?>>Name A–Z</option>
      </select>

      <button type="submit" class="btn-filter-apply">Apply</button>

      <?php if ($search || $category || $condition || $sort !== 'newest'): ?>
        <a href="products.php" class="btn-filter-clear">Clear</a>
      <?php endif; ?>
    </form>

    <?php if ($search || $category || $condition): ?>
    <div class="active-filters">
      <span class="active-filter-label">Filtering by:</span>
      <?php if ($search):    ?><span class="filter-tag">Search: "<?php echo htmlspecialchars($search); ?>"</span><?php endif; ?>
      <?php if ($category):  ?><span class="filter-tag">Category: <?php echo htmlspecialchars($category); ?></span><?php endif; ?>
      <?php if ($condition): ?><span class="filter-tag">Condition: <?php echo htmlspecialchars($condition); ?></span><?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
    <div class="row g-4">
      <?php while ($row = $result->fetch_assoc()):
        $image     = !empty($row['image_url']) ? htmlspecialchars($row['image_url']) : 'https://placehold.co/400x260/e9ecef/6c757d?text=No+Image';
        $name      = htmlspecialchars($row['name']);
        $desc      = htmlspecialchars($row['description']);
        $price     = number_format($row['price'], 2);
        $category_label  = htmlspecialchars($row['category'] ?? 'General');
        $cond      = htmlspecialchars($row['condition_of_product'] ?? 'Used');
        $id        = (int)$row['id'];

        $condClass = match(strtolower($cond)) {
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
            <span class="product-condition <?= $condClass ?>"><?= $cond ?></span>
          </div>
          <div class="product-body">
            <span class="product-category"><?= $category_label ?></span>
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

    <?php else: ?>
    <div class="empty-state">
      <div class="empty-icon">🔍</div>
      <h4>No products found</h4>
      <p>Try adjusting your search or filters.</p>
      <a href="products.php" class="btn-filter-apply">Clear All Filters</a>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php include("includes/footer.php"); ?>