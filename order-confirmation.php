<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('index.php');
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Get order details
$db = new Database();
$db->query("SELECT o.*, u.username 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = :id AND o.user_id = :user_id");
$db->bind(':id', $order_id);
$db->bind(':user_id', $user_id);
$order = $db->single();

if(!$order) {
    redirect('index.php');
}

// Get order items
$db->query("SELECT oi.*, p.name, p.image_url 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = :order_id");
$db->bind(':order_id', $order_id);
$order_items = $db->resultSet();

$page_title = 'Order Confirmation - ' . SITE_NAME;

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0">Order Confirmation</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <h4 class="alert-heading">Thank you for your order!</h4>
                    <p>Your order #<?php echo $order->id; ?> has been placed successfully.</p>
                    <p>A confirmation email has been sent to your registered email address.</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5>Order Summary</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th>Order Number</th>
                                <td>#<?php echo $order->id; ?></td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td><?php echo date('F j, Y, g:i a', strtotime($order->created_at)); ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge bg-<?php 
                                        switch($order->status) {
                                            case 'pending': echo 'warning'; break;
                                            case 'processing': echo 'info'; break;
                                            case 'shipped': echo 'primary'; break;
                                            case 'delivered': echo 'success'; break;
                                            case 'cancelled': echo 'danger'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>">
                                        <?php echo ucfirst($order->status); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Payment Method</th>
                                <td><?php echo ucwords(str_replace('_', ' ', $order->payment_method)); ?></td>
                            </tr>
                            <tr>
                                <th>Payment Status</th>
                                <td>
                                    <span class="badge bg-<?php 
                                        switch($order->payment_status) {
                                            case 'pending': echo 'warning'; break;
                                            case 'completed': echo 'success'; break;
                                            case 'failed': echo 'danger'; break;
                                            case 'refunded': echo 'info'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>">
                                        <?php echo ucfirst($order->payment_status); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Total Amount</th>
                                <td><?php echo CURRENCY . number_format($order->total_amount, 2); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Shipping Information</h5>
                        <address>
                            <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong><br>
                            <?php echo nl2br(htmlspecialchars($order->shipping_address)); ?>
                        </address>
                    </div>
                </div>
                
                <h5 class="mt-4">Order Items</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo $item->image_url ?: 'assets/images/product-placeholder.jpg'; ?>" 
                                                 width="50" class="me-3" alt="<?php echo htmlspecialchars($item->name); ?>">
                                            <div><?php echo htmlspecialchars($item->name); ?></div>
                                        </div>
                                    </td>
                                    <td><?php echo CURRENCY . number_format($item->price, 2); ?></td>
                                    <td><?php echo $item->quantity; ?></td>
                                    <td><?php echo CURRENCY . number_format($item->price * $item->quantity, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Subtotal:</th>
                                <td><?php echo CURRENCY . number_format($order->total_amount, 2); ?></td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Shipping:</th>
                                <td><?php echo CURRENCY; ?>0.00</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <td><?php echo CURRENCY . number_format($order->total_amount, 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="products.php" class="btn btn-outline-primary">Continue Shopping</a>
                    <a href="orders.php" class="btn btn-primary">View All Orders</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>