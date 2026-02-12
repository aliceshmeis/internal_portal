<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
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

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use GET.'
    ]);
    exit;
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Ticket ID is required'
    ]);
    exit;
}

$ticket_id = intval($_GET['id']);

try {
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    $user_role = $_SESSION['role'];
    $user_campus_id = $_SESSION['campus_id'];
    
    // Build query based on role
    if ($user_role === 'Admin') {
        // Admin can see any ticket
        $query = "SELECT t.*, u.name as creator_name, u.email as creator_email,
                  a.name as assignee_name, a.email as assignee_email,
                  c.campus_name
                  FROM tickets t
                  LEFT JOIN users u ON t.created_by = u.id
                  LEFT JOIN users a ON t.assigned_to = a.id
                  LEFT JOIN campuses c ON t.campus_id = c.id
                  WHERE t.id = :id";
    } else {
        // Staff can only see tickets from their campus
        $query = "SELECT t.*, u.name as creator_name, u.email as creator_email,
                  a.name as assignee_name, a.email as assignee_email,
                  c.campus_name
                  FROM tickets t
                  LEFT JOIN users u ON t.created_by = u.id
                  LEFT JOIN users a ON t.assigned_to = a.id
                  LEFT JOIN campuses c ON t.campus_id = c.id
                  WHERE t.id = :id AND t.campus_id = :campus_id";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $ticket_id);
    
    if ($user_role !== 'Admin') {
        $stmt->bindParam(':campus_id', $user_campus_id);
    }
    
    $stmt->execute();
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ticket) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Ticket not found or you do not have permission to view it'
        ]);
        exit;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Ticket retrieved successfully',
        'data' => $ticket
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
}
?>