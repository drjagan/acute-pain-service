<?php
/**
 * Regression test for catheter removal day counting.
 *
 * Same-day insertion/removal counts as one catheter day. The server also clamps
 * stale client-submitted zero values before storage.
 */

require_once __DIR__ . '/../config/config.php';

$controllerClass = new ReflectionClass(\Controllers\CatheterController::class);
$controller = $controllerClass->newInstanceWithoutConstructor();

$prepare = $controllerClass->getMethod('prepareRemovalData');
if (PHP_VERSION_ID < 80100) {
    $prepare->setAccessible(true);
}

$prepared = $prepare->invoke($controller, [
    'indication' => 'adequate_analgesia',
    'indication_notes' => '',
    'date_of_removal' => '2026-08-18',
    'number_of_catheter_days' => '0',
    'removal_complications' => '',
    'final_notes' => '',
    'patient_satisfaction' => '',
]);

if (($prepared['number_of_catheter_days'] ?? null) !== 1) {
    fwrite(STDERR, 'Expected stale zero catheter days to be clamped to 1, got: ' . var_export($prepared['number_of_catheter_days'] ?? null, true) . PHP_EOL);
    exit(1);
}

if (!$controllerClass->hasMethod('calculateCatheterDays')) {
    fwrite(STDERR, 'Expected CatheterController::calculateCatheterDays() to exist.' . PHP_EOL);
    exit(1);
}

$calculate = $controllerClass->getMethod('calculateCatheterDays');
if (PHP_VERSION_ID < 80100) {
    $calculate->setAccessible(true);
}

$sameDay = $calculate->invoke($controller, '2026-08-18', '2026-08-18');
$nextDay = $calculate->invoke($controller, '2026-08-18', '2026-08-19');
$twoDays = $calculate->invoke($controller, '2026-08-18', '2026-08-20');
$beforeInsertion = $calculate->invoke($controller, '2026-08-18', '2026-08-17');

if ($sameDay !== 1) {
    fwrite(STDERR, 'Expected same-day removal to count as 1 catheter day, got: ' . var_export($sameDay, true) . PHP_EOL);
    exit(1);
}

if ($nextDay !== 1 || $twoDays !== 2) {
    fwrite(STDERR, 'Expected elapsed-day counts of 1 and 2, got: ' . var_export([$nextDay, $twoDays], true) . PHP_EOL);
    exit(1);
}

if ($beforeInsertion !== null) {
    fwrite(STDERR, 'Expected removal before insertion to be invalid, got: ' . var_export($beforeInsertion, true) . PHP_EOL);
    exit(1);
}

$validate = $controllerClass->getMethod('validateRemovalData');
if (PHP_VERSION_ID < 80100) {
    $validate->setAccessible(true);
}

$validPayload = [
    'indication' => 'adequate_analgesia',
    'date_of_removal' => '2026-08-18',
    'number_of_catheter_days' => '1',
];
$valid = $validate->invoke($controller, $validPayload, ['date_of_insertion' => '2026-08-18']);

if (!$valid['valid']) {
    fwrite(STDERR, 'Expected normalized same-day removal data to validate, got: ' . $valid['message'] . PHP_EOL);
    exit(1);
}

$zeroPayload = $validPayload;
$zeroPayload['number_of_catheter_days'] = '0';
$zero = $validate->invoke($controller, $zeroPayload, ['date_of_insertion' => '2026-08-18']);

if ($zero['valid'] || str_contains($zero['message'] ?? '', 'required')) {
    fwrite(STDERR, 'Expected raw zero catheter days to be a range error, got: ' . var_export($zero, true) . PHP_EOL);
    exit(1);
}

echo "Catheter removal day checks passed." . PHP_EOL;
