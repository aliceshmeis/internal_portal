<?php
require_once __DIR__ . '/../../config/database.php';

class Ticket {
    
    /**
     * Create a new ticket - FIXED to support assigned_to
     * 
     * @param array $data
     * @return array|false
     */
    public static function create($data) {
    try {
        $database = new Database();
        $db       = $database->getConnection();

        $ticket_number = 'TKT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $query = "INSERT INTO tickets
                    (ticket_number, campus_id, title, description, status, priority,
                     category, building, floor, room, ssid,
                     created_by, assigned_to, created_at)
                  VALUES
                    (:ticket_number, :campus_id, :title, :description, 'Open', :priority,
                     :category, :building, :floor, :room, :ssid,
                     :created_by, :assigned_to, NOW())";

        $stmt = $db->prepare($query);
        $stmt->bindValue(':ticket_number', $ticket_number);
        $stmt->bindValue(':campus_id',     $data['campus_id'],   PDO::PARAM_INT);
        $stmt->bindValue(':title',         $data['title']);
        $stmt->bindValue(':description',   $data['description']);
        $stmt->bindValue(':priority',      $data['priority']);
        $stmt->bindValue(':category',      $data['category']  ?? null);
        $stmt->bindValue(':building',      $data['building']  ?? null);
        $stmt->bindValue(':floor',         $data['floor']     ?? null);
        $stmt->bindValue(':room',          $data['room']      ?? null);
        $stmt->bindValue(':ssid',          $data['ssid']      ?? null);
        $stmt->bindValue(':created_by',    $data['created_by'],  PDO::PARAM_INT);

        $assigned_to = $data['assigned_to'] ?? null;
        $stmt->bindValue(':assigned_to', $assigned_to, $assigned_to ? PDO::PARAM_INT : PDO::PARAM_NULL);

        if ($stmt->execute()) {
            return self::find($db->lastInsertId());
        }

        return false;
    } catch (Exception $e) {
        return false;
    }
}
    
    /**
     * Find ticket by ID
     * 
     * @param int $id
     * @return array|false
     */
    public static function find($id) {
    try {
        $database = new Database();
        $db       = $database->getConnection();

        $query = "SELECT t.*,
                    u.name  AS creator_name,  u.email  AS creator_email,
                    a.name  AS assigned_name, a.email  AS assigned_email,
                    c.campus_name
                  FROM tickets t
                  LEFT JOIN users    u ON t.created_by  = u.id
                  LEFT JOIN users    a ON t.assigned_to = a.id
                  LEFT JOIN campuses c ON t.campus_id   = c.id
                  WHERE t.id = :id";

        $stmt = $db->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$ticket) return false;

        // Attach file attachments
        $attachStmt = $db->prepare("
            SELECT ta.*, u.name AS uploaded_by_name
            FROM ticket_attachments ta
            LEFT JOIN users u ON ta.uploaded_by = u.id
            WHERE ta.ticket_id = :ticket_id
            ORDER BY ta.uploaded_at ASC
        ");
        $attachStmt->bindValue(':ticket_id', $id, PDO::PARAM_INT);
        $attachStmt->execute();
        $ticket['attachments'] = $attachStmt->fetchAll(PDO::FETCH_ASSOC);

        return $ticket;
    } catch (Exception $e) {
        return false;
    }
}
    
    /**
     * Get all tickets (Admin view)
     * 
     * @param array $filters
     * @return array
     */
    public static function getAll($filters = []) {
    try {
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT t.*,
                         u.name  AS creator_name,
                         a.name  AS assignee_name,
                         c.campus_name,
                         d.name  AS department_name
                  FROM tickets t
                  LEFT JOIN users u       ON t.created_by   = u.id
                  LEFT JOIN users a       ON t.assigned_to  = a.id
                  LEFT JOIN campuses c    ON t.campus_id    = c.id
                  LEFT JOIN departments d ON a.department_id = d.id
                  WHERE 1=1";

        if (!empty($filters['status']))   $query .= " AND t.status   = :status";
        if (!empty($filters['priority'])) $query .= " AND t.priority = :priority";
        if (!empty($filters['campus_id'])) $query .= " AND t.campus_id = :campus_id";

        $query .= " ORDER BY t.created_at DESC";

        $stmt = $db->prepare($query);

        if (!empty($filters['status']))    $stmt->bindParam(':status',    $filters['status']);
        if (!empty($filters['priority']))  $stmt->bindParam(':priority',  $filters['priority']);
        if (!empty($filters['campus_id'])) $stmt->bindParam(':campus_id', $filters['campus_id']);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}
    
    /**
     * Get tickets by campus (Staff view)
     * 
     * @param int $campus_id
     * @param array $filters
     * @return array
     */
    public static function getByCampus($campus_id, $filters = []) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT t.*, u.name as creator_name, a.name as assignee_name, c.campus_name
                      FROM tickets t
                      LEFT JOIN users u ON t.created_by = u.id
                      LEFT JOIN users a ON t.assigned_to = a.id
                      LEFT JOIN campuses c ON t.campus_id = c.id
                      WHERE t.campus_id = :campus_id";
            
            if (!empty($filters['status'])) {
                $query .= " AND t.status = :status";
            }
            
            if (!empty($filters['priority'])) {
                $query .= " AND t.priority = :priority";
            }
            
            $query .= " ORDER BY t.created_at DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':campus_id', $campus_id);
            
            if (!empty($filters['status'])) {
                $stmt->bindParam(':status', $filters['status']);
            }
            
            if (!empty($filters['priority'])) {
                $stmt->bindParam(':priority', $filters['priority']);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Update ticket
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
            
            $query = "UPDATE tickets SET " . implode(', ', $fields) . " WHERE id = :id";
            
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
     * Close ticket
     * 
     * @param int $id
     * @return array|false
     */
    public static function close($id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "UPDATE tickets 
                      SET status = 'Closed',
                          closed_at = NOW(),
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
     * Assign ticket to user
     * 
     * @param int $id
     * @param int $user_id
     * @return array|false
     */
    public static function assign($id, $user_id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "UPDATE tickets 
                      SET assigned_to = :user_id,
                          status = CASE 
                              WHEN status = 'Open' THEN 'In Progress'
                              ELSE status
                          END,
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
     * Resolve ticket
     * 
     * @param int $id
     * @param string|null $notes
     * @return array|false
     */
    public static function resolve($id, $notes = null) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $resolution_text = $notes ?? 'Issue resolved';
            
            $query = "UPDATE tickets 
                      SET status = 'Resolved',
                          description = CONCAT(description, '\n\n[Resolution Notes] ', :notes),
                          updated_at = NOW()
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':notes', $resolution_text);
            
            if ($stmt->execute()) {
                return self::find($id);
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Add comment to ticket
     * 
     * @param int $ticket_id
     * @param int $user_id
     * @param string $comment
     * @return array|false
     */
    public static function addComment($ticket_id, $user_id, $comment) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "INSERT INTO ticket_comments 
                      (ticket_id, user_id, comment, created_at) 
                      VALUES 
                      (:ticket_id, :user_id, :comment, NOW())";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':ticket_id', $ticket_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':comment', $comment);
            
            if ($stmt->execute()) {
                $comment_id = $db->lastInsertId();
                
                // Update ticket's updated_at
                $update_query = "UPDATE tickets SET updated_at = NOW() WHERE id = :id";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(':id', $ticket_id);
                $update_stmt->execute();
                
                // Get the comment with user info
                return self::getComment($comment_id);
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get single comment
     * 
     * @param int $id
     * @return array|false
     */
    public static function getComment($id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT tc.*, u.name as user_name, u.email as user_email, u.role as user_role
                      FROM ticket_comments tc
                      LEFT JOIN users u ON tc.user_id = u.id
                      WHERE tc.id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get all comments for a ticket
     * 
     * @param int $ticket_id
     * @return array
     */
    public static function getComments($ticket_id) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT tc.*, u.name as user_name, u.email as user_email, u.role as user_role
                      FROM ticket_comments tc
                      LEFT JOIN users u ON tc.user_id = u.id
                      WHERE tc.ticket_id = :ticket_id
                      ORDER BY tc.created_at ASC";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':ticket_id', $ticket_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>