<?php
require_once __DIR__ . '/../../config/database.php';

class PurchaseOrder {
    
    /**
     * Create a new purchase order with items
     * 
     * @param array $data
     * @param array $items
     * @return array|false
     */
    public static function create($data, $items) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Start transaction
            $db->beginTransaction();
            
            // Generate PO number
            $po_number = 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            // Calculate total amount
            $total_amount = 0;
            foreach ($items as $item) {
                $total_amount += ($item['quantity'] * $item['unit_price']);
            }
            
            // Insert PO
            $query = "INSERT INTO purchase_orders 
                      (po_number, campus_id, supplier, total_amount, status, approval_status, created_by, notes, created_at) 
                      VALUES 
                      (:po_number, :campus_id, :supplier, :total_amount, 'Draft', 'Pending', :created_by, :notes, NOW())";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':po_number', $po_number);
            $stmt->bindParam(':campus_id', $data['campus_id']);
            $stmt->bindParam(':supplier', $data['supplier']);
            $stmt->bindParam(':total_amount', $total_amount);
            $stmt->bindParam(':created_by', $data['created_by']);
            $stmt->bindParam(':notes', $data['notes']);
            
            if (!$stmt->execute()) {
                $db->rollBack();
                return false;
            }
            
            $po_id = $db->lastInsertId();
            
            // Insert PO items
            $item_query = "INSERT INTO purchase_order_items 
                           (po_id, item_name, quantity, unit_price, total_price, notes) 
                           VALUES 
                           (:po_id, :item_name, :quantity, :unit_price, :total_price, :notes)";
            
            $item_stmt = $db->prepare($item_query);
            
            foreach ($items as $item) {
                $total_price = $item['quantity'] * $item['unit_price'];
                
                $item_stmt->bindParam(':po_id', $po_id);
                $item_stmt->bindParam(':item_name', $item['item_name']);
                $item_stmt->bindParam(':quantity', $item['quantity']);
                $item_stmt->bindParam(':unit_price', $item['unit_price']);
                $item_stmt->bindParam(':total_price', $total_price);
                $item_stmt->bindParam(':notes', $item['notes']);
                
                if (!$item_stmt->execute()) {
                    $db->rollBack();
                    return false;
                }
            }
            
            // Commit transaction
            $db->commit();
            
            return self::find($po_id);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return false;
        }
    }
    
    /**
     * Find purchase order by ID with items
     * 
     * @param int $id
     * @return array|false
     */
    public static function find($id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT po.*, 
                      c.campus_name,
                      u.name as created_by_name, u.email as created_by_email,
                      a.name as approved_by_name, a.email as approved_by_email
                      FROM purchase_orders po
                      LEFT JOIN campuses c ON po.campus_id = c.id
                      LEFT JOIN users u ON po.created_by = u.id
                      LEFT JOIN users a ON po.approved_by = a.id
                      WHERE po.id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $po = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($po) {
                // Get items
                $po['items'] = self::getItems($id);
                $po['items_count'] = count($po['items']);
            }
            
            return $po;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get PO items
     * 
     * @param int $po_id
     * @return array
     */
    public static function getItems($po_id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT * FROM purchase_order_items WHERE po_id = :po_id ORDER BY id ASC";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':po_id', $po_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get all purchase orders (Admin view)
     * 
     * @param array $filters
     * @return array
     */
    public static function getAll($filters = []) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT po.*, c.campus_name, u.name as created_by_name, a.name as approved_by_name
                      FROM purchase_orders po
                      LEFT JOIN campuses c ON po.campus_id = c.id
                      LEFT JOIN users u ON po.created_by = u.id
                      LEFT JOIN users a ON po.approved_by = a.id
                      WHERE 1=1";
            
            if (!empty($filters['status'])) {
                $query .= " AND po.status = :status";
            }
            
            if (!empty($filters['approval_status'])) {
                $query .= " AND po.approval_status = :approval_status";
            }
            
            $query .= " ORDER BY po.created_at DESC";
            
            $stmt = $db->prepare($query);
            
            if (!empty($filters['status'])) {
                $stmt->bindParam(':status', $filters['status']);
            }
            
            if (!empty($filters['approval_status'])) {
                $stmt->bindParam(':approval_status', $filters['approval_status']);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get purchase orders by campus
     * 
     * @param int $campus_id
     * @param array $filters
     * @return array
     */
    public static function getByCampus($campus_id, $filters = []) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT po.*, c.campus_name, u.name as created_by_name, a.name as approved_by_name
                      FROM purchase_orders po
                      LEFT JOIN campuses c ON po.campus_id = c.id
                      LEFT JOIN users u ON po.created_by = u.id
                      LEFT JOIN users a ON po.approved_by = a.id
                      WHERE po.campus_id = :campus_id";
            
            if (!empty($filters['status'])) {
                $query .= " AND po.status = :status";
            }
            
            if (!empty($filters['approval_status'])) {
                $query .= " AND po.approval_status = :approval_status";
            }
            
            $query .= " ORDER BY po.created_at DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':campus_id', $campus_id);
            
            if (!empty($filters['status'])) {
                $stmt->bindParam(':status', $filters['status']);
            }
            
            if (!empty($filters['approval_status'])) {
                $stmt->bindParam(':approval_status', $filters['approval_status']);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Update purchase order
     * 
     * @param int $id
     * @param array $data
     * @return array|false
     */
    public static function update($id, $data) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $fields = [];
            $params = [':id' => $id];
            
            foreach ($data as $key => $value) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
            
            $fields[] = "updated_at = NOW()";
            
            $query = "UPDATE purchase_orders SET " . implode(', ', $fields) . " WHERE id = :id";
            
            $stmt = $db->prepare($query);
            
            if ($stmt->execute($params)) {
                return self::find($id);
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Submit PO for approval
     * 
     * @param int $id
     * @return array|false
     */
    public static function submit($id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "UPDATE purchase_orders 
                      SET status = 'Pending Approval',
                          approval_status = 'Pending',
                          updated_at = NOW()
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return self::find($id);
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Approve or Reject PO
     * 
     * @param int $id
     * @param string $action ('approve' or 'reject')
     * @param int $approved_by
     * @param string|null $reason
     * @return array|false
     */
    public static function approveOrReject($id, $action, $approved_by, $reason = null) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            if ($action === 'approve') {
                $approval_status = 'Approved';
                $status = 'Approved';
                $decision_note = 'Approved by Admin';
            } else {
                $approval_status = 'Rejected';
                $status = 'Rejected';
                $decision_note = 'Rejected by Admin. Reason: ' . $reason;
            }
            
            $query = "UPDATE purchase_orders 
                      SET approval_status = :approval_status,
                          status = :status,
                          approved_by = :approved_by,
                          approved_at = NOW(),
                          notes = CONCAT(COALESCE(notes, ''), '\n[Admin Decision] ', :decision_note),
                          updated_at = NOW()
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':approval_status', $approval_status);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':approved_by', $approved_by);
            $stmt->bindParam(':decision_note', $decision_note);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return self::find($id);
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Mark PO as received and update stock
     * 
     * @param int $id
     * @param string|null $notes
     * @return array|false
     */
    public static function receive($id, $notes = null) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Start transaction
            $db->beginTransaction();
            
            // Get PO details
            $po = self::find($id);
            
            if (!$po) {
                $db->rollBack();
                return false;
            }
            
            // Update stock for each item
            $stock_updates = [];
            foreach ($po['items'] as $item) {
                $item_name = $item['item_name'];
                $quantity_received = $item['quantity'];
                
                // Check if stock exists
                $stock_check = "SELECT * FROM stock WHERE campus_id = :campus_id AND item_name = :item_name";
                $stock_stmt = $db->prepare($stock_check);
                $stock_stmt->bindParam(':campus_id', $po['campus_id']);
                $stock_stmt->bindParam(':item_name', $item_name);
                $stock_stmt->execute();
                $existing_stock = $stock_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing_stock) {
                    // Update existing stock
                    $new_quantity = $existing_stock['quantity'] + $quantity_received;
                    $update_stock = "UPDATE stock 
                                     SET quantity = :new_quantity,
                                         last_updated = NOW()
                                     WHERE id = :id";
                    $update_stmt = $db->prepare($update_stock);
                    $update_stmt->bindParam(':new_quantity', $new_quantity);
                    $update_stmt->bindParam(':id', $existing_stock['id']);
                    $update_stmt->execute();
                    
                    $stock_updates[] = [
                        'item_name' => $item_name,
                        'old_quantity' => $existing_stock['quantity'],
                        'added' => $quantity_received,
                        'new_quantity' => $new_quantity
                    ];
                } else {
                    // Create new stock item
                    $create_stock = "INSERT INTO stock 
                                     (campus_id, item_name, category, quantity, unit, minimum_threshold, created_at)
                                     VALUES
                                     (:campus_id, :item_name, 'General', :quantity, 'units', 10, NOW())";
                    $create_stmt = $db->prepare($create_stock);
                    $create_stmt->bindParam(':campus_id', $po['campus_id']);
                    $create_stmt->bindParam(':item_name', $item_name);
                    $create_stmt->bindParam(':quantity', $quantity_received);
                    $create_stmt->execute();
                    
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
            $received_note = 'Items received and stock updated. ' . ($notes ?? '');
            $complete_query = "UPDATE purchase_orders 
                               SET status = 'Completed',
                                   notes = CONCAT(COALESCE(notes, ''), '\n[Received] ', :received_note),
                                   updated_at = NOW()
                               WHERE id = :id";
            
            $complete_stmt = $db->prepare($complete_query);
            $complete_stmt->bindParam(':received_note', $received_note);
            $complete_stmt->bindParam(':id', $id);
            $complete_stmt->execute();
            
            // Commit transaction
            $db->commit();
            
            $result = self::find($id);
            $result['stock_updates'] = $stock_updates;
            
            return $result;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return false;
        }
    }
    
    /**
     * Cancel purchase order
     * 
     * @param int $id
     * @param string $reason
     * @return array|false
     */
    public static function cancel($id, $reason, $cancelled_by) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $cancel_note = 'Cancelled. Reason: ' . $reason;
            
            $query = "UPDATE purchase_orders 
                      SET status = 'Cancelled',
                          approval_status = 'Cancelled',
                          notes = CONCAT(COALESCE(notes, ''), '\n[Cancelled] ', :cancel_note),
                          updated_at = NOW()
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':cancel_note', $cancel_note);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return self::find($id);
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Delete (retire) purchase order
     * 
     * @param int $id
     * @return bool
     */
    public static function delete($id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Soft delete - just mark as cancelled
            $query = "UPDATE purchase_orders 
                      SET status = 'Cancelled',
                          approval_status = 'Cancelled',
                          updated_at = NOW()
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
?>