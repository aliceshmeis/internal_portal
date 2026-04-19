<?php
class Auth {
    
    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    public static function check() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Get logged in user ID
     * 
     * @return int|null
     */
    public static function userId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get logged in user email
     * 
     * @return string|null
     */
    public static function email() {
        return $_SESSION['email'] ?? null;
    }
    
    /**
     * Get logged in user name
     * 
     * @return string|null
     */
    public static function name() {
        return $_SESSION['name'] ?? null;
    }
    
    /**
     * Get logged in user role
     * 
     * @return string|null
     */
    public static function role() {
        return $_SESSION['role'] ?? null;
    }
    
    /**
     * Get logged in user campus ID
     * 
     * @return int|null
     */
    public static function campusId() {
        return $_SESSION['campus_id'] ?? null;
    }
    
    /**
     * Check if user is Admin
     * 
     * @return bool
     */
    public static function isAdmin() {
        return self::role() === 'Admin';
    }
    
    /**
     * Check if user is Staff
     * 
     * @return bool
     */
    public static function isStaff() {
        return self::role() === 'Staff';
    }
    
    /**
     * Check if user is Asset Manager
     * 
     * @return bool
     */
    public static function isAssetManager() {
        return self::role() === 'Asset Manager';
    }
    
    /**
     * Check if user is Viewer
     * 
     * @return bool
     */
    public static function isViewer() {
        return self::role() === 'Viewer';
    }
    
    /**
     * Check if user has one of the given roles
     * 
     * @param array $roles
     * @return bool
     */
    public static function hasRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        return in_array(self::role(), $roles);
    }
    
    /**
     * Check if user is active
     * 
     * @return bool
     */
    public static function isActive() {
        return isset($_SESSION['is_active']) && $_SESSION['is_active'] == 1;
    }
    
    /**
     * Get all user session data
     * 
     * @return array
     */
    public static function user() {
        if (!self::check()) {
            return null;
        }
        
        return [
            'id' => self::userId(),
            'email' => self::email(),
            'name' => self::name(),
            'role' => self::role(),
            'campus_id' => self::campusId(),
            'is_active' => self::isActive()
        ];
    }
    public static function isHead() {
    return isset($_SESSION['is_head']) && $_SESSION['is_head'] == 1;
}

public static function departmentId() {
    return $_SESSION['department_id'] ?? null;
}
}
?>