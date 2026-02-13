<?php
require_once __DIR__ . '/../../config/database.php';

class Asset {
    
    /**
     * Create a new asset
     * 
     * @param array $data
     * @return array|false
     */
    public static function create($data) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Generate asset tag
            $asset_tag = 'AST-' . strtoupper($data['category'][0]) . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            $query = "INSERT INTO assets 
                      (asset_tag, campus_id, category, name, description, serial_number, status, 
                       purchase_date, purchase_cost, warranty_expiry, created_at) 
                      VALUES 
                      (:asset_tag, :campus_id, :category, :name, :description, :serial_number, :status,
                       :purchase_date, :purchase_cost, :warranty_expiry, NOW())";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':asset_tag', $asset_tag);
            $stmt->bindParam(':campus_id', $data['campus_id']);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':serial_number', $data['serial_number']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':purchase_date', $data['purchase_date']);
            $stmt->bindParam(':purchase_cost', $data['purchase_cost']);
            $stmt->bindParam(':warranty_expiry', $data['warranty_expiry']);
            
            if ($stmt->execute()) {
                return self::find($db->lastInsertId());
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Find asset by ID
     * 
     * @param int $id
     * @return array|false
     */
    public static function find($id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT a.*, 
                      c.campus_name,
                      u.name as assigned_user_name, u.email as assigned_user_email
                      FROM assets a
                      LEFT JOIN campuses c ON a.campus_id = c.id
                      LEFT JOIN users u ON a.assigned_to = u.id
                      WHERE a.id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get all assets (Admin view)
     * 
     * @param array $filters
     * @return array
     */
    public static function getAll($filters = []) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT a.*, c.campus_name, u.name as assigned_user_name
                      FROM assets a
                      LEFT JOIN campuses c ON a.campus_id = c.id
                      LEFT JOIN users u ON a.assigned_to = u.id
                      WHERE 1=1";
            
            if (!empty($filters['category'])) {
                $query .= " AND a.category = :category";
            }
            
            if (!empty($filters['status'])) {
                $query .= " AND a.status = :status";
            }
            
            $query .= " ORDER BY a.created_at DESC";
            
            $stmt = $db->prepare($query);
            
            if (!empty($filters['category'])) {
                $stmt->bindParam(':category', $filters['category']);
            }
            
            if (!empty($filters['status'])) {
                $stmt->bindParam(':status', $filters['status']);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get assets by campus
     * 
     * @param int $campus_id
     * @param array $filters
     * @return array
     */
    public static function getByCampus($campus_id, $filters = []) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT a.*, c.campus_name, u.name as assigned_user_name
                      FROM assets a
                      LEFT JOIN campuses c ON a.campus_id = c.id
                      LEFT JOIN users u ON a.assigned_to = u.id
                      WHERE a.campus_id = :campus_id";
            
            if (!empty($filters['category'])) {
                $query .= " AND a.category = :category";
            }
            
            if (!empty($filters['status'])) {
                $query .= " AND a.status = :status";
            }
            
            $query .= " ORDER BY a.created_at DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':campus_id', $campus_id);
            
            if (!empty($filters['category'])) {
                $stmt->bindParam(':category', $filters['category']);
            }
            
            if (!empty($filters['status'])) {
                $stmt->bindParam(':status', $filters['status']);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Update asset
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
            
            $query = "UPDATE assets SET " . implode(', ', $fields) . " WHERE id = :id";
            
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
     * Retire/delete asset (soft delete)
     * 
     * @param int $id
     * @return array|false
     */
    public static function delete($id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "UPDATE assets 
                      SET status = 'Retired',
                          assigned_to = NULL,
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
     * Assign asset to user
     * 
     * @param int $id
     * @param int $user_id
     * @return array|false
     */
    public static function assign($id, $user_id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "UPDATE assets 
                      SET assigned_to = :user_id,
                          status = 'In Use',
                          updated_at = NOW()
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $user_id);
            
            if ($stmt->execute()) {
                return self::find($id);
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Return asset (make available)
     * 
     * @param int $id
     * @return array|false
     */
    public static function returnAsset($id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "UPDATE assets 
                      SET assigned_to = NULL,
                          status = 'Available',
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
}
?>