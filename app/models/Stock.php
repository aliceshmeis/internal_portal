<?php
require_once __DIR__ . '/../../config/database.php';

class Stock {
    
    /**
     * Create a new stock item
     * 
     * @param array $data
     * @return array|false
     */
    public static function create($data) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Check if item already exists for this campus
            $check_query = "SELECT * FROM stock WHERE campus_id = :campus_id AND item_name = :item_name";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(':campus_id', $data['campus_id']);
            $check_stmt->bindParam(':item_name', $data['item_name']);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                return false; // Item already exists
            }
            
            $query = "INSERT INTO stock 
                      (campus_id, item_name, category, quantity, unit, minimum_threshold, created_at) 
                      VALUES 
                      (:campus_id, :item_name, :category, :quantity, :unit, :minimum_threshold, NOW())";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':campus_id', $data['campus_id']);
            $stmt->bindParam(':item_name', $data['item_name']);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':quantity', $data['quantity']);
            $stmt->bindParam(':unit', $data['unit']);
            $stmt->bindParam(':minimum_threshold', $data['minimum_threshold']);
            
            if ($stmt->execute()) {
                return self::find($db->lastInsertId());
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Find stock item by ID
     * 
     * @param int $id
     * @return array|false
     */
    public static function find($id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT s.*, c.campus_name,
                      CASE WHEN s.quantity <= s.minimum_threshold THEN 1 ELSE 0 END as is_low_stock
                      FROM stock s
                      LEFT JOIN campuses c ON s.campus_id = c.id
                      WHERE s.id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get all stock items (Admin view)
     * 
     * @param array $filters
     * @return array
     */
    public static function getAll($filters = []) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT s.*, c.campus_name,
                      CASE WHEN s.quantity <= s.minimum_threshold THEN 1 ELSE 0 END as is_low_stock
                      FROM stock s
                      LEFT JOIN campuses c ON s.campus_id = c.id
                      WHERE 1=1";
            
            if (!empty($filters['category'])) {
                $query .= " AND s.category = :category";
            }
            
            if (isset($filters['low_stock']) && $filters['low_stock'] === true) {
                $query .= " AND s.quantity <= s.minimum_threshold";
            }
            
            $query .= " ORDER BY s.last_updated DESC";
            
            $stmt = $db->prepare($query);
            
            if (!empty($filters['category'])) {
                $stmt->bindParam(':category', $filters['category']);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get stock items by campus
     * 
     * @param int $campus_id
     * @param array $filters
     * @return array
     */
    public static function getByCampus($campus_id, $filters = []) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT s.*, c.campus_name,
                      CASE WHEN s.quantity <= s.minimum_threshold THEN 1 ELSE 0 END as is_low_stock
                      FROM stock s
                      LEFT JOIN campuses c ON s.campus_id = c.id
                      WHERE s.campus_id = :campus_id";
            
            if (!empty($filters['category'])) {
                $query .= " AND s.category = :category";
            }
            
            if (isset($filters['low_stock']) && $filters['low_stock'] === true) {
                $query .= " AND s.quantity <= s.minimum_threshold";
            }
            
            $query .= " ORDER BY s.last_updated DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':campus_id', $campus_id);
            
            if (!empty($filters['category'])) {
                $stmt->bindParam(':category', $filters['category']);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Update stock item
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
            
            $fields[] = "last_updated = NOW()";
            
            $query = "UPDATE stock SET " . implode(', ', $fields) . " WHERE id = :id";
            
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
     * Delete stock item
     * 
     * @param int $id
     * @return bool
     */
    public static function delete($id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "DELETE FROM stock WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Count low stock items//happening now
     * 
     * @param int|null $campus_id
     * @return int
     */
    public static function countLowStock($campus_id = null) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            if ($campus_id) {
                $query = "SELECT COUNT(*) as count FROM stock 
                          WHERE campus_id = :campus_id AND quantity <= minimum_threshold";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':campus_id', $campus_id);
            } else {
                $query = "SELECT COUNT(*) as count FROM stock WHERE quantity <= minimum_threshold";
                $stmt = $db->prepare($query);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? intval($result['count']) : 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}
?>