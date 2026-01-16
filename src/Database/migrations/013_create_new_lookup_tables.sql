-- Migration 013: Create New Lookup Tables for Master Data Management
-- Version: 1.2.0
-- Description: Creates new lookup tables for catheter indications, removal indications, 
--              sentinel events, and specialties

-- ============================================================================
-- Catheter Insertion Indications
-- ============================================================================
CREATE TABLE IF NOT EXISTS lookup_catheter_indications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    is_common BOOLEAN DEFAULT FALSE COMMENT 'Frequently used indications appear first',
    active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL COMMENT 'Soft delete timestamp',
    INDEX idx_active (active),
    INDEX idx_is_common (is_common),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Reasons for catheter insertion';

-- ============================================================================
-- Catheter Removal Indications
-- ============================================================================
CREATE TABLE IF NOT EXISTS lookup_removal_indications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique code for programmatic reference',
    description TEXT NULL,
    requires_notes BOOLEAN DEFAULT FALSE COMMENT 'Force additional notes when selected',
    is_planned BOOLEAN DEFAULT TRUE COMMENT 'Planned vs unplanned removal',
    active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    INDEX idx_active (active),
    INDEX idx_code (code),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Reasons for catheter removal';

-- ============================================================================
-- Sentinel Events (Adverse Events and Complications)
-- ============================================================================
CREATE TABLE IF NOT EXISTS lookup_sentinel_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category ENUM('infection', 'neurological', 'cardiovascular', 'respiratory', 'mechanical', 'other') NOT NULL,
    severity ENUM('mild', 'moderate', 'severe', 'critical') NOT NULL,
    requires_immediate_action BOOLEAN DEFAULT FALSE,
    description TEXT NULL,
    active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    INDEX idx_active (active),
    INDEX idx_category (category),
    INDEX idx_severity (severity),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Adverse events and complications tracking';

-- ============================================================================
-- Medical Specialties
-- ============================================================================
CREATE TABLE IF NOT EXISTS lookup_specialties (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE COMMENT 'Short code (e.g., GEN, ORTHO)',
    description TEXT NULL,
    active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    INDEX idx_active (active),
    INDEX idx_code (code),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Medical and surgical specialties';

-- ============================================================================
-- Add deleted_at to existing lookup tables for soft delete support
-- ============================================================================

-- Add deleted_at to lookup_comorbidities
ALTER TABLE lookup_comorbidities 
ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL COMMENT 'Soft delete timestamp' AFTER updated_at;

-- Add deleted_at to lookup_surgeries
ALTER TABLE lookup_surgeries 
ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL COMMENT 'Soft delete timestamp' AFTER updated_at;

-- Add deleted_at to lookup_drugs
ALTER TABLE lookup_drugs 
ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL COMMENT 'Soft delete timestamp' AFTER updated_at;

-- Add deleted_at to lookup_adjuvants
ALTER TABLE lookup_adjuvants 
ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL COMMENT 'Soft delete timestamp' AFTER updated_at;

-- Add deleted_at to lookup_red_flags
ALTER TABLE lookup_red_flags 
ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL COMMENT 'Soft delete timestamp' AFTER updated_at;

-- ============================================================================
-- Initial Seed Data
-- ============================================================================

-- Seed Catheter Insertion Indications
INSERT IGNORE INTO lookup_catheter_indications (name, description, is_common, sort_order) VALUES
('Post-operative pain management', 'Continuous pain relief after major surgery', TRUE, 1),
('Chronic pain management', 'Long-term pain control for chronic conditions', TRUE, 2),
('Labour analgesia', 'Pain management during childbirth', TRUE, 3),
('Trauma pain management', 'Acute pain control following traumatic injury', FALSE, 4),
('Cancer pain management', 'Palliative pain control for oncology patients', FALSE, 5),
('Vascular surgery', 'Regional anesthesia for vascular procedures', FALSE, 6),
('Orthopedic surgery', 'Post-operative orthopedic pain control', TRUE, 7),
('Thoracic surgery', 'Post-thoracotomy pain management', FALSE, 8),
('Other', 'Other clinical indications', FALSE, 99);

-- Seed Catheter Removal Indications (from CatheterRemoval model)
INSERT IGNORE INTO lookup_removal_indications (code, name, description, requires_notes, is_planned, sort_order) VALUES
('adequate_analgesia', 'Adequate Analgesia Achieved', 'Pain adequately controlled, catheter no longer needed', FALSE, TRUE, 1),
('adverse_effects', 'Adverse Effects', 'Removal due to side effects or complications', TRUE, FALSE, 2),
('patient_request', 'Patient Request', 'Patient requested catheter removal', TRUE, FALSE, 3),
('infection', 'Infection', 'Signs of infection at catheter site', TRUE, FALSE, 4),
('catheter_displacement', 'Catheter Displacement', 'Catheter has been dislodged or displaced', TRUE, FALSE, 5),
('surgical_completion', 'Surgical Completion', 'Surgery completed, catheter no longer required', FALSE, TRUE, 6),
('other', 'Other', 'Other reason for removal', TRUE, FALSE, 99);

-- Seed Sentinel Events
INSERT IGNORE INTO lookup_sentinel_events (name, category, severity, requires_immediate_action, sort_order) VALUES
('Catheter-related bloodstream infection', 'infection', 'severe', TRUE, 1),
('Local site infection', 'infection', 'moderate', FALSE, 2),
('Epidural abscess', 'infection', 'critical', TRUE, 3),
('Meningitis', 'infection', 'critical', TRUE, 4),
('Nerve injury', 'neurological', 'severe', TRUE, 5),
('Spinal cord injury', 'neurological', 'critical', TRUE, 6),
('Motor block', 'neurological', 'moderate', FALSE, 7),
('Epidural hematoma', 'cardiovascular', 'critical', TRUE, 8),
('Hypotension', 'cardiovascular', 'moderate', FALSE, 9),
('Bradycardia', 'cardiovascular', 'moderate', FALSE, 10),
('Respiratory depression', 'respiratory', 'severe', TRUE, 11),
('Catheter displacement', 'mechanical', 'moderate', FALSE, 12),
('Catheter occlusion', 'mechanical', 'mild', FALSE, 13),
('Leakage at site', 'mechanical', 'mild', FALSE, 14),
('Allergic reaction', 'other', 'severe', TRUE, 15);

-- Seed Medical Specialties (extract unique values from existing lookup_surgeries)
INSERT IGNORE INTO lookup_specialties (name, code, sort_order) VALUES
('General Surgery', 'GEN', 1),
('Orthopedic Surgery', 'ORTHO', 2),
('Vascular Surgery', 'VASC', 3),
('Thoracic Surgery', 'THOR', 4),
('Cardiac Surgery', 'CARD', 5),
('Neurosurgery', 'NEURO', 6),
('Urology', 'URO', 7),
('Gynecology', 'GYN', 8),
('Obstetrics', 'OBS', 9),
('Plastic Surgery', 'PLAST', 10),
('ENT Surgery', 'ENT', 11),
('Ophthalmology', 'OPHT', 12),
('Dental Surgery', 'DENT', 13),
('Bariatric Surgery', 'BARI', 14),
('Transplant Surgery', 'TRANS', 15),
('Other', 'OTHER', 99);
