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
        
        // Default values for new users
        $defaultRole = ROLE_STAFF; // New users are Staff by default
        $defaultCampusId = 1; // Assign to first campus by default (you can change this)
        $isActive = 1;
        
        $stmt->bindParam(':google_id', $googleData['id']);
        $stmt->bindParam(':email', $googleData['email']);
        $stmt->bindParam(':name', $googleData['name']);
        $stmt->bindParam(':profile_picture', $googleData['picture']);
        $stmt->bindParam(':role', $defaultRole);
        $stmt->bindParam(':campus_id', $defaultCampusId);
        $stmt->bindParam(':is_active', $isActive);
        
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
}
?>