<?php
require_once __DIR__ . '/../../../config/google.php';

// Build Google OAuth URL manually
$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => GOOGLE_SCOPE,
    'access_type' => 'online',
    'prompt' => 'select_account'
];

$auth_url = GOOGLE_AUTH_URL . '?' . http_build_query($params);

header('Location: ' . $auth_url);
exit;
?>