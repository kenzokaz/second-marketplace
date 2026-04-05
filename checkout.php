<?php
include("includes/header.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT products.id, products.name, products.price, products.image_url,
           products.stock, cart.quantity
    FROM cart
    JOIN products ON cart.product_id = products.id
    WHERE cart.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    echo "<div class='container mt-5'><div class='alert alert-warning'>Your cart is empty. <a href='products.php'>Browse products</a></div></div>";
    include("includes/footer.php");
    exit();
}

$items        = [];
$total        = 0;
$stock_errors = [];

while ($row = $result->fetch_assoc()) {
    if ($row['stock'] !== null && $row['quantity'] > $row['stock']) {
        $stock_errors[] = "<strong>" . htmlspecialchars($row['name']) . "</strong> - only {$row['stock']} left in stock.";
    }
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total          += $row['subtotal'];
    $items[]         = $row;
}

$order_placed = false;
$error        = '';

if (isset($_POST['confirm_order'])) {

    if (!empty($stock_errors)) {
        $error = "Some items exceed available stock. Please update your cart.";
    } else {

        $conn->begin_transaction();

        try {
            $ins = $conn->prepare("INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, 'Placed')");
            $ins->bind_param("id", $user_id, $total);
            $ins->execute();
            $order_id = $conn->insert_id;
            $ins->close();

            $item_stmt  = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
            $stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

            foreach ($items as $item) {
                $pid   = (int)   $item['id'];
                $qty   = (int)   $item['quantity'];
                $price = (float) $item['price'];

                $item_stmt->bind_param("iiid", $order_id, $pid, $qty, $price);
                $item_stmt->execute();

                if ($item['stock'] !== null) {
                    $stock_stmt->bind_param("ii", $qty, $pid);
                    $stock_stmt->execute();
                }
            }

            $item_stmt->close();
            $stock_stmt->close();

            $del = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $del->bind_param("i", $user_id);
            $del->execute();
            $del->close();

            $conn->commit();
            $order_placed = true;

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<div class="container checkout-page">

<?php if ($order_placed): ?>

    <div class="order-success">
        <div class="success-icon">✅</div>
        <h2>Order Placed!</h2>
        <p>Thanks for your purchase. Your order has been received.</p>
        <p class="order-total-confirm">Total paid: <strong>$<?= number_format($total, 2) ?></strong></p>
        <div class="success-actions">
            <a href="order_history.php" class="btn-filter-apply">View My Orders</a>
            <a href="products.php" class="btn-filter-clear">Continue Shopping</a>
        </div>
    </div>

<?php else: ?>

    <h1 class="section-title mb-1">Checkout</h1>
    <p class="text-muted mb-4">Review your order before confirming.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($stock_errors)): ?>
        <div class="alert alert-warning">
            <strong>Stock issues:</strong>
            <ul class="mb-0 mt-1 ps-3">
                <?php foreach ($stock_errors as $se): ?><li><?= $se ?></li><?php endforeach; ?>
            </ul>
            <a href="cart.php" class="btn-back-shop d-inline-block mt-2">← Update Cart</a>
        </div>
    <?php endif; ?>

    <div class="checkout-layout">

        <div class="checkout-items">
            <h5 class="checkout-section-label">Your Items</h5>
            <?php foreach ($items as $item):
                $img  = !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'https://placehold.co/64x64/e9ecef/6c757d?text=?';
                $name = htmlspecialchars($item['name']);
                $stock_warn = ($item['stock'] !== null && $item['stock'] <= 3 && $item['stock'] > 0);
            ?>
            <div class="checkout-item">
                <img src="<?= $img ?>" alt="<?= $name ?>" class="checkout-item-img">
                <div class="checkout-item-info">
                    <span class="checkout-item-name"><?= $name ?></span>
                    <span class="checkout-item-qty">Qty: <?= (int)$item['quantity'] ?></span>
                    <?php if ($stock_warn): ?>
                        <span class="stock-warning">⚠ Only <?= (int)$item['stock'] ?> left!</span>
                    <?php endif; ?>
                </div>
                <span class="checkout-item-price">$<?= number_format($item['subtotal'], 2) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary">
            <h4 class="summary-title">Order Total</h4>
            <div class="summary-row">
                <span>Subtotal</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping</span>
                <span class="text-muted">TBD with seller</span>
            </div>
            <div class="summary-divider"></div>
            <div class="summary-row summary-total">
                <span>Total</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>

            <?php if (empty($stock_errors)): ?>
                <form method="POST" id="checkoutForm">
                    <button type="submit" name="confirm_order" class="btn-checkout">✓ Confirm Order</button>
                </form>
            <?php else: ?>
                <button class="btn-checkout" disabled style="opacity:.5;cursor:not-allowed;">Fix Stock Issues First</button>
            <?php endif; ?>

            <a href="cart.php" class="btn-back-shop text-center d-block mt-3">← Edit Cart</a>
        </div>

    </div>

<?php endif; ?>
</div>

<?php include("includes/footer.php"); ?>