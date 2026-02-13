<?php
// Stats endpoint for dashboard
require_once __DIR__ . '/../../../app/controllers/DashboardController.php';

$controller = new DashboardController();
$controller->getStats();
?>