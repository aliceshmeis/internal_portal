<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Response.php';

if (!Auth::check())                                                     { echo Response::unauthorized(); exit; }
if (!in_array(Auth::role(), ['Admin', 'Asset Manager']))                { echo Response::forbidden('Access denied'); exit; }

try {
    $db   = (new Database())->getConnection();
    $stmt = $db->prepare(
        "SELECT u.id, u.name, u.email, u.role, c.campus_name
         FROM users u
         LEFT JOIN campuses c ON c.id = u.campus_id
         WHERE u.is_active = 1
           AND u.role NOT IN ('Asset Manager', 'Admin')
         ORDER BY u.name ASC"
    );
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo Response::success('Staff retrieved', $users, 200, ['count' => count($users)]);
} catch (Exception $e) {
    echo Response::serverError('Failed to retrieve staff');
}
?>