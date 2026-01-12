-- Patients table (Screen 1 Data)
CREATE TABLE IF NOT EXISTS patients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Patient Identification
    patient_name VARCHAR(255) NOT NULL,
    hospital_number VARCHAR(50) NOT NULL UNIQUE,
    
    -- Demographics
    age TINYINT UNSIGNED NOT NULL,
    gender ENUM('male', 'female', 'transgender') NOT NULL,
    
    -- Anthropometric Data
    height DECIMAL(5,2) NOT NULL COMMENT 'in cm',
    weight DECIMAL(5,2) NOT NULL COMMENT 'in kg',
    bmi DECIMAL(5,2) NOT NULL COMMENT 'auto-calculated',
    height_unit ENUM('cm', 'feet') DEFAULT 'cm',
    
    -- Clinical Information
    comorbid_illness JSON NULL COMMENT 'Array of comorbidities',
    speciality ENUM(
        'general_surgery', 
        'orthopaedics', 
        'obg', 
        'urology', 
        'pediatric', 
        'plastic', 
        'oncosurgery', 
        'cardiothoracic'
    ) NOT NULL,
    diagnosis TEXT NOT NULL,
    surgery JSON NULL COMMENT 'Array of surgeries',
    asa_status TINYINT NOT NULL COMMENT '1-5',
    
    -- Status Tracking
    status ENUM('admitted', 'active_catheter', 'discharged', 'transferred') DEFAULT 'admitted',
    admission_date DATE NOT NULL,
    discharge_date DATE NULL,
    
    -- Soft Delete
    deleted_at DATETIME NULL,
    
    -- Audit Trail
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NOT NULL,
    updated_by INT UNSIGNED NULL,
    
    INDEX idx_hospital_number (hospital_number),
    INDEX idx_status (status),
    INDEX idx_speciality (speciality),
    INDEX idx_admission_date (admission_date),
    INDEX idx_deleted (deleted_at),
    INDEX idx_created_by (created_by),
    
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    
    CONSTRAINT chk_patients_age CHECK (age BETWEEN 0 AND 120),
    CONSTRAINT chk_patients_height CHECK (height BETWEEN 50 AND 250),
    CONSTRAINT chk_patients_weight CHECK (weight BETWEEN 20 AND 300),
    CONSTRAINT chk_patients_asa CHECK (asa_status BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
