<?php
session_start();

require_once __DIR__ . '/../../../app/Controllers/PurchaseOrderController.php';

$controller = new PurchaseOrderController();
echo $controller->cancel();
?>