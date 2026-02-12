<?php
class Response {
    
    /**
     * Success response
     * 
     * @param string $message Success message
     * @param mixed $data Response data
     * @param int $code HTTP status code (default 200)
     * @param array $meta Additional metadata
     * @return string JSON response
     */
    public static function success($message, $data = null, $code = 200, $meta = []) {
        http_response_code($code);
        
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        // Add metadata if provided
        if (!empty($meta)) {
            foreach ($meta as $key => $value) {
                $response[$key] = $value;
            }
        }
        
        return json_encode($response);
    }
    
    /**
     * Error response
     * 
     * @param string $message Error message
     * @param int $code HTTP status code (default 400)
     * @param array $errors Array of error details
     * @return string JSON response
     */
    public static function error($message, $code = 400, $errors = []) {
        http_response_code($code);
        
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        return json_encode($response);
    }
    
    /**
     * Unauthorized response (401)
     */
    public static function unauthorized($message = 'Unauthorized. Please login first.') {
        return self::error($message, 401);
    }
    
    /**
     * Forbidden response (403)
     */
    public static function forbidden($message = 'Forbidden. You do not have permission.') {
        return self::error($message, 403);
    }
    
    /**
     * Not found response (404)
     */
    public static function notFound($message = 'Resource not found.') {
        return self::error($message, 404);
    }
    
    /**
     * Method not allowed response (405)
     */
    public static function methodNotAllowed($allowed = 'GET, POST, PUT, DELETE') {
        return self::error('Method not allowed. Allowed methods: ' . $allowed, 405);
    }
    
    /**
     * Server error response (500)
     */
    public static function serverError($message = 'Internal server error.', $error = null) {
        $errors = [];
        if ($error !== null) {
            $errors['error'] = $error;
        }
        return self::error($message, 500, $errors);
    }
}
?>