<?php
require_once 'config/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/cart_functions.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get cart items
$cart_items = getCartItems($_SESSION['user_id']);

// Redirect to cart if empty
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$delivery_fee = 50.00;
$total = $subtotal + $delivery_fee;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cod';
    
    // Validate inputs
    $errors = [];
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($address)) $errors[] = "Delivery address is required";
    if (empty($contact_number)) $errors[] = "Contact number is required";
    
    if (empty($errors)) {
        $conn = getConnection();
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, full_name, email, address, contact_number, subtotal, delivery_fee, total, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("issssddds", $_SESSION['user_id'], $full_name, $email, $address, $contact_number, $subtotal, $delivery_fee, $total, $payment_method);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $order_id = $conn->insert_id;
            $stmt->close();
            
            // Insert order items
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare order items failed: " . $conn->error);
            }
            
            foreach ($cart_items as $item) {
                $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                if (!$stmt->execute()) {
                    throw new Exception("Execute order items failed: " . $stmt->error);
                }
            }
            $stmt->close();
            
            // Clear cart
            clearCart($_SESSION['user_id']);
            
            // Commit transaction
            $conn->commit();
            $conn->close();
            
            // Redirect to success page
            $_SESSION['order_success'] = true;
            $_SESSION['order_id'] = $order_id;
            header('Location: order_success.php');
            exit;
            
        } catch (Exception $e) {
            $conn->rollback();
            $conn->close();
            $errors[] = "Failed to process order: " . $e->getMessage();
        }
    }
}

// Get user info
$user_info = getUserInfo($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - KusinaSaBahay</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .checkout-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 30px;
        }
        
        .checkout-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .order-summary-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .item-quantity {
            color: #666;
            font-size: 14px;
        }
        
        .item-price {
            font-weight: bold;
            color: #e74c3c;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }
        
        .summary-total {
            font-size: 20px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 15px;
            margin-top: 10px;
        }
        
        .place-order-btn {
            width: 100%;
            padding: 15px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            margin-top: 20px;
        }
        
        .place-order-btn:hover {
            background: #c0392b;
        }
        
        .error-message {
            background: #ffe6e6;
            color: #c0392b;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
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
                <li><a href="cart.php">Cart (<?php echo count($cart_items); ?>)</a></li>
                <li><a href="logout.php" class="btn">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="checkout-container">
        <h1 style="margin-bottom: 30px;">Checkout</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="checkout-grid">
            <div class="checkout-form">
                <h2>Delivery Information</h2>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" 
                               name="full_name" 
                               value="<?php echo htmlspecialchars($user_info['full_name'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($user_info['email'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label>Delivery Address *</label>
                        <textarea name="address" required><?php echo htmlspecialchars($user_info['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Contact Number *</label>
                        <input type="tel" 
                               name="contact_number" 
                               value="<?php echo htmlspecialchars($user_info['contact_number'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="payment_method" required>
                            <option value="cod">Cash on Delivery</option>
                            <option value="gcash">GCash</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="place-order-btn">Place Order</button>
                </form>
            </div>
            
            <div class="order-summary-box">
                <h2>Order Summary</h2>
                
                <div style="margin: 20px 0;">
                    <?php foreach ($cart_items as $item): 
                        $item_total = $item['price'] * $item['quantity'];
                    ?>
                    <div class="order-item">
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-quantity">Qty: <?php echo $item['quantity']; ?> × ₱<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                        <div class="item-price">
                            ₱<?php echo number_format($item_total, 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <strong>₱<?php echo number_format($subtotal, 2); ?></strong>
                </div>
                
                <div class="summary-row">
                    <span>Delivery Fee:</span>
                    <strong>₱<?php echo number_format($delivery_fee, 2); ?></strong>
                </div>
                
                <div class="summary-row summary-total">
                    <span>Total:</span>
                    <strong>₱<?php echo number_format($total, 2); ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 KusinaSaBahay. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>