<?php
namespace Services;

/**
 * Email Service (Stub for Phase 1)
 * Will be upgraded to use real SMTP in later phases
 */
class EmailService {
    
    /**
     * Send email (stub - logs to file)
     */
    public static function send($to, $subject, $message, $from = null) {
        $from = $from ?? 'noreply@hospital.com';
        
        $emailLog = "=================================\n";
        $emailLog .= "Email Sent (Stub Mode)\n";
        $emailLog .= "=================================\n";
        $emailLog .= "To: {$to}\n";
        $emailLog .= "From: {$from}\n";
        $emailLog .= "Subject: {$subject}\n";
        $emailLog .= "Time: " . date('Y-m-d H:i:s') . "\n";
        $emailLog .= "---------------------------------\n";
        $emailLog .= $message . "\n";
        $emailLog .= "=================================\n\n";
        
        // Log to file
        file_put_contents(LOGS_PATH . '/email.log', $emailLog, FILE_APPEND);
        
        // Also log to main app log
        logMessage("Email sent to {$to}: {$subject}", 'INFO');
        
        return true;
    }
}
