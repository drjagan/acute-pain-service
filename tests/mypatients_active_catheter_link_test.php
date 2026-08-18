<?php
/**
 * Regression test for My Patients active catheter action links.
 *
 * The active catheter action must never render /catheters/viewCatheter/#.
 */

require_once __DIR__ . '/../config/config.php';

\Helpers\Session::start();
\Helpers\Session::set('user_id', 1);
\Helpers\Session::set('username', 'testdoctor');
\Helpers\Session::set('email', 'testdoctor@example.test');
\Helpers\Session::set('role', 'attending');
\Helpers\Session::set('first_name', 'Test');
\Helpers\Session::set('last_name', 'Doctor');

$user = [
    'first_name' => 'Test',
    'last_name' => 'Doctor',
];
$total = 2;
$patients = [
    [
        'patient_id' => 101,
        'patient_name' => 'Patient With Catheter ID',
        'hospital_number' => 'HN101',
        'age' => 55,
        'gender' => 'female',
        'speciality' => 'general_surgery',
        'active_catheters' => 1,
        'latest_catheter' => [
            'id' => 77,
            'catheter_type' => 'lumbar',
            'date_of_insertion' => '2026-08-18',
        ],
        'assigned_at' => '2026-08-18 09:00:00',
        'physician_type' => 'attending',
        'is_primary' => 1,
    ],
    [
        'patient_id' => 102,
        'patient_name' => 'Patient Missing Catheter ID',
        'hospital_number' => 'HN102',
        'age' => 60,
        'gender' => 'male',
        'speciality' => 'orthopedics',
        'active_catheters' => 1,
        'latest_catheter' => [
            'catheter_type' => 'upper_thoracic',
            'date_of_insertion' => '2026-08-18',
        ],
        'assigned_at' => '2026-08-18 10:00:00',
        'physician_type' => 'resident',
        'is_primary' => 0,
    ],
];

ob_start();
include VIEWS_PATH . '/mypatients/index.php';
$html = ob_get_clean();

if (!str_contains($html, '/catheters/viewCatheter/77')) {
    fwrite(STDERR, 'Expected My Patients view to link active catheter id 77.' . PHP_EOL);
    exit(1);
}

if (str_contains($html, '/catheters/viewCatheter/#')) {
    fwrite(STDERR, 'My Patients view rendered a broken active catheter # link.' . PHP_EOL);
    exit(1);
}

final class MyPatientsLatestCatheterFakePatientModel
{
    public function getPatientsByPhysician($userId, $limit = null): array
    {
        return [
            [
                'patient_id' => 201,
                'patient_name' => 'Patient With Multiple Catheters',
                'hospital_number' => 'HN201',
                'age' => 48,
                'gender' => 'female',
                'speciality' => 'general_surgery',
                'assigned_at' => '2026-08-18 11:00:00',
                'physician_type' => 'attending',
                'is_primary' => 1,
            ],
        ];
    }
}

final class MyPatientsLatestCatheterFakeStatement
{
    private array $rows = [];
    private $resolver;

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

final class MyPatientsLatestCatheterFakeDatabase
{
    public function prepare(string $sql): MyPatientsLatestCatheterFakeStatement
    {
        return new MyPatientsLatestCatheterFakeStatement(function (array $params) use ($sql): array {
            if (str_contains($sql, 'COUNT(*) as active_count')) {
                return [['active_count' => 2]];
            }

            if (str_contains($sql, 'FROM catheters')) {
                if (str_contains($sql, 'ORDER BY created_at DESC, id DESC')) {
                    return [[
                        'id' => 99,
                        'catheter_type' => 'upper_thoracic',
                        'date_of_insertion' => '2026-08-19',
                        'status' => 'active',
                    ]];
                }

                return [[
                    'id' => 77,
                    'catheter_type' => 'lumbar',
                    'date_of_insertion' => '2026-08-18',
                    'status' => 'active',
                ]];
            }

            return [];
        });
    }
}

$controllerClass = new ReflectionClass(\Controllers\PatientController::class);
$controller = $controllerClass->newInstanceWithoutConstructor();

$baseClass = new ReflectionClass(\Controllers\BaseController::class);
$dbProperty = $baseClass->getProperty('db');
if (PHP_VERSION_ID < 80100) {
    $dbProperty->setAccessible(true);
}
$dbProperty->setValue($controller, new MyPatientsLatestCatheterFakeDatabase());

$patientProperty = $controllerClass->getProperty('patientModel');
if (PHP_VERSION_ID < 80100) {
    $patientProperty->setAccessible(true);
}
$patientProperty->setValue($controller, new MyPatientsLatestCatheterFakePatientModel());

$_SERVER['REQUEST_URI'] = '/patients/myPatients';

ob_start();
$controller->myPatients();
$controllerHtml = ob_get_clean();

if (!str_contains($controllerHtml, '/catheters/viewCatheter/99')) {
    fwrite(STDERR, 'Expected My Patients controller to link the last added active catheter.' . PHP_EOL);
    exit(1);
}

echo "My Patients active catheter link checks passed." . PHP_EOL;
