<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to update cart.']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if($cart_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart item.']);
        exit;
    }
    
    if($quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1.']);
        exit;
    }
    
    // Verify cart item belongs to user
    $user_id = $_SESSION['user_id'];
    $db = new Database();
    $db->query("SELECT c.id, p.stock_quantity 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.id = :cart_id AND c.user_id = :user_id");
    $db->bind(':cart_id', $cart_id);
    $db->bind(':user_id', $user_id);
    $item = $db->single();
    
    if(!$item) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
        exit;
    }
    
    if($item->stock_quantity < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available.']);
        exit;
    }
    
    // Update quantity
    $db->query("UPDATE cart SET quantity = :quantity WHERE id = :id");
    $db->bind(':quantity', $quantity);
    $db->bind(':id', $cart_id);
    
    if($db->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cart updated.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>