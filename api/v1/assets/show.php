<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

require_once __DIR__ . '/../../../config/database.php';

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login first.'
    ]);
    exit;
}

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
        'message' => 'Asset ID is required'
    ]);
    exit;
}

$asset_id = intval($_GET['id']);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $user_role = $_SESSION['role'];
    $user_campus_id = $_SESSION['campus_id'];
    
    // Build query based on role
    if ($user_role === 'Admin') {
        $query = "SELECT a.*, c.campus_name, u.name as assigned_user_name, u.email as assigned_user_email
                  FROM assets a
                  LEFT JOIN campuses c ON a.campus_id = c.id
                  LEFT JOIN users u ON a.assigned_to = u.id
                  WHERE a.id = :id";
    } else {
        $query = "SELECT a.*, c.campus_name, u.name as assigned_user_name, u.email as assigned_user_email
                  FROM assets a
                  LEFT JOIN campuses c ON a.campus_id = c.id
                  LEFT JOIN users u ON a.assigned_to = u.id
                  WHERE a.id = :id AND a.campus_id = :campus_id";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $asset_id);
    
    if ($user_role !== 'Admin') {
        $stmt->bindParam(':campus_id', $user_campus_id);
    }
    
    $stmt->execute();
    $asset = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$asset) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Asset not found or you do not have permission to view it'
        ]);
        exit;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Asset retrieved successfully',
        'data' => $asset
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