<?php
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

requireLogin();

$user = getUserProfile(getCurrentUserId());
$orders = getUserOrders(getCurrentUserId());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Kusina sa Bahay</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        nav {
            background: #8B4513;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        nav .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        nav .logo {
            font-size: 24px;
            font-weight: bold;
        }
        
        nav ul {
            list-style: none;
            display: flex;
            gap: 30px;
            align-items: center;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        
        nav a:hover {
            opacity: 0.8;
        }
        
        .page-header {
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 42px;
            margin-bottom: 10px;
        }
        
        .main-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .orders-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state h2 {
            font-size: 28px;
            margin-bottom: 15px;
            color: #8B4513;
        }
        
        .empty-state p {
            font-size: 18px;
            margin-bottom: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #8B4513;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #A0522D;
        }
        
        .order-card {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            transition: border-color 0.3s;
        }
        
        .order-card:hover {
            border-color: #8B4513;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .order-id {
            font-size: 18px;
            font-weight: 600;
            color: #8B4513;
        }
        
        .order-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .order-details {
            margin-bottom: 15px;
        }
        
        .order-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #666;
        }
        
        .order-info label {
            font-weight: 600;
            color: #333;
        }
        
        .order-items {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .order-items h4 {
            color: #8B4513;
            margin-bottom: 12px;
            font-size: 16px;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .item-row:last-child {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: 500;
            color: #333;
        }
        
        .item-quantity {
            color: #666;
            font-size: 14px;
        }
        
        .item-price {
            color: #8B4513;
            font-weight: 600;
        }
        
        .order-total {
            font-size: 24px;
            color: #8B4513;
            font-weight: bold;
            text-align: right;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #e0e0e0;
        }
        
        footer {
            background: #8B4513;
            color: white;
            text-align: center;
            padding: 30px 20px;
            margin-top: 60px;
        }
    </style>
</head>
<body>
    <nav>
        <div class="container">
            <div class="logo">Kusina sa Bahay</div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php">My Orders</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li>
                    <span>👤 <?php echo htmlspecialchars($user['username']); ?></span>
                    <a href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="page-header">
        <h1>📦 My Orders</h1>
        <p>Track your order history</p>
    </div>
    
    <div class="main-container">
        <div class="orders-container">
            <?php if (empty($orders)): ?>
            <div class="empty-state">
                <h2>📦 No Orders Yet</h2>
                <p>You haven't placed any orders. Start exploring our delicious menu!</p>
                <a href="products.php" class="btn">Browse Products</a>
            </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <?php $orderItems = getOrderItems($order['id']); ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-id">Order #<?php echo $order['id']; ?></div>
                            <div class="order-status status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </div>
                        </div>
                        
                        <div class="order-details">
                            <div class="order-info">
                                <label>📅 Order Date:</label>
                                <span><?php echo date('F d, Y h:i A', strtotime($order['order_date'])); ?></span>
                            </div>
                            
                            <?php if ($order['delivery_address']): ?>
                            <div class="order-info">
                                <label>📍 Delivery Address:</label>
                                <span><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($orderItems)): ?>
                        <div class="order-items">
                            <h4>📋 Order Items:</h4>
                            <?php foreach ($orderItems as $item): ?>
                            <div class="item-row">
                                <div>
                                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                                </div>
                                <div class="item-price">
                                    ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="order-total">
                            Total: ₱<?php echo number_format($order['total_amount'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <footer>
        <p><strong>Kusina sa Bahay</strong></p>
        <p>📍 123 Poblacion, Argao, Cebu | 📞 (+63) 946-990-5762</p>
        <p>© 2025 Kusina sa Bahay. All rights reserved.</p>
    </footer>
</body>
</html>