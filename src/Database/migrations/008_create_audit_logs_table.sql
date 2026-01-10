-- Audit Logs table (Optional but desirable)
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- User & Action
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL COMMENT 'create, update, delete, login, etc.',
    entity_type VARCHAR(50) NOT NULL COMMENT 'patient, catheter, user, etc.',
    entity_id INT UNSIGNED NULL,
    
    -- Request Details
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    request_method VARCHAR(10) NULL,
    request_url TEXT NULL,
    
    -- Change Details
    old_values JSON NULL COMMENT 'Before update',
    new_values JSON NULL COMMENT 'After update',
    
    -- Additional Context
    description TEXT NULL,
    
    -- Timestamp
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
