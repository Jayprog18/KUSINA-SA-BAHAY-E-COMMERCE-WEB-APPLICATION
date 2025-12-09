<?php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/cart_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    
    if ($cart_id <= 0 || $quantity < 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }
    
    if (updateCartQuantity($cart_id, $quantity, $_SESSION['user_id'])) {
        echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>