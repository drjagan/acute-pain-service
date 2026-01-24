-- Migration 014: Update Surgeries Table with Specialty Foreign Key (FIXED)
-- Version: 1.2.0
-- Description: Migrates specialty data from VARCHAR to foreign key relationship,
--              adds specialty_id to lookup_surgeries, and removes old speciality column
-- FIX: Removed IF NOT EXISTS/IF EXISTS from ALTER TABLE statements (not supported in older MySQL)

DELIMITER $$

-- ============================================================================
-- STEP 1: Add specialty_id column to lookup_surgeries
-- ============================================================================

DROP PROCEDURE IF EXISTS add_specialty_id_column$$
CREATE PROCEDURE add_specialty_id_column()
BEGIN
    IF NOT EXISTS (
        SELECT NULL FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'lookup_surgeries'
        AND COLUMN_NAME = 'specialty_id'
    ) THEN
        ALTER TABLE lookup_surgeries 
        ADD COLUMN specialty_id INT UNSIGNED NULL COMMENT 'Foreign key to lookup_specialties' 
        AFTER name;
    END IF;
END$$

CALL add_specialty_id_column()$$
DROP PROCEDURE IF EXISTS add_specialty_id_column$$

DELIMITER ;

-- ============================================================================
-- STEP 2: Migrate existing specialty text data to lookup_specialties
-- ============================================================================

-- First, insert any specialties that don't exist yet from the surgeries table
INSERT IGNORE INTO lookup_specialties (name, code, sort_order, active)
SELECT DISTINCT 
    TRIM(speciality) as name,
    UPPER(LEFT(TRIM(speciality), 5)) as code,
    0 as sort_order,
    TRUE as active
FROM lookup_surgeries 
WHERE speciality IS NOT NULL 
    AND TRIM(speciality) != ''
    AND NOT EXISTS (
        SELECT 1 FROM lookup_specialties ls 
        WHERE LOWER(TRIM(ls.name)) = LOWER(TRIM(lookup_surgeries.speciality))
    );

-- ============================================================================
-- STEP 3: Update lookup_surgeries to link to lookup_specialties
-- ============================================================================

-- Match surgeries to specialties by name (case-insensitive)
UPDATE lookup_surgeries ls
INNER JOIN lookup_specialties lsp ON LOWER(TRIM(lsp.name)) = LOWER(TRIM(ls.speciality))
SET ls.specialty_id = lsp.id
WHERE ls.speciality IS NOT NULL AND TRIM(ls.speciality) != '';

-- Set 'Other' specialty for surgeries without a specialty
UPDATE lookup_surgeries ls
SET ls.specialty_id = (SELECT id FROM lookup_specialties WHERE code = 'OTHER')
WHERE ls.specialty_id IS NULL;

-- ============================================================================
-- STEP 4: Add index and foreign key constraint
-- ============================================================================

DELIMITER $$

-- Add index for foreign key
DROP PROCEDURE IF EXISTS add_specialty_index$$
CREATE PROCEDURE add_specialty_index()
BEGIN
    IF NOT EXISTS (
        SELECT NULL FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'lookup_surgeries'
        AND INDEX_NAME = 'idx_specialty_id'
    ) THEN
        ALTER TABLE lookup_surgeries 
        ADD INDEX idx_specialty_id (specialty_id);
    END IF;
END$$

CALL add_specialty_index()$$
DROP PROCEDURE IF EXISTS add_specialty_index$$

-- Add foreign key constraint
DROP PROCEDURE IF EXISTS add_specialty_fk$$
CREATE PROCEDURE add_specialty_fk()
BEGIN
    IF NOT EXISTS (
        SELECT NULL FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
        AND TABLE_NAME = 'lookup_surgeries'
        AND CONSTRAINT_NAME = 'fk_surgery_specialty'
    ) THEN
        ALTER TABLE lookup_surgeries
        ADD CONSTRAINT fk_surgery_specialty
        FOREIGN KEY (specialty_id) 
        REFERENCES lookup_specialties(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE;
    END IF;
END$$

CALL add_specialty_fk()$$
DROP PROCEDURE IF EXISTS add_specialty_fk$$

DELIMITER ;

-- ============================================================================
-- STEP 5: Make specialty_id NOT NULL now that all records have values
-- ============================================================================

ALTER TABLE lookup_surgeries 
MODIFY COLUMN specialty_id INT UNSIGNED NOT NULL COMMENT 'Foreign key to lookup_specialties';

-- ============================================================================
-- STEP 6: Drop old speciality column (VARCHAR)
-- ============================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS drop_speciality_column$$
CREATE PROCEDURE drop_speciality_column()
BEGIN
    IF EXISTS (
        SELECT NULL FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'lookup_surgeries'
        AND COLUMN_NAME = 'speciality'
    ) THEN
        ALTER TABLE lookup_surgeries 
        DROP COLUMN speciality;
    END IF;
END$$

CALL drop_speciality_column()$$
DROP PROCEDURE IF EXISTS drop_speciality_column$$

DELIMITER ;

-- ============================================================================
-- STEP 7: Add sort_order to drugs and adjuvants if not exists
-- ============================================================================

DELIMITER $$

-- Add sort_order to lookup_drugs
DROP PROCEDURE IF EXISTS add_sort_order_drugs$$
CREATE PROCEDURE add_sort_order_drugs()
BEGIN
    IF NOT EXISTS (
        SELECT NULL FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'lookup_drugs'
        AND COLUMN_NAME = 'sort_order'
    ) THEN
        ALTER TABLE lookup_drugs 
        ADD COLUMN sort_order INT DEFAULT 0 AFTER active;
    END IF;
END$$

CALL add_sort_order_drugs()$$
DROP PROCEDURE IF EXISTS add_sort_order_drugs$$

-- Add sort_order to lookup_adjuvants
DROP PROCEDURE IF EXISTS add_sort_order_adjuvants$$
CREATE PROCEDURE add_sort_order_adjuvants()
BEGIN
    IF NOT EXISTS (
        SELECT NULL FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'lookup_adjuvants'
        AND COLUMN_NAME = 'sort_order'
    ) THEN
        ALTER TABLE lookup_adjuvants 
        ADD COLUMN sort_order INT DEFAULT 0 AFTER active;
    END IF;
END$$

CALL add_sort_order_adjuvants()$$
DROP PROCEDURE IF EXISTS add_sort_order_adjuvants$$

DELIMITER ;

-- ============================================================================
-- Verification Query (commented out - uncomment to test)
-- ============================================================================

/*
-- Verify the migration
SELECT 
    ls.id,
    ls.name AS surgery_name,
    lsp.name AS specialty_name,
    lsp.code AS specialty_code,
    ls.active,
    ls.sort_order
FROM lookup_surgeries ls
LEFT JOIN lookup_specialties lsp ON ls.specialty_id = lsp.id
ORDER BY lsp.name, ls.name;

-- Count surgeries per specialty
SELECT 
    lsp.name AS specialty,
    lsp.code,
    COUNT(ls.id) AS surgery_count
FROM lookup_specialties lsp
LEFT JOIN lookup_surgeries ls ON lsp.id = ls.specialty_id
GROUP BY lsp.id, lsp.name, lsp.code
ORDER BY surgery_count DESC;
*/
