<?php
session_start();

// Stats endpoint for dashboard
require_once __DIR__ . '/../../../app/controllers/DashboardController.php';

$controller = new DashboardController();
echo $controller->getStats(); // ← ADD "echo" HERE!
?>