<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PATCH');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Include database connection
require_once __DIR__ . '/../../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login first.'
    ]);
    exit;
}

// Accept POST or PATCH requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST or PATCH.'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate ticket ID
if (empty($input['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Ticket ID is required'
    ]);
    exit;
}

$ticket_id = intval($input['id']);

try {
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    $user_role = $_SESSION['role'];
    $user_campus_id = $_SESSION['campus_id'];
    
    // Check if ticket exists and user has permission
    if ($user_role === 'Admin') {
        $check_query = "SELECT * FROM tickets WHERE id = :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $ticket_id);
    } else {
        $check_query = "SELECT * FROM tickets WHERE id = :id AND campus_id = :campus_id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $ticket_id);
        $check_stmt->bindParam(':campus_id', $user_campus_id);
    }
    
    $check_stmt->execute();
    $existing_ticket = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing_ticket) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Ticket not found or you do not have permission to close it'
        ]);
        exit;
    }
    
    // Check if already closed
    if ($existing_ticket['status'] === 'Closed') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Ticket is already closed'
        ]);
        exit;
    }
    
    // Close the ticket
    $close_query = "UPDATE tickets 
                    SET status = 'Closed', 
                        closed_at = NOW(),
                        updated_at = NOW()
                    WHERE id = :id";
    
    $close_stmt = $db->prepare($close_query);
    $close_stmt->bindParam(':id', $ticket_id);
    
    if ($close_stmt->execute()) {
        // Get updated ticket
        $get_query = "SELECT t.*, u.name as creator_name, a.name as assignee_name
                      FROM tickets t
                      LEFT JOIN users u ON t.created_by = u.id
                      LEFT JOIN users a ON t.assigned_to = a.id
                      WHERE t.id = :id";
        $get_stmt = $db->prepare($get_query);
        $get_stmt->bindParam(':id', $ticket_id);
        $get_stmt->execute();
        $closed_ticket = $get_stmt->fetch(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Ticket closed successfully',
            'data' => $closed_ticket
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to close ticket'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
}
?>