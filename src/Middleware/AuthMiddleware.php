<?php
namespace Middleware;

use Helpers\Session;
use Helpers\Flash;

/**
 * Authentication Middleware
 * Checks if user is authenticated before accessing routes
 */
class AuthMiddleware {
    
    /**
     * Check if user is authenticated
     */
    public static function check() {
        if (!Session::has('user_id')) {
            Flash::error('Please login to continue');
            redirect('/auth/login');
        }
    }
    
    /**
     * Check if user is guest (not authenticated)
     */
    public static function guest() {
        if (Session::has('user_id')) {
            redirect('/dashboard');
        }
    }
    
    /**
     * Check if user has required role
     */
    public static function role($requiredRoles) {
        self::check();
        
        $userRole = Session::get('role');
        $requiredRoles = (array)$requiredRoles;
        
        // Admin has access to everything
        if ($userRole === 'admin') {
            return true;
        }
        
        if (!in_array($userRole, $requiredRoles)) {
            Flash::error('You do not have permission to access this page');
            redirect('/dashboard');
        }
    }
}
