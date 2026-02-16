<?php
/**
 * Staff Update Ticket Route
 * POST /api/v1/tickets/staff-update.php
 */

require_once __DIR__ . '/../../../app/controllers/TicketController.php';

// Handle the request
$controller = new TicketController();
$controller->staffUpdate();
?>
