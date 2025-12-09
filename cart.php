<?php
require_once 'config/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/cart_functions.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'update' && isset($_POST['cart_id']) && isset($_POST['quantity'])) {
            $cart_id = intval($_POST['cart_id']);
            $quantity = intval($_POST['quantity']);
            updateCartQuantity($cart_id, $_SESSION['user_id'], $quantity);
        } elseif ($action === 'remove' && isset($_POST['cart_id'])) {
            $cart_id = intval($_POST['cart_id']);
            removeFromCart($cart_id, $_SESSION['user_id']);
        }
        
        // Redirect to refresh the page
        header('Location: cart.php');
        exit;
    }
}

// Get cart items
$cart_items = getCartItems($_SESSION['user_id']);
$subtotal = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - KusinaSaBahay</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .cart-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .cart-items {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .cart-item {
            display: flex;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid #eee;
            position: relative;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .cart-item-price {
            color: #e74c3c;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            font-size: 18px;
            border-radius: 5px;
        }
        
        .quantity-btn:hover {
            background: #f0f0f0;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 5px;
        }
        
        .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .remove-btn:hover {
            background: #c0392b;
        }
        
        .order-summary {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .order-summary h2 {
            margin-bottom: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .summary-total {
            font-size: 20px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 15px;
            margin-top: 10px;
        }
        
        .checkout-btn {
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
        
        .checkout-btn:hover {
            background: #c0392b;
        }
        
        .continue-shopping {
            width: 100%;
            padding: 15px;
            background: white;
            color: #333;
            border: 2px solid #333;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
        .continue-shopping:hover {
            background: #f0f0f0;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-cart h2 {
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                flex-direction: column;
            }
            
            .cart-item-image {
                width: 100%;
                height: 200px;
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
                <li><a href="cart.php" class="active">Cart (<?php echo count($cart_items); ?>)</a></li>
                <li><a href="logout.php" class="btn">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="cart-container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <h2>Your cart is empty</h2>
                <p>Add some delicious Filipino dishes to get started!</p>
                <a href="index.php" class="checkout-btn" style="max-width: 300px; margin: 20px auto;">
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): 
                        $item_total = $item['price'] * $item['quantity'];
                        $subtotal += $item_total;
                    ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             class="cart-item-image">
                        
                        <div class="cart-item-details">
                            <div class="cart-item-name">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </div>
                            <div class="cart-item-price">
                                ₱<?php echo number_format($item['price'], 2); ?>
                            </div>
                            
                            <div class="quantity-controls">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="quantity" value="<?php echo max(1, $item['quantity'] - 1); ?>">
                                    <button type="submit" class="quantity-btn">-</button>
                                </form>
                                
                                <input type="text" 
                                       class="quantity-input" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       readonly>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="quantity" value="<?php echo $item['quantity'] + 1; ?>">
                                    <button type="submit" class="quantity-btn">+</button>
                                </form>
                                
                                <form method="POST" style="display: inline; margin-left: 20px;">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="remove-btn" 
                                            onclick="return confirm('Remove this item from cart?')">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div style="text-align: right;">
                            <strong>₱<?php echo number_format($item_total, 2); ?></strong>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-summary">
                    <h2>Order Summary</h2>
                    
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <strong>₱<?php echo number_format($subtotal, 2); ?></strong>
                    </div>
                    
                    <div class="summary-row">
                        <span>Delivery Fee:</span>
                        <strong>₱50.00</strong>
                    </div>
                    
                    <div class="summary-row summary-total">
                        <span>Total:</span>
                        <strong>₱<?php echo number_format($subtotal + 50, 2); ?></strong>
                    </div>
                    
                    <button class="checkout-btn" onclick="window.location.href='checkout.php'">
                        Proceed to Checkout
                    </button>
                    
                    <a href="index.php" class="continue-shopping">
                        Continue Shopping
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 KusinaSaBahay. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>