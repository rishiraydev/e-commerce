<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if(!isset($_GET['id'])) {
    redirect('products.php');
}

$product_id = intval($_GET['id']);
$product = getProduct($product_id);

if(!$product) {
    redirect('products.php');
}

$page_title = $product->name . ' - ' . SITE_NAME;

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-6">
        <img src="<?php echo $product->image_url ?: 'assets/images/product-placeholder.jpg'; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($product->name); ?>">
    </div>
    <div class="col-md-6">
        <h1><?php echo htmlspecialchars($product->name); ?></h1>
        <p class="lead"><?php echo CURRENCY . number_format($product->price, 2); ?></p>
        
        <?php if($product->stock_quantity > 0): ?>
            <p class="text-success">In Stock (<?php echo $product->stock_quantity; ?> available)</p>
        <?php else: ?>
            <p class="text-danger">Out of Stock</p>
        <?php endif; ?>
        
        <p><?php echo nl2br(htmlspecialchars($product->description)); ?></p>
        
        <?php if(isLoggedIn() && $product->stock_quantity > 0): ?>
            <form class="row g-3" id="add-to-cart-form">
                <div class="col-auto">
                    <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product->stock_quantity; ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                </div>
                <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
            </form>
        <?php elseif(!isLoggedIn()): ?>
            <p><a href="login.php" class="btn btn-primary">Login to Purchase</a></p>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <h3>Product Details</h3>
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th>Category</th>
                    <td>
                        <?php 
                        if($product->category_id) {
                            $db = new Database();
                            $db->query("SELECT name FROM categories WHERE id = :id");
                            $db->bind(':id', $product->category_id);
                            $category = $db->single();
                            echo $category ? htmlspecialchars($category->name) : 'Uncategorized';
                        } else {
                            echo 'Uncategorized';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Availability</th>
                    <td><?php echo $product->stock_quantity > 0 ? 'In Stock' : 'Out of Stock'; ?></td>
                </tr>
                <tr>
                    <th>Added On</th>
                    <td><?php echo date('F j, Y', strtotime($product->created_at)); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const quantity = formData.get('quantity');
    const productId = formData.get('product_id');
    
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Product added to cart!');
            // Update cart count in navbar
            const cartCount = document.querySelector('.badge.bg-primary');
            if(cartCount) {
                cartCount.textContent = parseInt(cartCount.textContent) + parseInt(quantity);
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding to cart.');
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>