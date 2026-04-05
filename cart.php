<?php
include("includes/header.php");

if (!isset($_SESSION['user_id'])) {
    echo "<div class='container mt-5'><div class='alert alert-warning'>Please <a href='login.php'>login</a> to view your cart.</div></div>";
    include("includes/footer.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

if (isset($_POST['remove_item']) && is_numeric($_POST['remove_item'])) {
    $product_id = (int) $_POST['remove_item'];
    $del = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $del->bind_param("ii", $user_id, $product_id);
    $del->execute();
    $del->close();
}

if (isset($_POST['clear_cart'])) {
    $clr = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clr->bind_param("i", $user_id);
    $clr->execute();
    $clr->close();
}

$stmt = $conn->prepare("
    SELECT products.id, products.name, products.price, products.image_url, cart.quantity
    FROM cart
    JOIN products ON cart.product_id = products.id
    WHERE cart.user_id = ?
    ORDER BY products.name
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<div class="container cart-page">

    <div class="cart-header">
        <h1 class="section-title">Your Cart</h1>
        <a href="products.php" class="btn-back-shop">← Keep Shopping</a>
    </div>

    <?php if ($result->num_rows > 0):
        $total = 0;
    ?>

    <div class="cart-layout">

        <!-- Cart Items -->
        <div class="cart-items">
            <?php while ($row = $result->fetch_assoc()):
                $subtotal  = $row['price'] * $row['quantity'];
                $total    += $subtotal;
                $img       = !empty($row['image_url']) ? htmlspecialchars($row['image_url']) : 'https://placehold.co/80x80/e9ecef/6c757d?text=?';
                $name      = htmlspecialchars($row['name']);
                $pid       = (int) $row['id'];
            ?>
            <div class="cart-item">
                <img src="<?= $img ?>" alt="<?= $name ?>" class="cart-item-img">
                <div class="cart-item-info">
                    <h5 class="cart-item-name">
                        <a href="product.php?id=<?= $pid ?>"><?= $name ?></a>
                    </h5>
                    <p class="cart-item-price">$<?= number_format($row['price'], 2) ?> × <?= (int)$row['quantity'] ?></p>
                </div>
                <div class="cart-item-right">
                    <span class="cart-item-subtotal">$<?= number_format($subtotal, 2) ?></span>
                    <form method="POST">
                        <input type="hidden" name="remove_item" value="<?= $pid ?>">
                        <button type="submit" class="btn-remove-item" title="Remove">✕</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>

            <!-- Clear cart -->
            <form method="POST" class="clear-cart-form">
                <button type="submit" name="clear_cart"
                        onclick="return confirm('Clear your entire cart?')"
                        class="btn-clear-cart">
                    Clear Cart
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="cart-summary">
            <h4 class="summary-title">Order Summary</h4>
            <div class="summary-row">
                <span>Subtotal</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping</span>
                <span class="text-muted">Arranged with seller</span>
            </div>
            <div class="summary-divider"></div>
            <div class="summary-row summary-total">
                <span>Total</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>
            <a href="checkout.php" class="btn-checkout">Proceed to Checkout →</a>
        </div>

    </div>

    <?php else: ?>

    <div class="empty-state">
        <div class="empty-icon">🛒</div>
        <h4>Your cart is empty</h4>
        <p>Browse listings and add something you like.</p>
        <a href="products.php" class="btn-filter-apply">Browse Products</a>
    </div>

    <?php endif; ?>
</div>

<?php include("includes/footer.php"); ?>
