-- Catheter Removals table (Screen 5 Data - Single Entry)
CREATE TABLE IF NOT EXISTS catheter_removals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Foreign Keys
    catheter_id INT UNSIGNED NOT NULL UNIQUE,
    patient_id INT UNSIGNED NOT NULL,
    
    -- Removal Data
    indication ENUM(
        'adequate_analgesia',
        'adverse_effects',
        'patient_request',
        'infection',
        'catheter_displacement',
        'surgical_completion',
        'other'
    ) NOT NULL,
    indication_notes TEXT NULL,
    
    date_of_removal DATE NOT NULL,
    number_of_catheter_days TINYINT UNSIGNED NOT NULL COMMENT 'Auto-calculated',
    
    -- Removal Assessment
    catheter_tip_intact BOOLEAN DEFAULT TRUE,
    removal_complications TEXT NULL,
    
    -- Final Assessment
    final_notes TEXT NULL,
    patient_satisfaction ENUM('poor', 'fair', 'good', 'excellent') NULL,
    
    -- Soft Delete
    deleted_at DATETIME NULL,
    
    -- Audit Trail
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NOT NULL,
    updated_by INT UNSIGNED NULL,
    
    INDEX idx_catheter_id (catheter_id),
    INDEX idx_patient_id (patient_id),
    INDEX idx_removal_date (date_of_removal),
    INDEX idx_indication (indication),
    INDEX idx_deleted (deleted_at),
    
    FOREIGN KEY (catheter_id) REFERENCES catheters(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
