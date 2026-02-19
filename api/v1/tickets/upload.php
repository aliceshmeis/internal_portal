<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Response.php';

if (!Auth::check()) {
    echo Response::unauthorized(); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo Response::methodNotAllowed('POST'); exit;
}

$ticket_id = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : 0;
if (!$ticket_id) {
    echo json_encode(['success' => false, 'message' => 'Ticket ID required']); exit;
}

if (empty($_FILES['attachments'])) {
    echo json_encode(['success' => false, 'message' => 'No files uploaded']); exit;
}

// Upload directory
$upload_dir = __DIR__ . '/../../../../storage/attachments/' . $ticket_id . '/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$allowed_types = ['image/png', 'image/jpeg', 'image/gif', 'application/pdf',
                  'application/msword',
                  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                  'text/plain'];

$max_size   = 5 * 1024 * 1024; // 5MB
$uploaded   = [];
$errors     = [];

$db = Database::getInstance()->getConnection();

// Handle multiple files
$files = $_FILES['attachments'];
$count = is_array($files['name']) ? count($files['name']) : 1;

for ($i = 0; $i < $count; $i++) {
    $name     = is_array($files['name'])     ? $files['name'][$i]     : $files['name'];
    $tmp      = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
    $size     = is_array($files['size'])     ? $files['size'][$i]     : $files['size'];
    $type     = is_array($files['type'])     ? $files['type'][$i]     : $files['type'];
    $error    = is_array($files['error'])    ? $files['error'][$i]    : $files['error'];

    if ($error !== UPLOAD_ERR_OK) {
        $errors[] = "$name: Upload error"; continue;
    }
    if ($size > $max_size) {
        $errors[] = "$name: Exceeds 5MB limit"; continue;
    }

    // Re-check MIME type server-side
    $finfo     = finfo_open(FILEINFO_MIME_TYPE);
    $real_type = finfo_file($finfo, $tmp);
    finfo_close($finfo);

    if (!in_array($real_type, $allowed_types)) {
        $errors[] = "$name: File type not allowed"; continue;
    }

    // Generate safe filename
    $ext       = pathinfo($name, PATHINFO_EXTENSION);
    $safe_name = uniqid('attach_') . '.' . strtolower($ext);
    $dest      = $upload_dir . $safe_name;
    $web_path  = '/internal_portal/storage/attachments/' . $ticket_id . '/' . $safe_name;

    if (!move_uploaded_file($tmp, $dest)) {
        $errors[] = "$name: Failed to save file"; continue;
    }

    // Save to database
    $stmt = $db->prepare("
        INSERT INTO ticket_attachments (ticket_id, file_name, file_path, file_size, file_type, uploaded_by)
        VALUES (:ticket_id, :file_name, :file_path, :file_size, :file_type, :uploaded_by)
    ");
    $stmt->execute([
        ':ticket_id'   => $ticket_id,
        ':file_name'   => $name,
        ':file_path'   => $web_path,
        ':file_size'   => $size,
        ':file_type'   => $real_type,
        ':uploaded_by' => Auth::userId()
    ]);

    $uploaded[] = ['name' => $name, 'path' => $web_path, 'size' => $size];
}

echo json_encode([
    'success'  => count($uploaded) > 0,
    'message'  => count($uploaded) . ' file(s) uploaded' . (count($errors) ? ', ' . count($errors) . ' failed' : ''),
    'uploaded' => $uploaded,
    'errors'   => $errors
]);