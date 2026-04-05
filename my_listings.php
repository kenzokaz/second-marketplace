<?php
include("includes/header.php");

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$success = '';
$errors  = [];

// ── DELETE listing ───────────────────────────────────────────
if (isset($_POST['delete_listing']) && is_numeric($_POST['delete_listing'])) {
    $del_id = (int) $_POST['delete_listing'];

    // Make sure it belongs to this user
    $own = $conn->prepare("SELECT image_url FROM products WHERE id = ? AND seller_id = ?");
    $own->bind_param("ii", $del_id, $user_id);
    $own->execute();
    $own_row = $own->get_result()->fetch_assoc();
    $own->close();

    if ($own_row) {
        // Delete image file if it exists
        if (!empty($own_row['image_url']) && file_exists($own_row['image_url'])) {
            unlink($own_row['image_url']);
        }
        $del = $conn->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
        $del->bind_param("ii", $del_id, $user_id);
        $del->execute();
        $del->close();
        $success = "Listing deleted.";
    }
}

// ── MARK AS SOLD (set stock to 0) ───────────────────────────
if (isset($_POST['mark_sold']) && is_numeric($_POST['mark_sold'])) {
    $sold_id = (int) $_POST['mark_sold'];
    $upd = $conn->prepare("UPDATE products SET stock = 0 WHERE id = ? AND seller_id = ?");
    $upd->bind_param("ii", $sold_id, $user_id);
    $upd->execute();
    $upd->close();
    $success = "Item marked as sold.";
}

// ── EDIT listing ─────────────────────────────────────────────
if (isset($_POST['edit_listing']) && is_numeric($_POST['edit_id'])) {
    $edit_id     = (int) $_POST['edit_id'];
    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = trim($_POST['price']       ?? '');
    $category    = trim($_POST['category']    ?? '');
    $condition   = trim($_POST['condition']   ?? '');
    $stock       = (int) ($_POST['stock']     ?? 0);

    if ($name === '')                        $errors[] = "Name is required.";
    if ($description === '')                 $errors[] = "Description is required.";
    if (!is_numeric($price) || $price <= 0) $errors[] = "Enter a valid price.";

    // Handle new image upload
    $cur = $conn->prepare("SELECT image_url FROM products WHERE id = ? AND seller_id = ?");
    $cur->bind_param("ii", $edit_id, $user_id);
    $cur->execute();
    $cur_row   = $cur->get_result()->fetch_assoc();
    $cur->close();

    if (!$cur_row) {
        $errors[] = "Product not found.";
    }

    $image_url = $cur_row['image_url'] ?? '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed  = ['image/jpeg', 'image/png', 'image/webp'];
        $ext_map  = ['jpg' => true, 'jpeg' => true, 'png' => true, 'webp' => true];
        $ftype    = mime_content_type($_FILES['image']['tmp_name']);
        $fsize    = $_FILES['image']['size'];
        $fext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ftype, $allowed) || !isset($ext_map[$fext])) {
            $errors[] = "Only JPG, PNG, WEBP images allowed.";
        } elseif ($fsize > 5 * 1024 * 1024) {
            $errors[] = "Image must be under 5MB.";
        } else {
            $upload_dir = "images/uploads/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $filename = uniqid('img_', true) . '.' . $fext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                if (!empty($image_url) && file_exists($image_url)) unlink($image_url);
                $image_url = $upload_dir . $filename;
            } else {
                $errors[] = "Image upload failed.";
            }
        }
    }

    if (empty($errors)) {
        $upd = $conn->prepare("
            UPDATE products
            SET name=?, description=?, price=?, category=?, condition_of_product=?, image_url=?, stock=?
            WHERE id=? AND seller_id=?
        ");
        $upd->bind_param("ssdsssiii", $name, $description, $price, $category, $condition, $image_url, $stock, $edit_id, $user_id);
        if ($upd->execute()) $success = "Listing updated.";
        else $errors[] = "Update failed. Please try again.";
        $upd->close();
    }
}

// ── Fetch this user's listings ───────────────────────────────
$listings = $conn->prepare("SELECT * FROM products WHERE seller_id = ? ORDER BY id DESC");
$listings->bind_param("i", $user_id);
$listings->execute();
$my_products = $listings->get_result();
$listings->close();

// Edit mode
$edit_product = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id_get = (int) $_GET['edit'];
    $e = $conn->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
    $e->bind_param("ii", $edit_id_get, $user_id);
    $e->execute();
    $edit_product = $e->get_result()->fetch_assoc();
    $e->close();
}

$categories = ['Electronics', 'Clothing', 'Books', 'Vinyl', 'Collectibles', 'Furniture', 'Other'];
$conditions = ['New', 'Like New', 'Good', 'Used'];
?>

<div class="container seller-page">

    <div class="admin-header">
        <div>
            <p class="section-eyebrow">Your Account</p>
            <h1 class="section-title">My Listings</h1>
        </div>
        <a href="sell.php" class="btn-filter-apply">+ New Listing</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success mb-4">✓ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-danger mb-4">
            <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <!-- EDIT FORM — only shown when ?edit=ID -->
    <?php if ($edit_product): ?>
    <div class="admin-section mb-4">
        <h4 class="admin-section-title">Editing: <?= htmlspecialchars($edit_product['name']) ?></h4>
        <form method="POST" enctype="multipart/form-data" class="listing-form">
            <input type="hidden" name="edit_id" value="<?= (int)$edit_product['id'] ?>">

            <div class="form-group">
                <label class="form-label">Product Name *</label>
                <input type="text" name="name" class="form-input" required
                       value="<?= htmlspecialchars($edit_product['name']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Description *</label>
                <textarea name="description" class="form-input form-textarea" required><?= htmlspecialchars($edit_product['description']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Price (CAD) *</label>
                    <div class="input-prefix-wrap">
                        <span class="input-prefix">$</span>
                        <input type="number" name="price" step="0.01" min="0.01"
                               class="form-input input-with-prefix" required
                               value="<?= htmlspecialchars($edit_product['price']) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-input">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= ($edit_product['category'] === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Condition</label>
                    <select name="condition" class="form-input">
                        <?php foreach ($conditions as $cond): ?>
                            <option value="<?= $cond ?>" <?= ($edit_product['condition_of_product'] === $cond) ? 'selected' : '' ?>><?= $cond ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-input" min="0"
                           value="<?= (int)$edit_product['stock'] ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Image <?= !empty($edit_product['image_url']) ? '(leave empty to keep current)' : '' ?></label>
                <?php if (!empty($edit_product['image_url'])): ?>
                    <div class="current-image-wrap">
                        <img src="<?= htmlspecialchars($edit_product['image_url']) ?>" class="current-image-thumb" alt="Current">
                        <span class="current-image-label">Current image</span>
                    </div>
                <?php endif; ?>
                <div class="upload-zone">
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="upload-input">
                    <div class="upload-placeholder">
                        <span class="upload-icon">📷</span>
                        <span class="upload-text">Click to upload new image</span>
                        <span class="upload-hint">JPG, PNG, WEBP — max 5MB</span>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="edit_listing" class="btn-submit">Save Changes →</button>
                <a href="my_listings.php" class="btn-filter-clear">Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- LISTINGS TABLE -->
    <?php if ($my_products->num_rows > 0): ?>
    <div class="admin-section">
        <h4 class="admin-section-title">Your Products (<?= $my_products->num_rows ?>)</h4>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Reset result pointer
                $listings2 = $conn->prepare("SELECT * FROM products WHERE seller_id = ? ORDER BY id DESC");
                $listings2->bind_param("i", $user_id);
                $listings2->execute();
                $my_products2 = $listings2->get_result();
                $listings2->close();

                while ($p = $my_products2->fetch_assoc()):
                    $thumb = !empty($p['image_url'])
                        ? htmlspecialchars($p['image_url'])
                        : 'https://placehold.co/48x48/e9ecef/6c757d?text=?';
                    $stock     = (int) $p['stock'];
                    $is_sold   = $stock === 0;
                    $low_stock = !$is_sold && $stock <= 3;
                ?>
                    <tr>
                        <td><img src="<?= $thumb ?>" alt="" class="admin-thumb"></td>
                        <td>
                            <?= htmlspecialchars($p['name']) ?>
                        </td>
                        <td>$<?= number_format($p['price'], 2) ?></td>
                        <td>
                            <?php if ($is_sold): ?>
                                <span class="stock-badge stock-sold">Sold Out</span>
                            <?php elseif ($low_stock): ?>
                                <span class="stock-badge stock-low"><?= $stock ?> left</span>
                            <?php else: ?>
                                <span class="stock-badge stock-ok"><?= $stock ?> in stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge"><?= $is_sold ? 'Sold' : 'Active' ?></span>
                        </td>
                        <td class="actions-cell">
                            <a href="my_listings.php?edit=<?= (int)$p['id'] ?>" class="btn-table-edit">Edit</a>

                            <?php if (!$is_sold): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="mark_sold" value="<?= (int)$p['id'] ?>">
                                <button type="submit" class="btn-table-sold"
                                        onclick="return confirm('Mark this item as sold out?')">Sold</button>
                            </form>
                            <?php endif; ?>

                            <form method="POST" style="display:inline"
                                  onsubmit="return confirm('Delete this listing permanently?')">
                                <input type="hidden" name="delete_listing" value="<?= (int)$p['id'] ?>">
                                <button type="submit" class="btn-table-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">🏷️</div>
        <h4>No listings yet</h4>
        <p>Start selling by posting your first item.</p>
        <a href="sell.php" class="btn-filter-apply">Post a Listing</a>
    </div>
    <?php endif; ?>

</div>

<?php include("includes/footer.php"); ?>