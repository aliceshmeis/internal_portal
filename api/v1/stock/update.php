<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, PATCH');
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

// Only Admin and Asset Manager can update stock
if (!in_array($_SESSION['role'], ['Admin', 'Asset Manager'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden. Only Admin and Asset Manager can update stock.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use PUT or PATCH.'
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
    
    $user_role = $_SESSION['role'];
    $user_campus_id = $_SESSION['campus_id'];
    
    // Check if stock exists and user has permission
    if ($user_role === 'Admin') {
        $check_query = "SELECT * FROM stock WHERE id = :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $stock_id);
    } else {
        $check_query = "SELECT * FROM stock WHERE id = :id AND campus_id = :campus_id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $stock_id);
        $check_stmt->bindParam(':campus_id', $user_campus_id);
    }
    
    $check_stmt->execute();
    $existing_stock = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing_stock) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Stock item not found or you do not have permission to update it'
        ]);
        exit;
    }
    
    // Build update query dynamically
    $update_fields = [];
    $params = [];
    
    if (isset($input['item_name'])) {
        $update_fields[] = "item_name = :item_name";
        $params[':item_name'] = trim($input['item_name']);
    }
    
    if (isset($input['category'])) {
        $update_fields[] = "category = :category";
        $params[':category'] = trim($input['category']);
    }
    
    if (isset($input['quantity'])) {
        if (!is_numeric($input['quantity']) || $input['quantity'] < 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Quantity must be a non-negative number'
            ]);
            exit;
        }
        $update_fields[] = "quantity = :quantity";
        $params[':quantity'] = intval($input['quantity']);
    }
    
    if (isset($input['unit'])) {
        $update_fields[] = "unit = :unit";
        $params[':unit'] = trim($input['unit']);
    }
    
    if (isset($input['minimum_threshold'])) {
        if (!is_numeric($input['minimum_threshold']) || $input['minimum_threshold'] < 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Minimum threshold must be a non-negative number'
            ]);
            exit;
        }
        $update_fields[] = "minimum_threshold = :minimum_threshold";
        $params[':minimum_threshold'] = intval($input['minimum_threshold']);
    }
    
    if (empty($update_fields)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No fields to update'
        ]);
        exit;
    }
    
    $update_fields[] = "last_updated = NOW()";
    
    $update_query = "UPDATE stock SET " . implode(', ', $update_fields) . " WHERE id = :id";
    $params[':id'] = $stock_id;
    
    $update_stmt = $db->prepare($update_query);
    
    foreach ($params as $key => $value) {
        $update_stmt->bindValue($key, $value);
    }
    
    if ($update_stmt->execute()) {
        // Get updated stock item
        $get_query = "SELECT s.*, c.campus_name,
                      CASE WHEN s.quantity <= s.minimum_threshold THEN 1 ELSE 0 END as is_low_stock
                      FROM stock s
                      LEFT JOIN campuses c ON s.campus_id = c.id
                      WHERE s.id = :id";
        $get_stmt = $db->prepare($get_query);
        $get_stmt->bindParam(':id', $stock_id);
        $get_stmt->execute();
        $updated_stock = $get_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Stock alert logic
        $alert_message = null;
        if ($updated_stock['is_low_stock'] == 1) {
            $alert_message = "⚠️ ALERT: Stock quantity ({$updated_stock['quantity']} {$updated_stock['unit']}) is at or below minimum threshold ({$updated_stock['minimum_threshold']} {$updated_stock['unit']})!";
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Stock updated successfully',
            'alert' => $alert_message,
            'data' => $updated_stock
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update stock'
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