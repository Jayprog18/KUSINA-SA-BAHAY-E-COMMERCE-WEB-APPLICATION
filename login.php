<?php
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

// Redirect if already logged in
redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } else {
        $result = loginUser($username, $password);
        
        if ($result['success']) {
            // Redirect to profile page
            header('Location: profile.php');
            exit();
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
    <title>Login - KusinaSaBahay</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="container">
            <a href="index.php" class="logo">KusinaSaBahay</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php" class="btn">Login</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="form-container">
        <h2>Login</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php" onsubmit="return validateLoginForm()">
            <div class="form-group">
                <label for="username">Username or Email:</label>
                <input type="text" id="username" name="username" required>
                <span id="usernameError" class="error"></span>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <span id="passwordError" class="error"></span>
            </div>
            
            <button type="submit" class="btn-submit">Login</button>
        </form>
        
        <div class="form-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

    <script src="js/validation.js"></script>
</body>
</html>