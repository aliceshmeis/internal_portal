<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../../app/Controllers/PurchaseOrderController.php';

$controller = new PurchaseOrderController();
echo $controller->approve();
?>