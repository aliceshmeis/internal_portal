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

// Only Admin and Asset Manager can update POs
if (!in_array($_SESSION['role'], ['Admin', 'Asset Manager'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden. Only Admin and Asset Manager can update purchase orders.'
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

// Validate PO ID
if (empty($input['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Purchase order ID is required'
    ]);
    exit;
}

$po_id = intval($input['id']);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $user_role = $_SESSION['role'];
    $user_campus_id = $_SESSION['campus_id'];
    
    // Check if PO exists and user has permission
    if ($user_role === 'Admin') {
        $check_query = "SELECT * FROM purchase_orders WHERE id = :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $po_id);
    } else {
        $check_query = "SELECT * FROM purchase_orders WHERE id = :id AND campus_id = :campus_id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $po_id);
        $check_stmt->bindParam(':campus_id', $user_campus_id);
    }
    
    $check_stmt->execute();
    $existing_po = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing_po) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Purchase order not found or you do not have permission to update it'
        ]);
        exit;
    }
    
    // Can't update if already approved or completed
    if (in_array($existing_po['status'], ['Completed', 'Cancelled'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Cannot update purchase order with status: ' . $existing_po['status']
        ]);
        exit;
    }
    
    // Build update query dynamically
    $update_fields = [];
    $params = [];
    
    if (isset($input['supplier'])) {
        $update_fields[] = "supplier = :supplier";
        $params[':supplier'] = trim($input['supplier']);
    }
    
    if (isset($input['status'])) {
        $allowed_statuses = ['Draft', 'Pending Approval', 'Approved', 'Rejected', 'Completed', 'Cancelled'];
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
    
    if (isset($input['notes'])) {
        $update_fields[] = "notes = :notes";
        $params[':notes'] = trim($input['notes']);
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
    
    $update_query = "UPDATE purchase_orders SET " . implode(', ', $update_fields) . " WHERE id = :id";
    $params[':id'] = $po_id;
    
    $update_stmt = $db->prepare($update_query);
    
    foreach ($params as $key => $value) {
        $update_stmt->bindValue($key, $value);
    }
    
    if ($update_stmt->execute()) {
        // Get updated PO with items
        $get_query = "SELECT po.*, c.campus_name, u.name as created_by_name
                      FROM purchase_orders po
                      LEFT JOIN campuses c ON po.campus_id = c.id
                      LEFT JOIN users u ON po.created_by = u.id
                      WHERE po.id = :id";
        $get_stmt = $db->prepare($get_query);
        $get_stmt->bindParam(':id', $po_id);
        $get_stmt->execute();
        $updated_po = $get_stmt->fetch(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Purchase order updated successfully',
            'data' => $updated_po
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update purchase order'
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