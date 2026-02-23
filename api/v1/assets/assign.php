<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../../app/Controllers/AssetController.php';

$controller = new AssetController();
echo $controller->assign();
?>