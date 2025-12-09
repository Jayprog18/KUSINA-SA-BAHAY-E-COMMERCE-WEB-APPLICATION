<?php
// add_to_cart.php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/cart_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        exit;
    }
    
    if (addToCart($_SESSION['user_id'], $product_id, $quantity)) {
        $cart_count = getCartCount($_SESSION['user_id']);
        echo json_encode([
            'success' => true, 
            'message' => 'Product added to cart!',
            'cart_count' => $cart_count
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>