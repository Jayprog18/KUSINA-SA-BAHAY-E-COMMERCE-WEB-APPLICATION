<?php
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

// Redirect to home if no order success flag
if (!isset($_SESSION['order_success']) || !$_SESSION['order_success']) {
    header('Location: index.php');
    exit;
}

$order_id = $_SESSION['order_id'] ?? null;

// Clear the success flag
unset($_SESSION['order_success']);
unset($_SESSION['order_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful - KusinaSaBahay</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 40px;
            text-align: center;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #27ae60;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 40px;
            color: white;
        }
        
        .success-container h1 {
            color: #27ae60;
            margin-bottom: 20px;
        }
        
        .order-number {
            font-size: 24px;
            color: #333;
            margin: 20px 0;
        }
        
        .success-message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-primary, .btn-secondary {
            padding: 15px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #e74c3c;
            color: white;
        }
        
        .btn-primary:hover {
            background: #c0392b;
        }
        
        .btn-secondary {
            background: white;
            color: #333;
            border: 2px solid #333;
        }
        
        .btn-secondary:hover {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="container">
            <a href="index.php" class="logo">KusinaSaBahay</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="cart.php">Cart (0)</a></li>
                <li><a href="logout.php" class="btn">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="success-container">
        <div class="success-icon">✓</div>
        
        <h1>Order Placed Successfully!</h1>
        
        <?php if ($order_id): ?>
            <div class="order-number">
                Order #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?>
            </div>
        <?php endif; ?>
        
        <p class="success-message">
            Thank you for your order! We've received your request and will start preparing your delicious Filipino dishes right away. 
            You'll receive a confirmation email shortly with your order details.
        </p>
        
        <div class="action-buttons">
            <a href="profile.php" class="btn-primary">View My Orders</a>
            <a href="index.php" class="btn-secondary">Continue Shopping</a>
        </div>
    </div>

    <!-- Footer -->
    <footer style="position: fixed; bottom: 0; width: 100%;">
        <div class="container">
            <p>&copy; 2025 KusinaSaBahay. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>