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
     * Show create patient form (Screen 1)
     */
    public function create() {
        // Only attending, resident, and admin can create patients
        $this->requireRole(['attending', 'resident', 'admin']);
        
        // Load lookup data
        $comorbidities = $this->getLookupData('lookup_comorbidities');
        $surgeries = $this->getLookupData('lookup_surgeries');
        
        $this->view('patients.create', [
            'comorbidities' => $comorbidities,
            'surgeries' => $surgeries,
            'specialities' => $this->getSpecialities()
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
        
        // Get active catheters for drug regime dropdown
        $activeCatheters = $catheterModel->getPatientCatheters($id, true); // active only
        
        $this->view('patients.view', [
            'patient' => $patient,
            'specialities' => $this->getSpecialities(),
            'catheters' => $catheters,
            'regimes' => $regimes,
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
        
        $comorbidities = $this->getLookupData('lookup_comorbidities');
        $surgeries = $this->getLookupData('lookup_surgeries');
        
        $this->view('patients.edit', [
            'patient' => $patient,
            'comorbidities' => $comorbidities,
            'surgeries' => $surgeries,
            'specialities' => $this->getSpecialities()
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
        $stmt = $this->db->query("SELECT * FROM {$table} WHERE active = 1 ORDER BY sort_order, name");
        return $stmt->fetchAll();
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
}
