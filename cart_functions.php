<?php
// includes/cart_functions.php

function addToCart($user_id, $product_id, $quantity = 1) {
    $conn = getConnection();
    
    // Validate inputs
    $user_id = intval($user_id);
    $product_id = intval($product_id);
    $quantity = intval($quantity);
    
    if ($user_id <= 0 || $product_id <= 0 || $quantity <= 0) {
        return false;
    }
    
    // Check if product exists and is available
    $stmt = $conn->prepare("SELECT id, stock FROM products WHERE id = ? AND stock > 0");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return false;
    }
    $stmt->close();
    
    // Check if item already exists in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing cart item
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;
        $stmt->close();
        
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    } else {
        // Insert new cart item
        $stmt->close();
        
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $success = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $success;
    }
}

function getCartCount($user_id) {
    $conn = getConnection();
    $user_id = intval($user_id);
    
    $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) as total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $row['total'];
}

function getCartItems($user_id) {
    $conn = getConnection();
    $user_id = intval($user_id);
    
    $stmt = $conn->prepare("
        SELECT c.id, c.quantity, p.id as product_id, p.name, p.description, 
               p.price, p.image_url 
        FROM cart c
        INNER JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.id DESC
    ");
    $stmt->bind_param("i", $user_id);
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

function updateCartQuantity($cart_id, $user_id, $quantity) {
    $conn = getConnection();
    
    $cart_id = intval($cart_id);
    $user_id = intval($user_id);
    $quantity = intval($quantity);
    
    if ($quantity <= 0) {
        return removeFromCart($cart_id, $user_id);
    }
    
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

function removeFromCart($cart_id, $user_id) {
    $conn = getConnection();
    
    $cart_id = intval($cart_id);
    $user_id = intval($user_id);
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

function clearCart($user_id) {
    $conn = getConnection();
    $user_id = intval($user_id);
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

function getCartTotal($user_id) {
    $conn = getConnection();
    $user_id = intval($user_id);
    
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(c.quantity * p.price), 0) as total
        FROM cart c
        INNER JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $row['total'];
}
?>