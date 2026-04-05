<?php
include("includes/header.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$success = '';
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = trim($_POST['price']       ?? '');
    $category    = trim($_POST['category']    ?? '');
    $condition   = trim($_POST['condition']   ?? '');
    $stock       = max(0, (int) ($_POST['stock'] ?? 1));

    if ($name === '')                         $errors[] = "Product name is required.";
    if ($description === '')                  $errors[] = "Description is required.";
    if (!is_numeric($price) || $price <= 0)  $errors[] = "Enter a valid price.";
    if ($category === '')                     $errors[] = "Select a category.";
    if ($condition === '')                    $errors[] = "Select a condition.";

    $image_url = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $max_size      = 5 * 1024 * 1024;
        $file_type     = mime_content_type($_FILES['image']['tmp_name']);
        $file_size     = $_FILES['image']['size'];
        $file_ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $ext_map       = ['jpg' => true, 'jpeg' => true, 'png' => true, 'webp' => true];

        if (!in_array($file_type, $allowed_types) || !isset($ext_map[$file_ext])) {
            $errors[] = "Only JPG, PNG, and WEBP images are allowed.";
        } elseif ($file_size > $max_size) {
            $errors[] = "Image must be under 5MB.";
        } else {
            $upload_dir = "images/uploads/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $filename = uniqid('img_', true) . '.' . $file_ext;
            $dest     = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $image_url = $dest;
            } else {
                $errors[] = "Failed to upload image. Check folder permissions.";
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO products (name, description, price, category, condition_of_product, image_url, seller_id, stock)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssdsssii", $name, $description, $price, $category, $condition, $image_url, $user_id, $stock);
        if ($stmt->execute()) {
            $success = "Your listing has been posted!";
        } else {
            $errors[] = "Database error. Please try again.";
        }
        $stmt->close();
    }
}

$categories = ['Electronics', 'Clothing', 'Books', 'Vinyl', 'Collectibles', 'Furniture', 'Other'];
$conditions = ['New', 'Like New', 'Good', 'Used'];
?>

<div class="container form-page">
    <div class="form-card">
        <div class="form-card-header">
            <h1 class="form-title">List an Item for Sale</h1>
            <p class="form-sub">Fill in the details below and your listing goes live immediately.</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                ✓ <?= htmlspecialchars($success) ?>
                — <a href="products.php">View all listings</a> or <a href="sell.php">post another</a>.
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="listingForm" class="listing-form">

            <div class="form-group">
                <label class="form-label">Product Name *</label>
                <input type="text" name="name" id="listingName" class="form-input"
                       placeholder="e.g. iPhone 11, Levi's Jacket, Harry Potter Box Set"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description *</label>
                <textarea name="description" id="listingDescription"
                          class="form-input form-textarea"
                          placeholder="Describe the item — age, features, any wear or defects..."
                          required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Price (CAD) *</label>
                    <div class="input-prefix-wrap">
                        <span class="input-prefix">$</span>
                        <input type="number" name="price" id="listingPrice"
                               step="0.01" min="0.01"
                               class="form-input input-with-prefix"
                               placeholder="0.00"
                               value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category" id="listingCategory" class="form-input" required>
                        <option value="">Select category...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>"
                                <?= (($_POST['category'] ?? '') === $cat) ? 'selected' : '' ?>>
                                <?= $cat ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Condition *</label>
                    <select name="condition" id="listingCondition" class="form-input" required>
                        <option value="">Select condition...</option>
                        <?php foreach ($conditions as $cond): ?>
                            <option value="<?= $cond ?>"
                                <?= (($_POST['condition'] ?? '') === $cond) ? 'selected' : '' ?>>
                                <?= $cond ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Quantity in Stock *</label>
                    <input type="number" name="stock" id="listingStock"
                           class="form-input" min="1" value="<?= (int)($_POST['stock'] ?? 1) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Product Image</label>
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

            <button type="submit" class="btn-submit">Post Listing →</button>

        </form>
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

<?php include("includes/footer.php"); ?>