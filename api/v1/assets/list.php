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
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    
    $user_role = $_SESSION['role'];
    $user_campus_id = $_SESSION['campus_id'];
    
    // Build query based on role
    if ($user_role === 'Admin') {
        // Admin sees all assets
        $query = "SELECT a.*, c.campus_name, u.name as assigned_user_name
                  FROM assets a
                  LEFT JOIN campuses c ON a.campus_id = c.id
                  LEFT JOIN users u ON a.assigned_to = u.id
                  WHERE 1=1";
    } else {
        // Others see only their campus assets
        $query = "SELECT a.*, c.campus_name, u.name as assigned_user_name
                  FROM assets a
                  LEFT JOIN campuses c ON a.campus_id = c.id
                  LEFT JOIN users u ON a.assigned_to = u.id
                  WHERE a.campus_id = :campus_id";
    }
    
    // Add filters
    if ($category) {
        $query .= " AND a.category = :category";
    }
    
    if ($status) {
        $query .= " AND a.status = :status";
    }
    
    $query .= " ORDER BY a.created_at DESC";
    
    $stmt = $db->prepare($query);
    
    // Bind parameters
    if ($user_role !== 'Admin') {
        $stmt->bindParam(':campus_id', $user_campus_id);
    }
    
    if ($category) {
        $stmt->bindParam(':category', $category);
    }
    
    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    
    $stmt->execute();
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Assets retrieved successfully',
        'count' => count($assets),
        'data' => $assets
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