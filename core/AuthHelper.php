<?php
require_once __DIR__ . '/../config/database.php';

class AuthHelper {
    
    /**
     * Register user with email and password
     */
    public static function registerUser($email, $password, $name, $campus_id, $role = 'Staff') {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Check if email already exists
            $check_query = "SELECT * FROM users WHERE email = :email";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(':email', $email);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $query = "INSERT INTO users 
                      (email, password, name, campus_id, role, login_method, is_active, email_verified, created_at) 
                      VALUES 
                      (:email, :password, :name, :campus_id, :role, 'email', 1, 1, NOW())";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':campus_id', $campus_id);
            $stmt->bindParam(':role', $role);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Registration successful'];
            }
            
            return ['success' => false, 'message' => 'Registration failed'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Login user with email and password
     */
    public static function loginUser($email, $password) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Get user
            $query = "SELECT * FROM users WHERE email = :email AND login_method = 'email' AND is_active = 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            // Update last login
            $update_query = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':id', $user['id']);
            $update_stmt->execute();
            
            // Set session
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['campus_id'] = $user['campus_id'];
            $_SESSION['is_active'] = $user['is_active'];
            $_SESSION['login_method'] = 'email';
            
            return ['success' => true, 'message' => 'Login successful', 'user' => $user];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        session_start();
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
}
?>