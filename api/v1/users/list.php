<?php
session_start();

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Request.php';

// Auth check
if (!Auth::check()) {
    echo Response::unauthorized();
    exit;
}

// Admin only
if (!Auth::isAdmin()) {
    echo Response::forbidden('Only administrators can view users');
    exit;
}

// Method check
if (!Request::isGet()) {
    echo Response::methodNotAllowed('GET');
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT u.*, c.campus_name 
              FROM users u
              LEFT JOIN campuses c ON u.campus_id = c.id
              ORDER BY u.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo Response::success('Users retrieved successfully', $users, 200, [
        'count' => count($users)
    ]);
    
} catch (Exception $e) {
    echo Response::serverError('Failed to retrieve users: ' . $e->getMessage());
}
?>