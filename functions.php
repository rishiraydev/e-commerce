<?php
require_once 'db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect function
function redirect($page) {
    header('Location: ' . $page);
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true);
}

// Get all products
function getProducts($limit = null, $category_id = null) {
    $db = new Database();
    $sql = "SELECT * FROM products";
    $params = [];
    
    if ($category_id) {
        $sql .= " WHERE category_id = :category_id";
        $params[':category_id'] = $category_id;
    }
    
    if ($limit) {
        $sql .= " LIMIT :limit";
        $params[':limit'] = $limit;
    }
    
    $db->query($sql);
    
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    
    return $db->resultSet();
}

// Get single product
function getProduct($id) {
    $db = new Database();
    $db->query("SELECT * FROM products WHERE id = :id");
    $db->bind(':id', $id);
    return $db->single();
}

// Get all categories
function getCategories() {
    $db = new Database();
    $db->query("SELECT * FROM categories");
    return $db->resultSet();
}

// Get user by ID
function getUser($id) {
    $db = new Database();
    $db->query("SELECT * FROM users WHERE id = :id");
    $db->bind(':id', $id);
    return $db->single();
}

// Add to cart
function addToCart($user_id, $product_id, $quantity = 1) {
    $db = new Database();
    
    // Check if item already in cart
    $db->query("SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id");
    $db->bind(':user_id', $user_id);
    $db->bind(':product_id', $product_id);
    $item = $db->single();
    
    if ($item) {
        // Update quantity
        $new_quantity = $item->quantity + $quantity;
        $db->query("UPDATE cart SET quantity = :quantity WHERE id = :id");
        $db->bind(':quantity', $new_quantity);
        $db->bind(':id', $item->id);
        return $db->execute();
    }
    
    // Add new item
    $db->query("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
    $db->bind(':user_id', $user_id);
    $db->bind(':product_id', $product_id);
    $db->bind(':quantity', $quantity);
    return $db->execute();
}

// Get cart items
function getCartItems($user_id) {
    $db = new Database();
    $db->query("SELECT cart.*, products.name, products.price, products.image_url 
                FROM cart 
                JOIN products ON cart.product_id = products.id 
                WHERE cart.user_id = :user_id");
    $db->bind(':user_id', $user_id);
    return $db->resultSet();
}

// Get cart total
function getCartTotal($user_id) {
    $items = getCartItems($user_id);
    $total = 0;
    
    foreach ($items as $item) {
        $total += $item->price * $item->quantity;
    }
    
    return $total;
}

// Remove from cart
function removeFromCart($cart_id) {
    $db = new Database();
    $db->query("DELETE FROM cart WHERE id = :id");
    $db->bind(':id', $cart_id);
    return $db->execute();
}

// Process checkout
function processCheckout($user_id, $payment_method) {
    $db = new Database();
    
    try {
        // Begin transaction
        $db->beginTransaction();
        
        // Get cart items
        $cart_items = getCartItems($user_id);
        $total = getCartTotal($user_id);
        
        // Create order
        $db->query("INSERT INTO orders (user_id, total_amount, payment_method, payment_status) 
                    VALUES (:user_id, :total_amount, :payment_method, 'pending')");
        $db->bind(':user_id', $user_id);
        $db->bind(':total_amount', $total);
        $db->bind(':payment_method', $payment_method);
        $db->execute();
        
        $order_id = $db->lastInsertId();
        
        // Add order items
        foreach ($cart_items as $item) {
            $db->query("INSERT INTO order_items (order_id, product_id, quantity, price) 
                        VALUES (:order_id, :product_id, :quantity, :price)");
            $db->bind(':order_id', $order_id);
            $db->bind(':product_id', $item->product_id);
            $db->bind(':quantity', $item->quantity);
            $db->bind(':price', $item->price);
            $db->execute();
            
            // Update product stock
            $db->query("UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :id");
            $db->bind(':quantity', $item->quantity);
            $db->bind(':id', $item->product_id);
            $db->execute();
        }
        
        // Clear cart
        $db->query("DELETE FROM cart WHERE user_id = :user_id");
        $db->bind(':user_id', $user_id);
        $db->execute();
        
        // Commit transaction
        $db->commit();
        return $order_id;
    } catch (PDOException $e) {
        // Rollback on error
        $db->rollBack();
        error_log("Checkout error: " . $e->getMessage());
        return false;
    }
}

// Get user orders
function getUserOrders($user_id) {
    $db = new Database();
    $db->query("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
    $db->bind(':user_id', $user_id);
    return $db->resultSet();
}

// Get order details
function getOrderDetails($order_id) {
    $db = new Database();
    $db->query("SELECT oi.*, p.name, p.image_url 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = :order_id");
    $db->bind(':order_id', $order_id);
    return $db->resultSet();
}
?>