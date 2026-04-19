<?php
// Load database connection class
require_once __DIR__ . '/../config/database.php';

class AuthHelper {

    // ==========================================================
    // 🔐 GENERATE CSRF TOKEN
    // ==========================================================
    // Creates a secure random token and stores it in session.
    // This token is used to protect forms against CSRF attacks.
    // It is generated only once per session (unless destroyed).
    public static function generateCSRFToken() {

        // If token does not already exist in session, create one
        if (!isset($_SESSION['csrf_token'])) {

            // Generate 32 random bytes and convert to hexadecimal string
            // This creates a very strong unpredictable token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Return the token (so it can be placed inside forms)
        return $_SESSION['csrf_token'];
    }


    // ==========================================================
    // 🛡 VERIFY CSRF TOKEN
    // ==========================================================
    // Compares the token submitted from the form
    // with the token stored in the session.
    // Returns TRUE if they match, FALSE otherwise.
    public static function verifyCSRFToken($token) {

        // If session token does not exist, reject request
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        // Use hash_equals for secure comparison
        // (Prevents timing attacks)
        return hash_equals($_SESSION['csrf_token'], $token);
    }


    // ==========================================================
    // 📝 REGISTER NEW USER
    // ==========================================================
    // Creates a new user account in the database.
    // Default role is "Staff" unless specified.
    public static function registerUser($email, $password, $name, $campus_id, $role = 'Staff') {

        try {
            // Connect to database
            $database = new Database();
            $db = $database->getConnection();

            // ------------------------------------------------------
            // 1️⃣ Check if email already exists
            // ------------------------------------------------------
            $check_query = "SELECT * FROM users WHERE email = :email";
            $check_stmt  = $db->prepare($check_query);
            $check_stmt->bindParam(':email', $email);
            $check_stmt->execute();

            if ($check_stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

            // ------------------------------------------------------
            // 2️⃣ Securely hash password before storing
            // ------------------------------------------------------
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // ------------------------------------------------------
            // 3️⃣ Insert new user into database
            // ------------------------------------------------------
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

            // Catch unexpected server/database errors
            return ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
        }
    }


    // ==========================================================
    // 🔑 LOGIN USER
    // ==========================================================
    // Authenticates user using email + password.
    // If valid:
    // - Updates last login time
    // - Stores user info in session
    // - Generates CSRF token
    public static function loginUser($email, $password) {

        try {
            // Connect to database
            $database = new Database();
            $db = $database->getConnection();

            // ------------------------------------------------------
            // 1️⃣ Get user by email (only active users)
            // Join departments to get department name
            // ------------------------------------------------------
            $query = "SELECT u.*, d.name AS department_name 
                      FROM users u
                      LEFT JOIN departments d ON u.department_id = d.id
                      WHERE u.email = :email AND u.is_active = 1";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // ------------------------------------------------------
            // 2️⃣ Validate user existence
            // ------------------------------------------------------
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            // ------------------------------------------------------
            // 3️⃣ Prevent email login if account is Google-based
            // ------------------------------------------------------
            if (empty($user['password'])) {
                return [
                    'success' => false,
                    'message' => 'This account uses Google login. Please login with Google.'
                ];
            }

            // ------------------------------------------------------
            // 4️⃣ Verify hashed password
            // ------------------------------------------------------
            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            // ------------------------------------------------------
            // 5️⃣ Update last login timestamp
            // ------------------------------------------------------
            $update_query = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $update_stmt  = $db->prepare($update_query);
            $update_stmt->bindParam(':id', $user['id']);
            $update_stmt->execute();

            // ------------------------------------------------------
            // 6️⃣ Store important user data in session
            // ------------------------------------------------------
            $_SESSION['logged_in']       = true;
            $_SESSION['user_id']         = $user['id'];
            $_SESSION['email']           = $user['email'];
            $_SESSION['name']            = $user['name'];
            $_SESSION['role']            = $user['role'];
            $_SESSION['campus_id']       = $user['campus_id'];
            $_SESSION['department_id']   = $user['department_id'];
            $_SESSION['department_name'] = $user['department_name'] ?? null;
            $_SESSION['is_active']       = $user['is_active'];
            $_SESSION['is_head'] = $user['is_head'] ?? 0;
            $_SESSION['login_method']    = 'email';
            $_SESSION['login_time']      = time(); // Used for session timeout

            // Generate CSRF token for future forms
            self::generateCSRFToken();

            return [
                'success' => true,
                'message' => 'Login successful',
                'user'    => $user
            ];

        } catch (Exception $e) {

            return ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
        }
    }


    // ==========================================================
    // 🚪 LOGOUT USER
    // ==========================================================
    // Destroys session completely and removes session cookie.
    public static function logout() {

        // Clear all session variables
        $_SESSION = array();

        // Delete session cookie from browser
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy session on server
        session_destroy();

        return ['success' => true, 'message' => 'Logged out successfully'];
    }


    // ==========================================================
    // ⏳ SESSION VALIDATION (AUTO TIMEOUT)
    // ==========================================================
    // Checks if session expired based on inactivity.
    // Default timeout = 3600 seconds (1 hour).
    public static function isSessionValid($timeout = 3600) {

        // If login time not set, session is invalid
        if (!isset($_SESSION['login_time'])) {
            return false;
        }

        // If inactive longer than timeout → logout
        if (time() - $_SESSION['login_time'] > $timeout) {
            self::logout();
            return false;
        }

        // Refresh activity time
        $_SESSION['login_time'] = time();

        return true;
    }
}
?>