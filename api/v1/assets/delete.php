<?php
session_start();

require_once __DIR__ . '/../../../app/Controllers/AssetController.php';

$controller = new AssetController();
echo $controller->delete();
?>