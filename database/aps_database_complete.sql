-- =====================================================
-- Acute Pain Service (APS) Management System
-- Complete Database Schema with Sample Data
-- Version: 1.1.2
-- Date: 2026-01-12
-- =====================================================
--
-- INSTALLATION INSTRUCTIONS:
-- 1. Create a new database in PhpMyAdmin (e.g., 'aps_database')
-- 2. Select the database
-- 3. Go to Import tab
-- 4. Choose this file
-- 5. Click 'Go' to import
--
-- DEFAULT TEST USERS (all passwords: admin123):
-- - admin / admin123 (System Administrator)
-- - dr.sharma / admin123 (Attending Physician)  
-- - dr.patel / admin123 (Resident)
-- - nurse.kumar / admin123 (Nurse)
--
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `aps_database`
--

-- =====================================================
-- TABLE STRUCTURE
-- =====================================================

-- Users table for authentication and RBAC
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Authentication
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    
    -- Personal Details
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NULL,
    
    -- Role & Status
    role ENUM('attending', 'resident', 'nurse', 'admin') NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    
    -- Remember Me
    remember_token VARCHAR(255) NULL,
    remember_token_expires DATETIME NULL,
    
    -- Password Reset
    password_reset_token VARCHAR(255) NULL,
    password_reset_expires DATETIME NULL,
    
    -- Session Management
    last_login_at DATETIME NULL,
    last_login_ip VARCHAR(45) NULL,
    session_timeout INT DEFAULT 3600 COMMENT 'seconds',
    
    -- Soft Delete
    deleted_at DATETIME NULL,
    
    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    
    -- Indexes
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_deleted (deleted_at),
    INDEX idx_remember_token (remember_token),
    INDEX idx_reset_token (password_reset_token),
    
    -- Foreign Keys
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
    
    CONSTRAINT chk_age CHECK (age BETWEEN 0 AND 120),
    CONSTRAINT chk_height CHECK (height BETWEEN 50 AND 250),
    CONSTRAINT chk_weight CHECK (weight BETWEEN 20 AND 300),
    CONSTRAINT chk_asa CHECK (asa_status BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
    UNIQUE KEY unique_pod_entry (catheter_id, pod),
    
    CONSTRAINT chk_pod CHECK (pod >= 0),
    CONSTRAINT chk_volume CHECK (volume BETWEEN 0 AND 50),
    CONSTRAINT chk_concentration CHECK (concentration BETWEEN 0 AND 100),
    CONSTRAINT chk_vnrs CHECK (
        baseline_vnrs_static BETWEEN 0 AND 10 AND
        baseline_vnrs_dynamic BETWEEN 0 AND 10 AND
        vnrs_15min_static BETWEEN 0 AND 10 AND
        vnrs_15min_dynamic BETWEEN 0 AND 10
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
    UNIQUE KEY unique_pod_entry (catheter_id, pod),
    
    CONSTRAINT chk_pod CHECK (pod >= 0),
    CONSTRAINT chk_spo2 CHECK (spo2_value IS NULL OR spo2_value BETWEEN 0 AND 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
-- Lookup Tables for predefined dropdown values

-- Comorbid Illnesses
CREATE TABLE IF NOT EXISTS lookup_comorbidities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Surgical Procedures
CREATE TABLE IF NOT EXISTS lookup_surgeries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    speciality VARCHAR(50) NULL,
    active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Drugs
CREATE TABLE IF NOT EXISTS lookup_drugs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    generic_name VARCHAR(100) NULL,
    typical_concentration DECIMAL(5,2) NULL,
    max_dose DECIMAL(8,2) NULL,
    unit VARCHAR(20) NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adjuvants
CREATE TABLE IF NOT EXISTS lookup_adjuvants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    typical_dose DECIMAL(8,2) NULL,
    unit VARCHAR(20) NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Red Flags (Adverse Events During Insertion)
CREATE TABLE IF NOT EXISTS lookup_red_flags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    severity ENUM('mild', 'moderate', 'severe') DEFAULT 'moderate',
    requires_immediate_action BOOLEAN DEFAULT FALSE,
    active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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

-- =====================================================
-- SEED DATA (Sample Users and Lookup Data)
-- =====================================================

-- Lookup Data Seeds for Medical Information

-- Comorbidities lookup data
INSERT INTO lookup_comorbidities (name, description, active, sort_order) VALUES
('Diabetes Mellitus', 'Type 1 or Type 2 Diabetes', 1, 1),
('Hypertension', 'High blood pressure', 1, 2),
('Coronary Artery Disease', 'CAD/IHD', 1, 3),
('Chronic Kidney Disease', 'CKD', 1, 4),
('COPD', 'Chronic Obstructive Pulmonary Disease', 1, 5),
('Asthma', 'Bronchial Asthma', 1, 6),
('Obesity', 'BMI >30', 1, 7),
('Sleep Apnea', 'OSA', 1, 8),
('Hypothyroidism', 'Thyroid disorder', 1, 9),
('None', 'No comorbidities', 1, 10);

-- Drugs lookup data
INSERT INTO lookup_drugs (name, generic_name, typical_concentration, max_dose, unit, active) VALUES
('Bupivacaine', 'Bupivacaine HCl', 0.125, 400, 'mg', 1),
('Ropivacaine', 'Ropivacaine HCl', 0.2, 300, 'mg', 1),
('Levobupivacaine', 'Levobupivacaine HCl', 0.125, 150, 'mg', 1),
('Lignocaine', 'Lidocaine HCl', 0.5, 300, 'mg', 1);

-- Adjuvants lookup data
INSERT INTO lookup_adjuvants (name, typical_dose, unit, active) VALUES
('Fentanyl', 2, 'mcg/ml', 1),
('Morphine', 0.05, 'mg/ml', 1),
('Clonidine', 1, 'mcg/ml', 1),
('Dexmedetomidine', 0.5, 'mcg/ml', 1);

-- Red Flags lookup data
INSERT INTO lookup_red_flags (name, severity, requires_immediate_action, active) VALUES
('Hypotension during insertion', 'moderate', 0, 1),
('Bradycardia during insertion', 'moderate', 0, 1),
('Paresthesia', 'mild', 0, 1),
('Blood in catheter', 'severe', 1, 1),
('Dural puncture', 'severe', 1, 1),
('Failed block', 'mild', 0, 1),
('Patient discomfort/anxiety', 'mild', 0, 1);

-- Surgeries lookup data (sample)
INSERT INTO lookup_surgeries (name, speciality, active, sort_order) VALUES
('Total Knee Replacement', 'orthopaedics', 1, 1),
('Total Hip Replacement', 'orthopaedics', 1, 2),
('Cesarean Section', 'obg', 1, 3),
('Laparotomy', 'general_surgery', 1, 4),
('Thoracotomy', 'cardiothoracic', 1, 5),
('Mastectomy', 'oncosurgery', 1, 6),
('Nephrectomy', 'urology', 1, 7),
('CABG', 'cardiothoracic', 1, 8),
('Spinal Fusion', 'orthopaedics', 1, 9),
('Abdominal Hysterectomy', 'obg', 1, 10),
('Whipple Procedure', 'oncosurgery', 1, 11),
('Radical Prostatectomy', 'urology', 1, 12),
('Cholecystectomy', 'general_surgery', 1, 13),
('Appendectomy', 'general_surgery', 1, 14),
('Hernia Repair', 'general_surgery', 1, 15);
-- Sample users for testing all 4 roles
-- Password for all: admin123 (hashed with bcrypt cost 12)
-- Hash: $2y$12$uyIAZsL4jfqqBqGm2zdwre67APUvCaXybNMN/0xQrh4fDw1ESxL9y

-- Admin user
INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, created_at) VALUES
('admin', 'admin@hospital.com', '$2y$12$uyIAZsL4jfqqBqGm2zdwre67APUvCaXybNMN/0xQrh4fDw1ESxL9y', 'System', 'Administrator', 'admin', 'active', NOW());

-- Attending Physician
INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, created_at) VALUES
('dr.sharma', 'sharma@hospital.com', '$2y$12$uyIAZsL4jfqqBqGm2zdwre67APUvCaXybNMN/0xQrh4fDw1ESxL9y', 'Rajesh', 'Sharma', 'attending', 'active', NOW());

-- Senior Resident
INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, created_at) VALUES
('dr.patel', 'patel@hospital.com', '$2y$12$uyIAZsL4jfqqBqGm2zdwre67APUvCaXybNMN/0xQrh4fDw1ESxL9y', 'Priya', 'Patel', 'resident', 'active', NOW());

-- Nurse
INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, created_at) VALUES
('nurse.kumar', 'kumar@hospital.com', '$2y$12$uyIAZsL4jfqqBqGm2zdwre67APUvCaXybNMN/0xQrh4fDw1ESxL9y', 'Anjali', 'Kumar', 'nurse', 'active', NOW());

COMMIT;
