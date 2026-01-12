-- Drug Regimes table (Screen 3 Data - Recurring Daily Entry)
CREATE TABLE IF NOT EXISTS drug_regimes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Foreign Keys
    catheter_id INT UNSIGNED NOT NULL,
    patient_id INT UNSIGNED NOT NULL,
    
    -- Post-Operative Day
    pod TINYINT UNSIGNED NOT NULL COMMENT 'Post-operative day 0-N',
    entry_date DATE NOT NULL,
    
    -- Drug Information
    drug VARCHAR(100) NOT NULL,
    volume DECIMAL(5,2) NOT NULL COMMENT 'in ml (0-50)',
    concentration DECIMAL(5,2) NOT NULL COMMENT 'percentage (0-100)',
    adjuvant VARCHAR(100) NULL,
    dose DECIMAL(8,2) NULL COMMENT 'in mg',
    
    -- Pain Assessment - Baseline
    baseline_vnrs_static TINYINT NOT NULL COMMENT 'VNRS 0-10 at rest pre-infusion',
    baseline_vnrs_dynamic TINYINT NOT NULL COMMENT 'VNRS 0-10 with movement pre-infusion',
    
    -- Pain Assessment - 15 Minutes Post
    vnrs_15min_static TINYINT NOT NULL COMMENT 'VNRS 0-10 at rest post-infusion',
    vnrs_15min_dynamic TINYINT NOT NULL COMMENT 'VNRS 0-10 with movement post-infusion',
    
    -- Efficacy
    effective_analgesia BOOLEAN NOT NULL COMMENT 'VNRS <3',
    troubleshooting_activated BOOLEAN DEFAULT FALSE,
    troubleshooting_notes TEXT NULL,
    
    -- Adverse Effects
    hypotension ENUM('none', 'mild', 'moderate', 'severe') DEFAULT 'none',
    bradycardia ENUM('none', 'mild', 'moderate', 'severe') DEFAULT 'none',
    sensory_motor_deficit ENUM('none', 'mild', 'moderate', 'severe') DEFAULT 'none',
    nausea_vomiting ENUM('none', 'mild', 'moderate', 'severe') DEFAULT 'none',
    
    -- Additional Notes
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
    INDEX idx_effective_analgesia (effective_analgesia),
    INDEX idx_deleted (deleted_at),
    
    FOREIGN KEY (catheter_id) REFERENCES catheters(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    
    -- Ensure one entry per catheter per POD
    UNIQUE KEY unique_drug_regime_pod_entry (catheter_id, pod),
    
    CONSTRAINT chk_drug_regime_pod CHECK (pod >= 0),
    CONSTRAINT chk_drug_regime_volume CHECK (volume BETWEEN 0 AND 50),
    CONSTRAINT chk_drug_regime_concentration CHECK (concentration BETWEEN 0 AND 100),
    CONSTRAINT chk_drug_regime_vnrs CHECK (
        baseline_vnrs_static BETWEEN 0 AND 10 AND
        baseline_vnrs_dynamic BETWEEN 0 AND 10 AND
        vnrs_15min_static BETWEEN 0 AND 10 AND
        vnrs_15min_dynamic BETWEEN 0 AND 10
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
