<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Request.php';

if (!Auth::check())     { echo Response::unauthorized(); exit; }
if (!Request::isPost()) { echo Response::methodNotAllowed('POST'); exit; }

$input   = Request::json();
$assetId = intval($input['asset_id'] ?? 0);

if (!$assetId) { echo Response::error('asset_id is required', 400); exit; }

try {
    $db   = (new Database())->getConnection();
    $stmt = $db->prepare("UPDATE assets SET assigned_to = NULL, status = 'Available', expected_return_date = NULL WHERE id = :id");
    $stmt->execute([':id' => $assetId]);
    echo Response::success('Asset returned successfully');
} catch (Exception $e) {
    echo Response::serverError('Failed to return asset');
}
?>