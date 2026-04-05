<?php
include("includes/header.php");

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Invalid product.</div></div>";
    include("includes/footer.php");
    exit();
}

$id = (int) $_GET['id'];

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='container mt-5'><div class='alert alert-warning'>Product not found. <a href='products.php'>Back to listings</a></div></div>";
    include("includes/footer.php");
    exit();
}

$product = $result->fetch_assoc();
$stmt->close();

$stock    = (int) $product['stock'];
$is_sold  = $stock === 0;
$low_stock = !$is_sold && $stock <= 3;

// ADD TO CART
$cart_message      = '';
$cart_message_type = '';

if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        $cart_message      = 'Please <a href="login.php">login</a> to add items to your cart.';
        $cart_message_type = 'warning';
    } elseif ($is_sold) {
        $cart_message      = 'Sorry, this item is sold out.';
        $cart_message_type = 'danger';
    } else {
        $user_id    = (int) $_SESSION['user_id'];
        $product_id = $id;

        $check = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
        $check->bind_param("ii", $user_id, $product_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $upd = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
            $upd->bind_param("ii", $user_id, $product_id);
            $upd->execute();
            $upd->close();
        } else {
            $ins = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
            $ins->bind_param("ii", $user_id, $product_id);
            $ins->execute();
            $ins->close();
        }
        $check->close();

        $cart_message      = '✓ Added to cart! <a href="cart.php">View Cart</a>';
        $cart_message_type = 'success';
    }
}

$name      = htmlspecialchars($product['name']);
$desc      = htmlspecialchars($product['description']);
$price     = number_format($product['price'], 2);
$category  = htmlspecialchars($product['category']             ?? 'General');
$condition = htmlspecialchars($product['condition_of_product'] ?? 'Used');
$image     = !empty($product['image_url'])
    ? htmlspecialchars($product['image_url'])
    : 'https://placehold.co/600x420/e9ecef/6c757d?text=No+Image';

$condClass = match(strtolower($condition)) {
    'new'      => 'badge-new',
    'like new' => 'badge-likenew',
    'good'     => 'badge-good',
    default    => 'badge-used'
};
?>

<div class="container product-detail-page">

    <a href="products.php" class="back-link">← Back to Listings</a>

    <div class="product-detail-grid">

        <!-- Image -->
        <div class="product-detail-img-wrap">
            <img src="<?= $image ?>" alt="<?= $name ?>" class="product-detail-img <?= $is_sold ? 'img-sold-out' : '' ?>">
            <span class="product-condition <?= $condClass ?> product-condition-lg"><?= $condition ?></span>
            <?php if ($is_sold): ?>
                <div class="sold-out-overlay">SOLD OUT</div>
            <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="product-detail-info">
            <span class="product-category"><?= $category ?></span>
            <h1 class="product-detail-name"><?= $name ?></h1>
            <p class="product-detail-price">$<?= $price ?></p>
            <p class="product-detail-desc"><?= $desc ?></p>

            <!-- Stock indicator -->
            <div class="stock-indicator">
                <?php if ($is_sold): ?>
                    <span class="stock-badge stock-sold">❌ Sold Out</span>
                <?php elseif ($low_stock): ?>
                    <span class="stock-badge stock-low">⚠ Only <?= $stock ?> left!</span>
                <?php else: ?>
                    <span class="stock-badge stock-ok">✓ <?= $stock ?> in stock</span>
                <?php endif; ?>
            </div>

            <?php if ($cart_message): ?>
                <div class="alert alert-<?= $cart_message_type ?> py-2 mt-3">
                    <?= $cart_message ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <button type="submit" name="add_to_cart"
                        class="btn-add-cart <?= $is_sold ? 'btn-add-cart--disabled' : '' ?>"
                        <?= $is_sold ? 'disabled' : '' ?>>
                    <?= $is_sold ? '❌ Sold Out' : '🛒 Add to Cart' ?>
                </button>
            </form>

            <div class="product-meta">
                <div class="meta-item">
                    <span class="meta-label">Category</span>
                    <span class="meta-value"><?= $category ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Condition</span>
                    <span class="meta-value"><?= $condition ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Stock</span>
                    <span class="meta-value"><?= $is_sold ? 'Sold Out' : $stock . ' available' ?></span>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include("includes/footer.php"); ?>