<?php
require_once __DIR__ . '/../../config/database.php';

class Asset {
    
    /**
     * Create a new asset
     */
   public static function create($data) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Generate asset tag
        $asset_tag = 'AST-' . strtoupper($data['category'][0]) . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
        $query = "INSERT INTO assets 
                  (asset_tag, campus_id, category, name, description, serial_number, status,
                   building, floor, room,
                   assigned_to, purchase_date, purchase_cost, warranty_expiry, created_at) 
                  VALUES 
                  (:asset_tag, :campus_id, :category, :name, :description, :serial_number, :status,
                   :building, :floor, :room,
                   :assigned_to, :purchase_date, :purchase_cost, :warranty_expiry, NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':asset_tag',       $asset_tag);
        $stmt->bindParam(':campus_id',       $data['campus_id']);
        $stmt->bindParam(':category',        $data['category']);
        $stmt->bindParam(':name',            $data['name']);
        $stmt->bindParam(':description',     $data['description']);
        $stmt->bindParam(':serial_number',   $data['serial_number']);
        $stmt->bindParam(':status',          $data['status']);
        $stmt->bindParam(':building',        $data['building']);
        $stmt->bindParam(':floor',           $data['floor']);
        $stmt->bindParam(':room',            $data['room']);
        $stmt->bindParam(':purchase_date',   $data['purchase_date']);
        $stmt->bindParam(':purchase_cost',   $data['purchase_cost']);
        $stmt->bindParam(':warranty_expiry', $data['warranty_expiry']);

        $assigned_to = $data['assigned_to'] ?? null;
        $stmt->bindParam(':assigned_to', $assigned_to, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return self::find($db->lastInsertId());
        }
        
        return false;
    } catch (Exception $e) {
        return false;
    }
}
    
    /**
     * Find asset by ID - with assigned user name
     */
    public static function find($id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT a.*, 
                      c.campus_name,
                      u.name  AS assigned_user_name,
                      u.email AS assigned_user_email
                      FROM assets a
                      LEFT JOIN campuses c ON a.campus_id = c.id
                      LEFT JOIN users u    ON a.assigned_to = u.id
                      WHERE a.id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
    
    public static function getByUser($user_id) {
    try {
        $db   = (new Database())->getConnection();
        $stmt = $db->prepare("
            SELECT a.id, a.asset_tag, a.name, a.category, a.status, c.campus_name
            FROM assets a
            LEFT JOIN campuses c ON a.campus_id = c.id
            WHERE a.assigned_to = :user_id
            ORDER BY a.updated_at DESC
            LIMIT 10
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}
    /**
     * Get all assets (Admin view)
     */
    public static function getAll($filters = []) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT a.*, 
                      c.campus_name,
                      u.name  AS assigned_user_name,
                      u.email AS assigned_user_email
                      FROM assets a
                      LEFT JOIN campuses c ON a.campus_id = c.id
                      LEFT JOIN users u    ON a.assigned_to = u.id
                      WHERE 1=1";

            $params = [];
            
            if (!empty($filters['category'])) {
                $query .= " AND a.category = :category";
                $params[':category'] = $filters['category'];
            }
            
            if (!empty($filters['status'])) {
                $query .= " AND a.status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['search'])) {
                $query .= " AND (a.name LIKE :search OR a.asset_tag LIKE :search OR a.serial_number LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            $query .= " ORDER BY a.created_at DESC";
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get assets by campus (Staff/Asset Manager view)
     */
    public static function getByCampus($campus_id, $filters = []) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT a.*, 
                      c.campus_name,
                      u.name  AS assigned_user_name,
                      u.email AS assigned_user_email
                      FROM assets a
                      LEFT JOIN campuses c ON a.campus_id = c.id
                      LEFT JOIN users u    ON a.assigned_to = u.id
                      WHERE a.campus_id = :campus_id";

            $params = [':campus_id' => $campus_id];
            
            if (!empty($filters['category'])) {
                $query .= " AND a.category = :category";
                $params[':category'] = $filters['category'];
            }
            
            if (!empty($filters['status'])) {
                $query .= " AND a.status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['search'])) {
                $query .= " AND (a.name LIKE :search OR a.asset_tag LIKE :search OR a.serial_number LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            $query .= " ORDER BY a.created_at DESC";
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Update asset
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
            $stmt->bindParam(':id',      $id);
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