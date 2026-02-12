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

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get filter parameters
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $approval_status = isset($_GET['approval_status']) ? $_GET['approval_status'] : null;
    
    $user_role = $_SESSION['role'];
    $user_campus_id = $_SESSION['campus_id'];
    
    // Build query based on role
    if ($user_role === 'Admin') {
        // Admin sees all POs
        $query = "SELECT po.*, c.campus_name, u.name as created_by_name, a.name as approved_by_name
                  FROM purchase_orders po
                  LEFT JOIN campuses c ON po.campus_id = c.id
                  LEFT JOIN users u ON po.created_by = u.id
                  LEFT JOIN users a ON po.approved_by = a.id
                  WHERE 1=1";
    } else {
        // Others see only their campus POs
        $query = "SELECT po.*, c.campus_name, u.name as created_by_name, a.name as approved_by_name
                  FROM purchase_orders po
                  LEFT JOIN campuses c ON po.campus_id = c.id
                  LEFT JOIN users u ON po.created_by = u.id
                  LEFT JOIN users a ON po.approved_by = a.id
                  WHERE po.campus_id = :campus_id";
    }
    
    // Add filters
    if ($status) {
        $query .= " AND po.status = :status";
    }
    
    if ($approval_status) {
        $query .= " AND po.approval_status = :approval_status";
    }
    
    $query .= " ORDER BY po.created_at DESC";
    
    $stmt = $db->prepare($query);
    
    // Bind parameters
    if ($user_role !== 'Admin') {
        $stmt->bindParam(':campus_id', $user_campus_id);
    }
    
    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    
    if ($approval_status) {
        $stmt->bindParam(':approval_status', $approval_status);
    }
    
    $stmt->execute();
    $purchase_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Purchase orders retrieved successfully',
        'count' => count($purchase_orders),
        'data' => $purchase_orders
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