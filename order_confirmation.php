<?php
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    header("Location: index.php");
    exit;
}

// Get order details
$sql = "SELECT o.*, 
        (SELECT SUM(oi.subtotal) FROM order_items oi WHERE oi.order_id = o.id) as items_total
        FROM orders o 
        WHERE o.id = ? AND o.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header("Location: index.php");
    exit;
}

// Get order items
$sql = "SELECT oi.*, p.name, p.image_url 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order_items = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - KusinaSaBahay</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .confirmation-section {
            padding: 60px 20px;
            text-align: center;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 40px;
            color: white;
        }
        
        .confirmation-content {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .order-number {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }
        
        .order-details {
            text-align: left;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #eee;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        
        .item-info {
            flex: 1;
            text-align: left;
        }
        
        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
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
                <li><a href="logout.php" class="btn">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Confirmation Section -->
    <section class="confirmation-section">
        <div class="confirmation-content">
            <div class="success-icon">✓</div>
            <h1>Order Placed Successfully!</h1>
            <p class="order-number">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
            <p>Thank you for your order! We'll prepare your delicious Filipino dishes with care.</p>
            
            <div class="order-details">
                <h2>Order Details</h2>
                
                <div class="detail-row">
                    <strong>Order Date:</strong>
                    <span><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></span>
                </div>
                
                <div class="detail-row">
                    <strong>Status:</strong>
                    <span style="color: #FF9800; text-transform: capitalize;"><?php echo $order['status']; ?></span>
                </div>
                
                <div class="detail-row">
                    <strong>Delivery Address:</strong>
                    <span><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                </div>
                
                <div class="detail-row">
                    <strong>Contact Number:</strong>
                    <span><?php echo htmlspecialchars($order['contact_number']); ?></span>
                </div>
                
                <h3 style="margin-top: 30px;">Ordered Items</h3>
                <?php foreach ($order_items as $item): ?>
                <div class="order-item">
                    <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>">
                    <div class="item-info">
                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                        <p>Quantity: <?php echo $item['quantity']; ?> × ₱<?php echo number_format($item['price'], 2); ?></p>
                    </div>
                    <strong>₱<?php echo number_format($item['subtotal'], 2); ?></strong>
                </div>
                <?php endforeach; ?>
                
                <div class="detail-row" style="font-size: 18px; margin-top: 20px;">
                    <strong>Total Amount:</strong>
                    <strong style="color: #d32f2f;">₱<?php echo number_format($order['total_amount'], 2); ?></strong>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="index.php" class="btn-primary">Continue Shopping</a>
                <a href="profile.php" class="btn">View My Orders</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 KusinaSaBahay. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>