<?php
require_once __DIR__ . '/../../config/database.php';

class Asset {
    
    public static function create($data) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
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
            $stmt  = $db->prepare($query);
            
            if ($stmt->execute($params)) {
                return self::find($id);
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public static function delete($id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $stmt = $db->prepare(
                "UPDATE assets SET status = 'Retired', assigned_to = NULL, updated_at = NOW() WHERE id = :id"
            );
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) return self::find($id);
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Assign asset to user — updates assets table AND inserts into asset_assignments
     */
    public static function assign($id, $user_id, $assigned_by = null, $expected_return_date = null) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            $db->beginTransaction();

            // 1. Close any existing open assignment for this asset
            $db->prepare(
                "UPDATE asset_assignments SET returned_at = NOW() WHERE asset_id = :id AND returned_at IS NULL"
            )->execute([':id' => $id]);

            // 2. Insert new assignment row
            $db->prepare(
                "INSERT INTO asset_assignments (asset_id, user_id, assigned_by, assigned_at, expected_return_date)
                 VALUES (:asset_id, :user_id, :assigned_by, NOW(), :ret_date)"
            )->execute([
                ':asset_id'    => $id,
                ':user_id'     => $user_id,
                ':assigned_by' => $assigned_by,
                ':ret_date'    => $expected_return_date,
            ]);

            // 3. Update assets table
            $db->prepare(
                "UPDATE assets SET assigned_to = :user_id, status = 'In Use',
                 expected_return_date = :ret_date, updated_at = NOW() WHERE id = :id"
            )->execute([
                ':user_id'   => $user_id,
                ':ret_date'  => $expected_return_date,
                ':id'        => $id,
            ]);

            $db->commit();
            return self::find($id);
        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            return false;
        }
    }
    
    /**
     * Return asset — closes assignment row AND resets assets table
     */
    public static function returnAsset($id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            $db->beginTransaction();

            // 1. Close open assignment row
            $db->prepare(
                "UPDATE asset_assignments SET returned_at = NOW() WHERE asset_id = :id AND returned_at IS NULL"
            )->execute([':id' => $id]);

            // 2. Reset asset
            $db->prepare(
                "UPDATE assets SET assigned_to = NULL, status = 'Available',
                 expected_return_date = NULL, updated_at = NOW() WHERE id = :id"
            )->execute([':id' => $id]);

            $db->commit();
            return self::find($id);
        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            return false;
        }
    }
}
?>