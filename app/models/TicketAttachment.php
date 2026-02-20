<?php
require_once __DIR__ . '/../../config/database.php';

class TicketAttachment {
    public static function create($data) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                INSERT INTO ticket_attachments 
                    (ticket_id, file_name, file_path, file_size, file_type, uploaded_by)
                VALUES 
                    (:ticket_id, :file_name, :file_path, :file_size, :file_type, :uploaded_by)
            ");
            $stmt->execute([
                ':ticket_id'   => $data['ticket_id'],
                ':file_name'   => $data['file_name'],
                ':file_path'   => $data['file_path'],
                ':file_size'   => $data['file_size'],
                ':file_type'   => $data['file_type'],
                ':uploaded_by' => $data['uploaded_by']
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
