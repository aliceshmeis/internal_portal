<?php
/**
 * Dashboard Controller
 */

class DashboardController extends Controller {
    
    /**
     * Show dashboard
     */
    public function index() {
        // Require authentication
        Auth::require();
        
        // Get current user
        $user = Auth::user();
        
        // Test database connection
        $db = new Database();
        $conn = $db->getConnection();
        
        if ($conn) {
            $dbStatus = "Connected ✅";
        } else {
            $dbStatus = "Connection Failed ❌";
        }
        
        // Load dashboard view
        $this->view('dashboard/index', [
            'user' => $user,
            'dbStatus' => $dbStatus
        ]);
    }
}
?>