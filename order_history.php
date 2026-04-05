<?php
include("includes/header.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Fetch all orders
$stmt = $conn->prepare("
    SELECT id, total_price, order_date, status
    FROM orders
    WHERE user_id = ?
    ORDER BY order_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();

// Fetch items for a given order
function get_order_items($conn, $order_id) {
    $stmt = $conn->prepare("
        SELECT products.name, products.image_url, order_items.quantity, order_items.price_at_purchase
        FROM order_items
        JOIN products ON order_items.product_id = products.id
        WHERE order_items.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}
?>

<div class="container order-history-page">

    <div class="admin-header">
        <div>
            <p class="section-eyebrow">Your Account</p>
            <h1 class="section-title">Order History</h1>
        </div>
        <a href="products.php" class="btn-back-shop">← Keep Shopping</a>
    </div>

    <?php if ($orders->num_rows > 0): ?>

    <div class="orders-list">
        <?php while ($order = $orders->fetch_assoc()):
            $status    = htmlspecialchars($order['status'] ?? 'Placed');
            $date      = date('F j, Y - g:i A', strtotime($order['order_date']));
            $total     = number_format($order['total_price'], 2);
            $order_id  = (int) $order['id'];
            $items     = get_order_items($conn, $order_id);

            $status_class = match(strtolower($status)) {
                'placed'    => 'status-placed',
                'shipped'   => 'status-shipped',
                'delivered' => 'status-delivered',
                'cancelled' => 'status-cancelled',
                default     => 'status-placed'
            };
        ?>
        <div class="order-card-full">

            <div class="order-card-header">
                <div class="order-card-left">
                    <span class="order-id">Order #<?= $order_id ?></span>
                    <span class="order-date"><?= $date ?></span>
                </div>
                <div class="order-card-center">
                    <span class="order-status-badge <?= $status_class ?>"><?= $status ?></span>
                </div>
                <div class="order-card-right">
                    <span class="order-total">$<?= $total ?></span>
                </div>
            </div>

            <?php if ($items->num_rows > 0): ?>
            <div class="order-items-list">
                <?php while ($item = $items->fetch_assoc()):
                    $img  = !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'https://placehold.co/48x48/e9ecef/6c757d?text=?';
                    $name = htmlspecialchars($item['name']);
                ?>
                <div class="order-item-row">
                    <img src="<?= $img ?>" alt="<?= $name ?>" class="order-item-img">
                    <span class="order-item-name"><?= $name ?></span>
                    <span class="order-item-qty">× <?= (int)$item['quantity'] ?></span>
                    <span class="order-item-price">$<?= number_format($item['price_at_purchase'] * $item['quantity'], 2) ?></span>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>

        </div>
        <?php endwhile; ?>
    </div>

    <?php else: ?>

    <div class="empty-state">
        <div class="empty-icon">📦</div>
        <h4>No orders yet</h4>
        <p>When you place an order it will show up here.</p>
        <a href="products.php" class="btn-filter-apply">Browse Products</a>
    </div>

    <?php endif; ?>
</div>

<?php include("includes/footer.php"); ?>