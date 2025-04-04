<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    redirect('../../login.php');
}

$page_title = 'Add Product - ' . SITE_NAME;

// Get categories for dropdown
$categories = getCategories();

// Process form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $stock_quantity = isset($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : 0;
    
    // Validate inputs
    $errors = [];
    
    if(empty($name)) {
        $errors[] = 'Product name is required.';
    }
    
    if($price <= 0) {
        $errors[] = 'Price must be greater than 0.';
    }
    
    if($stock_quantity < 0) {
        $errors[] = 'Stock quantity cannot be negative.';
    }
    
    // Handle file upload
    $image_url = null;
    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../assets/images/products/';
        $file_name = time() . '_' . basename($_FILES['image']['name']);
        $target_path = $upload_dir . $file_name;
        
        // Check file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if(in_array($file_type, $allowed_types)) {
            if(move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_url = 'assets/images/products/' . $file_name;
            } else {
                $errors[] = 'Failed to upload image.';
            }
        } else {
            $errors[] = 'Only JPG, PNG, and GIF images are allowed.';
        }
    }
    
    // If no errors, save product
    if(empty($errors)) {
        $db = new Database();
        $db->query("INSERT INTO products (name, description, price, category_id, stock_quantity, image_url) 
                    VALUES (:name, :description, :price, :category_id, :stock_quantity, :image_url)");
        
        $db->bind(':name', $name);
        $db->bind(':description', $description);
        $db->bind(':price', $price);
        $db->bind(':category_id', $category_id ?: null);
        $db->bind(':stock_quantity', $stock_quantity);
        $db->bind(':image_url', $image_url);
        
        if($db->execute()) {
            $_SESSION['message'] = 'Product added successfully.';
            redirect('products.php');
        } else {
            $errors[] = 'Failed to add product. Please try again.';
        }
    }
}

require_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'dashboard.php'; // Include sidebar ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Add Product</h1>
            </div>
            
            <?php if(!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label for="stock_quantity" class="form-label">Stock Quantity</label>
                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">-- Select Category --</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo $category->id; ?>"><?php echo htmlspecialchars($category->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Product Image</label>
                    <input type="file" class="form-control" id="image" name="image">
                </div>
                <button type="submit" class="btn btn-primary">Add Product</button>
                <a href="products.php" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </main>
    </div>
</div>

<?php
require_once '../../includes/footer.php';
?>