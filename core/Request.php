<?php
class Request {
    
    /**
     * Get JSON input from request body
     * 
     * @return array
     */
    public static function json() {
        $input = file_get_contents('php://input');//This reads the raw HTTP request body.
        return json_decode($input, true) ?? [];
    }
    
    /**
     * Get query parameter (GET)
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null) {
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Get all query parameters
     * 
     * @return array
     */
    public static function all() {
        return $_GET;
    }
    
    /**
     * Check if request method is GET
     * 
     * @return bool
     */
    public static function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Check if request method is POST
     * 
     * @return bool
     */
    public static function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Check if request method is PUT
     * 
     * @return bool
     */
    public static function isPut() {
        return $_SERVER['REQUEST_METHOD'] === 'PUT';
    }
    
    /**
     * Check if request method is PATCH
     * 
     * @return bool
     */
    public static function isPatch() {
        return $_SERVER['REQUEST_METHOD'] === 'PATCH';
    }
    
    /**
     * Check if request method is DELETE
     * 
     * @return bool
     */
    public static function isDelete() {
        return $_SERVER['REQUEST_METHOD'] === 'DELETE';
    }
    
    /**
     * Get request method
     * 
     * @return string
     */
    public static function method() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Get request URI
     * 
     * @return string
     */
    public static function uri() {
        return $_SERVER['REQUEST_URI'];
    }
}
?>