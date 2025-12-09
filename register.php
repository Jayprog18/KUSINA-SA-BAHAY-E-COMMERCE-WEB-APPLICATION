<?php
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

// Redirect if already logged in
redirectIfLoggedIn();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = sanitizeInput($_POST['username']);
    $email      = sanitizeInput($_POST['email']);
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm_password'];
    $full_name  = sanitizeInput($_POST['full_name']);

    // Server-side validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm) || empty($full_name)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match';
    } else {
        $result = registerUser($username, $email, $password, $full_name);

        if ($result['success']) {
            $success = $result['message'];
            header("refresh:2;url=login.php");
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - KusinaSaBahay</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav>
    <div class="container">
        <a href="index.php" class="logo">KusinaSaBahay</a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php" class="btn">Register</a></li>
        </ul>
    </div>
</nav>

<div class="form-container">
    <h2>Create Account</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?> Redirecting...</div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" required>
        </div>

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn-submit">Register</button>
    </form>

    <div class="form-footer">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

</body>
</html>
