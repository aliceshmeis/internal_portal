<?php
/**
 * Web Routes
 * Define all web application routes here
 */

// Authentication routes
$router->add('auth/login', 'AuthController', 'login');
$router->add('auth/google-callback', 'AuthController', 'googleCallback');
$router->add('auth/logout', 'AuthController', 'logout');

// Dashboard
$router->add('dashboard', 'DashboardController', 'index');
$router->add('', 'DashboardController', 'index'); // Default route

// Tickets (coming soon)
// $router->add('tickets', 'TicketController', 'index');
// $router->add('tickets/create', 'TicketController', 'create');

// Assets (coming soon)
// $router->add('assets', 'AssetController', 'index');

// Stock (coming soon)
// $router->add('stock', 'StockController', 'index');

// Purchase Orders (coming soon)
// $router->add('purchase-orders', 'PurchaseOrderController', 'index');
?>