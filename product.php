<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Products - ' . SITE_NAME;
$category_id = isset($_GET['category']) ? intval($_GET['category']) : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get products with optional category filter and search
$db = new Database();
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if($category_id) {
    $sql .= " AND category_id = :category_id";
    $params[':category_id'] = $category_id;
}

if($search) {
    $sql .= " AND (name LIKE :search OR description LIKE :search)";
    $params[':search'] = "%$search%";
}

$db->query($sql);
foreach($params as $key => $value) {
    $db->bind($key, $value);
}
$products = $db->resultSet();

// Get categories for sidebar
$categories = getCategories();

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Categories</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><a href="products.php">All Categories</a></li>
                    <?php foreach($categories as $category): ?>
                        <li><a href="products.php?category=<?php echo $category->id; ?>"><?php echo htmlspecialchars($category->name); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="row mb-4">
            <div class="col-12">
                <form action="products.php" method="get" class="row g-3">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="row">
            <?php if(count($products) > 0): ?>
                <?php foreach($products as $product): ?>
                    <div class="col-md-4 mb-4">
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
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">No products found.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>