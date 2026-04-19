<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Request.php';
require_once __DIR__ . '/../../../app/Models/Supplier.php';
require_once __DIR__ . '/../../../app/controllers/SupplierController.php';

$controller = new SupplierController();
echo $controller->update();