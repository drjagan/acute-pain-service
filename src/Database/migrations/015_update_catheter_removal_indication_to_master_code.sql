-- Migration 015: Store catheter removal indication as master-data code
-- Version: 1.2.2
-- Description: Converts catheter_removals.indication from a fixed ENUM to
--              VARCHAR(50), matching lookup_removal_indications.code.

ALTER TABLE catheter_removals
    MODIFY indication VARCHAR(50) NOT NULL COMMENT 'lookup_removal_indications.code';
