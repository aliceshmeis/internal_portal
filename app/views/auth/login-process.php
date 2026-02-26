<?php
// Start the session (must always be at the top before any output)
session_start();

// Load authentication helper (contains login logic & CSRF verification)
require_once __DIR__ . '/../../../core/AuthHelper.php';


// ======================================================
// 1️⃣ CSRF TOKEN VERIFICATION (Security Protection)
// ======================================================
// Prevents Cross-Site Request Forgery attacks
if (!isset($_POST['csrf_token']) || 
    !AuthHelper::verifyCSRFToken($_POST['csrf_token'])) {

    // Store error message in session
    $_SESSION['error'] = 'Invalid request. Please try again.';
    
    // Redirect back to login page
    header('Location: login.php');
    exit;
}


// ======================================================
// 2️⃣ GET FORM INPUT DATA
// ======================================================
// Trim removes extra spaces from email
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';


// ======================================================
// 3️⃣ BASIC VALIDATION
// ======================================================
// Ensure email and password are not empty
if (empty($email) || empty($password)) {
    
    $_SESSION['error'] = 'Please enter both email and password.';
    header('Location: login.php');
    exit;
}


// ======================================================
// 4️⃣ AUTHENTICATE USER
// ======================================================
// Calls AuthHelper::loginUser()
// This function:
// - Finds user by email
// - Verifies hashed password
// - Stores user data in session if correct
$result = AuthHelper::loginUser($email, $password);


// ======================================================
// 5️⃣ HANDLE FAILED LOGIN
// ======================================================
if (!$result['success']) {
    
    // Store error message (example: Wrong password)
    $_SESSION['error'] = $result['message'];
    
    header('Location: login.php');
    exit;
}


// ======================================================
// 6️⃣ SESSION SECURITY
// ======================================================
// Regenerate session ID to prevent session fixation attacks
session_regenerate_id(true);


// ======================================================
// 7️⃣ SMART REDIRECT BASED ON USER ROLE
// ======================================================
// Role was stored inside session during loginUser()
$role = $_SESSION['role'] ?? '';

if ($role === 'Admin') {
    
    // Redirect Admin to Admin Dashboard
    header('Location: /internal_portal/app/views/dashboard/dashboard.php');

} elseif ($role === 'Asset Manager') {
    
    // Redirect Asset Manager to their Dashboard
    header('Location: /internal_portal/app/views/asset_manager/dashboard.php');

} else {
    
    // All other roles (Staff, Doctor, etc.)
    header('Location: /internal_portal/app/views/dashboard/staff-dashboard.php');
}

// Always exit after header redirect
exit;
?>