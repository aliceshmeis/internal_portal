<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
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

// Only Admin can delete assets
if ($_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden. Only Admin can delete assets.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use DELETE or POST.'
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
    
    // Check if asset exists
    $check_query = "SELECT * FROM assets WHERE id = :id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':id', $asset_id);
    $check_stmt->execute();
    $existing_asset = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing_asset) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Asset not found'
        ]);
        exit;
    }
    
    // Soft delete: Set status to Retired instead of actually deleting
    $delete_query = "UPDATE assets 
                     SET status = 'Retired', 
                         assigned_to = NULL,
                         updated_at = NOW()
                     WHERE id = :id";
    
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':id', $asset_id);
    
    if ($delete_stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Asset retired successfully (soft delete)',
            'data' => [
                'asset_id' => $asset_id,
                'asset_tag' => $existing_asset['asset_tag'],
                'status' => 'Retired'
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to retire asset'
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