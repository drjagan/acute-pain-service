-- Functional Outcomes table (Screen 4 Data - Recurring Daily Entry)
CREATE TABLE IF NOT EXISTS functional_outcomes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Foreign Keys
    catheter_id INT UNSIGNED NOT NULL,
    patient_id INT UNSIGNED NOT NULL,
    
    -- Post-Operative Day
    pod TINYINT UNSIGNED NOT NULL COMMENT 'Post-operative day 0-N',
    entry_date DATE NOT NULL,
    
    -- Functional Assessments
    incentive_spirometry ENUM('yes', 'no', 'partial', 'unable') NOT NULL,
    ambulation ENUM('independent', 'assisted', 'bedbound') NOT NULL,
    cough_ability ENUM('effective', 'weak', 'unable') NOT NULL,
    room_air_spo2 ENUM('yes', 'no', 'requires_o2') NOT NULL,
    spo2_value TINYINT NULL COMMENT 'SpO2 percentage if measured',
    
    -- Safety Monitoring
    catheter_site_infection ENUM('none', 'redness', 'discharge', 'swelling') DEFAULT 'none',
    sentinel_events ENUM('none', 'fall', 'aspiration', 'other') DEFAULT 'none',
    sentinel_event_details TEXT NULL,
    
    -- Additional Observations
    clinical_notes TEXT NULL,
    
    -- Soft Delete
    deleted_at DATETIME NULL,
    
    -- Audit Trail
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NOT NULL,
    updated_by INT UNSIGNED NULL,
    
    INDEX idx_catheter_id (catheter_id),
    INDEX idx_patient_id (patient_id),
    INDEX idx_pod (pod),
    INDEX idx_entry_date (entry_date),
    INDEX idx_infection (catheter_site_infection),
    INDEX idx_sentinel (sentinel_events),
    INDEX idx_deleted (deleted_at),
    
    FOREIGN KEY (catheter_id) REFERENCES catheters(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    
    -- Ensure one entry per catheter per POD
    UNIQUE KEY unique_functional_outcomes_pod_entry (catheter_id, pod),
    
    CONSTRAINT chk_functional_outcomes_pod CHECK (pod >= 0),
    CONSTRAINT chk_functional_outcomes_spo2 CHECK (spo2_value IS NULL OR spo2_value BETWEEN 0 AND 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
