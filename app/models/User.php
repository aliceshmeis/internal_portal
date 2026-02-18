<?php
/**
 * User Model
 */

class User extends Model {
    protected $table = 'users';

    /**
     * Find user by Google ID
     */
    public function findByGoogleId($googleId) {
        $query = "SELECT * FROM " . $this->table . " WHERE google_id = :google_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':google_id', $googleId);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Create new user from Google data
     */
    public function createFromGoogle($googleData) {
        $query = "INSERT INTO " . $this->table . " 
                  (google_id, email, name, profile_picture, role, campus_id, is_active) 
                  VALUES 
                  (:google_id, :email, :name, :profile_picture, :role, :campus_id, :is_active)";

        $stmt = $this->db->prepare($query);

        $defaultRole     = ROLE_STAFF;
        $defaultCampusId = 1;
        $isActive        = 1;

        $stmt->bindParam(':google_id',       $googleData['id']);
        $stmt->bindParam(':email',           $googleData['email']);
        $stmt->bindParam(':name',            $googleData['name']);
        $stmt->bindParam(':profile_picture', $googleData['picture']);
        $stmt->bindParam(':role',            $defaultRole);
        $stmt->bindParam(':campus_id',       $defaultCampusId);
        $stmt->bindParam(':is_active',       $isActive);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Update last login time
     */
    public function updateLastLogin($userId) {
        $query = "UPDATE " . $this->table . " SET last_login = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }

    /**
     * Get all users with campus name
     */
    public function getAll() {
        $query = "SELECT u.id, u.name, u.email, u.role, u.campus_id,
                         u.is_active, u.login_method, u.last_login, u.created_at,
                         c.campus_name
                  FROM " . $this->table . " u
                  LEFT JOIN campuses c ON u.campus_id = c.id
                  ORDER BY u.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find user by ID (with campus)
     */
    public function findById($id) {
        $query = "SELECT u.*, c.campus_name
                  FROM " . $this->table . " u
                  LEFT JOIN campuses c ON u.campus_id = c.id
                  WHERE u.id = :id LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Create a new user (email/password login)
     */
    public function createUser($data) {
        $query = "INSERT INTO " . $this->table . "
                    (name, email, password, role, campus_id, department_id, is_active, login_method, created_at)
                  VALUES
                    (:name, :email, :password, :role, :campus_id, :department_id, :is_active, 'email', NOW())";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name',      $data['name']);
        $stmt->bindParam(':email',     $data['email']);
        $stmt->bindParam(':password',  $data['password']);
        $stmt->bindParam(':role',      $data['role']);
        $stmt->bindParam(':campus_id', $data['campus_id'], PDO::PARAM_INT);
        $stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_INT);

        if (is_null($data['department_id'])) {
            $stmt->bindValue(':department_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':department_id', intval($data['department_id']), PDO::PARAM_INT);
        }

        if ($stmt->execute()) {
            return $this->findById($this->db->lastInsertId());
        }

        return false;
    }

    public function updateUser($id, $name, $email, $role, $campus_id, $department_id, $status) {
    $sql = "UPDATE users 
            SET name = :name, 
                email = :email, 
                role = :role, 
                campus_id = :campus_id, 
                department_id = :department_id, 
                status = :status
            WHERE id = :id";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':name',          $name,                     PDO::PARAM_STR);
    $stmt->bindValue(':email',         $email,                    PDO::PARAM_STR);
    $stmt->bindValue(':role',          $role,                     PDO::PARAM_STR);
    $stmt->bindValue(':campus_id',     $campus_id,                $campus_id    ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':department_id', $department_id,            $department_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':status',        $status,                   PDO::PARAM_STR);
    $stmt->bindValue(':id',            $id,                       PDO::PARAM_INT);

    return $stmt->execute();
}

public function deleteUser($id) {
    $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}

    /**
     * Update a user
     */
    public function updateUser($id, $data) {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $fields[]        = "$key = :$key";
            $params[":$key"] = $value;
        }

        $fields[] = "updated_at = NOW()";

        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt  = $this->db->prepare($query);

        if ($stmt->execute($params)) {
            return $this->findById($id);
        }

        return false;
    }

    /**
     * Delete a user
     */
    public function deleteUser($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>