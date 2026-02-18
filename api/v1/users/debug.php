<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing UserController...</h2>";

try {
    require_once __DIR__ . '/../../../config/database.php';
    echo "✅ database.php loaded<br>";
    
    require_once __DIR__ . '/../../../core/Auth.php';
    echo "✅ Auth.php loaded<br>";
    
    require_once __DIR__ . '/../../../core/Response.php';
    echo "✅ Response.php loaded<br>";
    
    require_once __DIR__ . '/../../../core/Request.php';
    echo "✅ Request.php loaded<br>";
    
    require_once __DIR__ . '/../../../app/Controllers/UserController.php';
    echo "✅ UserController.php loaded<br>";
    
    $controller = new UserController();
    echo "✅ UserController instantiated<br>";
    
} catch (Error $e) {
    echo "❌ ERROR: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
$base = 'C:/wamp64/www/internal_portal/';
$found = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
foreach ($iterator as $file) {
    if ($file->getFilename() === 'Model.php') {
        $found[] = $file->getPathname();
    }
}
print_r($found);
?>