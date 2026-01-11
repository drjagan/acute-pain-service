<?php
/**
 * Test Script for v1.1.0 Features
 * Tests patient-physician associations and notifications
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Models/Patient.php';
require_once __DIR__ . '/src/Models/PatientPhysician.php';
require_once __DIR__ . '/src/Models/Notification.php';
require_once __DIR__ . '/src/Models/User.php';

echo "\n";
echo "========================================\n";
echo "  APS v1.1.0 - Feature Test Suite\n";
echo "========================================\n";
echo "\n";

// Connect to database
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "✓ Connected to database\n\n";
} catch (PDOException $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// Initialize models
$patientModel = new Models\Patient();
$patientPhysicianModel = new Models\PatientPhysician();
$notificationModel = new Models\Notification();
$userModel = new Models\User();

echo "TEST 1: Patient-Physician Associations\n";
echo "========================================\n";

// Get existing users
$users = $db->query("SELECT id, username, first_name, last_name, role FROM users WHERE role IN ('attending', 'resident') AND deleted_at IS NULL ORDER BY role, id LIMIT 5")->fetchAll();

if (count($users) < 2) {
    echo "⚠ Not enough physicians found. Need at least 2 users with attending/resident roles.\n";
    echo "  Found: " . count($users) . " physicians\n\n";
} else {
    echo "✓ Found " . count($users) . " physicians:\n";
    foreach ($users as $user) {
        echo "  - {$user['first_name']} {$user['last_name']} ({$user['role']}, ID: {$user['id']})\n";
    }
    echo "\n";
}

// Get existing patients
$patients = $db->query("SELECT id, patient_name, hospital_number FROM patients WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 3")->fetchAll();

if (count($patients) === 0) {
    echo "⚠ No patients found in database. Please create a patient first.\n\n";
} else {
    echo "✓ Found " . count($patients) . " patients:\n";
    foreach ($patients as $patient) {
        echo "  - {$patient['patient_name']} (HN: {$patient['hospital_number']}, ID: {$patient['id']})\n";
    }
    echo "\n";
    
    // Test assigning physicians to first patient
    if (count($users) >= 2 && count($patients) >= 1) {
        $testPatient = $patients[0];
        $attendingIds = [];
        $residentIds = [];
        
        // Separate by role
        foreach ($users as $user) {
            if ($user['role'] === 'attending' && count($attendingIds) < 2) {
                $attendingIds[] = $user['id'];
            } elseif ($user['role'] === 'resident' && count($residentIds) < 2) {
                $residentIds[] = $user['id'];
            }
        }
        
        if (!empty($attendingIds) || !empty($residentIds)) {
            echo "TEST: Assigning physicians to patient '{$testPatient['patient_name']}'...\n";
            echo "  Attendings: [" . implode(', ', $attendingIds) . "]\n";
            echo "  Residents: [" . implode(', ', $residentIds) . "]\n";
            
            try {
                $result = $patientPhysicianModel->syncPhysicians(
                    $testPatient['id'],
                    $attendingIds,
                    $residentIds,
                    1 // Admin user
                );
                
                if ($result) {
                    echo "  ✓ Physicians assigned successfully\n";
                    
                    // Verify assignment
                    $assignedPhysicians = $patientPhysicianModel->getPhysiciansByPatient($testPatient['id']);
                    echo "  ✓ Verified: " . count($assignedPhysicians) . " physicians assigned\n";
                    
                    foreach ($assignedPhysicians as $physician) {
                        $primary = $physician['is_primary'] ? ' (PRIMARY)' : '';
                        echo "    - {$physician['physician_name']} ({$physician['physician_type']}){$primary}\n";
                    }
                } else {
                    echo "  ✗ Failed to assign physicians\n";
                }
            } catch (Exception $e) {
                echo "  ✗ Error: " . $e->getMessage() . "\n";
            }
            echo "\n";
        }
    }
}

echo "TEST 2: Notification System\n";
echo "========================================\n";

// Test creating a notification
if (count($users) >= 1) {
    $testUser = $users[0];
    
    echo "TEST: Creating test notification for {$testUser['first_name']} {$testUser['last_name']}...\n";
    
    try {
        $notificationId = $notificationModel->notify(
            $testUser['id'],
            'system',
            'Test Notification - v1.1.0',
            'This is a test notification created by the test script. The notification system is working correctly!',
            [
                'priority' => 'medium',
                'color' => 'info',
                'icon' => 'bi-info-circle',
                'action_url' => '/dashboard',
                'action_text' => 'Go to Dashboard',
                'send_email' => false,
                'created_by' => 1
            ]
        );
        
        if ($notificationId) {
            echo "  ✓ Notification created (ID: {$notificationId})\n";
            
            // Get unread count
            $unreadCount = $notificationModel->getUnreadCount($testUser['id']);
            echo "  ✓ Unread notifications for user: {$unreadCount}\n";
            
            // Get notifications
            $notifications = $notificationModel->getUnreadByUser($testUser['id'], 5);
            echo "  ✓ Retrieved " . count($notifications) . " notifications\n";
            
            if (count($notifications) > 0) {
                echo "\n  Recent notifications:\n";
                foreach ($notifications as $notif) {
                    $time = date('H:i:s', strtotime($notif['created_at']));
                    echo "    [{$notif['priority']}] {$notif['title']} (at {$time})\n";
                }
            }
        } else {
            echo "  ✗ Failed to create notification\n";
        }
    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "TEST 3: Get 'My Patients' for Physicians\n";
echo "========================================\n";

if (count($users) >= 1 && count($patients) >= 1) {
    $testUser = $users[0];
    
    echo "TEST: Getting patients for {$testUser['first_name']} {$testUser['last_name']}...\n";
    
    try {
        $myPatients = $patientModel->getPatientsByPhysician($testUser['id'], 5);
        
        if (count($myPatients) > 0) {
            echo "  ✓ Found " . count($myPatients) . " patients assigned to this physician\n";
            
            foreach ($myPatients as $patient) {
                echo "    - {$patient['patient_name']} (HN: {$patient['hospital_number']})\n";
                echo "      Assigned: " . date('Y-m-d', strtotime($patient['assigned_at'])) . "\n";
            }
        } else {
            echo "  ⚠ No patients assigned to this physician yet\n";
            echo "    This is normal if you just ran migrations.\n";
            echo "    Assign physicians to patients via the patient edit form.\n";
        }
    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "TEST 4: SMTP Settings\n";
echo "========================================\n";

try {
    $stmt = $db->query("SELECT id, smtp_host, smtp_port, from_email, is_active FROM smtp_settings LIMIT 1");
    $smtpSettings = $stmt->fetch();
    
    if ($smtpSettings) {
        echo "✓ SMTP settings record exists (ID: {$smtpSettings['id']})\n";
        echo "  Host: {$smtpSettings['smtp_host']}\n";
        echo "  Port: {$smtpSettings['smtp_port']}\n";
        echo "  From: {$smtpSettings['from_email']}\n";
        echo "  Active: " . ($smtpSettings['is_active'] ? 'Yes' : 'No') . "\n";
        echo "\n";
        echo "  Note: Configure SMTP settings at: http://localhost:8000/settings/smtp (admin only)\n";
    } else {
        echo "⚠ No SMTP settings found (this is normal after migration)\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "========================================\n";
echo "  Test Summary\n";
echo "========================================\n";
echo "\n";

// Get statistics
$stats = [
    'patients' => $db->query("SELECT COUNT(*) as count FROM patients WHERE deleted_at IS NULL")->fetch()['count'],
    'users' => $db->query("SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL")->fetch()['count'],
    'patient_physicians' => $db->query("SELECT COUNT(*) as count FROM patient_physicians")->fetch()['count'],
    'notifications' => $db->query("SELECT COUNT(*) as count FROM notifications")->fetch()['count'],
    'unread_notifications' => $db->query("SELECT COUNT(*) as count FROM notifications WHERE is_read = 0")->fetch()['count']
];

echo "Database Statistics:\n";
echo "  Patients: {$stats['patients']}\n";
echo "  Users: {$stats['users']}\n";
echo "  Patient-Physician Associations: {$stats['patient_physicians']}\n";
echo "  Total Notifications: {$stats['notifications']}\n";
echo "  Unread Notifications: {$stats['unread_notifications']}\n";
echo "\n";

echo "========================================\n";
echo "  Manual Testing Checklist\n";
echo "========================================\n";
echo "\n";
echo "1. Open the application: http://localhost:8000/\n";
echo "2. Login with credentials:\n";
echo "   - Admin: admin / admin123\n";
echo "   - Attending: dr.sharma / admin123\n";
echo "   - Resident: dr.patel / admin123\n";
echo "\n";
echo "3. Check notification bell icon in header\n";
echo "   - Should show badge if unread notifications exist\n";
echo "   - Click to see dropdown with notifications\n";
echo "\n";
echo "4. Create/Edit a patient:\n";
echo "   - Look for 'Attending Physicians' and 'Residents' dropdowns\n";
echo "   - Select multiple physicians using Select2\n";
echo "   - Save and verify notifications are sent\n";
echo "\n";
echo "5. Check 'My Patients' widget on dashboard:\n";
echo "   - Login as attending or resident\n";
echo "   - Dashboard should show assigned patients widget\n";
echo "\n";
echo "6. Insert a catheter:\n";
echo "   - Create catheter for a patient with assigned physicians\n";
echo "   - Check that physicians receive notifications\n";
echo "\n";
echo "7. Configure SMTP (admin only):\n";
echo "   - Go to /settings/smtp\n";
echo "   - Configure email settings\n";
echo "   - Test email functionality\n";
echo "\n";
echo "========================================\n";
echo "  All Tests Complete!\n";
echo "========================================\n";
echo "\n";
