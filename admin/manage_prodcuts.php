<?php
include("../includes/header.php");

// Admin only
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header("Location: ../index.php");
    exit();
}

$categories = ['Electronics', 'Clothing', 'Books', 'Vinyl', 'Collectibles', 'Furniture', 'Other'];
$conditions = ['New', 'Like New', 'Good', 'Used'];
$success    = '';
$errors     = [];

function handle_upload($file, $conn) {
    if ($file['error'] !== UPLOAD_ERR_OK) return ['url' => '', 'error' => ''];

    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size      = 5 * 1024 * 1024;
    $file_type     = mime_content_type($file['tmp_name']);
    $file_size     = $file['size'];
    $file_ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $ext_map       = ['jpg' => true, 'jpeg' => true, 'png' => true, 'webp' => true];

    if (!in_array($file_type, $allowed_types) || !isset($ext_map[$file_ext]))
        return ['url' => '', 'error' => 'Only JPG, PNG, and WEBP images are allowed.'];
    if ($file_size > $max_size)
        return ['url' => '', 'error' => 'Image must be under 5MB.'];

    $upload_dir = "../images/uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $filename = uniqid('img_', true) . '.' . $file_ext;
    $dest     = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest))
        return ['url' => '', 'error' => 'Failed to save image. Check folder permissions.'];

    return ['url' => 'images/uploads/' . $filename, 'error' => ''];
}

if (isset($_POST['delete_product']) && is_numeric($_POST['delete_product'])) {
    $del_id = (int) $_POST['delete_product'];

    $img_stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
    $img_stmt->bind_param("i", $del_id);
    $img_stmt->execute();
    $img_row = $img_stmt->get_result()->fetch_assoc();
    $img_stmt->close();

    if (!empty($img_row['image_url']) && file_exists("../" . $img_row['image_url'])) {
        unlink("../" . $img_row['image_url']);
    }

    $del = $conn->prepare("DELETE FROM products WHERE id = ?");
    $del->bind_param("i", $del_id);
    $del->execute();
    $del->close();
    $success = "Product deleted.";
}

if (isset($_POST['add_product'])) {
    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = trim($_POST['price']       ?? '');
    $category    = trim($_POST['category']    ?? '');
    $condition   = trim($_POST['condition']   ?? '');

    if ($name === '')                         $errors[] = "Name is required.";
    if ($description === '')                  $errors[] = "Description is required.";
    if (!is_numeric($price) || $price <= 0)  $errors[] = "Enter a valid price.";
    if ($category === '')                     $errors[] = "Select a category.";
    if ($condition === '')                    $errors[] = "Select a condition.";

    $image_url = '';
    if (isset($_FILES['image'])) {
        $upload = handle_upload($_FILES['image'], $conn);
        if ($upload['error']) $errors[] = $upload['error'];
        else $image_url = $upload['url'];
    }

    if (empty($errors)) {
        $admin_id = (int) $_SESSION['user_id'];
        $stmt = $conn->prepare("
            INSERT INTO products (name, description, price, category, condition_of_product, image_url, seller_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssdsssi", $name, $description, $price, $category, $condition, $image_url, $admin_id);
        if ($stmt->execute()) $success = "Product added successfully.";
        else $errors[] = "Database error: " . $conn->error;
        $stmt->close();
    }
}

if (isset($_POST['edit_product']) && is_numeric($_POST['edit_id'])) {
    $edit_id     = (int) $_POST['edit_id'];
    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = trim($_POST['price']       ?? '');
    $category    = trim($_POST['category']    ?? '');
    $condition   = trim($_POST['condition']   ?? '');

    if ($name === '')                         $errors[] = "Name is required.";
    if ($description === '')                  $errors[] = "Description is required.";
    if (!is_numeric($price) || $price <= 0)  $errors[] = "Enter a valid price.";

    // Get current image
    $cur = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
    $cur->bind_param("i", $edit_id);
    $cur->execute();
    $cur_row   = $cur->get_result()->fetch_assoc();
    $cur->close();
    $image_url = $cur_row['image_url'] ?? '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload = handle_upload($_FILES['image'], $conn);
        if ($upload['error']) $errors[] = $upload['error'];
        else {
            if (!empty($image_url) && file_exists("../" . $image_url)) {
                unlink("../" . $image_url);
            }
            $image_url = $upload['url'];
        }
    }

    if (empty($errors)) {
        $upd = $conn->prepare("
            UPDATE products
            SET name = ?, description = ?, price = ?, category = ?, condition_of_product = ?, image_url = ?
            WHERE id = ?
        ");
        $upd->bind_param("ssdsssı", $name, $description, $price, $category, $condition, $image_url, $edit_id);
        // Fix: correct bind_param types
        $upd->close();

        $upd2 = $conn->prepare("
            UPDATE products
            SET name=?, description=?, price=?, category=?, condition_of_product=?, image_url=?
            WHERE id=?
        ");
        $upd2->bind_param("ssdsssi", $name, $description, $price, $category, $condition, $image_url, $edit_id);
        if ($upd2->execute()) $success = "Product updated.";
        else $errors[] = "Database error: " . $conn->error;
        $upd2->close();
    }
}

$products = $conn->query("SELECT * FROM products ORDER BY id DESC");

$edit_product = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $e = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $e->bind_param("i", (int)$_GET['edit']);
    $e->execute();
    $edit_product = $e->get_result()->fetch_assoc();
    $e->close();
}
?>

<div class="container admin-page">

    <div class="admin-header">
        <div>
            <p class="section-eyebrow">Admin Panel</p>
            <h1 class="section-title"><?= $edit_product ? 'Edit Product' : 'Manage Products' ?></h1>
        </div>
        <a href="dashboard.php" class="btn-back-shop">← Dashboard</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success mb-4">✓ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-danger mb-4">
            <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <!-- Aadd / Edit FORM -->
    <div class="admin-section">
        <h4 class="admin-section-title"><?= $edit_product ? 'Editing: ' . htmlspecialchars($edit_product['name']) : 'Add New Product' ?></h4>

        <form method="POST" enctype="multipart/form-data" class="listing-form">
            <?php if ($edit_product): ?>
                <input type="hidden" name="edit_id" value="<?= (int)$edit_product['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">Product Name *</label>
                <input type="text" name="name" class="form-input" required
                       value="<?= htmlspecialchars($edit_product['name'] ?? $_POST['name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Description *</label>
                <textarea name="description" class="form-input form-textarea" required><?= htmlspecialchars($edit_product['description'] ?? $_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Price (CAD) *</label>
                    <div class="input-prefix-wrap">
                        <span class="input-prefix">$</span>
                        <input type="number" name="price" step="0.01" min="0.01"
                               class="form-input input-with-prefix" required
                               value="<?= htmlspecialchars($edit_product['price'] ?? $_POST['price'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-input" required>
                        <option value="">Select...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>"
                                <?= (($edit_product['category'] ?? $_POST['category'] ?? '') === $cat) ? 'selected' : '' ?>>
                                <?= $cat ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Condition *</label>
                    <select name="condition" class="form-input" required>
                        <option value="">Select...</option>
                        <?php foreach ($conditions as $cond): ?>
                            <option value="<?= $cond ?>"
                                <?= (($edit_product['condition_of_product'] ?? $_POST['condition'] ?? '') === $cond) ? 'selected' : '' ?>>
                                <?= $cond ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    Product Image <?= $edit_product ? '(leave empty to keep current)' : '' ?>
                </label>

                <?php if (!empty($edit_product['image_url'])): ?>
                    <div class="current-image-wrap">
                        <img src="../<?= htmlspecialchars($edit_product['image_url']) ?>"
                             alt="Current image" class="current-image-thumb">
                        <span class="current-image-label">Current image</span>
                    </div>
                <?php endif; ?>

                <div class="upload-zone" id="uploadZone">
                    <input type="file" name="image" id="imageInput"
                           accept=".jpg,.jpeg,.png,.webp" class="upload-input">
                    <div class="upload-placeholder" id="uploadPlaceholder">
                        <span class="upload-icon">📷</span>
                        <span class="upload-text">Click to upload or drag & drop</span>
                        <span class="upload-hint">JPG, PNG, WEBP — max 5MB</span>
                    </div>
                    <img id="imagePreview" class="upload-preview" src="" alt="Preview" style="display:none;">
                </div>
            </div>

            <?php if ($edit_product): ?>
                <div class="form-actions">
                    <button type="submit" name="edit_product" class="btn-submit">Save Changes →</button>
                    <a href="manage_products.php" class="btn-filter-clear">Cancel</a>
                </div>
            <?php else: ?>
                <button type="submit" name="add_product" class="btn-submit">Add Product →</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="admin-section">
        <h4 class="admin-section-title">All Products (<?= $products->num_rows ?>)</h4>

        <?php if ($products->num_rows > 0): ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Condition</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($p = $products->fetch_assoc()):
                    $thumb = !empty($p['image_url'])
                        ? '../' . htmlspecialchars($p['image_url'])
                        : 'https://placehold.co/48x48/e9ecef/6c757d?text=?';
                ?>
                    <tr>
                        <td><img src="<?= $thumb ?>" alt="" class="admin-thumb"></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['category'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($p['condition_of_product'] ?? '—') ?></td>
                        <td>$<?= number_format($p['price'], 2) ?></td>
                        <td>
                            <a href="manage_products.php?edit=<?= (int)$p['id'] ?>"
                               class="btn-table-edit">Edit</a>

                            <form method="POST" style="display:inline"
                                  onsubmit="return confirm('Delete this product?')">
                                <input type="hidden" name="delete_product" value="<?= (int)$p['id'] ?>">
                                <button type="submit" class="btn-table-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="text-muted">No products yet. Add one above.</p>
        <?php endif; ?>
    </div>

</div>

<script>
const input       = document.getElementById('imageInput');
const preview     = document.getElementById('imagePreview');
const placeholder = document.getElementById('uploadPlaceholder');

input.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        preview.src = e.target.result;
        preview.style.display = 'block';
        placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
});
</script>

<?php include("../includes/footer.php"); ?>
