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
    
    if($cart_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart item.']);
        exit;
    }
    
    // Verify cart item belongs to user
    $user_id = $_SESSION['user_id'];
    $db = new Database();
    $db->query("SELECT id FROM cart WHERE id = :cart_id AND user_id = :user_id");
    $db->bind(':cart_id', $cart_id);
    $db->bind(':user_id', $user_id);
    $item = $db->single();
    
    if(!$item) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
        exit;
    }
    
    // Remove item
    $db->query("DELETE FROM cart WHERE id = :id");
    $db->bind(':id', $cart_id);
    
    if($db->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item removed from cart.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove item.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>