<?php
/**
 * Application Constants
 */

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_ATTENDING', 'attending');
define('ROLE_RESIDENT', 'resident');
define('ROLE_NURSE', 'nurse');

// User Status
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_SUSPENDED', 'suspended');

// Patient Status
define('PATIENT_ADMITTED', 'admitted');
define('PATIENT_ACTIVE_CATHETER', 'active_catheter');
define('PATIENT_DISCHARGED', 'discharged');
define('PATIENT_TRANSFERRED', 'transferred');

// Catheter Status
define('CATHETER_ACTIVE', 'active');
define('CATHETER_REMOVED', 'removed');
define('CATHETER_DISPLACED', 'displaced');
define('CATHETER_INFECTED', 'infected');

// Alert Severity
define('ALERT_INFO', 'info');
define('ALERT_WARNING', 'warning');
define('ALERT_CRITICAL', 'critical');

// Flash Message Types
define('FLASH_SUCCESS', 'success');
define('FLASH_ERROR', 'danger');
define('FLASH_WARNING', 'warning');
define('FLASH_INFO', 'info');

// Date Formats
define('DATE_FORMAT_DB', 'Y-m-d');
define('DATE_FORMAT_DISPLAY', 'd-m-Y');
define('DATETIME_FORMAT_DB', 'Y-m-d H:i:s');
define('DATETIME_FORMAT_DISPLAY', 'd-m-Y h:i A');
