<?php
session_start();//lets you save logindata in session

require_once __DIR__ . '/../../../config/google.php';
require_once __DIR__ . '/../../../config/database.php';

// Get authorization code
if (!isset($_GET['code'])) {
    $_SESSION['error'] = 'Google login failed - no authorization code';
    header('Location: login.php');
    exit;
}

$code = $_GET['code'];

try {
    // Exchange code for access token
    $token_params = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    //this sends a post request to google token endpoint
    $ch = curl_init(GOOGLE_TOKEN_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_params));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For localhost
    $token_response = curl_exec($ch);//The response is JSON text stored in $token_response.
    
    if (curl_errno($ch)) {//if my connection to google failed 
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    $token_data = json_decode($token_response, true);//convert it to json 
    
    if (!isset($token_data['access_token'])) {//if no access token 
        throw new Exception('Failed to get access token: ' . ($token_data['error_description'] ?? 'Unknown error'));
    }
    
    $access_token = $token_data['access_token'];//now we have a token that requests user data 
    
    // Get user info from Google
    $ch = curl_init(GOOGLE_USERINFO_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For localhost
    $userinfo_response = curl_exec($ch);//give me user info, stored here
    
    if (curl_errno($ch)) {
        throw new Exception('Curl error getting user info: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    $google_user = json_decode($userinfo_response, true);//google returns users data!
    
    if (!isset($google_user['id']) || !isset($google_user['email'])) {
        throw new Exception('Failed to get user info from Google');
    }
    
    $google_id = $google_user['id'];
    $email = $google_user['email'];
    $name = $google_user['name'] ?? $google_user['email'];
    
    // Database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Step 1: Try to find user by google_id
    $query = "SELECT * FROM users WHERE google_id = :google_id";
    $stmt = $db->prepare($query);//gets this query ready 
    $stmt->bindParam(':google_id', $google_id);//putting real value 
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Step 2: If not found, try to find by email (account linking)
    if (!$user) {
        $query2 = "SELECT * FROM users WHERE email = :email";
        $stmt2 = $db->prepare($query2);
        $stmt2->bindParam(':email', $email);
        $stmt2->execute();
        
        $user = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Link Google account to existing user
            $link_query = "UPDATE users SET google_id = :google_id, last_login = NOW() WHERE id = :id";
            $link_stmt = $db->prepare($link_query);
            $link_stmt->bindParam(':google_id', $google_id);
            $link_stmt->bindParam(':id', $user['id']);
            $link_stmt->execute();
            
            $user['google_id'] = $google_id;
        }
    }
    
    // Step 3: If still no user, create new account
    if (!$user) {
        $insert_query = "INSERT INTO users 
                         (google_id, email, name, campus_id, role, login_method, is_active, email_verified, created_at) 
                         VALUES 
                         (:google_id, :email, :name, 1, 'Staff', 'google', 1, 1, NOW())";
        
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':google_id', $google_id);
        $insert_stmt->bindParam(':email', $email);
        $insert_stmt->bindParam(':name', $name);
        $insert_stmt->execute();
        
        $user_id = $db->lastInsertId();
        
        // Get the newly created user
        $user_query = "SELECT * FROM users WHERE id = :id";
        $user_stmt = $db->prepare($user_query);
        $user_stmt->bindParam(':id', $user_id);
        $user_stmt->execute();
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Update last login for existing user
        $update_query = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':id', $user['id']);
        $update_stmt->execute();
    }
    
    // Set session
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['campus_id'] = $user['campus_id'];
    $_SESSION['is_active'] = $user['is_active'];
    $_SESSION['login_method'] = 'google';
    
    // Redirect to dashboard
   header('Location: /internal_portal/app/views/dashboard/dashboard.php');
    exit;
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Google login failed: ' . $e->getMessage();
    header('Location: login.php');
    exit;
}
?>