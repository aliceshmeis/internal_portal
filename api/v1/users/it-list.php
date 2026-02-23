<?php
session_start();
error_reporting(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../../core/Model.php';
require_once __DIR__ . '/../../../app/Controllers/UserController.php';

$controller = new UserController();
echo $controller->itList();
?>