-- Alerts table for system notifications
CREATE TABLE IF NOT EXISTS alerts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Alert Association
    patient_id INT UNSIGNED NOT NULL,
    catheter_id INT UNSIGNED NULL,
    drug_regime_id INT UNSIGNED NULL,
    
    -- Alert Details
    alert_type ENUM(
        'pain_control_inadequate',
        'troubleshooting_activated',
        'severe_adverse_effect',
        'catheter_infection',
        'sentinel_event',
        'vnrs_high',
        'system_notification'
    ) NOT NULL,
    
    severity ENUM('info', 'warning', 'critical') DEFAULT 'warning',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    
    -- Alert Data (JSON for flexibility)
    alert_data JSON NULL COMMENT 'Additional structured alert data',
    
    -- Alert Status
    status ENUM('unread', 'read', 'acknowledged', 'resolved') DEFAULT 'unread',
    acknowledged_by INT UNSIGNED NULL,
    acknowledged_at DATETIME NULL,
    
    -- Notification Targets (which roles should see this)
    target_roles JSON NOT NULL COMMENT 'Array of roles: [attending, resident, nurse]',
    
    -- Soft Delete
    deleted_at DATETIME NULL,
    
    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_patient_id (patient_id),
    INDEX idx_catheter_id (catheter_id),
    INDEX idx_alert_type (alert_type),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_deleted (deleted_at),
    
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (catheter_id) REFERENCES catheters(id) ON DELETE CASCADE,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
