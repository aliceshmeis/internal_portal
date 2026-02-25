<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Model.php';
require_once __DIR__ . '/../../../app/Models/Ticket.php';

if (!Auth::check()) {
    echo Response::unauthorized();
    exit;
}

$tickets = Ticket::getAssignedTo(Auth::userId());
echo Response::success('Assigned tickets retrieved', $tickets);
?>