<?php
session_start();

require_once __DIR__ . '/../../../../app/Controllers/TicketController.php';

$controller = new TicketController();
echo $controller->getComments();
?>