<?php
/**
 * Regression test for catheter insertion indication master-data selection.
 *
 * The create/edit forms post lookup_catheter_indications.id as indication_id,
 * while catheters.indication stores the selected master name for backwards
 * compatibility with existing reports.
 */

require_once __DIR__ . '/../config/config.php';

final class CatheterIndicationFakePatientModel
{
    public function find($id)
    {
        return (string)$id === '123' ? ['id' => 123, 'patient_name' => 'Test Patient'] : false;
    }
}

final class CatheterIndicationFakeStatement
{
    private $resolver;
    private array $rows = [];

    public function __construct(callable $resolver)
    {
        $this->resolver = $resolver;
    }

    public function execute(array $params = []): bool
    {
        $this->rows = ($this->resolver)($params);
        return true;
    }

    public function fetch()
    {
        return $this->rows[0] ?? false;
    }
}

final class CatheterIndicationFakeDatabase
{
    private array $indications = [
        5 => [
            'id' => 5,
            'name' => 'Post-operative pain management',
            'active' => 1,
            'deleted_at' => null,
        ],
    ];

    public function prepare(string $sql): CatheterIndicationFakeStatement
    {
        return new CatheterIndicationFakeStatement(function (array $params) use ($sql): array {
            if (str_contains($sql, 'id = ?')) {
                $id = (int)($params[0] ?? 0);
                return isset($this->indications[$id]) ? [$this->indications[$id]] : [];
            }

            if (str_contains($sql, 'name = ?')) {
                $name = (string)($params[0] ?? '');
                foreach ($this->indications as $indication) {
                    if ($indication['name'] === $name) {
                        return [$indication];
                    }
                }
            }

            return [];
        });
    }
}

$controllerClass = new ReflectionClass(\Controllers\CatheterController::class);
$controller = $controllerClass->newInstanceWithoutConstructor();

$baseClass = new ReflectionClass(\Controllers\BaseController::class);
$dbProperty = $baseClass->getProperty('db');
if (PHP_VERSION_ID < 80100) {
    $dbProperty->setAccessible(true);
}
$dbProperty->setValue($controller, new CatheterIndicationFakeDatabase());

$patientProperty = $controllerClass->getProperty('patientModel');
if (PHP_VERSION_ID < 80100) {
    $patientProperty->setAccessible(true);
}
$patientProperty->setValue($controller, new CatheterIndicationFakePatientModel());

$payload = [
    'patient_id' => '123',
    'date_of_insertion' => '2026-08-18',
    'settings' => 'elective',
    'performer' => 'consultant',
    'catheter_category' => 'epidural',
    'catheter_type' => 'lumbar',
    'indication_id' => '5',
];

$validate = $controllerClass->getMethod('validateCatheterData');
if (PHP_VERSION_ID < 80100) {
    $validate->setAccessible(true);
}
$validation = $validate->invoke($controller, $payload);

if (!$validation['valid']) {
    fwrite(STDERR, 'Expected selected indication_id to validate, got: ' . $validation['message'] . PHP_EOL);
    exit(1);
}

$prepare = $controllerClass->getMethod('prepareCatheterData');
if (PHP_VERSION_ID < 80100) {
    $prepare->setAccessible(true);
}
$prepared = $prepare->invoke($controller, $payload);

if (($prepared['indication'] ?? null) !== 'Post-operative pain management') {
    fwrite(STDERR, 'Expected indication master name to be saved, got: ' . var_export($prepared['indication'] ?? null, true) . PHP_EOL);
    exit(1);
}

$getId = $controllerClass->getMethod('getCatheterIndicationIdByName');
if (PHP_VERSION_ID < 80100) {
    $getId->setAccessible(true);
}
$indicationId = $getId->invoke($controller, 'Post-operative pain management');

if ($indicationId !== 5) {
    fwrite(STDERR, 'Expected saved indication text to resolve back to id 5, got: ' . var_export($indicationId, true) . PHP_EOL);
    exit(1);
}

echo "Catheter indication selection checks passed." . PHP_EOL;
