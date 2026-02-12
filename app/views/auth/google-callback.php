<?php
// Enable ALL error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

echo "<h2>Google Callback Debug</h2>";
echo "<h3>1. Checking if code was received...</h3>";

// Check if we received an authorization code
if (!isset($_GET['code'])) {
    die("ERROR: No authorization code received from Google!");
}

echo "✅ Code received: " . substr($_GET['code'], 0, 20) . "...<br>";

echo "<h3>2. Loading config files...</h3>";

require_once __DIR__ . '/../../../config/database.php';
echo "✅ Database config loaded<br>";

require_once __DIR__ . '/../../../config/google.php';
echo "✅ Google config loaded<br>";

$auth_code = $_GET['code'];

echo "<h3>3. Exchanging code for access token...</h3>";

// Exchange authorization code for access token
$token_data = [
    'code' => $auth_code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
];

$ch = curl_init(GOOGLE_TOKEN_URL);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // FIX: Disable SSL verification for local dev
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // FIX: Disable SSL verification for local dev
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
$token_response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    die("CURL ERROR: " . $curl_error);
}

echo "✅ Token response received<br>";
echo "<pre>Token Response: " . htmlspecialchars($token_response) . "</pre>";

$token_info = json_decode($token_response, true);

if (!isset($token_info['access_token'])) {
    die("ERROR: No access token in response. Check the token response above.");
}

$access_token = $token_info['access_token'];
echo "✅ Access token: " . substr($access_token, 0, 20) . "...<br>";

echo "<h3>4. Getting user info from Google...</h3>";

// Get user information from Google
$ch = curl_init(GOOGLE_USERINFO_URL . '?access_token=' . $access_token);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // FIX: Disable SSL verification for local dev
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // FIX: Disable SSL verification for local dev
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$userinfo_response = curl_exec($ch);
curl_close($ch);

echo "✅ User info received<br>";
echo "<pre>User Info: " . htmlspecialchars($userinfo_response) . "</pre>";

$user_info = json_decode($userinfo_response, true);

if (!isset($user_info['email'])) {
    die("ERROR: No email in user info response. Check the response above.");
}

// Extract user information
$email = $user_info['email'];
$name = $user_info['name'] ?? '';
$google_id = $user_info['id'];
$profile_picture = $user_info['picture'] ?? null;

echo "<h3>5. User Information Extracted:</h3>";
echo "Email: " . $email . "<br>";
echo "Name: " . $name . "<br>";
echo "Google ID: " . $google_id . "<br>";
echo "Profile Picture: " . $profile_picture . "<br>";

echo "<h3>6. Connecting to database...</h3>";

// Connect to database
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    die("DATABASE CONNECTION ERROR: " . $e->getMessage());
}

echo "<h3>7. Checking if user already exists...</h3>";

// Check if user exists
$query = "SELECT * FROM users WHERE email = :email OR google_id = :google_id LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':google_id', $google_id);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    echo "✅ User already exists! Logging them in...<br>";
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Update last login
    $update_query = "UPDATE users SET 
                     google_id = :google_id, 
                     profile_picture = :profile_picture,
                     last_login = NOW() 
                     WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':google_id', $google_id);
    $update_stmt->bindParam(':profile_picture', $profile_picture);
    $update_stmt->bindParam(':id', $user['id']);
    
    if ($update_stmt->execute()) {
        echo "✅ User updated successfully<br>";
    } else {
        echo "⚠️ Update failed: " . print_r($update_stmt->errorInfo(), true) . "<br>";
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['campus_id'] = $user['campus_id'];
    $_SESSION['is_active'] = $user['is_active'];
    $_SESSION['logged_in'] = true;
    
    echo "<h3>✅ Login Successful! Session created.</h3>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
    echo "<p><a href='/internal_portal/app/views/dashboard/index.php'>Go to Dashboard</a></p>";
    // header('Location: /internal_portal/app/views/dashboard/index.php');
    exit;
    
} else {
    echo "ℹ️ New user - needs to be registered<br>";
    
    echo "<h3>8. Checking for campus...</h3>";
    
    // Check if campus exists
    $campus_query = "SELECT id FROM campuses WHERE is_active = 1 ORDER BY id ASC LIMIT 1";
    $campus_stmt = $db->prepare($campus_query);
    $campus_stmt->execute();
    
    if ($campus_stmt->rowCount() == 0) {
        die("ERROR: No active campus found in database! Please create a campus first:<br><br>
             <code>INSERT INTO campuses (campus_name, campus_code, location, is_active, created_at) 
             VALUES ('Main Campus', 'MAIN', 'Beirut', 1, NOW());</code>");
    }
    
    $campus_row = $campus_stmt->fetch(PDO::FETCH_ASSOC);
    $default_campus_id = $campus_row['id'];
    echo "✅ Found campus ID: " . $default_campus_id . "<br>";
    
    $default_role = 'Staff';
    
    echo "<h3>9. Inserting new user into database...</h3>";
    
    // Insert new user
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
    
    echo "Attempting to insert user with:<br>";
    echo "- Google ID: " . $google_id . "<br>";
    echo "- Email: " . $email . "<br>";
    echo "- Name: " . $name . "<br>";
    echo "- Role: " . $default_role . "<br>";
    echo "- Campus ID: " . $default_campus_id . "<br>";
    
    if ($insert_stmt->execute()) {
        $new_user_id = $db->lastInsertId();
        echo "<h3>✅ SUCCESS! User created with ID: " . $new_user_id . "</h3>";
        
        // Set session
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $name;
        $_SESSION['role'] = $default_role;
        $_SESSION['campus_id'] = $default_campus_id;
        $_SESSION['is_active'] = 1;
        $_SESSION['logged_in'] = true;
        
        echo "<h3>✅ Session created!</h3>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
        
        echo "<p><a href='/internal_portal/app/views/dashboard/index.php'>Go to Dashboard</a></p>";
        // header('Location: /internal_portal/app/views/dashboard/index.php');
        exit;
    } else {
        echo "<h3>❌ INSERT FAILED!</h3>";
        echo "<pre>";
        print_r($insert_stmt->errorInfo());
        echo "</pre>";
        die("Registration failed. See error above.");
    }
}
?>