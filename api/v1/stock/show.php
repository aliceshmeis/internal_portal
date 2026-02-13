<?php
session_start();

require_once __DIR__ . '/../../../app/Controllers/StockController.php';

$controller = new StockController();
echo $controller->show();
?>