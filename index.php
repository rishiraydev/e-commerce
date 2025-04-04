<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Home - ' . SITE_NAME;
$products = getProducts(8); // Get 8 featured products

require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner rounded">
                <div class="carousel-item active">
                    <img src="assets/images/banner1.jpg" class="d-block w-100" alt="Banner 1">
                </div>
                <div class="carousel-item">
                    <img src="assets/images/banner2.jpg" class="d-block w-100" alt="Banner 2">
                </div>
                <div class="carousel-item">
                    <img src="assets/images/banner3.jpg" class="d-block w-100" alt="Banner 3">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>
</div>

<h2 class="mb-4">Featured Products</h2>
<div class="row">
    <?php foreach($products as $product): ?>
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <img src="<?php echo $product->image_url ?: 'assets/images/product-placeholder.jpg'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product->name); ?>">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($product->name); ?></h5>
                <p class="card-text"><?php echo CURRENCY . number_format($product->price, 2); ?></p>
                <a href="product-details.php?id=<?php echo $product->id; ?>" class="btn btn-primary">View Details</a>
                <?php if(isLoggedIn()): ?>
                    <button class="btn btn-outline-secondary add-to-cart" data-product-id="<?php echo $product->id; ?>">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php
require_once 'includes/footer.php';
?>