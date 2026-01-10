<?php
namespace Controllers;

use Helpers\Session;
use Helpers\Flash;
use Helpers\CSRF;

/**
 * Base Controller
 * All controllers extend this class
 */
abstract class BaseController {
    
    protected $db;
    
    public function __construct() {
        // Get database instance
        $this->db = \Database::getInstance();
        
        // Start session
        Session::start();
    }
    
    /**
     * Render a view
     */
    protected function view($view, $data = [], $layout = 'main') {
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            die("View not found: {$viewFile}");
        }
        
        include $viewFile;
        
        // Get view content
        $content = ob_get_clean();
        
        // Include layout if specified
        if ($layout) {
            $layoutFile = VIEWS_PATH . '/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                include $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect($url) {
        redirect($url);
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Check if user is authenticated
     */
    protected function requireAuth() {
        if (!Session::has('user_id')) {
            Flash::error('Please login to continue');
            $this->redirect('/auth/login');
        }
    }
    
    /**
     * Check if user has required role
     */
    protected function requireRole($roles) {
        $this->requireAuth();
        
        $userRole = Session::get('role');
        $roles = (array)$roles;
        
        if (!in_array($userRole, $roles) && $userRole !== 'admin') {
            Flash::error('You do not have permission to access this page');
            $this->redirect('/dashboard');
        }
    }
    
    /**
     * Get current user
     */
    protected function user() {
        return currentUser();
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCSRF() {
        CSRF::check();
    }
}
