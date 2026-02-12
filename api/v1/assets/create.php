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

// Only Admin and Asset Manager can create assets
if (!in_array($_SESSION['role'], ['Admin', 'Asset Manager'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden. Only Admin and Asset Manager can create assets.'
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

if (empty($input['name'])) {
    $errors[] = 'Asset name is required';
}

if (empty($input['category'])) {
    $errors[] = 'Category is required';
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
$name = trim($input['name']);
$category = $input['category'];
$description = isset($input['description']) ? trim($input['description']) : null;
$serial_number = isset($input['serial_number']) ? trim($input['serial_number']) : null;
$status = isset($input['status']) ? $input['status'] : 'Available';
$purchase_date = isset($input['purchase_date']) ? $input['purchase_date'] : null;
$purchase_cost = isset($input['purchase_cost']) ? floatval($input['purchase_cost']) : null;
$warranty_expiry = isset($input['warranty_expiry']) ? $input['warranty_expiry'] : null;
$campus_id = $_SESSION['campus_id'];

// Validate category
$allowed_categories = ['Laptop', 'Printer', 'Network Equipment', 'Furniture', 'Other'];
if (!in_array($category, $allowed_categories)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid category. Allowed: ' . implode(', ', $allowed_categories)
    ]);
    exit;
}

// Validate status
$allowed_statuses = ['Available', 'In Use', 'Maintenance', 'Retired'];
if (!in_array($status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status. Allowed: ' . implode(', ', $allowed_statuses)
    ]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Generate unique asset tag
    $asset_tag = 'AST-' . strtoupper($category[0]) . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Insert asset
    $query = "INSERT INTO assets 
              (asset_tag, campus_id, category, name, description, serial_number, status, 
               purchase_date, purchase_cost, warranty_expiry, created_at) 
              VALUES 
              (:asset_tag, :campus_id, :category, :name, :description, :serial_number, :status,
               :purchase_date, :purchase_cost, :warranty_expiry, NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':asset_tag', $asset_tag);
    $stmt->bindParam(':campus_id', $campus_id);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':serial_number', $serial_number);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':purchase_date', $purchase_date);
    $stmt->bindParam(':purchase_cost', $purchase_cost);
    $stmt->bindParam(':warranty_expiry', $warranty_expiry);
    
    if ($stmt->execute()) {
        $asset_id = $db->lastInsertId();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Asset created successfully',
            'data' => [
                'asset_id' => $asset_id,
                'asset_tag' => $asset_tag,
                'name' => $name,
                'category' => $category,
                'status' => $status
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create asset',
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