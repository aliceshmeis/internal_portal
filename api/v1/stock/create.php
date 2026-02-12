<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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

// Only Admin and Asset Manager can create stock items
if (!in_array($_SESSION['role'], ['Admin', 'Asset Manager'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden. Only Admin and Asset Manager can create stock items.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$errors = [];

if (empty($input['item_name'])) {
    $errors[] = 'Item name is required';
}

if (!isset($input['quantity']) || !is_numeric($input['quantity'])) {
    $errors[] = 'Valid quantity is required';
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

// Get data
$item_name = trim($input['item_name']);
$category = isset($input['category']) ? trim($input['category']) : null;
$quantity = intval($input['quantity']);
$unit = isset($input['unit']) ? trim($input['unit']) : 'units';
$minimum_threshold = isset($input['minimum_threshold']) ? intval($input['minimum_threshold']) : 10;
$campus_id = $_SESSION['campus_id'];

// Validate quantity is not negative
if ($quantity < 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Quantity cannot be negative'
    ]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if item already exists for this campus (unique constraint)
    $check_query = "SELECT * FROM stock WHERE campus_id = :campus_id AND item_name = :item_name";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':campus_id', $campus_id);
    $check_stmt->bindParam(':item_name', $item_name);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Stock item with this name already exists in your campus. Use update instead.'
        ]);
        exit;
    }
    
    // Insert stock item
    $query = "INSERT INTO stock 
              (campus_id, item_name, category, quantity, unit, minimum_threshold, created_at) 
              VALUES 
              (:campus_id, :item_name, :category, :quantity, :unit, :minimum_threshold, NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':campus_id', $campus_id);
    $stmt->bindParam(':item_name', $item_name);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':unit', $unit);
    $stmt->bindParam(':minimum_threshold', $minimum_threshold);
    
    if ($stmt->execute()) {
        $stock_id = $db->lastInsertId();
        
        // Check if stock is low
        $is_low_stock = ($quantity <= $minimum_threshold);
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Stock item created successfully',
            'alert' => $is_low_stock ? 'WARNING: Stock quantity is at or below minimum threshold!' : null,
            'data' => [
                'stock_id' => $stock_id,
                'item_name' => $item_name,
                'category' => $category,
                'quantity' => $quantity,
                'unit' => $unit,
                'minimum_threshold' => $minimum_threshold,
                'is_low_stock' => $is_low_stock
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create stock item',
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