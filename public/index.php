<?php
/**
 * Application Entry Point
 * All requests come through this file
 */

// Start session
session_start();

// Define paths
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP_PATH', ROOT_PATH . 'app' . DIRECTORY_SEPARATOR);
define('CORE_PATH', ROOT_PATH . 'core' . DIRECTORY_SEPARATOR);
define('CONFIG_PATH', ROOT_PATH . 'config' . DIRECTORY_SEPARATOR);

// Load configuration files
require_once CONFIG_PATH . 'constants.php';
require_once CONFIG_PATH . 'database.php';
require_once CONFIG_PATH . 'google.php';

// Load core classes
require_once CORE_PATH . 'Model.php';
require_once CORE_PATH . 'Controller.php';
require_once CORE_PATH . 'Auth.php';
require_once CORE_PATH . 'Router.php';

// Load helpers
require_once APP_PATH . 'helpers/GoogleOAuth.php';

// Helper function for audit logging
function logAudit($user_id, $campus_id, $action, $table_name = null, $record_id = null, $old_values = null, $new_values = null) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $sql = "INSERT INTO audit_logs 
                (user_id, campus_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
                VALUES 
                (:user_id, :campus_id, :action, :table_name, :record_id, :old_values, :new_values, :ip_address, :user_agent)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':campus_id' => $campus_id,
            ':action' => $action,
            ':table_name' => $table_name,
            ':record_id' => $record_id,
            ':old_values' => $old_values ? json_encode($old_values) : null,
            ':new_values' => $new_values ? json_encode($new_values) : null,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Audit log error: " . $e->getMessage());
        return false;
    }
}

// Create router instance
$router = new Router();

// Load routes
require_once ROOT_PATH . 'routes/web.php';

// Get requested URL
$url = $_GET['url'] ?? '';

// Dispatch request
$router->dispatch($url);
?>