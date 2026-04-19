<?php
require_once __DIR__ . '/../../config/database.php';

class Supplier {

    public static function create($data) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                INSERT INTO suppliers (name, email, phone, address, is_active)
                VALUES (:name, :email, :phone, :address, 1)
            ");
            $stmt->execute([
                ':name'    => trim($data['name']),
                ':email'   => trim($data['email']   ?? ''),
                ':phone'   => trim($data['phone']   ?? ''),
                ':address' => trim($data['address'] ?? ''),
            ]);
            return self::find($db->lastInsertId());
        } catch (Exception $e) { return false; }
    }

    public static function find($id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("SELECT * FROM suppliers WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return false; }
    }

    public static function getAll() {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("SELECT * FROM suppliers WHERE is_active = 1 ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public static function update($id, $data) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                UPDATE suppliers
                SET name = :name, email = :email, phone = :phone, address = :address
                WHERE id = :id
            ");
            $stmt->execute([
                ':name'    => trim($data['name']),
                ':email'   => trim($data['email']   ?? ''),
                ':phone'   => trim($data['phone']   ?? ''),
                ':address' => trim($data['address'] ?? ''),
                ':id'      => $id,
            ]);
            return self::find($id);
        } catch (Exception $e) { return false; }
    }

    public static function deactivate($id) {
        try {
            $db   = (new Database())->getConnection();
            $db->prepare("UPDATE suppliers SET is_active = 0 WHERE id = :id")->execute([':id' => $id]);
            return true;
        } catch (Exception $e) { return false; }
    }
}
?>