<?php
/**
 * Generate Password Hashes for Test Users
 * 
 * Run this file in your browser or command line to get SQL UPDATE statements
 * with proper bcrypt hashes for each user's unique password
 */

// Define users and their passwords
$users = [
    'sarah.johnson@liu.edu.lb' => 'Sarah2024!',
    'michael.chen@liu.edu.lb' => 'Michael2024!',
    'emily.rodriguez@liu.edu.lb' => 'Emily2024!',
    'david.kim@liu.edu.lb' => 'David2024!',
    'lisa.martinez@liu.edu.lb' => 'Lisa2024!',
    'james.thompson@liu.edu.lb' => 'Asset2024!',
    '82230025@students.liu.edu.lb' => 'Mhamad2024!'
];

echo "-- ============================================================\n";
echo "-- USER PASSWORD UPDATES - UNIQUE PASSWORDS\n";
echo "-- ============================================================\n";
echo "-- Copy these SQL statements and run them in phpMyAdmin\n";
echo "-- ============================================================\n\n";

foreach ($users as $email => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "-- Email: $email | Password: $password\n";
    echo "UPDATE `users` SET `password` = '$hash' WHERE `email` = '$email';\n\n";
}

echo "\n-- ============================================================\n";
echo "-- PASSWORD REFERENCE\n";
echo "-- ============================================================\n";
foreach ($users as $email => $password) {
    $name = explode('@', $email)[0];
    echo "-- $email → $password\n";
}
echo "-- ============================================================\n";

?>