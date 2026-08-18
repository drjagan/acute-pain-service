<?php
/**
 * Regression test for master-data removal indication storage.
 *
 * Removal indications are managed by lookup_removal_indications.code, so the
 * removal record must store a VARCHAR code rather than a fixed ENUM list.
 */

$root = dirname(__DIR__);
$schemaFiles = [
    'src/Database/migrations/006_create_catheter_removals_table.sql',
    'database.sql',
    'documentation/database/aps_database_complete.sql',
];

foreach ($schemaFiles as $relativePath) {
    $sql = file_get_contents($root . '/' . $relativePath);

    if (preg_match('/\bindication\s+ENUM\s*\(/i', $sql)) {
        fwrite(STDERR, "$relativePath still defines catheter_removals.indication as ENUM." . PHP_EOL);
        exit(1);
    }

    if (!preg_match('/\bindication\s+VARCHAR\(50\)\s+NOT\s+NULL/i', $sql)) {
        fwrite(STDERR, "$relativePath does not define catheter_removals.indication as VARCHAR(50) NOT NULL." . PHP_EOL);
        exit(1);
    }
}

$migrationPath = $root . '/src/Database/migrations/015_update_catheter_removal_indication_to_master_code.sql';
if (!file_exists($migrationPath)) {
    fwrite(STDERR, 'Missing migration 015 for existing catheter_removals.indication columns.' . PHP_EOL);
    exit(1);
}

$migrationSql = file_get_contents($migrationPath);
if (
    !preg_match('/ALTER\s+TABLE\s+catheter_removals/i', $migrationSql) ||
    !preg_match('/MODIFY\s+indication\s+VARCHAR\(50\)\s+NOT\s+NULL/i', $migrationSql)
) {
    fwrite(STDERR, 'Migration 015 does not convert catheter_removals.indication to VARCHAR(50).' . PHP_EOL);
    exit(1);
}

$manualRunner = file_get_contents($root . '/run_master_data_migrations_v2.php');
if (!str_contains($manualRunner, '015_update_catheter_removal_indication_to_master_code.sql')) {
    fwrite(STDERR, 'Manual migration runner does not include migration 015.' . PHP_EOL);
    exit(1);
}

echo "Catheter removal indication schema checks passed." . PHP_EOL;
