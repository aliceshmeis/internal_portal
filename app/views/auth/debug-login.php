<?php
require_once __DIR__ . '/../../../config/database.php';

echo "<!DOCTYPE html><html><head><title>Login Debug</title></head><body>";
echo "<h1>Login Debug Test</h1>";

$email = '82230025@students.liu.edu.lb';
$password = 'password123';

echo "<p><strong>Testing Email:</strong> $email</p>";
echo "<p><strong>Testing Password:</strong> $password</p>";
echo "<hr>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>✅ Step 1: Database Connected</h2>";
    
    // Get user
    $query = "SELECT * FROM users WHERE email = :email AND is_active = 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Step 2: Find User</h2>";
    if ($user) {
        echo "✅ <strong>User Found!</strong><br>";
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse; margin-top:10px;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>ID</td><td>" . $user['id'] . "</td></tr>";
        echo "<tr><td>Name</td><td>" . $user['name'] . "</td></tr>";
        echo "<tr><td>Email</td><td>" . $user['email'] . "</td></tr>";
        echo "<tr><td>Role</td><td>" . $user['role'] . "</td></tr>";
        echo "<tr><td>Login Method</td><td>" . $user['login_method'] . "</td></tr>";
        echo "<tr><td>Is Active</td><td>" . $user['is_active'] . "</td></tr>";
        echo "<tr><td>Has Password</td><td>" . (!empty($user['password']) ? "YES ✅" : "NO ❌") . "</td></tr>";
        echo "</table>";
    } else {
        echo "❌ <strong>User NOT Found!</strong><br>";
        echo "<p>This means the email doesn't exist or user is inactive.</p>";
        die();
    }
    
    echo "<h2>Step 3: Check Password</h2>";
    if (empty($user['password'])) {
        echo "❌ <strong>No password set!</strong><br>";
        echo "<p>User has no password in database.</p>";
        die();
    } else {
        echo "✅ Password hash exists<br>";
        echo "<p><strong>Hash:</strong> <code style='background:#f0f0f0;padding:5px;'>" . substr($user['password'], 0, 60) . "...</code></p>";
    }
    
    echo "<h2>Step 4: Verify Password</h2>";
    echo "<p>Testing password: <strong>'$password'</strong></p>";
    
    $verify = password_verify($password, $user['password']);
    
    if ($verify) {
        echo "✅ <strong style='color:green; font-size:20px;'>PASSWORD MATCHES!</strong><br>";
        echo "<p>The password verification works correctly!</p>";
        echo "<h3>Login should work with:</h3>";
        echo "<ul>";
        echo "<li>Email: <strong>$email</strong></li>";
        echo "<li>Password: <strong>$password</strong></li>";
        echo "</ul>";
    } else {
        echo "❌ <strong style='color:red; font-size:20px;'>PASSWORD DOES NOT MATCH!</strong><br>";
        echo "<p>The password '$password' does not match the hash in database.</p>";
        
        echo "<h3>Solution: Generate New Hash</h3>";
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        echo "<p>Run this SQL to fix it:</p>";
        echo "<textarea rows='3' cols='100' style='font-family:monospace;'>UPDATE users SET password = '$new_hash' WHERE id = " . $user['id'] . ";</textarea>";
    }
    
    echo "<hr>";
    echo "<h2>Step 5: Test Full Login Flow</h2>";
    
    // Simulate the login
    if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
        echo "✅ <strong style='color:green;'>FULL LOGIN WOULD SUCCEED!</strong><br>";
        echo "<p>All checks passed. Login should work.</p>";
    } else {
        echo "❌ <strong style='color:red;'>LOGIN WOULD FAIL!</strong><br>";
        if (!$user) {
            echo "<p>Reason: User not found</p>";
        } elseif (empty($user['password'])) {
            echo "<p>Reason: No password set</p>";
        } elseif (!password_verify($password, $user['password'])) {
            echo "<p>Reason: Password doesn't match</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2 style='color:red;'>❌ ERROR</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "</body></html>";
?>