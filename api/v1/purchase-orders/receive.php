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

// Only Asset Manager can mark POs as received
if (!in_array($_SESSION['role'], ['Admin', 'Asset Manager'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden. Only Asset Manager can mark purchase orders as received.'
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
$received_notes = isset($input['notes']) ? trim($input['notes']) : null;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $user_role = $_SESSION['role'];
    $user_id = $_SESSION['user_id'];
    $user_campus_id = $_SESSION['campus_id'];
    
    // Start transaction (because we update multiple tables)
    $db->beginTransaction();
    
    // Check if PO exists and user has permission
    if ($user_role === 'Admin') {
        $check_query = "SELECT * FROM purchase_orders WHERE id = :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $po_id);
    } else {
        // Asset Manager can only receive their own campus POs
        $check_query = "SELECT * FROM purchase_orders WHERE id = :id AND campus_id = :campus_id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $po_id);
        $check_stmt->bindParam(':campus_id', $user_campus_id);
    }
    
    $check_stmt->execute();
    $existing_po = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing_po) {
        $db->rollBack();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Purchase order not found or you do not have permission to receive it'
        ]);
        exit;
    }
    
    // Validate current status - must be Approved
    if ($existing_po['status'] !== 'Approved') {
        $db->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Can only mark Approved POs as received. Current status: ' . $existing_po['status']
        ]);
        exit;
    }
    
    // Get PO items
    $items_query = "SELECT * FROM purchase_order_items WHERE po_id = :po_id";
    $items_stmt = $db->prepare($items_query);
    $items_stmt->bindParam(':po_id', $po_id);
    $items_stmt->execute();
    $po_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Update stock quantities for each item
    $stock_updates = [];
    foreach ($po_items as $item) {
        $item_name = $item['item_name'];
        $quantity_received = $item['quantity'];
        
        // Check if stock item exists for this item in this campus
        $stock_check_query = "SELECT * FROM stock WHERE campus_id = :campus_id AND item_name = :item_name";
        $stock_check_stmt = $db->prepare($stock_check_query);
        $stock_check_stmt->bindParam(':campus_id', $existing_po['campus_id']);
        $stock_check_stmt->bindParam(':item_name', $item_name);
        $stock_check_stmt->execute();
        $existing_stock = $stock_check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_stock) {
            // Update existing stock - ADD quantity
            $new_quantity = $existing_stock['quantity'] + $quantity_received;
            $update_stock_query = "UPDATE stock 
                                   SET quantity = :new_quantity,
                                       last_updated = NOW()
                                   WHERE id = :id";
            $update_stock_stmt = $db->prepare($update_stock_query);
            $update_stock_stmt->bindParam(':new_quantity', $new_quantity);
            $update_stock_stmt->bindParam(':id', $existing_stock['id']);
            $update_stock_stmt->execute();
            
            $stock_updates[] = [
                'item_name' => $item_name,
                'old_quantity' => $existing_stock['quantity'],
                'added' => $quantity_received,
                'new_quantity' => $new_quantity
            ];
        } else {
            // Create new stock item
            $create_stock_query = "INSERT INTO stock 
                                   (campus_id, item_name, category, quantity, unit, minimum_threshold, created_at)
                                   VALUES
                                   (:campus_id, :item_name, 'General', :quantity, 'units', 10, NOW())";
            $create_stock_stmt = $db->prepare($create_stock_query);
            $create_stock_stmt->bindParam(':campus_id', $existing_po['campus_id']);
            $create_stock_stmt->bindParam(':item_name', $item_name);
            $create_stock_stmt->bindParam(':quantity', $quantity_received);
            $create_stock_stmt->execute();
            
            $stock_updates[] = [
                'item_name' => $item_name,
                'old_quantity' => 0,
                'added' => $quantity_received,
                'new_quantity' => $quantity_received,
                'new_item' => true
            ];
        }
    }
    
    // Mark PO as completed
    $complete_query = "UPDATE purchase_orders 
                       SET status = 'Completed',
                           notes = CONCAT(COALESCE(notes, ''), '\n[Received] ', :received_note),
                           updated_at = NOW()
                       WHERE id = :id";
    
    $received_note = 'Items received and stock updated. ' . ($received_notes ? $received_notes : '');
    
    $complete_stmt = $db->prepare($complete_query);
    $complete_stmt->bindParam(':received_note', $received_note);
    $complete_stmt->bindParam(':id', $po_id);
    $complete_stmt->execute();
    
    // Commit transaction
    $db->commit();
    
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
        'message' => 'Purchase order marked as received and stock updated successfully',
        'stock_updates' => $stock_updates,
        'data' => $updated_po
    ]);
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
}
?>