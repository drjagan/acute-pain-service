-- Patient-Physician association table (many-to-many)
-- Links patients with their attending physicians and residents
CREATE TABLE IF NOT EXISTS patient_physicians (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Relationships
    patient_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    
    -- Physician Type
    physician_type ENUM('attending', 'resident') NOT NULL,
    
    -- Status
    is_primary BOOLEAN DEFAULT FALSE COMMENT 'Primary attending or resident',
    status ENUM('active', 'inactive') DEFAULT 'active',
    
    -- Assignment Period
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    unassigned_at DATETIME NULL,
    
    -- Notes
    notes TEXT NULL COMMENT 'Reason for assignment or other notes',
    
    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    
    -- Indexes
    INDEX idx_patient (patient_id),
    INDEX idx_user (user_id),
    INDEX idx_physician_type (physician_type),
    INDEX idx_status (status),
    INDEX idx_is_primary (is_primary),
    UNIQUE KEY unique_patient_physician (patient_id, user_id, physician_type),
    
    -- Foreign Keys
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
