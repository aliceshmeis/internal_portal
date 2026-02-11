<?php
/**
 * Google OAuth Callback Handler
 * Processes the response from Google after user authentication
 */

session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/google.php';

// Check if we received an authorization code
if (!isset($_GET['code'])) {
    header('Location: /internal_portal/app/views/auth/login.php?error=oauth_failed');
    exit;
}

$auth_code = $_GET['code'];

// Exchange authorization code for access token
$token_data = [
    'code' => $auth_code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
];

$ch = curl_init(GOOGLE_TOKEN_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
$token_response = curl_exec($ch);
curl_close($ch);

$token_info = json_decode($token_response, true);

if (!isset($token_info['access_token'])) {
    header('Location: /internal_portal/app/views/auth/login.php?error=token_failed');
    exit;
}

$access_token = $token_info['access_token'];

// Get user information from Google
$ch = curl_init(GOOGLE_USERINFO_URL . '?access_token=' . $access_token);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$userinfo_response = curl_exec($ch);
curl_close($ch);

$user_info = json_decode($userinfo_response, true);

if (!isset($user_info['email'])) {
    header('Location: /internal_portal/app/views/auth/login.php?error=userinfo_failed');
    exit;
}

// Extract user information
$email = $user_info['email'];
$name = $user_info['name'] ?? '';
$google_id = $user_info['id'];
$profile_picture = $user_info['picture'] ?? null;

// Connect to database
$database = new Database();
$db = $database->getConnection();

// Check if user exists by email OR google_id
$query = "SELECT * FROM users WHERE email = :email OR google_id = :google_id LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':google_id', $google_id);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    // User exists - log them in
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Update google_id and profile_picture if not set
    $update_query = "UPDATE users SET 
                     google_id = :google_id, 
                     profile_picture = :profile_picture,
                     last_login = NOW() 
                     WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':google_id', $google_id);
    $update_stmt->bindParam(':profile_picture', $profile_picture);
    $update_stmt->bindParam(':id', $user['id']);
    $update_stmt->execute();
    
    // Set session variables (using correct column names)
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['campus_id'] = $user['campus_id'];
    $_SESSION['is_active'] = $user['is_active'];
    $_SESSION['logged_in'] = true;
    
    // Redirect to dashboard
    header('Location: /internal_portal/app/views/dashboard/index.php');
    exit;
    
} else {
    // New user - register them automatically with Staff role
    
    // Get the first active campus (or create one if none exists)
    $campus_query = "SELECT id FROM campuses WHERE is_active = 1 ORDER BY id ASC LIMIT 1";
    $campus_stmt = $db->prepare($campus_query);
    $campus_stmt->execute();
    
    if ($campus_stmt->rowCount() == 0) {
        // No campus exists - show error
        header('Location: /internal_portal/app/views/auth/login.php?error=no_campus');
        exit;
    }
    
    $campus_row = $campus_stmt->fetch(PDO::FETCH_ASSOC);
    $default_campus_id = $campus_row['id'];
    
    $default_role = 'Staff'; // Auto-assign as Staff
    
    // Insert new user with ALL required fields
    $insert_query = "INSERT INTO users 
                     (google_id, email, name, profile_picture, role, campus_id, is_active, last_login, created_at) 
                     VALUES 
                     (:google_id, :email, :name, :profile_picture, :role, :campus_id, 1, NOW(), NOW())";
    
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':google_id', $google_id);
    $insert_stmt->bindParam(':email', $email);
    $insert_stmt->bindParam(':name', $name);
    $insert_stmt->bindParam(':profile_picture', $profile_picture);
    $insert_stmt->bindParam(':role', $default_role);
    $insert_stmt->bindParam(':campus_id', $default_campus_id);
    
    if ($insert_stmt->execute()) {
        $new_user_id = $db->lastInsertId();
        
        // Set session variables for new user
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $name;
        $_SESSION['role'] = $default_role;
        $_SESSION['campus_id'] = $default_campus_id;
        $_SESSION['is_active'] = 1;
        $_SESSION['logged_in'] = true;
        
        // Redirect to dashboard
        header('Location: /internal_portal/app/views/dashboard/index.php');
        exit;
    } else {
        // Log the actual error for debugging
        error_log("User insert failed: " . print_r($insert_stmt->errorInfo(), true));
        header('Location: /internal_portal/app/views/auth/login.php?error=registration_failed');
        exit;
    }
}
?>