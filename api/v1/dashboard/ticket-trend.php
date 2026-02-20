<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Response.php';

if (!Auth::check()) { echo Response::unauthorized(); exit; }

try {
    $db = (new Database())->getConnection();

    $query = "
        SELECT DATE(created_at) as date, COUNT(*) as count
        FROM tickets
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo Response::success('Trend data loaded', $rows);
} catch (Exception $e) {
    echo Response::serverError('Failed to load trend data');
}
?>