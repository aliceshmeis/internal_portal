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

// Only Admin and Asset Manager can create POs
if (!in_array($_SESSION['role'], ['Admin', 'Asset Manager'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden. Only Admin and Asset Manager can create purchase orders.'
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

if (empty($input['supplier'])) {
    $errors[] = 'Supplier is required';
}

if (empty($input['items']) || !is_array($input['items']) || count($input['items']) === 0) {
    $errors[] = 'At least one item is required';
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
$supplier = trim($input['supplier']);
$notes = isset($input['notes']) ? trim($input['notes']) : null;
$campus_id = $_SESSION['campus_id'];
$created_by = $_SESSION['user_id'];
$items = $input['items'];

// Validate items
foreach ($items as $index => $item) {
    if (empty($item['item_name'])) {
        $errors[] = "Item #" . ($index + 1) . ": Item name is required";
    }
    if (!isset($item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] <= 0) {
        $errors[] = "Item #" . ($index + 1) . ": Valid quantity is required";
    }
    if (!isset($item['unit_price']) || !is_numeric($item['unit_price']) || $item['unit_price'] < 0) {
        $errors[] = "Item #" . ($index + 1) . ": Valid unit price is required";
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Item validation failed',
        'errors' => $errors
    ]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Start transaction
    $db->beginTransaction();
    
    // Generate unique PO number
    $po_number = 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Calculate total amount
    $total_amount = 0;
    foreach ($items as $item) {
        $quantity = intval($item['quantity']);
        $unit_price = floatval($item['unit_price']);
        $total_amount += ($quantity * $unit_price);
    }
    
    // Insert PO
    $po_query = "INSERT INTO purchase_orders 
                 (po_number, campus_id, supplier, total_amount, status, approval_status, created_by, notes, created_at) 
                 VALUES 
                 (:po_number, :campus_id, :supplier, :total_amount, 'Draft', 'Pending', :created_by, :notes, NOW())";
    
    $po_stmt = $db->prepare($po_query);
    $po_stmt->bindParam(':po_number', $po_number);
    $po_stmt->bindParam(':campus_id', $campus_id);
    $po_stmt->bindParam(':supplier', $supplier);
    $po_stmt->bindParam(':total_amount', $total_amount);
    $po_stmt->bindParam(':created_by', $created_by);
    $po_stmt->bindParam(':notes', $notes);
    
    if (!$po_stmt->execute()) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create purchase order',
            'error' => $po_stmt->errorInfo()
        ]);
        exit;
    }
    
    $po_id = $db->lastInsertId();
    
    // Insert PO items
    $item_query = "INSERT INTO purchase_order_items 
                   (po_id, item_name, quantity, unit_price, total_price, notes) 
                   VALUES 
                   (:po_id, :item_name, :quantity, :unit_price, :total_price, :notes)";
    
    $item_stmt = $db->prepare($item_query);
    
    foreach ($items as $item) {
        $item_name = trim($item['item_name']);
        $quantity = intval($item['quantity']);
        $unit_price = floatval($item['unit_price']);
        $total_price = $quantity * $unit_price;
        $item_notes = isset($item['notes']) ? trim($item['notes']) : null;
        
        $item_stmt->bindParam(':po_id', $po_id);
        $item_stmt->bindParam(':item_name', $item_name);
        $item_stmt->bindParam(':quantity', $quantity);
        $item_stmt->bindParam(':unit_price', $unit_price);
        $item_stmt->bindParam(':total_price', $total_price);
        $item_stmt->bindParam(':notes', $item_notes);
        
        if (!$item_stmt->execute()) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add PO items',
                'error' => $item_stmt->errorInfo()
            ]);
            exit;
        }
    }
    
    // Commit transaction
    $db->commit();
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Purchase order created successfully',
        'data' => [
            'po_id' => $po_id,
            'po_number' => $po_number,
            'supplier' => $supplier,
            'total_amount' => $total_amount,
            'status' => 'Draft',
            'approval_status' => 'Pending',
            'items_count' => count($items)
        ]
    ]);
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
}
?>