<?php
require_once __DIR__ . '/../../../app/Controllers/UserController.php';

$controller = new UserController();
echo $controller->create();
?>