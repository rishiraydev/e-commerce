<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    redirect('../../login.php');
}

$page_title = 'Manage Products - ' . SITE_NAME;

// Get all products
$db = new Database();
$db->query("SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC");
$products = $db->resultSet();

// Delete product
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    
    $db->query("DELETE FROM products WHERE id = :id");
    $db->bind(':id', $product_id);
    
    if($db->execute()) {
        $_SESSION['message'] = 'Product deleted successfully.';
        redirect('products.php');
    } else {
        $error = 'Failed to delete product.';
    }
}

require_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'dashboard.php'; // Include sidebar ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Products</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add-product.php" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-plus"></i> Add Product
                    </a>
                </div>
            </div>
            
            <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $product): ?>
                            <tr>
                                <td><?php echo $product->id; ?></td>
                                <td>
                                    <img src="<?php echo SITE_URL . '/' . ($product->image_url ?: 'assets/images/product-placeholder.jpg'); ?>" 
                                         width="50" alt="<?php echo htmlspecialchars($product->name); ?>">
                                </td>
                                <td><?php echo htmlspecialchars($product->name); ?></td>
                                <td><?php echo $product->category_name ?: 'Uncategorized'; ?></td>
                                <td><?php echo CURRENCY . number_format($product->price, 2); ?></td>
                                <td><?php echo $product->stock_quantity; ?></td>
                                <td>
                                    <a href="edit-product.php?id=<?php echo $product->id; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="products.php?delete=<?php echo $product->id; ?>" class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Are you sure you want to delete this product?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php
require_once '../../includes/footer.php';
?>