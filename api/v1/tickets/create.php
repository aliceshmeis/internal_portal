<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Request.php';
require_once __DIR__ . '/../../../core/Model.php';
require_once __DIR__ . '/../../../app/Models/Ticket.php';
require_once __DIR__ . '/../../../app/Controllers/TicketController.php';

$controller = new TicketController();
echo $controller->create();
?>