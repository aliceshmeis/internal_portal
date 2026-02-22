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
    $_SESSION['error'] = $result['message'];
    header('Location: login.php');
    exit;
}

// =============================================
// SMART REDIRECT based on role + department
// =============================================
$role            = $_SESSION['role'] ?? '';
$department_name = $_SESSION['department_name'] ?? '';

if ($role === 'Admin') {
    // Admins → admin dashboard
    header('Location: /internal_portal/app/views/dashboard/dashboard.php');

} elseif ($role === 'Staff' && strtoupper($department_name) === 'IT') {
    // IT Staff → IT dashboard
    header('Location: /internal_portal/app/views/it/it-dashboard.php');

} elseif ($role === 'Asset Manager') {
    // Asset Managers → asset manager dashboard
    header('Location: /internal_portal/app/views/asset_manager/dashboard.php');

} else {
    // All other staff → staff dashboard
    header('Location: /internal_portal/app/views/dashboard/staff-dashboard.php');
}

exit;
?>