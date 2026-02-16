<?php
require_once __DIR__ . '/../config/database.php';

class AuthHelper {
    
    /**
     * Generate CSRF Token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF Token
     */
    public static function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
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
     * SECURITY: Includes session regeneration
     */
    public static function loginUser($email, $password) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Get user by email (supports both email and google login methods)
            $query = "SELECT * FROM users WHERE email = :email AND is_active = 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            // Check if user has a password set
            if (empty($user['password'])) {
                return ['success' => false, 'message' => 'This account uses Google login. Please login with Google.'];
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            // SECURITY: Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);
            
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
            $_SESSION['login_time'] = time();
            
            // Generate CSRF token for this session
            self::generateCSRFToken();
            
            return ['success' => true, 'message' => 'Login successful', 'user' => $user];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Logout user
     * SECURITY: Properly destroy session
     */
    public static function logout() {
        // Unset all session variables
        $_SESSION = array();
        
        // Delete session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
        
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    /**
     * Check if session is valid (timeout check)
     */
    public static function isSessionValid($timeout = 3600) {
        if (!isset($_SESSION['login_time'])) {
            return false;
        }
        
        if (time() - $_SESSION['login_time'] > $timeout) {
            self::logout();
            return false;
        }
        
        // Refresh login time
        $_SESSION['login_time'] = time();
        return true;
    }
}
?>