<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Request.php';

if (!Auth::check())    { echo Response::unauthorized(); exit; }
if (!Request::isPost()) { echo Response::methodNotAllowed('POST'); exit; }

$input    = Request::json();
$assetId  = intval($input['asset_id'] ?? 0);
$userId   = intval($input['assigned_to'] ?? 0);
$retDate  = !empty($input['expected_return_date']) ? $input['expected_return_date'] : null;

if (!$assetId || !$userId) { echo Response::error('asset_id and assigned_to are required', 400); exit; }

try {
    $db   = (new Database())->getConnection();
    $stmt = $db->prepare("UPDATE assets SET assigned_to = :uid, status = 'In Use', expected_return_date = :ret WHERE id = :id");
    $stmt->execute([':uid' => $userId, ':ret' => $retDate, ':id' => $assetId]);
    echo Response::success('Asset assigned successfully');
} catch (Exception $e) {
    echo Response::serverError('Failed to assign asset');
}
?>