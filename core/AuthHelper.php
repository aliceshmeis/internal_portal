<?php
require_once __DIR__ . '/../config/database.php';

class AuthHelper {
    
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function registerUser($email, $password, $name, $campus_id, $role = 'Staff') {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $check_query = "SELECT * FROM users WHERE email = :email";
            $check_stmt  = $db->prepare($check_query);
            $check_stmt->bindParam(':email', $email);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
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
    
    public static function loginUser($email, $password) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // JOIN departments to get department name (IT, Engineering, etc.)
            $query = "SELECT u.*, d.name AS department_name 
                      FROM users u
                      LEFT JOIN departments d ON u.department_id = d.id
                      WHERE u.email = :email AND u.is_active = 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            if (empty($user['password'])) {
                return ['success' => false, 'message' => 'This account uses Google login. Please login with Google.'];
            }
            
            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            session_regenerate_id(true);
            
            $update_query = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $update_stmt  = $db->prepare($update_query);
            $update_stmt->bindParam(':id', $user['id']);
            $update_stmt->execute();
            
            // Set session
            $_SESSION['logged_in']       = true;
            $_SESSION['user_id']         = $user['id'];
            $_SESSION['email']           = $user['email'];
            $_SESSION['name']            = $user['name'];
            $_SESSION['role']            = $user['role'];
            $_SESSION['campus_id']       = $user['campus_id'];
            $_SESSION['department_id']   = $user['department_id'];
            $_SESSION['department_name'] = $user['department_name'] ?? null; // "IT", "Engineering"...
            $_SESSION['is_active']       = $user['is_active'];
            $_SESSION['login_method']    = 'email';
            $_SESSION['login_time']      = time();
            
            self::generateCSRFToken();
            
            return ['success' => true, 'message' => 'Login successful', 'user' => $user];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
        }
    }
    
    public static function logout() {
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    public static function isSessionValid($timeout = 3600) {
        if (!isset($_SESSION['login_time'])) {
            return false;
        }
        
        if (time() - $_SESSION['login_time'] > $timeout) {
            self::logout();
            return false;
        }
        
        $_SESSION['login_time'] = time();
        return true;
    }
}
?>