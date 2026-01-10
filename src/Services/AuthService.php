<?php
namespace Services;

use Models\User;
use Helpers\Session;

/**
 * Authentication Service
 * Handles all authentication-related business logic
 */
class AuthService {
    
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Attempt to login user
     */
    public function attempt($username, $password, $remember = false) {
        // Find user by username or email
        $user = $this->userModel->findByUsername($username);
        if (!$user) {
            $user = $this->userModel->findByEmail($username);
        }
        
        if (!$user) {
            return false;
        }
        
        // Check if user is active
        if ($user['status'] !== 'active') {
            return false;
        }
        
        // Verify password
        if (!$this->userModel->verifyPassword($password, $user['password_hash'])) {
            return false;
        }
        
        // Login successful - create session
        $this->createSession($user);
        
        // Update last login
        $this->userModel->updateLastLogin($user['id'], getIpAddress());
        
        // Handle remember me
        if ($remember) {
            $this->createRememberToken($user['id']);
        }
        
        return true;
    }
    
    /**
     * Create user session
     */
    private function createSession($user) {
        Session::regenerate();
        
        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        Session::set('email', $user['email']);
        Session::set('role', $user['role']);
        Session::set('first_name', $user['first_name']);
        Session::set('last_name', $user['last_name']);
        Session::set('timeout', $user['session_timeout'] ?? SESSION_LIFETIME);
        Session::set('logged_in_at', time());
    }
    
    /**
     * Create remember me token
     */
    private function createRememberToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + REMEMBER_ME_LIFETIME);
        
        $this->userModel->setRememberToken($userId, $token, $expires);
        
        // Set cookie
        setcookie(
            'remember_token',
            $token,
            time() + REMEMBER_ME_LIFETIME,
            '/',
            '',
            false, // Set to true in production with HTTPS
            true // HttpOnly
        );
    }
    
    /**
     * Check remember me token
     */
    public function checkRememberToken() {
        if (!isset($_COOKIE['remember_token'])) {
            return false;
        }
        
        $user = $this->userModel->findByRememberToken($_COOKIE['remember_token']);
        
        if ($user) {
            $this->createSession($user);
            return true;
        }
        
        // Invalid token - clear cookie
        setcookie('remember_token', '', time() - 3600, '/');
        return false;
    }
    
    /**
     * Logout user
     */
    public function logout() {
        $userId = Session::get('user_id');
        
        if ($userId) {
            $this->userModel->clearRememberToken($userId);
        }
        
        Session::destroy();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }
    
    /**
     * Request password reset
     */
    public function requestPasswordReset($email) {
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        
        $this->userModel->setResetToken($user['id'], $token, $expires);
        
        // Send email (stub for Phase 1)
        $this->sendPasswordResetEmail($user, $token);
        
        return true;
    }
    
    /**
     * Send password reset email (stub)
     */
    private function sendPasswordResetEmail($user, $token) {
        $resetUrl = BASE_URL . '/auth/reset-password?token=' . $token;
        
        $message = "Password reset request for {$user['email']}\n\n";
        $message .= "Click here to reset: {$resetUrl}\n\n";
        $message .= "This link expires in 1 hour.\n";
        
        // Log to file (Phase 1 - email stub)
        logMessage("Password Reset Email: " . $message, 'INFO');
        
        // In later phases, integrate real email service
        // EmailService::send($user['email'], 'Password Reset', $message);
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword($token, $newPassword) {
        $user = $this->userModel->findByResetToken($token);
        
        if (!$user) {
            return false;
        }
        
        $passwordHash = $this->userModel->hashPassword($newPassword);
        $this->userModel->updatePassword($user['id'], $passwordHash);
        $this->userModel->clearResetToken($user['id']);
        
        return true;
    }
}
