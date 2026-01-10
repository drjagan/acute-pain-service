<?php
namespace Controllers;

use Models\Catheter;
use Models\Patient;
use Helpers\Flash;
use Helpers\CSRF;
use Helpers\Sanitizer;

/**
 * Catheter Controller (Screen 2 - Catheter Insertion)
 * Handles catheter insertion and management
 */
class CatheterController extends BaseController {
    
    private $catheterModel;
    private $patientModel;
    
    public function __construct() {
        parent::__construct();
        $this->catheterModel = new Catheter();
        $this->patientModel = new Patient();
    }
    
    /**
     * List all catheters
     */
    public function index() {
        $this->requireAuth();
        
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        
        if ($search) {
            $catheters = $this->catheterModel->search($search);
        } elseif ($category) {
            $catheters = $this->catheterModel->getCathetersByCategory($category);
        } else {
            $catheters = $this->catheterModel->getActiveCatheters(100);
        }
        
        $total = count($catheters);
        
        $this->view('catheters.list', [
            'catheters' => $catheters,
            'total' => $total,
            'search' => $search,
            'category' => $category,
            'categories' => Catheter::getCategoryNames()
        ]);
    }
    
    /**
     * Show create catheter form (Screen 2)
     * Can be called with patient_id parameter
     */
    public function create() {
        $this->requireRole(['attending', 'resident', 'admin']);
        
        $patientId = $_GET['patient_id'] ?? null;
        $selectedPatient = null;
        
        // If patient_id provided, load patient details
        if ($patientId) {
            $selectedPatient = $this->patientModel->find($patientId);
            if (!$selectedPatient) {
                Flash::error('Patient not found');
                return $this->redirect('/patients');
            }
        }
        
        // Load all patients for dropdown
        $patients = $this->patientModel->all();
        
        // Load lookup data
        $redFlags = $this->getLookupData('lookup_red_flags');
        
        $this->view('catheters.create', [
            'patients' => $patients,
            'selectedPatient' => $selectedPatient,
            'redFlags' => $redFlags,
            'catheterTypes' => Catheter::getCatheterTypes(),
            'categories' => Catheter::getCategoryNames()
        ]);
    }
    
    /**
     * Store new catheter
     */
    public function store() {
        $this->requireRole(['attending', 'resident', 'admin']);
        $this->validateCSRF();
        
        // Validate input
        $validation = $this->validateCatheterData($_POST);
        if (!$validation['valid']) {
            Flash::error($validation['message']);
            return $this->redirect('/catheters/create?patient_id=' . ($_POST['patient_id'] ?? ''));
        }
        
        // Prepare data
        $data = $this->prepareCatheterData($_POST);
        $data['created_by'] = $this->user()['id'];
        $data['status'] = 'active';
        
        try {
            $catheterId = $this->catheterModel->create($data);
            
            // Update patient status to active_catheter
            $this->patientModel->update($data['patient_id'], [
                'status' => 'active_catheter',
                'updated_by' => $this->user()['id']
            ]);
            
            Flash::success('Catheter inserted successfully');
            return $this->redirect('/catheters/viewCatheter/' . $catheterId);
            
        } catch (\Exception $e) {
            Flash::error('Failed to record catheter insertion: ' . $e->getMessage());
            return $this->redirect('/catheters/create?patient_id=' . ($_POST['patient_id'] ?? ''));
        }
    }
    
    /**
     * View catheter details
     */
    public function viewCatheter($id) {
        $this->requireAuth();
        
        $catheter = $this->catheterModel->getCatheterWithDetails($id);
        
        if (!$catheter) {
            Flash::error('Catheter not found');
            return $this->redirect('/catheters');
        }
        
        // Decode JSON fields
        $catheter['red_flags_decoded'] = json_decode($catheter['red_flags'], true) ?: [];
        
        // Get red flag names
        $catheter['red_flag_names'] = $this->getLookupNamesWithSeverity(
            'lookup_red_flags', 
            $catheter['red_flags_decoded']
        );
        
        $this->view('catheters.view', [
            'catheter' => $catheter,
            'categories' => Catheter::getCategoryNames(),
            'catheterTypes' => Catheter::getCatheterTypes()
        ]);
    }
    
    /**
     * Show edit catheter form
     */
    public function edit($id) {
        $this->requireRole(['attending', 'resident', 'admin']);
        
        $catheter = $this->catheterModel->find($id);
        
        if (!$catheter) {
            Flash::error('Catheter not found');
            return $this->redirect('/catheters');
        }
        
        // Decode JSON fields
        $catheter['red_flags'] = json_decode($catheter['red_flags'], true) ?: [];
        
        // Load patients and lookup data
        $patients = $this->patientModel->all();
        $redFlags = $this->getLookupData('lookup_red_flags');
        
        $this->view('catheters.edit', [
            'catheter' => $catheter,
            'patients' => $patients,
            'redFlags' => $redFlags,
            'catheterTypes' => Catheter::getCatheterTypes(),
            'categories' => Catheter::getCategoryNames()
        ]);
    }
    
    /**
     * Update catheter
     */
    public function update($id) {
        $this->requireRole(['attending', 'resident', 'admin']);
        $this->validateCSRF();
        
        $catheter = $this->catheterModel->find($id);
        if (!$catheter) {
            Flash::error('Catheter not found');
            return $this->redirect('/catheters');
        }
        
        // Validate input
        $validation = $this->validateCatheterData($_POST);
        if (!$validation['valid']) {
            Flash::error($validation['message']);
            return $this->redirect('/catheters/edit/' . $id);
        }
        
        // Prepare data
        $data = $this->prepareCatheterData($_POST);
        $data['updated_by'] = $this->user()['id'];
        
        try {
            $this->catheterModel->update($id, $data);
            
            Flash::success('Catheter updated successfully');
            return $this->redirect('/catheters/viewCatheter/' . $id);
            
        } catch (\Exception $e) {
            Flash::error('Failed to update catheter: ' . $e->getMessage());
            return $this->redirect('/catheters/edit/' . $id);
        }
    }
    
    /**
     * Update catheter status (remove, displaced, infected)
     */
    public function updateStatus($id) {
        $this->requireRole(['attending', 'resident', 'admin']);
        $this->validateCSRF();
        
        $status = $_POST['status'] ?? '';
        $validStatuses = ['active', 'removed', 'displaced', 'infected'];
        
        if (!in_array($status, $validStatuses)) {
            Flash::error('Invalid status');
            return $this->redirect('/catheters/viewCatheter/' . $id);
        }
        
        try {
            $this->catheterModel->updateStatus($id, $status, $this->user()['id']);
            
            Flash::success('Catheter status updated to: ' . ucfirst($status));
            return $this->redirect('/catheters/viewCatheter/' . $id);
            
        } catch (\Exception $e) {
            Flash::error('Failed to update status: ' . $e->getMessage());
            return $this->redirect('/catheters/viewCatheter/' . $id);
        }
    }
    
    /**
     * Delete catheter (soft delete)
     */
    public function delete($id) {
        $this->requireRole('attending');
        $this->validateCSRF();
        
        $catheter = $this->catheterModel->find($id);
        if (!$catheter) {
            Flash::error('Catheter not found');
            return $this->redirect('/catheters');
        }
        
        try {
            $this->catheterModel->delete($id);
            Flash::success('Catheter record deleted successfully');
        } catch (\Exception $e) {
            Flash::error('Failed to delete catheter: ' . $e->getMessage());
        }
        
        return $this->redirect('/patients/viewPatient/' . $catheter['patient_id']);
    }
    
    /**
     * Validate catheter data
     */
    private function validateCatheterData($data) {
        $errors = [];
        
        // Required fields
        $required = [
            'patient_id', 'date_of_insertion', 'settings', 'performer',
            'catheter_category', 'catheter_type', 'indication'
        ];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        // Validate patient exists
        if (!empty($data['patient_id'])) {
            $patient = $this->patientModel->find($data['patient_id']);
            if (!$patient) {
                $errors[] = 'Invalid patient selected';
            }
        }
        
        // Validate date
        if (!empty($data['date_of_insertion'])) {
            $date = \DateTime::createFromFormat('Y-m-d', $data['date_of_insertion']);
            if (!$date || $date->format('Y-m-d') !== $data['date_of_insertion']) {
                $errors[] = 'Invalid date format';
            }
        }
        
        // Validate category and type match
        if (!empty($data['catheter_category']) && !empty($data['catheter_type'])) {
            $types = Catheter::getCatheterTypes();
            if (!isset($types[$data['catheter_category']][$data['catheter_type']])) {
                $errors[] = 'Catheter type does not match selected category';
            }
        }
        
        if (!empty($errors)) {
            return ['valid' => false, 'message' => implode(', ', $errors)];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Prepare catheter data for storage
     */
    private function prepareCatheterData($data) {
        return [
            'patient_id' => (int)$data['patient_id'],
            'date_of_insertion' => $data['date_of_insertion'],
            'settings' => $data['settings'],
            'performer' => $data['performer'],
            'catheter_category' => $data['catheter_category'],
            'catheter_type' => $data['catheter_type'],
            'indication' => Sanitizer::string($data['indication']),
            'functional_confirmation' => isset($data['functional_confirmation']) ? 1 : 0,
            'anatomical_confirmation' => isset($data['anatomical_confirmation']) ? 1 : 0,
            'red_flags' => json_encode($data['red_flags'] ?? [])
        ];
    }
    
    /**
     * Get lookup data
     */
    private function getLookupData($table) {
        $stmt = $this->db->query("SELECT * FROM {$table} WHERE active = 1 ORDER BY severity DESC, name");
        return $stmt->fetchAll();
    }
    
    /**
     * Get lookup names with severity info
     */
    private function getLookupNamesWithSeverity($table, $ids) {
        if (empty($ids)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("
            SELECT id, name, severity, requires_immediate_action 
            FROM {$table} 
            WHERE id IN ({$placeholders})
        ");
        $stmt->execute($ids);
        
        return $stmt->fetchAll();
    }
}
