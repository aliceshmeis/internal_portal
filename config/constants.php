<?php
/**
 * Application Constants
 */

// Base URL (WAMP)
define('BASE_URL', 'http://localhost/internal_portal/public/');
define('PUBLIC_URL', BASE_URL);

// Application paths (already defined in index.php, but keep for compatibility)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}
if (!defined('APP_PATH')) {
    define('APP_PATH', ROOT_PATH . 'app' . DIRECTORY_SEPARATOR);
}
if (!defined('CORE_PATH')) {
    define('CORE_PATH', ROOT_PATH . 'core' . DIRECTORY_SEPARATOR);
}
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', ROOT_PATH . 'config' . DIRECTORY_SEPARATOR);
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', ROOT_PATH . 'public' . DIRECTORY_SEPARATOR);
}

define('STORAGE_PATH', ROOT_PATH . 'storage' . DIRECTORY_SEPARATOR);
define('UPLOAD_PATH', STORAGE_PATH . 'uploads' . DIRECTORY_SEPARATOR);

// Session configuration
define('SESSION_LIFETIME', 3600); // 1 hour

// Pagination
define('RECORDS_PER_PAGE', 10);

// User roles
define('ROLE_ADMIN', 'Admin');
define('ROLE_STAFF', 'Staff');
define('ROLE_ASSET_MANAGER', 'Asset Manager');
define('ROLE_VIEWER', 'Viewer');

// Ticket statuses
define('TICKET_OPEN', 'Open');
define('TICKET_IN_PROGRESS', 'In Progress');
define('TICKET_PENDING', 'Pending');
define('TICKET_RESOLVED', 'Resolved');
define('TICKET_CLOSED', 'Closed');

// Asset statuses
define('ASSET_AVAILABLE', 'Available');
define('ASSET_IN_USE', 'In Use');
define('ASSET_MAINTENANCE', 'Maintenance');
define('ASSET_RETIRED', 'Retired');

// PO statuses
define('PO_DRAFT', 'Draft');
define('PO_PENDING_APPROVAL', 'Pending Approval');
define('PO_APPROVED', 'Approved');
define('PO_REJECTED', 'Rejected');
define('PO_COMPLETED', 'Completed');
define('PO_CANCELLED', 'Cancelled');
?>