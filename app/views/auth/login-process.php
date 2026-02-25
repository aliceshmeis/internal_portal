<?php
session_start();

require_once __DIR__ . '/../../../core/AuthHelper.php';

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !AuthHelper::verifyCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid request. Please try again.';
    header('Location: login.php');
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Please enter both email and password.';
    header('Location: login.php');
    exit;
}

$result = AuthHelper::loginUser($email, $password);

if (!$result['success']) {
    die('Error: ' . $result['message'] . ' | Email: ' . $email);
}
session_regenerate_id(true); 

// =============================================
// SMART REDIRECT based on role + department
// =============================================
$role = $_SESSION['role'] ?? '';

if ($role === 'Admin') {
    header('Location: /internal_portal/app/views/dashboard/dashboard.php');
} elseif ($role === 'Asset Manager') {
    header('Location: /internal_portal/app/views/asset_manager/dashboard.php');
} else {
    header('Location: /internal_portal/app/views/dashboard/staff-dashboard.php');
}

exit;
?>