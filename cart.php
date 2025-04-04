<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($user_id);
$cart_total = getCartTotal($user_id);
$page_title = 'Shopping Cart - ' . SITE_NAME;

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2>Shopping Cart</h2>
        
        <?php if(count($cart_items) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $item->image_url ?: 'assets/images/product-placeholder.jpg'; ?>" width="50" class="me-3" alt="<?php echo htmlspecialchars($item->name); ?>">
                                        <div><?php echo htmlspecialchars($item->name); ?></div>
                                    </div>
                                </td>
                                <td><?php echo CURRENCY . number_format($item->price, 2); ?></td>
                                <td>
                                    <input type="number" class="form-control quantity-input" value="<?php echo $item->quantity; ?>" min="1" data-cart-id="<?php echo $item->id; ?>" style="width: 70px;">
                                </td>
                                <td><?php echo CURRENCY . number_format($item->price * $item->quantity, 2); ?></td>
                                <td>
                                    <button class="btn btn-danger btn-sm remove-from-cart" data-cart-id="<?php echo $item->id; ?>">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td colspan="2"><?php echo CURRENCY . number_format($cart_total, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="products.php" class="btn btn-outline-secondary">Continue Shopping</a>
                <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Your cart is empty.</div>
            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
        <?php endif; ?>
    </div>
</div>

<script>
// Update quantity
document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function() {
        const cartId = this.dataset.cartId;
        const quantity = this.value;
        
        fetch('ajax/update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload(); // Refresh to update totals
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating quantity.');
        });
    });
});

// Remove item
document.querySelectorAll('.remove-from-cart').forEach(button => {
    button.addEventListener('click', function() {
        if(confirm('Are you sure you want to remove this item from your cart?')) {
            const cartId = this.dataset.cartId;
            
            fetch('ajax/remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cart_id=${cartId}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload(); // Refresh to update cart
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while removing item.');
            });
        }
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>