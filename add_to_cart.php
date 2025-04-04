<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart.']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product.']);
        exit;
    }
    
    if($quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1.']);
        exit;
    }
    
    // Check product exists and has enough stock
    $db = new Database();
    $db->query("SELECT id, stock_quantity FROM products WHERE id = :id");
    $db->bind(':id', $product_id);
    $product = $db->single();
    
    if(!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit;
    }
    
    if($product->stock_quantity < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available.']);
        exit;
    }
    
    // Add to cart
    $user_id = $_SESSION['user_id'];
    if(addToCart($user_id, $product_id, $quantity)) {
        echo json_encode(['success' => true, 'message' => 'Product added to cart.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to cart.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>