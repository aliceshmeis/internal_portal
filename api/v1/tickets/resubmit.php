<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Request.php';
require_once __DIR__ . '/../../../app/models/Ticket.php';
require_once __DIR__ . '/../../../app/controllers/TicketController.php';

$controller = new TicketController();
echo $controller->resubmit();