<?php
namespace Models;

use PDO;

/**
 * SmtpSettings Model
 * Manages SMTP configuration for email notifications
 */
class SmtpSettings extends BaseModel {
    
    protected $table = 'smtp_settings';
    
    /**
     * Get active SMTP settings
     * @return array|false
     */
    public function getActiveSettings() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 LIMIT 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
    
    /**
     * Get all SMTP configurations
     * @return array
     */
    public function getAll() {
        $sql = "SELECT * FROM {$this->table} ORDER BY is_active DESC, created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Update SMTP settings
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateSettings($id, $data) {
        // Encrypt password if provided
        if (isset($data['smtp_password']) && !empty($data['smtp_password'])) {
            $data['smtp_password'] = $this->encryptPassword($data['smtp_password']);
        } else {
            // Don't update password if not provided
            unset($data['smtp_password']);
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Create SMTP settings
     * @param array $data
     * @return int|false
     */
    public function createSettings($data) {
        // Encrypt password
        if (isset($data['smtp_password'])) {
            $data['smtp_password'] = $this->encryptPassword($data['smtp_password']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Test SMTP connection
     * @param int $id
     * @param string $testEmail
     * @return array ['success' => bool, 'message' => string]
     */
    public function testConnection($id, $testEmail) {
        $settings = $this->find($id);
        
        if (!$settings) {
            return ['success' => false, 'message' => 'Settings not found'];
        }
        
        // Decrypt password
        $settings['smtp_password'] = $this->decryptPassword($settings['smtp_password']);
        
        // Update test timestamp
        $this->update($id, [
            'last_tested_at' => date('Y-m-d H:i:s')
        ]);
        
        try {
            // Use EmailService to send test email
            if (!class_exists('EmailService')) {
                require_once __DIR__ . '/../Helpers/EmailService.php';
            }
            
            $emailService = new \EmailService();
            $result = $emailService->sendTestEmail($settings, $testEmail);
            
            // Update test result
            $this->update($id, [
                'last_test_result' => $result['success'] ? 'success' : 'failed',
                'last_test_error' => $result['success'] ? null : $result['message']
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            $this->update($id, [
                'last_test_result' => 'failed',
                'last_test_error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Activate SMTP settings (deactivate others)
     * @param int $id
     * @return bool
     */
    public function activate($id) {
        // Deactivate all
        $sql = "UPDATE {$this->table} SET is_active = 0";
        $this->db->query($sql);
        
        // Activate selected
        return $this->update($id, ['is_active' => 1]);
    }
    
    /**
     * Deactivate all SMTP settings
     * @return bool
     */
    public function deactivateAll() {
        $sql = "UPDATE {$this->table} SET is_active = 0";
        $stmt = $this->db->query($sql);
        return true;
    }
    
    /**
     * Check if emails are enabled
     * @return bool
     */
    public function isEmailEnabled() {
        $settings = $this->getActiveSettings();
        return $settings && $settings['is_active'] == 1;
    }
    
    /**
     * Encrypt password (simple base64 - for production use proper encryption)
     * @param string $password
     * @return string
     */
    private function encryptPassword($password) {
        // For production, use proper encryption like openssl_encrypt()
        // This is a simple implementation for demonstration
        return base64_encode($password);
    }
    
    /**
     * Decrypt password
     * @param string $encryptedPassword
     * @return string
     */
    public function decryptPassword($encryptedPassword) {
        // For production, use proper decryption like openssl_decrypt()
        return base64_decode($encryptedPassword);
    }
    
    /**
     * Get decrypted SMTP settings for email sending
     * @return array|false
     */
    public function getDecryptedSettings() {
        $settings = $this->getActiveSettings();
        
        if ($settings && !empty($settings['smtp_password'])) {
            $settings['smtp_password'] = $this->decryptPassword($settings['smtp_password']);
        }
        
        return $settings;
    }
}
