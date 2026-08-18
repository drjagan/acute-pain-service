<?php
/**
 * Explicit application route map.
 *
 * Each route declares the HTTP method, URL path, controller action, and
 * front-door policy enforced before the controller is constructed.
 */

/**
 * Build a route definition.
 */
$route = static function (string $method, string $path, string $handler, array $policy = []): array
{
    [$controller, $action] = explode('@', $handler, 2);

    return [
        'method' => $method,
        'path' => $path,
        'controller' => $controller,
        'action' => $action,
        'policy' => $policy,
    ];
};

/**
 * Add CSRF protection to an existing route policy.
 */
$withCsrf = static function (array $policy = []): array
{
    return $policy + ['csrf' => true];
};

$auth = ['auth' => true];
$admin = ['roles' => [ROLE_ADMIN]];
$attendingOnly = ['roles' => [ROLE_ATTENDING]];
$patientWriters = ['roles' => [ROLE_ATTENDING, ROLE_RESIDENT, ROLE_ADMIN]];
$clinicalWriters = ['roles' => [ROLE_ATTENDING, ROLE_RESIDENT, ROLE_NURSE, ROLE_ADMIN]];

return [
    $route('GET', '/auth/login', 'AuthController@login', ['guest' => true]),
    $route('POST', '/auth/login', 'AuthController@authenticate', ['csrf' => true]),
    $route('GET', '/auth/logout', 'AuthController@logout', $auth),
    $route('GET', '/auth/forgot-password', 'AuthController@forgotPassword'),
    $route('POST', '/auth/forgot-password', 'AuthController@sendResetLink', ['csrf' => true]),
    $route('GET', '/auth/reset-password', 'AuthController@resetPassword'),
    $route('POST', '/auth/reset-password', 'AuthController@updatePassword', ['csrf' => true]),

    $route('GET', '/dashboard', 'DashboardController@index', $auth),

    $route('GET', '/patients', 'PatientController@index', $clinicalWriters),
    $route('GET', '/patients/myPatients', 'PatientController@myPatients', $patientWriters),
    $route('GET', '/patients/create', 'PatientController@create', $patientWriters),
    $route('POST', '/patients/store', 'PatientController@store', $withCsrf($patientWriters)),
    $route('GET', '/patients/viewPatient/{id}', 'PatientController@viewPatient', $auth),
    $route('GET', '/patients/edit/{id}', 'PatientController@edit', $patientWriters),
    $route('POST', '/patients/update/{id}', 'PatientController@update', $withCsrf($patientWriters)),
    $route('POST', '/patients/delete/{id}', 'PatientController@delete', $withCsrf($attendingOnly)),
    $route(
        'POST',
        '/patients/check-hospital-number',
        'PatientController@checkHospitalNumber',
        $withCsrf($auth)
    ),
    $route('GET', '/patients/searchAjax', 'PatientController@searchAjax', $auth),

    $route('GET', '/catheters', 'CatheterController@index', $auth),
    $route('GET', '/catheters/create', 'CatheterController@create', $patientWriters),
    $route('POST', '/catheters/store', 'CatheterController@store', $withCsrf($patientWriters)),
    $route('GET', '/catheters/viewCatheter/{id}', 'CatheterController@viewCatheter', $auth),
    $route('GET', '/catheters/edit/{id}', 'CatheterController@edit', $patientWriters),
    $route('POST', '/catheters/update/{id}', 'CatheterController@update', $withCsrf($patientWriters)),
    $route(
        'POST',
        '/catheters/updateStatus/{id}',
        'CatheterController@updateStatus',
        $withCsrf($patientWriters)
    ),
    $route('GET', '/catheters/remove/{id}', 'CatheterController@remove', $patientWriters),
    $route(
        'POST',
        '/catheters/storeRemoval/{id}',
        'CatheterController@storeRemoval',
        $withCsrf($patientWriters)
    ),
    $route('GET', '/catheters/viewRemoval/{id}', 'CatheterController@viewRemoval', $auth),
    $route('POST', '/catheters/delete/{id}', 'CatheterController@delete', $withCsrf($attendingOnly)),

    $route('GET', '/regimes', 'DrugRegimeController@index', $auth),
    $route('GET', '/regimes/create', 'DrugRegimeController@create', $clinicalWriters),
    $route('POST', '/regimes/store', 'DrugRegimeController@store', $withCsrf($clinicalWriters)),
    $route('GET', '/regimes/viewRegime/{id}', 'DrugRegimeController@viewRegime', $auth),
    $route('GET', '/regimes/edit/{id}', 'DrugRegimeController@edit', $clinicalWriters),
    $route('POST', '/regimes/update/{id}', 'DrugRegimeController@update', $withCsrf($clinicalWriters)),
    $route('POST', '/regimes/delete/{id}', 'DrugRegimeController@delete', $withCsrf($attendingOnly)),

    $route('GET', '/outcomes', 'FunctionalOutcomeController@index', $auth),
    $route('GET', '/outcomes/create', 'FunctionalOutcomeController@create', $clinicalWriters),
    $route('POST', '/outcomes/store', 'FunctionalOutcomeController@store', $withCsrf($clinicalWriters)),
    $route('GET', '/outcomes/viewOutcome/{id}', 'FunctionalOutcomeController@viewOutcome', $auth),
    $route('GET', '/outcomes/edit/{id}', 'FunctionalOutcomeController@edit', $clinicalWriters),
    $route('POST', '/outcomes/update/{id}', 'FunctionalOutcomeController@update', $withCsrf($clinicalWriters)),
    $route('POST', '/outcomes/delete/{id}', 'FunctionalOutcomeController@delete', $withCsrf($attendingOnly)),

    $route('GET', '/reports', 'ReportController@index', $auth),
    $route('GET', '/reports/individual', 'ReportController@individual', $auth),
    $route('GET', '/reports/individual/{patientId}', 'ReportController@individual', $auth),
    $route('GET', '/reports/consolidated', 'ReportController@consolidated', $auth),
    $route('GET', '/reports/generateConsolidated', 'ReportController@generateConsolidated', $auth),

    $route('GET', '/notifications/getUnread', 'NotificationController@getUnread', $auth),
    $route('GET', '/notifications', 'NotificationController@index', $auth),
    $route('POST', '/notifications/markAsRead/{id}', 'NotificationController@markAsRead', $withCsrf($auth)),
    $route('POST', '/notifications/markAllAsRead', 'NotificationController@markAllAsRead', $withCsrf($auth)),
    $route('POST', '/notifications/delete/{id}', 'NotificationController@delete', $withCsrf($auth)),
    $route('GET', '/notifications/getUnreadCount', 'NotificationController@getUnreadCount', $auth),

    $route('GET', '/users', 'UserController@index', $admin),
    $route('GET', '/users/create', 'UserController@create', $admin),
    $route('POST', '/users/store', 'UserController@store', $withCsrf($admin)),
    $route('GET', '/users/edit/{id}', 'UserController@edit', $admin),
    $route('POST', '/users/update/{id}', 'UserController@update', $withCsrf($admin)),
    $route('POST', '/users/delete/{id}', 'UserController@delete', $withCsrf($admin)),
    $route('POST', '/users/toggleStatus/{id}', 'UserController@toggleStatus', $withCsrf($admin)),

    $route('GET', '/settings', 'SettingsController@index', $admin),
    $route('GET', '/settings/smtp', 'SettingsController@smtp', $admin),
    $route('POST', '/settings/saveSMTP', 'SettingsController@saveSMTP', $withCsrf($admin)),
    $route('POST', '/settings/testSMTP', 'SettingsController@testSMTP', $withCsrf($admin)),
    $route('POST', '/settings/toggleSMTP', 'SettingsController@toggleSMTP', $withCsrf($admin)),

    $route('GET', '/masterdata', 'MasterDataController@index', $admin),
    $route('GET', '/masterdata/index', 'MasterDataController@index', $admin),
    $route('GET', '/masterdata/list/{type}', 'MasterDataController@list', $admin),
    $route('GET', '/masterdata/create/{type}', 'MasterDataController@create', $admin),
    $route('POST', '/masterdata/store/{type}', 'MasterDataController@store', $withCsrf($admin)),
    $route('GET', '/masterdata/edit/{type}/{id}', 'MasterDataController@edit', $admin),
    $route('POST', '/masterdata/update/{type}/{id}', 'MasterDataController@update', $withCsrf($admin)),
    $route('POST', '/masterdata/delete/{type}/{id}', 'MasterDataController@delete', $withCsrf($admin)),
    $route(
        'POST',
        '/masterdata/toggleActive/{type}/{id}',
        'MasterDataController@toggleActive',
        $withCsrf($admin)
    ),
    $route('POST', '/masterdata/reorder/{type}', 'MasterDataController@reorder', $withCsrf($admin)),
    $route('GET', '/masterdata/export/{type}', 'MasterDataController@export', $admin),
    $route(
        'GET',
        '/masterdata/getSurgeriesBySpecialty/{specialtyId}',
        'MasterDataController@getSurgeriesBySpecialty',
        $admin
    ),
    $route('POST', '/masterdata/quickAdd/{type}', 'MasterDataController@quickAdd', $withCsrf($admin)),
    $route(
        'GET',
        '/masterdata/manageChildren/{parentType}/{parentId}',
        'MasterDataController@manageChildren',
        $admin
    ),
    $route(
        'POST',
        '/masterdata/storeChild/{parentType}/{parentId}',
        'MasterDataController@storeChild',
        $withCsrf($admin)
    ),
    $route(
        'POST',
        '/masterdata/updateChild/{childType}/{childId}',
        'MasterDataController@updateChild',
        $withCsrf($admin)
    ),
    $route(
        'POST',
        '/masterdata/deleteChild/{childType}/{childId}',
        'MasterDataController@deleteChild',
        $withCsrf($admin)
    ),
    $route(
        'POST',
        '/masterdata/toggleChildActive/{childType}/{childId}',
        'MasterDataController@toggleChildActive',
        $withCsrf($admin)
    ),
    $route(
        'POST',
        '/masterdata/reorderChildren/{childType}',
        'MasterDataController@reorderChildren',
        $withCsrf($admin)
    ),
];
