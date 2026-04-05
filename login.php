<?php
include("includes/db.php");
include("includes/header.php");

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if (isset($_POST['login'])) {

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, is_admin FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['name']     = $user['name'];
                $_SESSION['is_admin'] = (bool) $user['is_admin'];

                if ($_SESSION['is_admin']) {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: products.php");
                }
                exit();

            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with that email.";
        }
        $stmt->close();
    }
}
?>

<div class="container form-page">
    <div class="form-card">
        <div class="form-card-header">
            <h1 class="form-title">Welcome back</h1>
            <p class="form-sub">Login to your Marketplace account.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="loginForm" class="listing-form">

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" id="loginEmail" class="form-input"
                       placeholder="you@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" id="loginPassword" class="form-input"
                       placeholder="••••••••" required>
            </div>

            <button type="submit" name="login" class="btn-submit">Login →</button>

        </form>

        <p class="form-footer-link">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<?php include("includes/footer.php"); ?>