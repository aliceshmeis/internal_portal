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
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $low_stock_only = isset($_GET['low_stock']) && $_GET['low_stock'] === 'true';
    
    $user_role = $_SESSION['role'];
    $user_campus_id = $_SESSION['campus_id'];
    
    // Build query based on role
    if ($user_role === 'Admin') {
        // Admin sees all stock across all campuses
        $query = "SELECT s.*, c.campus_name,
                  CASE WHEN s.quantity <= s.minimum_threshold THEN 1 ELSE 0 END as is_low_stock
                  FROM stock s
                  LEFT JOIN campuses c ON s.campus_id = c.id
                  WHERE 1=1";
    } else {
        // Others see only their campus stock
        $query = "SELECT s.*, c.campus_name,
                  CASE WHEN s.quantity <= s.minimum_threshold THEN 1 ELSE 0 END as is_low_stock
                  FROM stock s
                  LEFT JOIN campuses c ON s.campus_id = c.id
                  WHERE s.campus_id = :campus_id";
    }
    
    // Add filters
    if ($category) {
        $query .= " AND s.category = :category";
    }
    
    if ($low_stock_only) {
        $query .= " AND s.quantity <= s.minimum_threshold";
    }
    
    $query .= " ORDER BY s.last_updated DESC";
    
    $stmt = $db->prepare($query);
    
    // Bind parameters
    if ($user_role !== 'Admin') {
        $stmt->bindParam(':campus_id', $user_campus_id);
    }
    
    if ($category) {
        $stmt->bindParam(':category', $category);
    }
    
    $stmt->execute();
    $stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count low stock items
    $low_stock_count = 0;
    foreach ($stock_items as $item) {
        if ($item['is_low_stock'] == 1) {
            $low_stock_count++;
        }
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Stock items retrieved successfully',
        'count' => count($stock_items),
        'low_stock_count' => $low_stock_count,
        'alert' => $low_stock_count > 0 ? "$low_stock_count item(s) are low on stock!" : null,
        'data' => $stock_items
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