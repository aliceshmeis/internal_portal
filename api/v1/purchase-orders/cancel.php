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

// Only Admin and Asset Manager can cancel POs
if (!in_array($_SESSION['role'], ['Admin', 'Asset Manager'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden. Only Admin and Asset Manager can cancel purchase orders.'
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
$cancel_reason = isset($input['reason']) ? trim($input['reason']) : 'No reason provided';

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
        // Asset Manager can only cancel their own POs
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
            'message' => 'Purchase order not found or you do not have permission to cancel it'
        ]);
        exit;
    }
    
    // Can only cancel Draft or Pending Approval POs
    if (!in_array($existing_po['status'], ['Draft', 'Pending Approval'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Can only cancel POs with Draft or Pending Approval status. Current status: ' . $existing_po['status']
        ]);
        exit;
    }
    
    // Cancel the PO
    $cancel_query = "UPDATE purchase_orders 
                     SET status = 'Cancelled',
                         approval_status = 'Cancelled',
                         notes = CONCAT(COALESCE(notes, ''), '\n[Cancelled] ', :cancel_note),
                         updated_at = NOW()
                     WHERE id = :id";
    
    $cancel_note = 'Cancelled by ' . $_SESSION['name'] . '. Reason: ' . $cancel_reason;
    
    $cancel_stmt = $db->prepare($cancel_query);
    $cancel_stmt->bindParam(':cancel_note', $cancel_note);
    $cancel_stmt->bindParam(':id', $po_id);
    
    if ($cancel_stmt->execute()) {
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
            'message' => 'Purchase order cancelled successfully',
            'data' => $updated_po
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to cancel purchase order'
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
```

---

## ✅ **PHASE 1 COMPLETE!**

You now have:

1. ✅ **submit.php** - Asset Manager submits PO for approval
2. ✅ **approve.php** - Admin approves/rejects with reason
3. ✅ **receive.php** - Asset Manager marks received → Stock AUTO-UPDATES! 🔥
4. ✅ **cancel.php** - Cancel PO if needed

---

## 🎯 **PURCHASE ORDER WORKFLOW IS NOW PROFESSIONAL!**

**Complete Flow:**
```
Draft → Submit → Pending Approval → Approve/Reject → 
  If Approved → Receive → Completed (Stock Updated) ✅
  If Rejected → Locked ❌
  Any time: Cancel (if Draft/Pending) 🚫