-- Catheters table (Screen 2 Data)
CREATE TABLE IF NOT EXISTS catheters (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Foreign Keys
    patient_id INT UNSIGNED NOT NULL,
    
    -- Procedural Data
    date_of_insertion DATE NOT NULL,
    settings ENUM('elective', 'emergency') NOT NULL,
    performer ENUM('consultant', 'resident') NOT NULL,
    
    -- Catheter Type
    catheter_category ENUM('epidural', 'peripheral_nerve', 'fascial_plane') NOT NULL,
    catheter_type VARCHAR(100) NOT NULL COMMENT 'Specific type within category',
    
    -- Clinical Indication
    indication TEXT NOT NULL,
    
    -- Confirmation
    functional_confirmation BOOLEAN NOT NULL DEFAULT FALSE,
    anatomical_confirmation BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Adverse Events
    red_flags JSON NULL COMMENT 'Array of adverse events during insertion',
    
    -- Catheter Status
    status ENUM('active', 'removed', 'displaced', 'infected') DEFAULT 'active',
    
    -- Soft Delete
    deleted_at DATETIME NULL,
    
    -- Audit Trail
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NOT NULL,
    updated_by INT UNSIGNED NULL,
    
    INDEX idx_patient_id (patient_id),
    INDEX idx_insertion_date (date_of_insertion),
    INDEX idx_catheter_category (catheter_category),
    INDEX idx_status (status),
    INDEX idx_deleted (deleted_at),
    
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
