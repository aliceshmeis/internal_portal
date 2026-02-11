<?php
/**
 * Authentication Controller
 */

class AuthController extends Controller {
    
    /**
     * Show login page
     */
    public function login() {
        // If already logged in, redirect to dashboard
        if (Auth::check()) {
            $this->redirect('dashboard');
        }
        
        // Get Google login URL
        $googleLoginUrl = GoogleOAuth::getLoginUrl();
        
        // Load login view
        $this->view('auth/login', [
            'googleLoginUrl' => $googleLoginUrl
        ]);
    }
    
    /**
     * Handle Google OAuth callback
     */
    public function googleCallback() {
        // Get authorization code from URL
        if (!isset($_GET['code'])) {
            $_SESSION['error'] = 'Authorization code not received from Google';
            $this->redirect('auth/login');
            return;
        }
        
        $code = $_GET['code'];
        
        // Exchange code for access token
        $tokenData = GoogleOAuth::getAccessToken($code);
        
        if (!isset($tokenData['access_token'])) {
            $_SESSION['error'] = 'Failed to get access token from Google';
            $this->redirect('auth/login');
            return;
        }
        
        $accessToken = $tokenData['access_token'];
        
        // Get user info from Google
        $googleUser = GoogleOAuth::getUserInfo($accessToken);
        
        if (!isset($googleUser['email'])) {
            $_SESSION['error'] = 'Failed to get user information from Google';
            $this->redirect('auth/login');
            return;
        }
        
        // Load User model
        $userModel = $this->model('User');
        
        // Check if user exists by Google ID
        $user = $userModel->findByGoogleId($googleUser['id']);
        
        // If not found, check by email
        if (!$user) {
            $user = $userModel->findByEmail($googleUser['email']);
        }
        
        // If user doesn't exist, create new user
        if (!$user) {
            $userId = $userModel->createFromGoogle($googleUser);
            
            if (!$userId) {
                $_SESSION['error'] = 'Failed to create user account';
                $this->redirect('auth/login');
                return;
            }
            
            // Get newly created user
            $user = $userModel->getById($userId);
        }
        
        // Check if user is active
        if (!$user['is_active']) {
            $_SESSION['error'] = 'Your account is inactive. Please contact administrator.';
            $this->redirect('auth/login');
            return;
        }
        
        // Update last login time
        $userModel->updateLastLogin($user['id']);
        
        // Log user in (create session)
        Auth::login($user);
        
        // Log audit trail
        logAudit($user['id'], $user['campus_id'], 'LOGIN', null, null, null, null);
        
        // Redirect to dashboard
        $_SESSION['success'] = 'Welcome, ' . $user['name'] . '!';
        $this->redirect('dashboard');
    }
    
    /**
     * Logout
     */
    public function logout() {
        // Log audit trail before destroying session
        if (Auth::check()) {
            $user = Auth::user();
            logAudit($user['id'], $user['campus_id'], 'LOGOUT', null, null, null, null);
        }
        
        // Destroy session
        Auth::logout();
        
        // Redirect to login
        $_SESSION['success'] = 'You have been logged out successfully';
        $this->redirect('auth/login');
    }
}
?>