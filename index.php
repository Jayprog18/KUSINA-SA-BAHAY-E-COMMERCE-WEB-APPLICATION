<?php
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

// Only include cart_functions if user is logged in
if (isset($_SESSION['user_id'])) {
    require_once 'includes/cart_functions.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KusinaSaBahay - Filipino Cuisine E-Commerce</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="container">
            <a href="index.php" class="logo">KusinaSaBahay</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="cart.php">Cart (<span id="cart-count"><?php echo getCartCount($_SESSION['user_id']); ?></span>)</a></li>
                    <li><a href="logout.php" class="btn">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php" class="btn">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <img src="images/logo.jpg" class="logo-img">
            <h1>Welcome to KusinaSaBahay</h1>
            <p>Authentic Filipino Cuisine Delivered to Your Home</p>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn-primary">Get Started</a>
            <?php else: ?>
                <p style="font-size: 24px; margin-top: 20px;">
                    Welcome back, <strong><?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Guest'; ?></strong>!
                </p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="container">
            <h2>Our Featured Dishes</h2>
            <div class="products-grid">
                <?php
                $products = getAllProducts();
                foreach ($products as $product):
                ?>
                <div class="product-card">
                    <img src="<?php echo $product['image_url']; ?>" 
                         alt="<?php echo $product['name']; ?>" 
                         class="product-image">
                    
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                        
                        <?php if (isLoggedIn()): ?>
                            <div class="product-actions">
                                <button class="btn-add-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                                    Add to Cart
                                </button>
                                <button class="btn-order-now" onclick="orderNow(<?php echo $product['id']; ?>)">
                                    Order Now
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="product-actions">
                                <button class="btn-add-cart" onclick="window.location.href='login.php'">
                                    Login to Order
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 KusinaSaBahay. All rights reserved. | Filipino Cuisine E-Commerce</p>
            <p>Developed by: Greyzel Garrido, Jenalyn Pantinople, Jay-r Pelonio, Rutcie Amancio</p>
        </div>
    </footer>

    <script>
    function addToCart(productId) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=1`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                // Update cart count in navigation
                document.getElementById('cart-count').textContent = data.cart_count;
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    function orderNow(productId) {
        // Add to cart first, then redirect to checkout
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=1`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to checkout page
                window.location.href = 'checkout.php';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
    </script>
</body>
</html>