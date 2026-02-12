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

// Only Asset Manager can submit POs (they created them)
if (!in_array($_SESSION['role'], ['Admin', 'Asset Manager'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden. Only Asset Manager can submit purchase orders.'
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
    $user_id = $_SESSION['user_id'];
    $user_campus_id = $_SESSION['campus_id'];
    
    // Check if PO exists and user has permission
    if ($user_role === 'Admin') {
        $check_query = "SELECT * FROM purchase_orders WHERE id = :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $po_id);
    } else {
        // Asset Manager can only submit their own POs
        $check_query = "SELECT * FROM purchase_orders WHERE id = :id AND campus_id = :campus_id AND created_by = :created_by";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $po_id);
        $check_stmt->bindParam(':campus_id', $user_campus_id);
        $check_stmt->bindParam(':created_by', $user_id);
    }
    
    $check_stmt->execute();
    $existing_po = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing_po) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Purchase order not found or you do not have permission to submit it'
        ]);
        exit;
    }
    
    // Validate current status - can only submit if Draft
    if ($existing_po['status'] !== 'Draft') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Can only submit purchase orders with Draft status. Current status: ' . $existing_po['status']
        ]);
        exit;
    }
    
    // Check if PO has items
    $items_check_query = "SELECT COUNT(*) as item_count FROM purchase_order_items WHERE po_id = :po_id";
    $items_check_stmt = $db->prepare($items_check_query);
    $items_check_stmt->bindParam(':po_id', $po_id);
    $items_check_stmt->execute();
    $items_check = $items_check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($items_check['item_count'] == 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Cannot submit purchase order without items'
        ]);
        exit;
    }
    
    // Submit PO - change status
    $submit_query = "UPDATE purchase_orders 
                     SET status = 'Pending Approval',
                         approval_status = 'Pending',
                         updated_at = NOW()
                     WHERE id = :id";
    
    $submit_stmt = $db->prepare($submit_query);
    $submit_stmt->bindParam(':id', $po_id);
    
    if ($submit_stmt->execute()) {
        
        // TODO: Send notification to Admin(s)
        // This is where you'd add email notification logic
        // Example: sendEmailToAdmins("New PO awaiting approval", $po_id);
        
        // Get updated PO
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
            'message' => 'Purchase order submitted for approval successfully',
            'notification' => 'Admin has been notified and will review your PO',
            'data' => $updated_po
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to submit purchase order'
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