<?php

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirectIfLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }
}

function registerUser($username, $email, $password, $full_name) {
    $conn = getConnection();  // IMPORTANT FIX

    // Check existing user
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        return [
            'success' => false,
            'message' => 'Username or email already exists'
        ];
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO users (username, email, password, full_name)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $full_name);

    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => 'Registration successful!'
        ];
    }

    return [
        'success' => false,
        'message' => 'Registration failed. Please try again.'
    ];
}



function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function login($email, $password) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT id, full_name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            
            $stmt->close();
            $conn->close();
            return true;
        }
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

function loginUser($username, $password) {
    $conn = getConnection(); // Use your connection function
    
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Start session if not started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];

            $stmt->close();
            $conn->close();
            return ['success' => true];
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Invalid password'];
        }
    } else {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'User not found'];
    }
}



function register($full_name, $email, $password, $contact_number = '', $address = '') {
    $conn = getConnection();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->close();
        return false; // Email already exists
    }
    $stmt->close();
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Check which columns exist
    $column_check = $conn->query("SHOW COLUMNS FROM users");
    $existing_columns = [];
    while ($row = $column_check->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    // Build INSERT query based on existing columns
    $has_contact = in_array('contact_number', $existing_columns);
    $has_address = in_array('address', $existing_columns);
    
    if ($has_contact && $has_address) {
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, contact_number, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $full_name, $email, $hashed_password, $contact_number, $address);
    } else {
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $full_name, $email, $hashed_password);
    }
    
    $success = $stmt->execute();
    
    if ($success) {
        $user_id = $conn->insert_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
    }
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

function logoutUser() {
    session_destroy();
    header('Location: login.php');
    exit;
}

// User Functions
function getUserInfo($user_id) {
    $conn = getConnection();
    $user_id = intval($user_id);
    
    // First check which columns exist in the users table
    $columns = ['id', 'full_name', 'email'];
    $result = $conn->query("SHOW COLUMNS FROM users");
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    // Add optional columns if they exist
    if (in_array('contact_number', $existing_columns)) {
        $columns[] = 'contact_number';
    }
    if (in_array('address', $existing_columns)) {
        $columns[] = 'address';
    }
    if (in_array('created_at', $existing_columns)) {
        $columns[] = 'created_at';
    }
    
    $column_list = implode(', ', $columns);
    
    $stmt = $conn->prepare("SELECT {$column_list} FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $user = null;
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Add default values for missing columns
        if (!isset($user['contact_number'])) {
            $user['contact_number'] = '';
        }
        if (!isset($user['address'])) {
            $user['address'] = '';
        }
    }
    
    $stmt->close();
    $conn->close();
    
    return $user;
}

function updateUserProfile($user_id, $full_name, $email, $contact_number = null, $address = null) {
    $conn = getConnection();
    $user_id = intval($user_id);
    
    // Check which columns exist
    $result = $conn->query("SHOW COLUMNS FROM users");
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    // Build UPDATE query based on existing columns
    $updates = ["full_name = ?", "email = ?"];
    $types = "ss";
    $params = [$full_name, $email];
    
    if (in_array('contact_number', $existing_columns) && $contact_number !== null) {
        $updates[] = "contact_number = ?";
        $types .= "s";
        $params[] = $contact_number;
    }
    
    if (in_array('address', $existing_columns) && $address !== null) {
        $updates[] = "address = ?";
        $types .= "s";
        $params[] = $address;
    }
    
    $types .= "i";
    $params[] = $user_id;
    
    $query = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $success = $stmt->execute();
    
    if ($success) {
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
    }
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

function updatePassword($user_id, $current_password, $new_password) {
    $conn = getConnection();
    $user_id = intval($user_id);
    
    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return false;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!password_verify($current_password, $user['password'])) {
        $conn->close();
        return false; // Current password is incorrect
    }
    
    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

// Product Functions
function getAllProducts() {
    $conn = getConnection();
    
    $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    $conn->close();
    
    return $products;
}

function getProductById($product_id) {
    $conn = getConnection();
    $product_id = intval($product_id);
    
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $product = null;
    if ($result->num_rows === 1) {
        $product = $result->fetch_assoc();
    }
    
    $stmt->close();
    $conn->close();
    
    return $product;
}

// Order Functions
function getUserOrders($user_id) {
    $conn = getConnection();
    $user_id = intval($user_id);
    
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $orders;
}

function getOrderItems($order_id) {
    $conn = getConnection();
    $order_id = intval($order_id);
    
    $stmt = $conn->prepare("
        SELECT oi.*, p.name, p.image_url 
        FROM order_items oi
        INNER JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $items;
}

// Utility Functions
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatPrice($price) {
    return '₱' . number_format($price, 2);
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}
?>