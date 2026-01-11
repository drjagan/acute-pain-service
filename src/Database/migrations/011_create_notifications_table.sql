-- Notifications table for in-app and email notifications
-- Tracks all system notifications for users
CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Recipient
    user_id INT UNSIGNED NOT NULL COMMENT 'User who receives this notification',
    
    -- Notification Details
    type ENUM('patient_created', 'catheter_inserted', 'catheter_removed', 'regime_updated', 'outcome_recorded', 'alert', 'system', 'other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    
    -- Priority & Style
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    color ENUM('info', 'success', 'warning', 'danger', 'primary', 'secondary') DEFAULT 'info' COMMENT 'Bootstrap color class',
    icon VARCHAR(50) NULL COMMENT 'Icon class (e.g., fa-user, fa-syringe)',
    
    -- Related Records (polymorphic)
    related_type VARCHAR(50) NULL COMMENT 'patients, catheters, regimes, outcomes, removals',
    related_id INT UNSIGNED NULL COMMENT 'ID of related record',
    
    -- Link to record
    action_url VARCHAR(255) NULL COMMENT 'Direct link to view related record',
    action_text VARCHAR(50) NULL DEFAULT 'View Details' COMMENT 'Button text for action',
    
    -- Status & Tracking
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME NULL,
    
    -- Email Delivery
    send_email BOOLEAN DEFAULT FALSE,
    email_sent BOOLEAN DEFAULT FALSE,
    email_sent_at DATETIME NULL,
    email_error TEXT NULL,
    
    -- Auto-dismiss
    auto_dismiss BOOLEAN DEFAULT TRUE COMMENT 'Auto-mark as read after 10 seconds',
    expires_at DATETIME NULL COMMENT 'Auto-delete notification after this date',
    
    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL COMMENT 'User who triggered the notification',
    
    -- Indexes
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_priority (priority),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_related (related_type, related_id),
    INDEX idx_expires (expires_at),
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
