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
        'message' => 'Stock ID is required'
    ]);
    exit;
}

$stock_id = intval($_GET['id']);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $user_role = $_SESSION['role'];
    $user_campus_id = $_SESSION['campus_id'];
    
    // Build query based on role
    if ($user_role === 'Admin') {
        $query = "SELECT s.*, c.campus_name,
                  CASE WHEN s.quantity <= s.minimum_threshold THEN 1 ELSE 0 END as is_low_stock
                  FROM stock s
                  LEFT JOIN campuses c ON s.campus_id = c.id
                  WHERE s.id = :id";
    } else {
        $query = "SELECT s.*, c.campus_name,
                  CASE WHEN s.quantity <= s.minimum_threshold THEN 1 ELSE 0 END as is_low_stock
                  FROM stock s
                  LEFT JOIN campuses c ON s.campus_id = c.id
                  WHERE s.id = :id AND s.campus_id = :campus_id";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $stock_id);
    
    if ($user_role !== 'Admin') {
        $stmt->bindParam(':campus_id', $user_campus_id);
    }
    
    $stmt->execute();
    $stock_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stock_item) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Stock item not found or you do not have permission to view it'
        ]);
        exit;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Stock item retrieved successfully',
        'alert' => $stock_item['is_low_stock'] == 1 ? 'WARNING: This item is low on stock!' : null,
        'data' => $stock_item
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