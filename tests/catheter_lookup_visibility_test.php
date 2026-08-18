<?php
/**
 * Regression test for catheter form lookup visibility.
 *
 * Master-data list pages hide soft-deleted lookup rows. Catheter forms must use
 * the same visibility rule, otherwise deleted duplicate indications remain in
 * the clinical dropdown and cannot be saved.
 */

require_once __DIR__ . '/../config/config.php';

final class CatheterLookupVisibilityStatement
{
    private array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function fetchAll(): array
    {
        return $this->rows;
    }
}

final class CatheterLookupVisibilityDatabase
{
    public string $lastSql = '';

    public function query(string $sql): CatheterLookupVisibilityStatement
    {
        $this->lastSql = $sql;

        $visible = [
            'id' => 1,
            'name' => 'Post-operative pain management',
            'active' => 1,
            'deleted_at' => null,
        ];
        $softDeleted = [
            'id' => 2,
            'name' => 'Post-operative pain management',
            'active' => 1,
            'deleted_at' => '2026-08-18 15:00:00',
        ];

        if (str_contains($sql, 'deleted_at IS NULL')) {
            return new CatheterLookupVisibilityStatement([$visible]);
        }

        return new CatheterLookupVisibilityStatement([$visible, $softDeleted]);
    }
}

$controllerClass = new ReflectionClass(\Controllers\CatheterController::class);
$controller = $controllerClass->newInstanceWithoutConstructor();

$fakeDb = new CatheterLookupVisibilityDatabase();
$baseClass = new ReflectionClass(\Controllers\BaseController::class);
$dbProperty = $baseClass->getProperty('db');
if (PHP_VERSION_ID < 80100) {
    $dbProperty->setAccessible(true);
}
$dbProperty->setValue($controller, $fakeDb);

$getLookupData = $controllerClass->getMethod('getLookupData');
if (PHP_VERSION_ID < 80100) {
    $getLookupData->setAccessible(true);
}

$rows = $getLookupData->invoke($controller, 'lookup_catheter_indications');

if (!str_contains($fakeDb->lastSql, 'deleted_at IS NULL')) {
    fwrite(STDERR, 'Expected catheter lookup SQL to exclude soft-deleted master rows.' . PHP_EOL);
    exit(1);
}

if (count($rows) !== 1 || $rows[0]['id'] !== 1) {
    fwrite(STDERR, 'Expected only non-deleted active catheter indication rows, got: ' . json_encode($rows) . PHP_EOL);
    exit(1);
}

echo "Catheter lookup visibility checks passed." . PHP_EOL;
