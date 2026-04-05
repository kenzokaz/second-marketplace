<?php
include("../includes/header.php");

// Admin only
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header("Location: ../index.php");
    exit();
}

// Stats
$total_products = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$total_users    = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$total_orders   = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$total_revenue  = $conn->query("SELECT SUM(total_price) AS s FROM orders")->fetch_assoc()['s'] ?? 0;

// Recent orders
$recent_orders = $conn->query("
    SELECT orders.id, orders.total_price, orders.status, orders.order_date, users.name AS buyer
    FROM orders
    JOIN users ON orders.user_id = users.id
    ORDER BY orders.order_date DESC
    LIMIT 5
");
?>

<div class="container admin-page">

    <div class="admin-header">
        <div>
            <p class="section-eyebrow">Admin Panel</p>
            <h1 class="section-title">Dashboard</h1>
        </div>
        <a href="manage_products.php" class="btn-filter-apply">Manage Products →</a>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-card-icon">📦</span>
            <span class="stat-card-number"><?= (int)$total_products ?></span>
            <span class="stat-card-label">Products Listed</span>
        </div>
        <div class="stat-card">
            <span class="stat-card-icon">👥</span>
            <span class="stat-card-number"><?= (int)$total_users ?></span>
            <span class="stat-card-label">Registered Users</span>
        </div>
        <div class="stat-card">
            <span class="stat-card-icon">🛒</span>
            <span class="stat-card-number"><?= (int)$total_orders ?></span>
            <span class="stat-card-label">Orders Placed</span>
        </div>
        <div class="stat-card stat-card--accent">
            <span class="stat-card-icon">💰</span>
            <span class="stat-card-number">$<?= number_format($total_revenue, 2) ?></span>
            <span class="stat-card-label">Total Revenue</span>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="admin-section">
        <h4 class="admin-section-title">Recent Orders</h4>

        <?php if ($recent_orders->num_rows > 0): ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Buyer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= (int)$order['id'] ?></td>
                        <td><?= htmlspecialchars($order['buyer']) ?></td>
                        <td>$<?= number_format($order['total_price'], 2) ?></td>
                        <td><span class="status-badge"><?= htmlspecialchars($order['status']) ?></span></td>
                        <td><?= htmlspecialchars($order['order_date']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="text-muted">No orders yet.</p>
        <?php endif; ?>
    </div>

    <!-- Quick Links -->
    <div class="admin-section">
        <h4 class="admin-section-title">Quick Actions</h4>
        <div class="quick-actions">
            <a href="manage_products.php" class="quick-action-card">
                <span class="quick-action-icon">📦</span>
                <span>Manage Products</span>
            </a>
            <a href="../products.php" class="quick-action-card">
                <span class="quick-action-icon">🛍️</span>
                <span>View Storefront</span>
            </a>
            <a href="../logout.php" class="quick-action-card quick-action-card--danger">
                <span class="quick-action-icon">🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </div>

</div>

<?php include("../includes/footer.php"); ?>