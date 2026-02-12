<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$errors = [];

if (empty($input['title'])) {
    $errors[] = 'Title is required';
}

if (empty($input['description'])) {
    $errors[] = 'Description is required';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $errors
    ]);
    exit;
}

// Get data from input
$title = trim($input['title']);
$description = trim($input['description']);
$priority = isset($input['priority']) ? $input['priority'] : 'Medium';
$campus_id = $_SESSION['campus_id']; // User's campus
$created_by = $_SESSION['user_id'];

// Validate priority
$allowed_priorities = ['Low', 'Medium', 'High', 'Critical'];
if (!in_array($priority, $allowed_priorities)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid priority. Allowed values: Low, Medium, High, Critical'
    ]);
    exit;
}

try {
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Generate unique ticket number
    $ticket_number = 'TKT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Insert ticket
    $query = "INSERT INTO tickets 
              (ticket_number, campus_id, title, description, status, priority, created_by, created_at) 
              VALUES 
              (:ticket_number, :campus_id, :title, :description, 'Open', :priority, :created_by, NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':ticket_number', $ticket_number);
    $stmt->bindParam(':campus_id', $campus_id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':priority', $priority);
    $stmt->bindParam(':created_by', $created_by);
    
    if ($stmt->execute()) {
        $ticket_id = $db->lastInsertId();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Ticket created successfully',
            'data' => [
                'ticket_id' => $ticket_id,
                'ticket_number' => $ticket_number,
                'title' => $title,
                'status' => 'Open',
                'priority' => $priority
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create ticket',
            'error' => $stmt->errorInfo()
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