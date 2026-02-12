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

// Check if ID is provided
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Purchase order ID is required'
    ]);
    exit;
}

$po_id = intval($_GET['id']);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $user_role = $_SESSION['role'];
    $user_campus_id = $_SESSION['campus_id'];
    
    // Build query based on role
    if ($user_role === 'Admin') {
        $query = "SELECT po.*, c.campus_name, u.name as created_by_name, u.email as created_by_email,
                  a.name as approved_by_name, a.email as approved_by_email
                  FROM purchase_orders po
                  LEFT JOIN campuses c ON po.campus_id = c.id
                  LEFT JOIN users u ON po.created_by = u.id
                  LEFT JOIN users a ON po.approved_by = a.id
                  WHERE po.id = :id";
    } else {
        $query = "SELECT po.*, c.campus_name, u.name as created_by_name, u.email as created_by_email,
                  a.name as approved_by_name, a.email as approved_by_email
                  FROM purchase_orders po
                  LEFT JOIN campuses c ON po.campus_id = c.id
                  LEFT JOIN users u ON po.created_by = u.id
                  LEFT JOIN users a ON po.approved_by = a.id
                  WHERE po.id = :id AND po.campus_id = :campus_id";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $po_id);
    
    if ($user_role !== 'Admin') {
        $stmt->bindParam(':campus_id', $user_campus_id);
    }
    
    $stmt->execute();
    $po = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$po) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Purchase order not found or you do not have permission to view it'
        ]);
        exit;
    }
    
    // Get PO items
    $items_query = "SELECT * FROM purchase_order_items WHERE po_id = :po_id ORDER BY id ASC";
    $items_stmt = $db->prepare($items_query);
    $items_stmt->bindParam(':po_id', $po_id);
    $items_stmt->execute();
    $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add items to PO data
    $po['items'] = $items;
    $po['items_count'] = count($items);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Purchase order retrieved successfully',
        'data' => $po
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