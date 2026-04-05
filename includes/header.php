<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("includes/db.php");

// Cart counter
$cart_count = 0;

if (isset($_SESSION['user_id'])) {
    $uid  = (int) $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $row        = $stmt->get_result()->fetch_assoc();
    $cart_count = $row['total'] ?? 0;
    $stmt->close();
}

// Detect if we're inside admin/ folder so links resolve correctly
$in_admin = str_contains($_SERVER['PHP_SELF'], '/admin/');
$root     = $in_admin ? '../' : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Second-Hand Marketplace</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles-->
    <link rel="stylesheet" href="<?= $root ?>css/style.css">
</head>

<body class="d-flex flex-column min-vh-100">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">

    <a class="navbar-brand" href="<?= $root ?>index.php">Marketplace</a>

    <!-- Mobile toggle -->
    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false"
            aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">

      <!-- Left nav links -->
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
            <a class="nav-link" href="<?= $root ?>products.php">Browse</a>
        </li>
        <?php if (isset($_SESSION['user_id'])): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= $root ?>sell.php">Sell</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= $root ?>my_listings.php">My Listings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= $root ?>order_history.php">My Orders</a>
        </li>
        <?php if (!empty($_SESSION['is_admin'])): ?>
        <li class="nav-item">
            <a class="nav-link nav-link-admin" href="<?= $root ?>admin/dashboard.php">⚙ Admin</a>
        </li>
        <?php endif; ?>
        <?php endif; ?>
      </ul>

      <div class="d-flex align-items-center gap-2">

        <!-- Cart -->
        <a class="btn btn-outline-light btn-sm position-relative" href="<?= $root ?>cart.php">
            🛒 Cart
            <?php if ($cart_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo (int)$cart_count; ?>
                </span>
            <?php endif; ?>
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="text-white opacity-75 small">
                Hi, <?php echo htmlspecialchars($_SESSION['name']); ?>
            </span>
            <a class="btn btn-danger btn-sm" href="<?= $root ?>logout.php">Logout</a>

        <?php else: ?>
            <a class="btn btn-outline-light btn-sm" href="<?= $root ?>login.php">Login</a>
            <a class="btn btn-warning btn-sm fw-semibold" href="<?= $root ?>register.php">Register</a>
        <?php endif; ?>

      </div>
    </div>

  </div>
</nav>

<main class="flex-grow-1">