<?php
session_start();

// Prevent any output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Simple test response
echo json_encode([
    'success' => true,
    'data' => [
        'tickets' => [
            'open' => 5,
            'in_progress' => 2,
            'resolved_month' => 8
        ],
        'assets' => [
            'count' => 3
        ],
        'my_tickets' => [],
        'my_assets' => []
    ]
]);
exit;
?>