-- SMTP settings table for email configuration
-- Single-row table (only one active configuration)
CREATE TABLE IF NOT EXISTS smtp_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- SMTP Server Configuration
    smtp_host VARCHAR(255) NOT NULL COMMENT 'e.g., smtp.gmail.com',
    smtp_port INT UNSIGNED NOT NULL DEFAULT 587 COMMENT 'Usually 587 (TLS) or 465 (SSL)',
    smtp_encryption ENUM('tls', 'ssl', 'none') DEFAULT 'tls',
    
    -- Authentication
    smtp_username VARCHAR(255) NOT NULL,
    smtp_password VARCHAR(255) NOT NULL COMMENT 'Encrypted password',
    
    -- Sender Details
    from_email VARCHAR(255) NOT NULL COMMENT 'Email address notifications are sent from',
    from_name VARCHAR(100) NOT NULL DEFAULT 'APS System' COMMENT 'Display name for sender',
    
    -- Reply-To (optional)
    reply_to_email VARCHAR(255) NULL,
    reply_to_name VARCHAR(100) NULL,
    
    -- Testing & Debug
    test_email VARCHAR(255) NULL COMMENT 'Email address for sending test emails',
    smtp_debug BOOLEAN DEFAULT FALSE COMMENT 'Enable SMTP debug mode',
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Enable/disable email notifications globally',
    last_tested_at DATETIME NULL,
    last_test_result ENUM('success', 'failed') NULL,
    last_test_error TEXT NULL,
    
    -- Email Template Settings
    use_html BOOLEAN DEFAULT TRUE COMMENT 'Send HTML emails',
    email_footer TEXT NULL COMMENT 'Footer text for all emails',
    
    -- Rate Limiting
    max_emails_per_hour INT UNSIGNED DEFAULT 100 COMMENT 'Maximum emails per hour',
    
    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    
    -- Indexes
    INDEX idx_is_active (is_active),
    
    -- Foreign Keys
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default SMTP configuration
INSERT INTO smtp_settings (
    smtp_host,
    smtp_port,
    smtp_encryption,
    smtp_username,
    smtp_password,
    from_email,
    from_name,
    is_active
) VALUES (
    'smtp.gmail.com',
    587,
    'tls',
    'your-email@gmail.com',
    '',
    'noreply@aps-system.local',
    'APS Notification System',
    FALSE
) ON DUPLICATE KEY UPDATE id=id;
