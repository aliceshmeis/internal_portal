<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
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

// Only Admin can delete stock items
if ($_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden. Only Admin can delete stock items.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use DELETE or POST.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate stock ID
if (empty($input['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Stock ID is required'
    ]);
    exit;
}

$stock_id = intval($input['id']);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if stock exists
    $check_query = "SELECT * FROM stock WHERE id = :id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':id', $stock_id);
    $check_stmt->execute();
    $existing_stock = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing_stock) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Stock item not found'
        ]);
        exit;
    }
    
    // Hard delete (actually remove from database)
    $delete_query = "DELETE FROM stock WHERE id = :id";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':id', $stock_id);
    
    if ($delete_stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Stock item deleted successfully',
            'data' => [
                'stock_id' => $stock_id,
                'item_name' => $existing_stock['item_name'],
                'deleted' => true
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete stock item'
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