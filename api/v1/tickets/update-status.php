<?php
/**
 * API: Update ticket status
 * File: api/v1/tickets/update-status.php
 * Method: POST
 * Body (JSON): { id: int, status: string }
 * Response: { success, message }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true);
$id     = (int)($body['id']    ?? 0);
$status = trim($body['status'] ?? '');

if (!$id || !$status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing id or status']);
    exit;
}

require_once __DIR__ . '/../../../app/controllers/ITTicketController.php';

try {
    $controller = new ITTicketController();
    $result     = $controller->updateStatus($id, $status, (int)$_SESSION['user_id']);

    http_response_code($result['code'] ?? 200);
    echo json_encode(['success' => $result['success'], 'message' => $result['message']]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}