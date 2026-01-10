<?php
namespace Models;

/**
 * User Model
 */
class User extends BaseModel {
    
    protected $table = 'users';
    
    /**
     * Find user by username
     */
    public function findByUsername($username) {
        return $this->findBy('username', $username);
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email) {
        return $this->findBy('email', $email);
    }
    
    /**
     * Find user by remember token
     */
    public function findByRememberToken($token) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE remember_token = ? 
            AND remember_token_expires > NOW()
            AND deleted_at IS NULL
        ");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }
    
    /**
     * Find user by reset token
     */
    public function findByResetToken($token) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE password_reset_token = ? 
            AND password_reset_expires > NOW()
            AND deleted_at IS NULL
        ");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }
    
    /**
     * Update last login
     */
    public function updateLastLogin($userId, $ip) {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET last_login_at = NOW(), last_login_ip = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$ip, $userId]);
    }
    
    /**
     * Set remember token
     */
    public function setRememberToken($userId, $token, $expires) {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET remember_token = ?, remember_token_expires = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$token, $expires, $userId]);
    }
    
    /**
     * Clear remember token
     */
    public function clearRememberToken($userId) {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET remember_token = NULL, remember_token_expires = NULL 
            WHERE id = ?
        ");
        return $stmt->execute([$userId]);
    }
    
    /**
     * Set password reset token
     */
    public function setResetToken($userId, $token, $expires) {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET password_reset_token = ?, password_reset_expires = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$token, $expires, $userId]);
    }
    
    /**
     * Clear password reset token
     */
    public function clearResetToken($userId) {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET password_reset_token = NULL, password_reset_expires = NULL 
            WHERE id = ?
        ");
        return $stmt->execute([$userId]);
    }
    
    /**
     * Update password
     */
    public function updatePassword($userId, $passwordHash) {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET password_hash = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([$passwordHash, $userId]);
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Hash password
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
    }
}
