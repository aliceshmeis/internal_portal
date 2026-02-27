<?php
require_once __DIR__ . '/../../config/database.php';

class Subtask {

    // ─── CREATE ───────────────────────────────────────────────────────────────
    public static function create($data) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                INSERT INTO ticket_subtasks
                    (ticket_id, title, description, priority, due_date, created_by)
                VALUES
                    (:ticket_id, :title, :description, :priority, :due_date, :created_by)
            ");
            $stmt->execute([
                ':ticket_id'   => $data['ticket_id'],
                ':title'       => $data['title'],
                ':description' => $data['description'] ?? null,
                ':priority'    => $data['priority']    ?? 'Medium',
                ':due_date'    => $data['due_date']    ?? null,
                ':created_by'  => $data['created_by'],
            ]);
            $subtask_id = $db->lastInsertId();

            if (!empty($data['user_ids']) && is_array($data['user_ids'])) {
                foreach ($data['user_ids'] as $user_id) {
                    self::assignUser($subtask_id, $user_id);
                }
            }

            return self::find($subtask_id);
        } catch (Exception $e) {
            return false;
        }
    }

    // ─── FIND BY ID (with assignments + comments) ─────────────────────────────
    public static function find($id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                SELECT s.*, u.name AS created_by_name
                FROM ticket_subtasks s
                LEFT JOIN users u ON s.created_by = u.id
                WHERE s.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $subtask = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$subtask) return false;

            $subtask['assignments'] = self::getAssignments($id);
            $subtask['comments']    = self::getComments($id);
            return $subtask;
        } catch (Exception $e) {
            return false;
        }
    }

    // ─── GET ALL FOR A TICKET (with assignments + comments) ───────────────────
    public static function getByTicket($ticket_id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                SELECT s.*, u.name AS created_by_name
                FROM ticket_subtasks s
                LEFT JOIN users u ON s.created_by = u.id
                WHERE s.ticket_id = :ticket_id
                ORDER BY s.created_at ASC
            ");
            $stmt->execute([':ticket_id' => $ticket_id]);
            $subtasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($subtasks as &$subtask) {
                $subtask['assignments'] = self::getAssignments($subtask['id']);
                $subtask['comments']    = self::getComments($subtask['id']);
            }

            return $subtasks;
        } catch (Exception $e) {
            return [];
        }
    }

    // ─── GET SUBTASKS ASSIGNED TO A USER ──────────────────────────────────────
    // No campus scoping here — user sees subtasks they are explicitly assigned to
    public static function getByUser($user_id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                SELECT s.*,
                       t.ticket_number, t.title AS ticket_title,
                       c.campus_name,
                       sa.user_status,
                       u.name AS created_by_name
                FROM ticket_subtask_assignments sa
                JOIN ticket_subtasks s ON sa.subtask_id = s.id
                JOIN tickets t         ON s.ticket_id   = t.id
                LEFT JOIN campuses c   ON t.campus_id   = c.id
                LEFT JOIN users u      ON s.created_by  = u.id
                WHERE sa.user_id = :user_id
                  AND s.status  != 'Done'
                ORDER BY s.updated_at DESC
            ");
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // ─── ASSIGN USER ──────────────────────────────────────────────────────────
    public static function assignUser($subtask_id, $user_id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                INSERT IGNORE INTO ticket_subtask_assignments (subtask_id, user_id)
                VALUES (:subtask_id, :user_id)
            ");
            $stmt->execute([':subtask_id' => $subtask_id, ':user_id' => $user_id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ─── GET ASSIGNMENTS ──────────────────────────────────────────────────────
    public static function getAssignments($subtask_id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                SELECT sa.*, u.name AS user_name, u.email AS user_email
                FROM ticket_subtask_assignments sa
                JOIN users u ON sa.user_id = u.id
                WHERE sa.subtask_id = :subtask_id
            ");
            $stmt->execute([':subtask_id' => $subtask_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // ─── UPDATE USER STATUS (AND logic) ───────────────────────────────────────
    public static function updateUserStatus($subtask_id, $user_id, $user_status) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                UPDATE ticket_subtask_assignments
                SET user_status = :user_status, updated_at = NOW()
                WHERE subtask_id = :subtask_id AND user_id = :user_id
            ");
            $stmt->execute([
                ':user_status' => $user_status,
                ':subtask_id'  => $subtask_id,
                ':user_id'     => $user_id,
            ]);

            self::recalculateStatus($subtask_id);
            return self::find($subtask_id);
        } catch (Exception $e) {
            return false;
        }
    }

    // ─── AND LOGIC STATUS RECALCULATION ───────────────────────────────────────
    private static function recalculateStatus($subtask_id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                SELECT user_status FROM ticket_subtask_assignments
                WHERE subtask_id = :subtask_id
            ");
            $stmt->execute([':subtask_id' => $subtask_id]);
            $statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($statuses)) return;

            if (count(array_filter($statuses, fn($s) => $s === 'Done')) === count($statuses)) {
                $new = 'Done';
            } elseif (in_array('In Progress', $statuses)) {
                $new = 'In Progress';
            } else {
                $new = 'Open';
            }

            $db->prepare("UPDATE ticket_subtasks SET status = :status, updated_at = NOW() WHERE id = :id")
               ->execute([':status' => $new, ':id' => $subtask_id]);
        } catch (Exception $e) {}
    }

    // ─── ADD COMMENT ──────────────────────────────────────────────────────────
    public static function addComment($subtask_id, $user_id, $comment) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                INSERT INTO subtask_comments (subtask_id, user_id, comment)
                VALUES (:subtask_id, :user_id, :comment)
            ");
            $stmt->execute([
                ':subtask_id' => $subtask_id,
                ':user_id'    => $user_id,
                ':comment'    => $comment,
            ]);
            $id = $db->lastInsertId();
            $db->prepare("UPDATE ticket_subtasks SET updated_at = NOW() WHERE id = :id")
               ->execute([':id' => $subtask_id]);
            return self::getComment($id);
        } catch (Exception $e) {
            return false;
        }
    }

    // ─── GET SINGLE COMMENT ───────────────────────────────────────────────────
    public static function getComment($id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                SELECT sc.*, u.name AS user_name, u.role AS user_role
                FROM subtask_comments sc
                JOIN users u ON sc.user_id = u.id
                WHERE sc.id = :id
            ");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    // ─── GET COMMENTS (only assigned users + admin can see) ───────────────────
    public static function getComments($subtask_id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                SELECT sc.*, u.name AS user_name, u.role AS user_role
                FROM subtask_comments sc
                JOIN users u ON sc.user_id = u.id
                WHERE sc.subtask_id = :subtask_id
                ORDER BY sc.created_at ASC
            ");
            $stmt->execute([':subtask_id' => $subtask_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // ─── CHECK IF USER IS ASSIGNED ────────────────────────────────────────────
    public static function isUserAssigned($subtask_id, $user_id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM ticket_subtask_assignments
                WHERE subtask_id = :subtask_id AND user_id = :user_id
            ");
            $stmt->execute([':subtask_id' => $subtask_id, ':user_id' => $user_id]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    // ─── GET DEPARTMENTS BY TYPE FOR ADMIN'S CAMPUS ───────────────────────────
    public static function getDepartmentsByType($campus_id, $type) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                SELECT id, name, type
                FROM departments
                WHERE campus_id = :campus_id
                  AND type      = :type
                  AND is_active = 1
                ORDER BY name ASC
            ");
            $stmt->execute([':campus_id' => $campus_id, ':type' => $type]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // ─── GET USERS BY DEPARTMENT (same campus) ────────────────────────────────
    public static function getUsersByDepartment($department_id, $campus_id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                SELECT u.id, u.name, u.email, u.role, d.name AS department_name
                FROM users u
                JOIN departments d ON u.department_id = d.id
                WHERE u.department_id = :department_id
                  AND u.campus_id     = :campus_id
                  AND u.is_active     = 1
                ORDER BY u.name ASC
            ");
            $stmt->execute([
                ':department_id' => $department_id,
                ':campus_id'     => $campus_id,
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}