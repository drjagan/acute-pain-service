<?php
namespace Controllers;

use Services\AuthService;
use Helpers\Flash;
use Helpers\CSRF;
use Helpers\Sanitizer;

/**
 * Authentication Controller
 * Handles login, logout, password reset
 */
class AuthController extends BaseController {
    
    private $authService;
    
    public function __construct() {
        parent::__construct();
        $this->authService = new AuthService();
    }
    
    /**
     * Show login form
     */
    public function login() {
        // If already logged in, redirect to dashboard
        if (isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        
        // Check remember me token
        if ($this->authService->checkRememberToken()) {
            $this->redirect('/dashboard');
        }
        
        $this->view('auth.login', [], 'auth');
    }
    
    /**
     * Process login
     */
    public function authenticate() {
        // Validate CSRF
        CSRF::check();
        
        $username = Sanitizer::string($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($username) || empty($password)) {
            Flash::error('Please provide username and password');
            $this->redirect('/auth/login');
        }
        
        if ($this->authService->attempt($username, $password, $remember)) {
            Flash::success('Welcome back!');
            $this->redirect('/dashboard');
        } else {
            Flash::error('Invalid credentials');
            $this->redirect('/auth/login');
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        $this->authService->logout();
        Flash::success('You have been logged out');
        $this->redirect('/auth/login');
    }
    
    /**
     * Show forgot password form
     */
    public function forgotPassword() {
        $this->view('auth.forgot-password', [], 'auth');
    }
    
    /**
     * Process forgot password
     */
    public function sendResetLink() {
        // Validate CSRF
        CSRF::check();
        
        $email = Sanitizer::email($_POST['email'] ?? '');
        
        if (empty($email)) {
            Flash::error('Please provide your email address');
            $this->redirect('/auth/forgot-password');
        }
        
        if ($this->authService->requestPasswordReset($email)) {
            Flash::success('Password reset link has been sent to your email (check logs/email.log in Phase 1)');
        } else {
            // Don't reveal if email exists or not (security)
            Flash::success('If the email exists, a reset link has been sent');
        }
        
        $this->redirect('/auth/login');
    }
    
    /**
     * Show reset password form
     */
    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            Flash::error('Invalid reset token');
            $this->redirect('/auth/login');
        }
        
        $this->view('auth.reset-password', ['token' => $token], 'auth');
    }
    
    /**
     * Process password reset
     */
    public function updatePassword() {
        // Validate CSRF
        CSRF::check();
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($token) || empty($password)) {
            Flash::error('All fields are required');
            $this->redirect('/auth/reset-password?token=' . $token);
        }
        
        if ($password !== $confirmPassword) {
            Flash::error('Passwords do not match');
            $this->redirect('/auth/reset-password?token=' . $token);
        }
        
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            Flash::error('Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters');
            $this->redirect('/auth/reset-password?token=' . $token);
        }
        
        if ($this->authService->resetPassword($token, $password)) {
            Flash::success('Password has been reset. Please login with your new password');
            $this->redirect('/auth/login');
        } else {
            Flash::error('Invalid or expired reset token');
            $this->redirect('/auth/forgot-password');
        }
    }
}
