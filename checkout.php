<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($user_id);
$cart_total = getCartTotal($user_id);
$user = getUser($user_id);

if(count($cart_items) === 0) {
    redirect('cart.php');
}

$page_title = 'Checkout - ' . SITE_NAME;

// Process checkout form
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
    
    if(!in_array($payment_method, ['credit_card', 'paypal', 'stripe'])) {
        $error = 'Please select a valid payment method.';
    } else {
        // Process payment (simplified for this example)
        // In a real application, you would integrate with a payment gateway here
        
        // Create order
        $order_id = processCheckout($user_id, $payment_method);
        
        if($order_id) {
            // Redirect to order confirmation
            redirect('order-confirmation.php?id=' . $order_id);
        } else {
            $error = 'There was an error processing your order. Please try again.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <h2>Checkout</h2>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Shipping Information</h5>
            </div>
            <div class="card-body">
                <address>
                    <strong><?php echo htmlspecialchars($user->first_name . ' ' . $user->last_name); ?></strong><br>
                    <?php echo htmlspecialchars($user->address); ?><br>
                    <?php echo htmlspecialchars($user->city . ', ' . $user->state . ' ' . $user->zip_code); ?><br>
                    <?php echo htmlspecialchars($user->country); ?><br>
                    <abbr title="Phone">P:</abbr> <?php echo htmlspecialchars($user->phone); ?>
                </address>
                <a href="profile.php" class="btn btn-outline-secondary">Update Information</a>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Payment Method</h5>
            </div>
            <div class="card-body">
                <form method="post" id="checkout-form">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                        <label class="form-check-label" for="credit_card">
                            Credit Card
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                        <label class="form-check-label" for="paypal">
                            PayPal
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="payment_method" id="stripe" value="stripe">
                        <label class="form-check-label" for="stripe">
                            Stripe
                        </label>
                    </div>
                    
                    <!-- Credit card form (shown when credit card is selected) -->
                    <div id="credit-card-form" class="mt-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="card_number" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="card_number" placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="col-md-3">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" id="expiry_date" placeholder="MM/YY">
                            </div>
                            <div class="col-md-3">
                                <label for="cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cvv" placeholder="123">
                            </div>
                            <div class="col-12">
                                <label for="card_name" class="form-label">Name on Card</label>
                                <input type="text" class="form-control" id="card_name" placeholder="John Doe">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Order Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cart_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item->name); ?> × <?php echo $item->quantity; ?></td>
                                <td><?php echo CURRENCY . number_format($item->price * $item->quantity, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Subtotal</th>
                            <td><?php echo CURRENCY . number_format($cart_total, 2); ?></td>
                        </tr>
                        <tr>
                            <th>Shipping</th>
                            <td><?php echo CURRENCY; ?>0.00</td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <td><?php echo CURRENCY . number_format($cart_total, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
                
                <button type="submit" form="checkout-form" class="btn btn-primary w-100">Place Order</button>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide payment forms based on selection
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('credit-card-form').style.display = 
            this.value === 'credit_card' ? 'block' : 'none';
    });
});

// Initialize - hide credit card form if not selected
document.addEventListener('DOMContentLoaded', function() {
    const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
    if(selectedMethod !== 'credit_card') {
        document.getElementById('credit-card-form').style.display = 'none';
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>