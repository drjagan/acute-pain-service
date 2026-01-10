<?php
namespace Controllers;

/**
 * Dashboard Controller
 */
class DashboardController extends BaseController {
    
    /**
     * Show dashboard
     */
    public function index() {
        // Require authentication
        $this->requireAuth();
        
        $user = $this->user();
        
        // Get dashboard data based on role
        $data = [
            'user' => $user,
            'stats' => $this->getDashboardStats($user['role'])
        ];
        
        $this->view('dashboard.index', $data);
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats($role) {
        $stats = [];
        
        // Phase 1: Basic stats (will be expanded in later phases)
        $stats['total_patients'] = 0;
        $stats['active_catheters'] = 0;
        $stats['pending_alerts'] = 0;
        $stats['recent_activities'] = [];
        
        return $stats;
    }
}
