<?php
include("includes/db.php");
include("includes/header.php");

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$errors  = [];
$success = '';

if (isset($_POST['register'])) {

    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';
    $confirm  =      $_POST['confirm']  ?? '';

    if ($name === '')                                    $errors[] = "Full name is required.";
    if (strlen($name) > 100)                             $errors[] = "Name is too long.";
    if ($email === '')                                   $errors[] = "Email is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))      $errors[] = "Enter a valid email address.";
    if (strlen($password) < 6)                           $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm)                          $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) $errors[] = "An account with that email already exists.";
        $check->close();
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt   = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
}
?>

<div class="container form-page">
    <div class="form-card">
        <div class="form-card-header">
            <h1 class="form-title">Create an account</h1>
            <p class="form-sub">Join the Marketplace — buy and sell in minutes.</p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                ✓ Account created! <a href="login.php">Login now →</a>
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

        <?php if (empty($success)): ?>
        <form method="POST" id="registerForm" class="listing-form">

            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" id="registerName" class="form-input"
                       placeholder="John Smith"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" id="registerEmail" class="form-input"
                       placeholder="you@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="registerPassword" class="form-input"
                           placeholder="Min. 8 characters" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm" id="registerConfirm" class="form-input"
                           placeholder="Repeat password" required>
                </div>
            </div>

            <button type="submit" name="register" class="btn-submit">Create Account →</button>

        </form>
        <?php endif; ?>

        <p class="form-footer-link">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</div>

<?php include("includes/footer.php"); ?>