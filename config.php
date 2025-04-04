<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ecommerce_platform');

// Site configuration
define('SITE_NAME', 'E-Commerce Platform');
define('SITE_URL', 'http://localhost/ecommerce-platform');
define('CURRENCY', '$');

// Stripe API configuration (for payments)
define('STRIPE_API_KEY', 'your_stripe_api_key');
define('STRIPE_PUBLISHABLE_KEY', 'your_stripe_publishable_key');

// Start session
if(!session_id()) {
    session_start();
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>