<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, PATCH');
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

// Accept PUT or PATCH requests
if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use PUT or PATCH.'
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
    
    // First, check if ticket exists and user has permission
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
            'message' => 'Ticket not found or you do not have permission to update it'
        ]);
        exit;
    }
    
    // Build update query dynamically based on provided fields
    $update_fields = [];
    $params = [];
    
    if (isset($input['title'])) {
        $update_fields[] = "title = :title";
        $params[':title'] = trim($input['title']);
    }
    
    if (isset($input['description'])) {
        $update_fields[] = "description = :description";
        $params[':description'] = trim($input['description']);
    }
    
    if (isset($input['status'])) {
        $allowed_statuses = ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'];
        if (!in_array($input['status'], $allowed_statuses)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid status. Allowed: ' . implode(', ', $allowed_statuses)
            ]);
            exit;
        }
        $update_fields[] = "status = :status";
        $params[':status'] = $input['status'];
    }
    
    if (isset($input['priority'])) {
        $allowed_priorities = ['Low', 'Medium', 'High', 'Critical'];
        if (!in_array($input['priority'], $allowed_priorities)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid priority. Allowed: ' . implode(', ', $allowed_priorities)
            ]);
            exit;
        }
        $update_fields[] = "priority = :priority";
        $params[':priority'] = $input['priority'];
    }
    
    if (isset($input['assigned_to'])) {
        $update_fields[] = "assigned_to = :assigned_to";
        $params[':assigned_to'] = $input['assigned_to'] ? intval($input['assigned_to']) : null;
    }
    
    if (empty($update_fields)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No fields to update'
        ]);
        exit;
    }
    
    // Always update the updated_at timestamp
    $update_fields[] = "updated_at = NOW()";
    
    $update_query = "UPDATE tickets SET " . implode(', ', $update_fields) . " WHERE id = :id";
    $params[':id'] = $ticket_id;
    
    $update_stmt = $db->prepare($update_query);
    
    foreach ($params as $key => $value) {
        $update_stmt->bindValue($key, $value);
    }
    
    if ($update_stmt->execute()) {
        // Get updated ticket
        $get_query = "SELECT t.*, u.name as creator_name, a.name as assignee_name
                      FROM tickets t
                      LEFT JOIN users u ON t.created_by = u.id
                      LEFT JOIN users a ON t.assigned_to = a.id
                      WHERE t.id = :id";
        $get_stmt = $db->prepare($get_query);
        $get_stmt->bindParam(':id', $ticket_id);
        $get_stmt->execute();
        $updated_ticket = $get_stmt->fetch(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Ticket updated successfully',
            'data' => $updated_ticket
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update ticket'
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