<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PATCH');
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

// Only Admin can approve/reject POs
if ($_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden. Only Admin can approve or reject purchase orders.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST or PATCH.'
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

// Validate action
if (empty($input['action']) || !in_array($input['action'], ['approve', 'reject'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Action is required. Valid values: approve, reject'
    ]);
    exit;
}

$po_id = intval($input['id']);
$action = $input['action'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if PO exists
    $check_query = "SELECT * FROM purchase_orders WHERE id = :id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':id', $po_id);
    $check_stmt->execute();
    $existing_po = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing_po) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Purchase order not found'
        ]);
        exit;
    }
    
    // Check if already approved/rejected
    if ($existing_po['approval_status'] !== 'Pending') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Purchase order has already been ' . strtolower($existing_po['approval_status'])
        ]);
        exit;
    }
    
    // Approve or Reject
    if ($action === 'approve') {
        $approval_status = 'Approved';
        $status = 'Approved';
        $message = 'Purchase order approved successfully';
    } else {
        $approval_status = 'Rejected';
        $status = 'Rejected';
        $message = 'Purchase order rejected';
    }
    
    $approved_by = $_SESSION['user_id'];
    
    $update_query = "UPDATE purchase_orders 
                     SET approval_status = :approval_status,
                         status = :status,
                         approved_by = :approved_by,
                         approved_at = NOW(),
                         updated_at = NOW()
                     WHERE id = :id";
    
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':approval_status', $approval_status);
    $update_stmt->bindParam(':status', $status);
    $update_stmt->bindParam(':approved_by', $approved_by);
    $update_stmt->bindParam(':id', $po_id);
    
    if ($update_stmt->execute()) {
        // Get updated PO
        $get_query = "SELECT po.*, c.campus_name, u.name as created_by_name, a.name as approved_by_name
                      FROM purchase_orders po
                      LEFT JOIN campuses c ON po.campus_id = c.id
                      LEFT JOIN users u ON po.created_by = u.id
                      LEFT JOIN users a ON po.approved_by = a.id
                      WHERE po.id = :id";
        $get_stmt = $db->prepare($get_query);
        $get_stmt->bindParam(':id', $po_id);
        $get_stmt->execute();
        $updated_po = $get_stmt->fetch(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $updated_po
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update purchase order status'
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