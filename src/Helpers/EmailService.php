<?php

/**
 * EmailService Helper
 * Handles sending emails via SMTP using PHPMailer
 * 
 * Note: This requires PHPMailer library
 * Install via: composer require phpmailer/phpmailer
 * Or use the bundled PHP mail() function as fallback
 */
class EmailService {
    
    private $settings;
    private $usePHPMailer = false;
    
    public function __construct() {
        // Check if PHPMailer is available
        $this->usePHPMailer = class_exists('PHPMailer\PHPMailer\PHPMailer');
    }
    
    /**
     * Send an email
     * @param array $settings SMTP settings
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML or plain text)
     * @param array $options Additional options (cc, bcc, attachments, etc.)
     * @return array ['success' => bool, 'message' => string]
     */
    public function send($settings, $to, $subject, $body, $options = []) {
        if (!$settings || $settings['is_active'] != 1) {
            return ['success' => false, 'message' => 'Email service is not configured or disabled'];
        }
        
        try {
            if ($this->usePHPMailer) {
                return $this->sendViaPHPMailer($settings, $to, $subject, $body, $options);
            } else {
                return $this->sendViaMail($settings, $to, $subject, $body, $options);
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Send email using PHPMailer (recommended)
     * @param array $settings
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param array $options
     * @return array
     */
    private function sendViaPHPMailer($settings, $to, $subject, $body, $options = []) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $settings['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $settings['smtp_username'];
            $mail->Password = $settings['smtp_password'];
            
            // Encryption
            if ($settings['smtp_encryption'] == 'tls') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($settings['smtp_encryption'] == 'ssl') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            }
            
            $mail->Port = $settings['smtp_port'];
            
            // Debug mode
            if ($settings['smtp_debug']) {
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = 'html';
            }
            
            // Recipients
            $mail->setFrom($settings['from_email'], $settings['from_name']);
            $mail->addAddress($to);
            
            // Reply-To
            if (!empty($settings['reply_to_email'])) {
                $mail->addReplyTo($settings['reply_to_email'], $settings['reply_to_name'] ?? '');
            }
            
            // CC, BCC
            if (isset($options['cc'])) {
                foreach ((array)$options['cc'] as $ccEmail) {
                    $mail->addCC($ccEmail);
                }
            }
            if (isset($options['bcc'])) {
                foreach ((array)$options['bcc'] as $bccEmail) {
                    $mail->addBCC($bccEmail);
                }
            }
            
            // Content
            $mail->isHTML($settings['use_html']);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            // Add footer if configured
            if (!empty($settings['email_footer'])) {
                $mail->Body .= "\n\n" . $settings['email_footer'];
            }
            
            // Alt body for non-HTML clients
            if ($settings['use_html']) {
                $mail->AltBody = strip_tags($body);
            }
            
            // Attachments
            if (isset($options['attachments'])) {
                foreach ((array)$options['attachments'] as $attachment) {
                    $mail->addAttachment($attachment);
                }
            }
            
            $mail->send();
            return ['success' => true, 'message' => 'Email sent successfully'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => "Email could not be sent. Error: {$mail->ErrorInfo}"];
        }
    }
    
    /**
     * Send email using PHP's mail() function (fallback)
     * @param array $settings
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param array $options
     * @return array
     */
    private function sendViaMail($settings, $to, $subject, $body, $options = []) {
        $headers = [];
        $headers[] = "From: {$settings['from_name']} <{$settings['from_email']}>";
        $headers[] = "Reply-To: {$settings['reply_to_email']}";
        
        if ($settings['use_html']) {
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        }
        
        // Add footer
        if (!empty($settings['email_footer'])) {
            $body .= "\n\n" . $settings['email_footer'];
        }
        
        $success = mail($to, $subject, $body, implode("\r\n", $headers));
        
        if ($success) {
            return ['success' => true, 'message' => 'Email sent successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to send email using mail() function'];
        }
    }
    
    /**
     * Send test email
     * @param array $settings
     * @param string $testEmail
     * @return array
     */
    public function sendTestEmail($settings, $testEmail) {
        $subject = "APS System - Test Email";
        $body = "
            <h2>SMTP Configuration Test</h2>
            <p>This is a test email from the Acute Pain Service Management System.</p>
            <p>If you received this email, your SMTP configuration is working correctly.</p>
            <hr>
            <p><strong>Server:</strong> {$settings['smtp_host']}:{$settings['smtp_port']}</p>
            <p><strong>Encryption:</strong> " . strtoupper($settings['smtp_encryption']) . "</p>
            <p><strong>Username:</strong> {$settings['smtp_username']}</p>
            <p><strong>Test Date:</strong> " . date('Y-m-d H:i:s') . "</p>
        ";
        
        return $this->send($settings, $testEmail, $subject, $body);
    }
    
    /**
     * Send notification email
     * @param array $settings
     * @param array $notification Notification record
     * @param string $recipientEmail
     * @param string $recipientName
     * @return array
     */
    public function sendNotificationEmail($settings, $notification, $recipientEmail, $recipientName) {
        $subject = "APS Alert: " . $notification['title'];
        
        // Color mapping for email styling
        $colorMap = [
            'info' => '#17a2b8',
            'success' => '#28a745',
            'warning' => '#ffc107',
            'danger' => '#dc3545',
            'primary' => '#007bff',
            'secondary' => '#6c757d'
        ];
        
        $color = $colorMap[$notification['color']] ?? '#17a2b8';
        
        $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: {$color}; color: white; padding: 20px; border-radius: 5px 5px 0 0;'>
                    <h2 style='margin: 0;'>{$notification['title']}</h2>
                    <p style='margin: 5px 0 0 0; opacity: 0.9;'>Priority: " . strtoupper($notification['priority']) . "</p>
                </div>
                <div style='background-color: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; border-top: none;'>
                    <p>Dear {$recipientName},</p>
                    <p>{$notification['message']}</p>
        ";
        
        if (!empty($notification['action_url'])) {
            $actionText = $notification['action_text'] ?? 'View Details';
            $body .= "
                    <p style='margin-top: 20px;'>
                        <a href='{$notification['action_url']}' 
                           style='background-color: {$color}; color: white; padding: 10px 20px; 
                                  text-decoration: none; border-radius: 5px; display: inline-block;'>
                            {$actionText}
                        </a>
                    </p>
            ";
        }
        
        $body .= "
                    <hr style='margin: 20px 0; border: none; border-top: 1px solid #dee2e6;'>
                    <p style='font-size: 12px; color: #6c757d;'>
                        This is an automated notification from the Acute Pain Service Management System.<br>
                        Sent: " . date('Y-m-d H:i:s', strtotime($notification['created_at'])) . "
                    </p>
                </div>
            </div>
        ";
        
        return $this->send($settings, $recipientEmail, $subject, $body);
    }
    
    /**
     * Check if PHPMailer is available
     * @return bool
     */
    public function isPHPMailerAvailable() {
        return $this->usePHPMailer;
    }
}
