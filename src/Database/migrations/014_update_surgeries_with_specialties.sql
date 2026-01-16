-- Migration 014: Update Surgeries Table with Specialty Foreign Key
-- Version: 1.2.0
-- Description: Migrates specialty data from VARCHAR to foreign key relationship,
--              adds specialty_id to lookup_surgeries, and removes old speciality column

-- ============================================================================
-- STEP 1: Add specialty_id column to lookup_surgeries
-- ============================================================================
ALTER TABLE lookup_surgeries 
ADD COLUMN IF NOT EXISTS specialty_id INT UNSIGNED NULL COMMENT 'Foreign key to lookup_specialties' 
AFTER name;

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
-- STEP 4: Add foreign key constraint
-- ============================================================================

-- Add index for foreign key
ALTER TABLE lookup_surgeries 
ADD INDEX IF NOT EXISTS idx_specialty_id (specialty_id);

-- Add foreign key constraint with ON DELETE RESTRICT to prevent deleting specialties with surgeries
ALTER TABLE lookup_surgeries
ADD CONSTRAINT fk_surgery_specialty
FOREIGN KEY (specialty_id) 
REFERENCES lookup_specialties(id)
ON DELETE RESTRICT
ON UPDATE CASCADE;

-- ============================================================================
-- STEP 5: Make specialty_id NOT NULL now that all records have values
-- ============================================================================
ALTER TABLE lookup_surgeries 
MODIFY COLUMN specialty_id INT UNSIGNED NOT NULL COMMENT 'Foreign key to lookup_specialties';

-- ============================================================================
-- STEP 6: Drop old speciality column (VARCHAR)
-- ============================================================================

-- Drop the old speciality VARCHAR column
ALTER TABLE lookup_surgeries 
DROP COLUMN IF EXISTS speciality;

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
