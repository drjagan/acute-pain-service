<?php
namespace Controllers;

use Models\Patient;
use Helpers\Flash;
use Helpers\CSRF;
use Helpers\Sanitizer;

/**
 * Patient Controller (Screen 1 - Patient Registration)
 * Handles CRUD operations for patient management
 */
class PatientController extends BaseController {
    
    private $patientModel;
    
    public function __construct() {
        parent::__construct();
        $this->patientModel = new Patient();
    }
    
    /**
     * List all patients
     */
    public function index() {
        $this->requireRole(['attending', 'resident', 'nurse', 'admin']);
        
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        
        if ($search) {
            $patients = $this->patientModel->search($search);
            $total = count($patients);
        } else {
            $patients = $this->patientModel->paginate($page, PER_PAGE);
            $total = $this->patientModel->getTotalCount();
        }
        
        $this->view('patients.list', [
            'patients' => $patients,
            'total' => $total,
            'page' => $page,
            'perPage' => PER_PAGE,
            'search' => $search
        ]);
    }
    
    /**
     * My Patients - Show patients assigned to logged-in physician (v1.1)
     * Note: Admins are also attending physicians with extra privileges
     */
    public function myPatients() {
        $this->requireRole(['attending', 'resident', 'admin']);
        
        $user = $this->user();
        
        // Get all patients assigned to this physician
        $myPatients = $this->patientModel->getPatientsByPhysician($user['id'], null); // No limit
        
        // Enrich with catheter status
        foreach ($myPatients as &$patient) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as active_count
                FROM catheters 
                WHERE patient_id = ? 
                AND status = 'active' 
                AND deleted_at IS NULL
            ");
            $stmt->execute([$patient['patient_id']]);
            $catheterData = $stmt->fetch();
            $patient['active_catheters'] = $catheterData['active_count'];
            
            // Get latest catheter info if exists
            $stmt = $this->db->prepare("
                SELECT catheter_type, date_of_insertion, status
                FROM catheters 
                WHERE patient_id = ? 
                AND deleted_at IS NULL
                ORDER BY created_at DESC
                LIMIT 1
            ");
            $stmt->execute([$patient['patient_id']]);
            $patient['latest_catheter'] = $stmt->fetch();
        }
        
        $this->view('mypatients.index', [
            'patients' => $myPatients,
            'user' => $user,
            'total' => count($myPatients)
        ]);
    }
    
    /**
     * Show create patient form (Screen 1)
     */
    public function create() {
        // Only attending, resident, and admin can create patients
        $this->requireRole(['attending', 'resident', 'admin']);
        
        // Load lookup data
        $comorbidities = $this->getLookupData('lookup_comorbidities');
        $physicians = $this->getPhysiciansForForm();
        
        // Load specialties and surgeries with relationships
        $specialtyModel = new \Models\LookupSpecialty();
        $surgeryModel = new \Models\LookupSurgery();
        
        // Format specialties as key-value array for dropdown
        $specialtiesRaw = $specialtyModel->getActive();
        $specialities = []; // Note: keeping 'ies' spelling to match view
        foreach ($specialtiesRaw as $specialty) {
            $specialities[$specialty['id']] = $specialty['name'];
        }
        
        // Get all active surgeries with specialty info
        $surgeries = $surgeryModel->getActiveWithSpecialty();
        
        $this->view('patients.create', [
            'comorbidities' => $comorbidities,
            'specialities' => $specialities,  // Key-value array
            'surgeries' => $surgeries,        // Array of objects with specialty_name
            'attendings' => $physicians['attendings'],
            'residents' => $physicians['residents']
        ]);
    }
    
    /**
     * Store new patient
     */
    public function store() {
        $this->requireRole(['attending', 'resident', 'admin']);
        $this->validateCSRF();
        
        // Validate input
        $validation = $this->validatePatientData($_POST);
        if (!$validation['valid']) {
            Flash::error($validation['message']);
            return $this->redirect('/patients/create');
        }
        
        // Prepare data
        $data = $this->preparePatientData($_POST);
        $data['created_by'] = $this->user()['id'];
        $data['status'] = 'admitted';
        $data['admission_date'] = date('Y-m-d');
        
        try {
            $patientId = $this->patientModel->create($data);
            
            // Sync physician associations (v1.1 feature)
            $attendingIds = $_POST['attending_physicians'] ?? [];
            $residentIds = $_POST['residents'] ?? [];
            if (!empty($attendingIds) || !empty($residentIds)) {
                $this->patientModel->syncPhysicians($patientId, $attendingIds, $residentIds, $this->user()['id']);
            }
            
            // Create notification for assigned physicians (v1.1 feature)
            $this->notifyPhysiciansAboutNewPatient($patientId, $attendingIds, $residentIds, $data['patient_name']);
            
            Flash::success('Patient registered successfully');
            return $this->redirect('/patients/viewPatient/' . $patientId);
            
        } catch (\Exception $e) {
            Flash::error('Failed to register patient: ' . $e->getMessage());
            return $this->redirect('/patients/create');
        }
    }
    
    /**
     * View patient details
     */
    public function viewPatient($id) {
        $this->requireAuth();
        
        $patient = $this->patientModel->find($id);
        
        if (!$patient) {
            Flash::error('Patient not found');
            return $this->redirect('/patients');
        }
        
        // Decode JSON fields for display
        $patient['comorbid_illness_decoded'] = json_decode($patient['comorbid_illness'], true) ?: [];
        $patient['surgery_decoded'] = json_decode($patient['surgery'], true) ?: [];
        
        // Get lookup names for display
        $patient['comorbidity_names'] = $this->getLookupNames('lookup_comorbidities', $patient['comorbid_illness_decoded']);
        $patient['surgery_names'] = $this->getLookupNames('lookup_surgeries', $patient['surgery_decoded']);
        
        // Get patient's catheters
        $catheterModel = new \Models\Catheter();
        $catheters = $catheterModel->getPatientCatheters($id);
        
        // Get patient's drug regimes
        $regimeModel = new \Models\DrugRegime();
        $regimes = $regimeModel->getPatientRegimes($id);
        
        // Get patient's functional outcomes
        $outcomeModel = new \Models\FunctionalOutcome();
        $outcomes = $outcomeModel->getPatientOutcomes($id);
        
        // Get patient's catheter removals
        $removalModel = new \Models\CatheterRemoval();
        $removals = $removalModel->getPatientRemovals($id);
        
        // Get active catheters for drug regime dropdown
        $activeCatheters = $catheterModel->getPatientCatheters($id, true); // active only
        
        $this->view('patients.view', [
            'patient' => $patient,
            'specialities' => $this->getSpecialities(),
            'catheters' => $catheters,
            'regimes' => $regimes,
            'outcomes' => $outcomes,
            'removals' => $removals,
            'activeCatheters' => $activeCatheters
        ]);
    }
    
    /**
     * Show edit patient form
     */
    public function edit($id) {
        $this->requireRole(['attending', 'resident', 'admin']);
        
        $patient = $this->patientModel->find($id);
        
        if (!$patient) {
            Flash::error('Patient not found');
            return $this->redirect('/patients');
        }
        
        // Decode JSON fields
        $patient['comorbid_illness'] = json_decode($patient['comorbid_illness'], true) ?: [];
        $patient['surgery'] = json_decode($patient['surgery'], true) ?: [];
        
        // Get patient with physicians (v1.1)
        $patientWithPhysicians = $this->patientModel->getPatientWithPhysicians($id);
        $patient['attending_physicians'] = $patientWithPhysicians['attending_physicians'] ?? [];
        $patient['residents'] = $patientWithPhysicians['residents'] ?? [];
        
        $comorbidities = $this->getLookupData('lookup_comorbidities');
        $physicians = $this->getPhysiciansForForm();
        
        // Load specialties and surgeries with relationships
        $specialtyModel = new \Models\LookupSpecialty();
        $surgeryModel = new \Models\LookupSurgery();
        
        // Format specialties as key-value array for dropdown
        $specialtiesRaw = $specialtyModel->getActive();
        $specialities = []; // Note: keeping 'ies' spelling to match view
        foreach ($specialtiesRaw as $specialty) {
            $specialities[$specialty['id']] = $specialty['name'];
        }
        
        // Get all active surgeries with specialty info
        $surgeries = $surgeryModel->getActiveWithSpecialty();
        
        $this->view('patients.edit', [
            'patient' => $patient,
            'comorbidities' => $comorbidities,
            'specialities' => $specialities,  // Key-value array
            'surgeries' => $surgeries,        // Array of objects with specialty_name
            'attendings' => $physicians['attendings'],
            'residents' => $physicians['residents']
        ]);
    }
    
    /**
     * Update patient
     */
    public function update($id) {
        $this->requireRole(['attending', 'resident', 'admin']);
        $this->validateCSRF();
        
        $patient = $this->patientModel->find($id);
        if (!$patient) {
            Flash::error('Patient not found');
            return $this->redirect('/patients');
        }
        
        // Validate input
        $validation = $this->validatePatientData($_POST, $id);
        if (!$validation['valid']) {
            Flash::error($validation['message']);
            return $this->redirect('/patients/edit/' . $id);
        }
        
        // Prepare data
        $data = $this->preparePatientData($_POST);
        $data['updated_by'] = $this->user()['id'];
        
        try {
            $this->patientModel->update($id, $data);
            
            // Sync physician associations (v1.1 feature)
            $attendingIds = $_POST['attending_physicians'] ?? [];
            $residentIds = $_POST['residents'] ?? [];
            $this->patientModel->syncPhysicians($id, $attendingIds, $residentIds, $this->user()['id']);
            
            Flash::success('Patient updated successfully');
            return $this->redirect('/patients/viewPatient/' . $id);
            
        } catch (\Exception $e) {
            Flash::error('Failed to update patient: ' . $e->getMessage());
            return $this->redirect('/patients/edit/' . $id);
        }
    }
    
    /**
     * Delete patient (soft delete)
     */
    public function delete($id) {
        // Only attending can delete
        $this->requireRole('attending');
        $this->validateCSRF();
        
        $patient = $this->patientModel->find($id);
        if (!$patient) {
            Flash::error('Patient not found');
            return $this->redirect('/patients');
        }
        
        try {
            $this->patientModel->delete($id);
            Flash::success('Patient deleted successfully');
        } catch (\Exception $e) {
            Flash::error('Failed to delete patient: ' . $e->getMessage());
        }
        
        return $this->redirect('/patients');
    }
    
    /**
     * Validate patient data
     */
    private function validatePatientData($data, $excludeId = null) {
        $errors = [];
        
        // Required fields
        $required = ['patient_name', 'hospital_number', 'age', 'gender', 'height', 'weight', 'speciality', 'diagnosis', 'asa_status'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        // Validate age
        if (isset($data['age']) && ($data['age'] < 0 || $data['age'] > 120)) {
            $errors[] = 'Age must be between 0 and 120';
        }
        
        // Validate height
        if (isset($data['height']) && ($data['height'] < 50 || $data['height'] > 250)) {
            $errors[] = 'Height must be between 50 and 250 cm';
        }
        
        // Validate weight
        if (isset($data['weight']) && ($data['weight'] < 20 || $data['weight'] > 300)) {
            $errors[] = 'Weight must be between 20 and 300 kg';
        }
        
        // Validate ASA status
        if (isset($data['asa_status']) && ($data['asa_status'] < 1 || $data['asa_status'] > 5)) {
            $errors[] = 'ASA status must be between 1 and 5';
        }
        
        // Check hospital number uniqueness
        if (!empty($data['hospital_number'])) {
            if (!$this->patientModel->isHospitalNumberUnique($data['hospital_number'], $excludeId)) {
                $errors[] = 'Hospital number already exists';
            }
        }
        
        if (!empty($errors)) {
            return ['valid' => false, 'message' => implode(', ', $errors)];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Prepare patient data for storage
     */
    private function preparePatientData($data) {
        return [
            'patient_name' => Sanitizer::string($data['patient_name']),
            'hospital_number' => Sanitizer::string($data['hospital_number']),
            'age' => (int)$data['age'],
            'gender' => $data['gender'],
            'height' => (float)$data['height'],
            'weight' => (float)$data['weight'],
            'bmi' => Patient::calculateBMI($data['height'], $data['weight']),
            'height_unit' => $data['height_unit'] ?? 'cm',
            'comorbid_illness' => json_encode($data['comorbid_illness'] ?? []),
            'speciality' => $data['speciality'],
            'diagnosis' => Sanitizer::string($data['diagnosis']),
            'surgery' => json_encode($data['surgery'] ?? []),
            'asa_status' => (int)$data['asa_status']
        ];
    }
    
    /**
     * Get lookup data
     */
    private function getLookupData($table) {
        // Try to order by sort_order if it exists, otherwise just by name
        try {
            $stmt = $this->db->query("SELECT * FROM {$table} WHERE active = 1 ORDER BY sort_order, name");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Fallback if sort_order column doesn't exist
            $stmt = $this->db->query("SELECT * FROM {$table} WHERE active = 1 ORDER BY name");
            return $stmt->fetchAll();
        }
    }
    
    /**
     * Get lookup names by IDs
     */
    private function getLookupNames($table, $ids) {
        if (empty($ids)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT name FROM {$table} WHERE id IN ({$placeholders})");
        $stmt->execute($ids);
        
        return array_column($stmt->fetchAll(), 'name');
    }
    
    /**
     * Get specialities list
     */
    private function getSpecialities() {
        return [
            'general_surgery' => 'General Surgery',
            'orthopaedics' => 'Orthopaedics',
            'obg' => 'OBG',
            'urology' => 'Urology',
            'pediatric' => 'Pediatric',
            'plastic' => 'Plastic',
            'oncosurgery' => 'Oncosurgery',
            'cardiothoracic' => 'Cardiothoracic'
        ];
    }
    
    /**
     * AJAX: Check hospital number uniqueness
     */
    public function checkHospitalNumber() {
        header('Content-Type: application/json');
        
        $hospitalNumber = $_POST['hospital_number'] ?? '';
        $excludeId = $_POST['exclude_id'] ?? null;
        
        $isUnique = $this->patientModel->isHospitalNumberUnique($hospitalNumber, $excludeId);
        
        echo json_encode([
            'available' => $isUnique,
            'message' => $isUnique ? 'Hospital number is available' : 'Hospital number already exists'
        ]);
        exit;
    }
    
    /**
     * AJAX endpoint for Select2 patient search
     * Returns latest 5 patients by default, or filtered results based on search term
     */
    public function searchAjax() {
        $this->requireAuth();
        
        header('Content-Type: application/json');
        
        $searchTerm = $_GET['q'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 10; // Results per page
        $offset = ($page - 1) * $limit;
        
        try {
            if (empty($searchTerm)) {
                // Return latest 5 patients (most recently created)
                $stmt = $this->db->prepare("
                    SELECT id, patient_name, hospital_number, age, gender, diagnosis
                    FROM patients
                    WHERE deleted_at IS NULL
                    ORDER BY created_at DESC
                    LIMIT 5
                ");
                $stmt->execute();
            } else {
                // Search by name or hospital number
                $searchParam = '%' . $searchTerm . '%';
                $stmt = $this->db->prepare("
                    SELECT id, patient_name, hospital_number, age, gender, diagnosis
                    FROM patients
                    WHERE deleted_at IS NULL
                    AND (patient_name LIKE ? OR hospital_number LIKE ?)
                    ORDER BY patient_name ASC
                    LIMIT ? OFFSET ?
                ");
                $stmt->bindValue(1, $searchParam, \PDO::PARAM_STR);
                $stmt->bindValue(2, $searchParam, \PDO::PARAM_STR);
                $stmt->bindValue(3, $limit, \PDO::PARAM_INT);
                $stmt->bindValue(4, $offset, \PDO::PARAM_INT);
                $stmt->execute();
            }
            
            $patients = $stmt->fetchAll();
            
            // Format results for Select2
            $results = [];
            foreach ($patients as $patient) {
                $results[] = [
                    'id' => $patient['id'],
                    'text' => $patient['patient_name'] . ' (HN: ' . $patient['hospital_number'] . ') - ' . 
                             $patient['age'] . 'y/' . ucfirst($patient['gender']),
                    'hospital_number' => $patient['hospital_number'],
                    'age' => $patient['age'],
                    'gender' => $patient['gender'],
                    'diagnosis' => $patient['diagnosis'] ?? ''
                ];
            }
            
            // Check if more results exist for pagination
            $hasMore = false;
            if (!empty($searchTerm)) {
                $countStmt = $this->db->prepare("
                    SELECT COUNT(*) as total
                    FROM patients
                    WHERE deleted_at IS NULL
                    AND (patient_name LIKE ? OR hospital_number LIKE ?)
                ");
                $countStmt->execute([$searchParam, $searchParam]);
                $totalResults = $countStmt->fetch()['total'];
                $hasMore = ($offset + $limit) < $totalResults;
            }
            
            echo json_encode([
                'results' => $results,
                'pagination' => [
                    'more' => $hasMore
                ]
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Failed to search patients',
                'message' => $e->getMessage()
            ]);
        }
        
        exit;
    }
    
    /**
     * Get all attending physicians and residents for forms (v1.1)
     * Note: Admins are also attending physicians with extra privileges
     */
    private function getPhysiciansForForm() {
        $stmt = $this->db->prepare("
            SELECT id, username, first_name, last_name, role,
                   CONCAT(first_name, ' ', last_name, ' (', username, ')') as display_name
            FROM users
            WHERE role IN ('attending', 'resident', 'admin')
            AND status = 'active'
            AND deleted_at IS NULL
            ORDER BY role ASC, last_name ASC, first_name ASC
        ");
        $stmt->execute();
        $physicians = $stmt->fetchAll();
        
        $attendings = [];
        $residents = [];
        
        foreach ($physicians as $physician) {
            // Admins are treated as attending physicians
            if ($physician['role'] == 'attending' || $physician['role'] == 'admin') {
                $attendings[] = $physician;
            } else {
                $residents[] = $physician;
            }
        }
        
        return [
            'attendings' => $attendings,
            'residents' => $residents
        ];
    }
    
    /**
     * Send notifications to assigned physicians about new patient (v1.1)
     * Note: Admins can also receive notifications as they are attending physicians
     */
    private function notifyPhysiciansAboutNewPatient($patientId, $attendingIds, $residentIds, $patientName) {
        require_once __DIR__ . '/../Models/Notification.php';
        $notificationModel = new \Models\Notification();
        
        $allPhysicianIds = array_merge($attendingIds, $residentIds);
        
        if (empty($allPhysicianIds)) {
            return;
        }
        
        $currentUser = $this->user();
        $message = "A new patient '{$patientName}' has been registered and assigned to you by {$currentUser['first_name']} {$currentUser['last_name']}.";
        
        $notificationModel->notifyMultiple(
            $allPhysicianIds,
            'patient_created',
            'New Patient Assigned',
            $message,
            [
                'priority' => 'medium',
                'color' => 'success',
                'icon' => 'fa-user-plus',
                'related_type' => 'patients',
                'related_id' => $patientId,
                'action_url' => '/patients/viewPatient/' . $patientId,
                'action_text' => 'View Patient',
                'send_email' => true,
                'created_by' => $currentUser['id']
            ]
        );
    }
}
