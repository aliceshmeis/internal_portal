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

// Only Admin and Asset Manager can update assets
if (!in_array($_SESSION['role'], ['Admin', 'Asset Manager'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden. Only Admin and Asset Manager can update assets.'
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

// Validate asset ID
if (empty($input['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Asset ID is required'
    ]);
    exit;
}

$asset_id = intval($input['id']);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $user_role = $_SESSION['role'];
    $user_campus_id = $_SESSION['campus_id'];
    
    // Check if asset exists and user has permission
    if ($user_role === 'Admin') {
        $check_query = "SELECT * FROM assets WHERE id = :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $asset_id);
    } else {
        $check_query = "SELECT * FROM assets WHERE id = :id AND campus_id = :campus_id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $asset_id);
        $check_stmt->bindParam(':campus_id', $user_campus_id);
    }
    
    $check_stmt->execute();
    $existing_asset = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing_asset) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Asset not found or you do not have permission to update it'
        ]);
        exit;
    }
    
    // Build update query dynamically
    $update_fields = [];
    $params = [];
    
    if (isset($input['name'])) {
        $update_fields[] = "name = :name";
        $params[':name'] = trim($input['name']);
    }
    
    if (isset($input['description'])) {
        $update_fields[] = "description = :description";
        $params[':description'] = trim($input['description']);
    }
    
    if (isset($input['serial_number'])) {
        $update_fields[] = "serial_number = :serial_number";
        $params[':serial_number'] = trim($input['serial_number']);
    }
    
    if (isset($input['category'])) {
        $allowed_categories = ['Laptop', 'Printer', 'Network Equipment', 'Furniture', 'Other'];
        if (!in_array($input['category'], $allowed_categories)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid category. Allowed: ' . implode(', ', $allowed_categories)
            ]);
            exit;
        }
        $update_fields[] = "category = :category";
        $params[':category'] = $input['category'];
    }
    
    if (isset($input['status'])) {
        $allowed_statuses = ['Available', 'In Use', 'Maintenance', 'Retired'];
        if (!in_array($input['status'], $allowed_statuses)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid status. Allowed: ' . implode(', ', $allowed_statuses)
            ]);
            exit;
        }
        $update_fields[] = "status = :status";
        $params[':status'] = $input['status'];
    }
    
    if (isset($input['assigned_to'])) {
        $update_fields[] = "assigned_to = :assigned_to";
        $params[':assigned_to'] = $input['assigned_to'] ? intval($input['assigned_to']) : null;
    }
    
    if (isset($input['purchase_date'])) {
        $update_fields[] = "purchase_date = :purchase_date";
        $params[':purchase_date'] = $input['purchase_date'];
    }
    
    if (isset($input['purchase_cost'])) {
        $update_fields[] = "purchase_cost = :purchase_cost";
        $params[':purchase_cost'] = floatval($input['purchase_cost']);
    }
    
    if (isset($input['warranty_expiry'])) {
        $update_fields[] = "warranty_expiry = :warranty_expiry";
        $params[':warranty_expiry'] = $input['warranty_expiry'];
    }
    
    if (empty($update_fields)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No fields to update'
        ]);
        exit;
    }
    
    $update_fields[] = "updated_at = NOW()";
    
    $update_query = "UPDATE assets SET " . implode(', ', $update_fields) . " WHERE id = :id";
    $params[':id'] = $asset_id;
    
    $update_stmt = $db->prepare($update_query);
    
    foreach ($params as $key => $value) {
        $update_stmt->bindValue($key, $value);
    }
    
    if ($update_stmt->execute()) {
        // Get updated asset
        $get_query = "SELECT a.*, c.campus_name, u.name as assigned_user_name
                      FROM assets a
                      LEFT JOIN campuses c ON a.campus_id = c.id
                      LEFT JOIN users u ON a.assigned_to = u.id
                      WHERE a.id = :id";
        $get_stmt = $db->prepare($get_query);
        $get_stmt->bindParam(':id', $asset_id);
        $get_stmt->execute();
        $updated_asset = $get_stmt->fetch(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Asset updated successfully',
            'data' => $updated_asset
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update asset'
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