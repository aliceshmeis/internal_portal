<?php
session_start();

require_once __DIR__ . '/../../../core/AuthHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$campus_id = intval($_POST['campus_id']);

// Validate
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

if (empty($password) || strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters';
}

if (empty($campus_id)) {
    $errors[] = 'Campus is required';
}

if (!empty($errors)) {
    $_SESSION['error'] = implode('. ', $errors);
    header('Location: register.php');
    exit;
}

// Register
$result = AuthHelper::registerUser($email, $password, $name, $campus_id);

if ($result['success']) {
    $_SESSION['success'] = 'Registration successful! Please login.';
    header('Location: login.php');
    exit;
} else {
    $_SESSION['error'] = $result['message'];
    header('Location: register.php');
    exit;
}
?>