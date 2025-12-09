<?php
require_once 'config/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/cart_functions.php';

requireLogin();

$success_message = '';
$error_message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($full_name) || empty($email)) {
        $error_message = "Full name and email are required";
    } else {
        if (updateUserProfile($_SESSION['user_id'], $full_name, $email, $contact_number, $address)) {
            $success_message = "Profile updated successfully!";
        } else {
            $error_message = "Failed to update profile";
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All password fields are required";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Password must be at least 6 characters";
    } else {
        if (updatePassword($_SESSION['user_id'], $current_password, $new_password)) {
            $success_message = "Password changed successfully!";
        } else {
            $error_message = "Current password is incorrect";
        }
    }
}

// Get user info and orders
$user_info = getUserInfo($_SESSION['user_id']);
$orders = getUserOrders($_SESSION['user_id']);
$cart_count = getCartCount($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - KusinaSaBahay</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }
        
        .profile-sidebar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: #e74c3c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            margin: 0 auto 20px;
        }
        
        .profile-name {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .profile-email {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
        }
        
        .profile-menu {
            list-style: none;
            padding: 0;
        }
        
        .profile-menu li {
            margin-bottom: 10px;
        }
        
        .profile-menu a {
            display: block;
            padding: 10px 15px;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .profile-menu a:hover,
        .profile-menu a.active {
            background: #f0f0f0;
        }
        
        .profile-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section {
            display: none;
        }
        
        .section.active {
            display: block;
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
        .form-group textarea {
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
        
        .btn-submit {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        
        .btn-submit:hover {
            background: #c0392b;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .order-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .order-id {
            font-weight: bold;
            font-size: 18px;
        }
        
        .order-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background: #cfe2ff;
            color: #084298;
        }
        
        .status-completed {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #842029;
        }
        
        .order-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .order-total {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #e74c3c;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #eee;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
            
            .order-details {
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
                <li><a href="profile.php" class="active">Profile</a></li>
                <li><a href="cart.php">Cart (<?php echo $cart_count; ?>)</a></li>
                <li><a href="logout.php" class="btn">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="profile-container">
        <h1 style="margin-bottom: 30px;">My Profile</h1>
        
        <div class="profile-grid">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user_info['full_name'], 0, 1)); ?>
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($user_info['full_name']); ?></div>
                <div class="profile-email"><?php echo htmlspecialchars($user_info['email']); ?></div>
                
                <ul class="profile-menu">
                    <li><a href="#" onclick="showSection('orders')" class="active">My Orders</a></li>
                    <li><a href="#" onclick="showSection('profile')">Edit Profile</a></li>
                    <li><a href="#" onclick="showSection('password')">Change Password</a></li>
                </ul>
            </div>
            
            <!-- Content -->
            <div class="profile-content">
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-error"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <!-- Orders Section -->
                <div id="orders" class="section active">
                    <h2>My Orders</h2>
                    
                    <?php if (empty($orders)): ?>
                        <div class="empty-state">
                            <h3>No orders yet</h3>
                            <p>Start ordering delicious Filipino dishes!</p>
                            <a href="index.php" class="btn-submit" style="display: inline-block; text-decoration: none; margin-top: 20px;">
                                Browse Menu
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-id">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
                                <div class="order-status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </div>
                            </div>
                            
                            <div class="order-details">
                                <div>
                                    <strong>Date:</strong> 
                                    <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                                </div>
                                <div>
                                    <strong>Payment:</strong> 
                                    <?php echo strtoupper($order['payment_method']); ?>
                                </div>
                                <div>
                                    <strong>Delivery Address:</strong>
                                    <?php echo nl2br(htmlspecialchars($order['address'])); ?>
                                </div>
                                <div>
                                    <strong>Contact:</strong>
                                    <?php echo htmlspecialchars($order['contact_number']); ?>
                                </div>
                            </div>
                            
                            <div class="order-total">
                                Total: ₱<?php echo number_format($order['total'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Edit Profile Section -->
                <div id="profile" class="section">
                    <h2>Edit Profile</h2>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_info['full_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contact_number" value="<?php echo htmlspecialchars($user_info['contact_number'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address"><?php echo htmlspecialchars($user_info['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn-submit">Update Profile</button>
                    </form>
                </div>
                
                <!-- Change Password Section -->
                <div id="password" class="section">
                    <h2>Change Password</h2>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Current Password *</label>
                            <input type="password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label>New Password *</label>
                            <input type="password" name="new_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Confirm New Password *</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn-submit">Change Password</button>
                    </form>
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

    <script>
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Remove active class from all menu items
            document.querySelectorAll('.profile-menu a').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(sectionId).classList.add('active');
            
            // Add active class to clicked menu item
            event.target.classList.add('active');
            
            event.preventDefault();
        }
    </script>
</body>
</html>
