<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Request.php';
require_once __DIR__ . '/../../../core/Model.php';
require_once __DIR__ . '/../../../app/Models/Asset.php';
require_once __DIR__ . '/../../../app/Controllers/AssetController.php';

$controller = new AssetController();
echo $controller->create();
?>