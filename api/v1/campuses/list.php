<?php
require_once __DIR__ . '/../../../app/Controllers/CampusController.php';

$controller = new CampusController();
echo $controller->list();
?>