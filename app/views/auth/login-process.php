<?php
session_start();

require_once __DIR__ . '/../../../core/AuthHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email']);
$password = $_POST['password'];

// Validate
if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Email and password are required';
    header('Location: login.php');
    exit;
}

// Login
$result = AuthHelper::loginUser($email, $password);

if ($result['success']) {
    // Redirect to dashboard
    header('Location: /internal_portal/app/views/dashboard/dashboard.php');
    exit;
} else {
    $_SESSION['error'] = $result['message'];
    header('Location: login.php');
    exit;
}
?>