<?php
namespace Controllers;

use Models\SmtpSettings;
use Helpers\Flash;
use Helpers\CSRF;
use Helpers\Sanitizer;

/**
 * Settings Controller (v1.1)
 * Handles system settings - SMTP configuration (admin only)
 */
class SettingsController extends BaseController {
    
    private $smtpModel;
    
    public function __construct() {
        parent::__construct();
        $this->smtpModel = new SmtpSettings();
    }
    
    /**
     * Settings main page - hub for all settings
     */
    public function index() {
        $this->requireRole('admin');
        
        $this->view('settings.index', [
            'db' => $this->db
        ]);
    }
    
    /**
     * Show SMTP settings page
     */
    public function smtp() {
        $this->requireRole('admin');
        
        $settings = $this->smtpModel->getActiveSettings();
        
        // If no settings exist, create default
        if (!$settings) {
            $settings = [
                'smtp_host' => '',
                'smtp_port' => 587,
                'smtp_encryption' => 'tls',
                'smtp_username' => '',
                'smtp_password' => '',
                'from_email' => '',
                'from_name' => 'APS System',
                'reply_to_email' => '',
                'reply_to_name' => '',
                'test_email' => '',
                'smtp_debug' => false,
                'is_active' => false,
                'use_html' => true,
                'email_footer' => '',
                'max_emails_per_hour' => 100
            ];
        }
        
        $this->view('settings.smtp', [
            'settings' => $settings
        ]);
    }
    
    /**
     * Save SMTP settings
     */
    public function saveSMTP() {
        $this->requireRole('admin');
        $this->validateCSRF();
        
        try {
            $data = [
                'smtp_host' => Sanitizer::string($_POST['smtp_host']),
                'smtp_port' => (int)$_POST['smtp_port'],
                'smtp_encryption' => $_POST['smtp_encryption'],
                'smtp_username' => Sanitizer::string($_POST['smtp_username']),
                'from_email' => Sanitizer::email($_POST['from_email']),
                'from_name' => Sanitizer::string($_POST['from_name']),
                'reply_to_email' => !empty($_POST['reply_to_email']) ? Sanitizer::email($_POST['reply_to_email']) : null,
                'reply_to_name' => !empty($_POST['reply_to_name']) ? Sanitizer::string($_POST['reply_to_name']) : null,
                'test_email' => !empty($_POST['test_email']) ? Sanitizer::email($_POST['test_email']) : null,
                'smtp_debug' => isset($_POST['smtp_debug']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'use_html' => isset($_POST['use_html']) ? 1 : 0,
                'email_footer' => Sanitizer::string($_POST['email_footer'] ?? ''),
                'max_emails_per_hour' => (int)($_POST['max_emails_per_hour'] ?? 100),
                'updated_by' => $this->user()['id']
            ];
            
            // Only update password if provided
            if (!empty($_POST['smtp_password'])) {
                $data['smtp_password'] = $_POST['smtp_password'];
            }
            
            $settings = $this->smtpModel->getActiveSettings();
            
            if ($settings) {
                // Update existing
                $this->smtpModel->updateSettings($settings['id'], $data);
            } else {
                // Create new
                $data['created_by'] = $this->user()['id'];
                $this->smtpModel->createSettings($data);
            }
            
            Flash::success('SMTP settings saved successfully');
            
        } catch (\Exception $e) {
            Flash::error('Failed to save SMTP settings: ' . $e->getMessage());
        }
        
        return $this->redirect('/settings/smtp');
    }
    
    /**
     * Test SMTP connection (AJAX)
     */
    public function testSMTP() {
        $this->requireRole('admin');
        header('Content-Type: application/json');
        
        try {
            $settings = $this->smtpModel->getActiveSettings();
            
            if (!$settings) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No SMTP settings configured'
                ]);
                exit;
            }
            
            $testEmail = $_POST['test_email'] ?? $settings['test_email'] ?? $this->user()['email'];
            
            if (empty($testEmail)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Please provide a test email address'
                ]);
                exit;
            }
            
            $result = $this->smtpModel->testConnection($settings['id'], $testEmail);
            
            echo json_encode($result);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage()
            ]);
        }
        
        exit;
    }
    
    /**
     * Toggle SMTP status (enable/disable)
     */
    public function toggleSMTP() {
        $this->requireRole('admin');
        $this->validateCSRF();
        
        try {
            $settings = $this->smtpModel->getActiveSettings();
            
            if (!$settings) {
                Flash::error('No SMTP settings found');
                return $this->redirect('/settings/smtp');
            }
            
            $newStatus = $settings['is_active'] ? 0 : 1;
            
            $this->smtpModel->update($settings['id'], [
                'is_active' => $newStatus,
                'updated_by' => $this->user()['id']
            ]);
            
            Flash::success('Email notifications ' . ($newStatus ? 'enabled' : 'disabled'));
            
        } catch (\Exception $e) {
            Flash::error('Failed to toggle SMTP status: ' . $e->getMessage());
        }
        
        return $this->redirect('/settings/smtp');
    }
}
