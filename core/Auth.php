<?php
class Auth {
    public static function check() {
        return isset($_SESSION['user_id']);
    }

    public static function user() {
        if (!self::check()) return null;
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'campus_id' => $_SESSION['campus_id'],
        ];
    }

    public static function hasRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    public static function isAdmin() {
        return self::hasRole(ROLE_ADMIN);
    }

    public static function login($userData) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_name'] = $userData['name'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['user_role'] = $userData['role'];
        $_SESSION['campus_id'] = $userData['campus_id'];
    }

    public static function logout() {
        session_unset();
        session_destroy();
    }

    public static function require() {
        if (!self::check()) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }
}
?>