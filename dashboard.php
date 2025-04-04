<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    redirect('../../login.php');
}

$page_title = 'Admin Dashboard - ' . SITE_NAME;

// Get stats for dashboard
$db = new Database();
$db->query("SELECT COUNT(*) as total_products FROM products");
$total_products = $db->single()->total_products;

$db->query("SELECT COUNT(*) as total_orders FROM orders");
$total_orders = $db->single()->total_orders;

$db->query("SELECT COUNT(*) as total_customers FROM users WHERE is_admin = 0");
$total_customers = $db->single()->total_customers;

$db->query("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'completed'");
$revenue = $db->single()->revenue ?: 0;

// Get recent orders
$db->query("SELECT o.*, u.username 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC 
            LIMIT 5");
$recent_orders = $db->resultSet();

require_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-box"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-list"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-shopping-cart"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customers.php">
                            <i class="fas fa-users"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
            </div>

            <!-- Stats cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Products</h5>
                            <p class="card-text display-4"><?php echo $total_products; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Orders</h5>
                            <p class="card-text display-4"><?php echo $total_orders; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Customers</h5>
                            <p class="card-text display-4"><?php echo $total_customers; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Revenue</h5>
                            <p class="card-text display-4"><?php echo CURRENCY . number_format($revenue, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent orders -->
            <div class="card">
                <div class="card-header">
                    <h5>Recent Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order->id; ?></td>
                                        <td><?php echo htmlspecialchars($order->username); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($order->created_at)); ?></td>
                                        <td><?php echo CURRENCY . number_format($order->total_amount, 2); ?></td>
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
                                        <td>
                                            <a href="order-details.php?id=<?php echo $order->id; ?>" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
require_once '../../includes/footer.php';
?>