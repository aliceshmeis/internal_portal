<?php
session_start();

require_once __DIR__ . '/../../../config/google.php';
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_GET['code'])) {
    $_SESSION['error'] = 'Google login failed - no authorization code';
    header('Location: login.php');
    exit;
}

$code = $_GET['code'];

try {
    $token_params = [
        'code'          => $code,
        'client_id'     => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'grant_type'    => 'authorization_code'
    ];

    $ch = curl_init(GOOGLE_TOKEN_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_params));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $token_response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    curl_close($ch);

    $token_data = json_decode($token_response, true);

    if (!isset($token_data['access_token'])) {
        throw new Exception('Failed to get access token: ' . ($token_data['error_description'] ?? 'Unknown error'));
    }

    $access_token = $token_data['access_token'];

    $ch = curl_init(GOOGLE_USERINFO_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $userinfo_response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception('Curl error getting user info: ' . curl_error($ch));
    }
    curl_close($ch);

    $google_user = json_decode($userinfo_response, true);

    if (!isset($google_user['id']) || !isset($google_user['email'])) {
        throw new Exception('Failed to get user info from Google');
    }

    $google_id = $google_user['id'];
    $email     = $google_user['email'];
    $name      = $google_user['name'] ?? $google_user['email'];

    $database = new Database();
    $db = $database->getConnection();

    // JOIN departments to get department name
    $query = "SELECT u.*, d.name AS department_name 
              FROM users u
              LEFT JOIN departments d ON u.department_id = d.id
              WHERE u.google_id = :google_id";
    $stmt  = $db->prepare($query);
    $stmt->bindParam(':google_id', $google_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $query2 = "SELECT u.*, d.name AS department_name 
                   FROM users u
                   LEFT JOIN departments d ON u.department_id = d.id
                   WHERE u.email = :email";
        $stmt2  = $db->prepare($query2);
        $stmt2->bindParam(':email', $email);
        $stmt2->execute();
        $user = $stmt2->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $link_query = "UPDATE users SET google_id = :google_id, last_login = NOW() WHERE id = :id";
            $link_stmt  = $db->prepare($link_query);
            $link_stmt->bindParam(':google_id', $google_id);
            $link_stmt->bindParam(':id', $user['id']);
            $link_stmt->execute();
        }
    }

    if (!$user) {
        $_SESSION['error'] = 'Access denied. Your account is not registered in the portal. Please contact your administrator.';
        header('Location: login.php');
        exit;
    } else {
        $update_query = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $update_stmt  = $db->prepare($update_query);
        $update_stmt->bindParam(':id', $user['id']);
        $update_stmt->execute();
    }

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
    $_SESSION['login_method']    = 'google';

    header('Location: /internal_portal/app/views/dashboard/dashboard.php');
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = 'Google login failed: ' . $e->getMessage();
    header('Location: login.php');
    exit;
}
?>