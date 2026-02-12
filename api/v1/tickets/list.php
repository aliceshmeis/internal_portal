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

try {
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Get filter parameters
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $priority = isset($_GET['priority']) ? $_GET['priority'] : null;
    
    // Build query based on user role
    $user_role = $_SESSION['role'];
    $user_campus_id = $_SESSION['campus_id'];
    
    if ($user_role === 'Admin') {
        // Admin sees ALL tickets
        $query = "SELECT t.*, u.name as creator_name, a.name as assignee_name, c.campus_name
                  FROM tickets t
                  LEFT JOIN users u ON t.created_by = u.id
                  LEFT JOIN users a ON t.assigned_to = a.id
                  LEFT JOIN campuses c ON t.campus_id = c.id
                  WHERE 1=1";
    } else {
        // Staff/others see only their campus tickets
        $query = "SELECT t.*, u.name as creator_name, a.name as assignee_name, c.campus_name
                  FROM tickets t
                  LEFT JOIN users u ON t.created_by = u.id
                  LEFT JOIN users a ON t.assigned_to = a.id
                  LEFT JOIN campuses c ON t.campus_id = c.id
                  WHERE t.campus_id = :campus_id";
    }
    
    // Add filters if provided
    if ($status) {
        $query .= " AND t.status = :status";
    }
    
    if ($priority) {
        $query .= " AND t.priority = :priority";
    }
    
    $query .= " ORDER BY t.created_at DESC";
    
    $stmt = $db->prepare($query);
    
    // Bind campus_id for non-admin users
    if ($user_role !== 'Admin') {
        $stmt->bindParam(':campus_id', $user_campus_id);
    }
    
    // Bind filters
    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    
    if ($priority) {
        $stmt->bindParam(':priority', $priority);
    }
    
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Tickets retrieved successfully',
        'count' => count($tickets),
        'data' => $tickets
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