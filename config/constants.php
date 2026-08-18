<?php
/**
 * Application Constants
 */

// User Roles
defined('ROLE_ADMIN') || define('ROLE_ADMIN', 'admin');
defined('ROLE_ATTENDING') || define('ROLE_ATTENDING', 'attending');
defined('ROLE_RESIDENT') || define('ROLE_RESIDENT', 'resident');
defined('ROLE_NURSE') || define('ROLE_NURSE', 'nurse');

// User Status
defined('STATUS_ACTIVE') || define('STATUS_ACTIVE', 'active');
defined('STATUS_INACTIVE') || define('STATUS_INACTIVE', 'inactive');
defined('STATUS_SUSPENDED') || define('STATUS_SUSPENDED', 'suspended');

// Patient Status
defined('PATIENT_ADMITTED') || define('PATIENT_ADMITTED', 'admitted');
defined('PATIENT_ACTIVE_CATHETER') || define('PATIENT_ACTIVE_CATHETER', 'active_catheter');
defined('PATIENT_DISCHARGED') || define('PATIENT_DISCHARGED', 'discharged');
defined('PATIENT_TRANSFERRED') || define('PATIENT_TRANSFERRED', 'transferred');

// Catheter Status
defined('CATHETER_ACTIVE') || define('CATHETER_ACTIVE', 'active');
defined('CATHETER_REMOVED') || define('CATHETER_REMOVED', 'removed');
defined('CATHETER_DISPLACED') || define('CATHETER_DISPLACED', 'displaced');
defined('CATHETER_INFECTED') || define('CATHETER_INFECTED', 'infected');

// Alert Severity
defined('ALERT_INFO') || define('ALERT_INFO', 'info');
defined('ALERT_WARNING') || define('ALERT_WARNING', 'warning');
defined('ALERT_CRITICAL') || define('ALERT_CRITICAL', 'critical');

// Flash Message Types
defined('FLASH_SUCCESS') || define('FLASH_SUCCESS', 'success');
defined('FLASH_ERROR') || define('FLASH_ERROR', 'danger');
defined('FLASH_WARNING') || define('FLASH_WARNING', 'warning');
defined('FLASH_INFO') || define('FLASH_INFO', 'info');

// Security Settings
defined('PASSWORD_COST') || define('PASSWORD_COST', 12); // Bcrypt cost factor (4-31, default 10, recommended 12)
defined('PASSWORD_MIN_LENGTH') || define('PASSWORD_MIN_LENGTH', 8); // Minimum password length

// Date Formats
defined('DATE_FORMAT_DB') || define('DATE_FORMAT_DB', 'Y-m-d');
defined('DATE_FORMAT_DISPLAY') || define('DATE_FORMAT_DISPLAY', 'd-m-Y');
defined('DATETIME_FORMAT_DB') || define('DATETIME_FORMAT_DB', 'Y-m-d H:i:s');
defined('DATETIME_FORMAT_DISPLAY') || define('DATETIME_FORMAT_DISPLAY', 'd-m-Y h:i A');
