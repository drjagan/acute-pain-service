<?php
/**
 * Regression test for My Patients active catheter action links.
 *
 * The active catheter action must never render /catheters/viewCatheter/#.
 */

require_once __DIR__ . '/../config/config.php';

\Helpers\Session::start();
\Helpers\Session::set('role', 'attending');

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

echo "My Patients active catheter link checks passed." . PHP_EOL;
