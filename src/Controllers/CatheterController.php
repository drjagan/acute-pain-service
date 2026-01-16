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
        $catheterIndications = $this->getLookupData('lookup_catheter_indications');
        
        $this->view('catheters.create', [
            'patients' => $patients,
            'selectedPatient' => $selectedPatient,
            'redFlags' => $redFlags,
            'catheterIndications' => $catheterIndications,
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
            
            // Send notifications to assigned physicians (v1.1)
            $this->notifyPhysiciansAboutCatheterInsertion($data['patient_id'], $catheterId, $data);
            
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
        
        // Get drug regimes for this catheter
        $regimeModel = new \Models\DrugRegime();
        $regimes = $regimeModel->getCatheterRegimes($id);
        
        // Get functional outcomes for this catheter
        $outcomeModel = new \Models\FunctionalOutcome();
        $outcomes = $outcomeModel->getCatheterOutcomes($id);
        
        // Get removal record if exists
        $removalModel = new \Models\CatheterRemoval();
        $removal = $removalModel->getRemovalByCatheter($id);
        
        $this->view('catheters.view', [
            'catheter' => $catheter,
            'categories' => Catheter::getCategoryNames(),
            'catheterTypes' => Catheter::getCatheterTypes(),
            'regimes' => $regimes,
            'outcomes' => $outcomes,
            'removal' => $removal
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
        $catheterIndications = $this->getLookupData('lookup_catheter_indications');
        
        $this->view('catheters.edit', [
            'catheter' => $catheter,
            'patients' => $patients,
            'redFlags' => $redFlags,
            'catheterIndications' => $catheterIndications,
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
     * Show catheter removal form (Screen 5)
     */
    public function remove($id) {
        $this->requireRole(['attending', 'resident', 'admin']);
        
        $catheter = $this->catheterModel->getCatheterWithDetails($id);
        
        if (!$catheter) {
            Flash::error('Catheter not found');
            return $this->redirect('/catheters');
        }
        
        // Check if already removed
        $removalModel = new \Models\CatheterRemoval();
        if ($removalModel->catheterHasRemoval($id)) {
            Flash::error('Catheter already has a removal record');
            return $this->redirect('/catheters/viewCatheter/' . $id);
        }
        
        // Calculate catheter days
        $insertionDate = new \DateTime($catheter['date_of_insertion']);
        $today = new \DateTime();
        $catheterDays = $today->diff($insertionDate)->days;
        
        // Load removal indications from database
        $indicationModel = new \Models\LookupRemovalIndication();
        $indications = $indicationModel->getActive();
        
        $this->view('catheters.remove', [
            'catheter' => $catheter,
            'catheterDays' => $catheterDays,
            'indications' => $indications
        ]);
    }
    
    /**
     * Store catheter removal record
     */
    public function storeRemoval($id) {
        $this->requireRole(['attending', 'resident', 'admin']);
        $this->validateCSRF();
        
        $catheter = $this->catheterModel->find($id);
        if (!$catheter) {
            Flash::error('Catheter not found');
            return $this->redirect('/catheters');
        }
        
        // Validate input
        $validation = $this->validateRemovalData($_POST);
        if (!$validation['valid']) {
            Flash::error($validation['message']);
            return $this->redirect('/catheters/remove/' . $id);
        }
        
        // Prepare data
        $data = $this->prepareRemovalData($_POST);
        $data['catheter_id'] = $id;
        $data['patient_id'] = $catheter['patient_id'];
        $data['created_by'] = $this->user()['id'];
        
        try {
            $removalModel = new \Models\CatheterRemoval();
            $removalId = $removalModel->create($data);
            
            // Update catheter status to 'removed'
            $this->catheterModel->update($id, [
                'status' => 'removed',
                'updated_by' => $this->user()['id']
            ]);
            
            // Send notifications to assigned physicians (v1.1)
            $this->notifyPhysiciansAboutCatheterRemoval($catheter['patient_id'], $id, $removalId, $data);
            
            Flash::success('Catheter removal documented successfully');
            return $this->redirect('/catheters/viewCatheter/' . $id);
            
        } catch (\Exception $e) {
            Flash::error('Failed to document catheter removal: ' . $e->getMessage());
            return $this->redirect('/catheters/remove/' . $id);
        }
    }
    
    /**
     * View catheter removal details
     */
    public function viewRemoval($id) {
        $this->requireAuth();
        
        $removalModel = new \Models\CatheterRemoval();
        $removal = $removalModel->getRemovalWithDetails($id);
        
        if (!$removal) {
            Flash::error('Removal record not found');
            return $this->redirect('/catheters');
        }
        
        $this->view('catheters.viewRemoval', [
            'removal' => $removal,
            'indications' => \Models\CatheterRemoval::getIndicationNames()
        ]);
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
     * Validate removal data
     */
    private function validateRemovalData($data) {
        $errors = [];
        
        // Required fields
        $required = ['indication', 'date_of_removal', 'number_of_catheter_days'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        // If indication is 'other', indication_notes is required
        if (!empty($data['indication']) && $data['indication'] === 'other' && empty($data['indication_notes'])) {
            $errors[] = 'Indication notes are required when indication is "Other"';
        }
        
        // Validate catheter days
        if (isset($data['number_of_catheter_days']) && ($data['number_of_catheter_days'] < 0 || $data['number_of_catheter_days'] > 30)) {
            $errors[] = 'Number of catheter days must be between 0 and 30';
        }
        
        if (!empty($errors)) {
            return ['valid' => false, 'message' => implode(', ', $errors)];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Prepare removal data for storage
     */
    private function prepareRemovalData($data) {
        return [
            'indication' => $data['indication'],
            'indication_notes' => !empty($data['indication_notes']) ? Sanitizer::string($data['indication_notes']) : null,
            'date_of_removal' => $data['date_of_removal'],
            'number_of_catheter_days' => (int)$data['number_of_catheter_days'],
            'catheter_tip_intact' => isset($data['catheter_tip_intact']) ? 1 : 0,
            'removal_complications' => !empty($data['removal_complications']) ? Sanitizer::string($data['removal_complications']) : null,
            'final_notes' => !empty($data['final_notes']) ? Sanitizer::string($data['final_notes']) : null,
            'patient_satisfaction' => !empty($data['patient_satisfaction']) ? $data['patient_satisfaction'] : null
        ];
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
        // Check if table has severity column (for red_flags, sentinel_events)
        $orderBy = "name";
        if ($table === 'lookup_red_flags' || $table === 'lookup_sentinel_events') {
            $orderBy = "severity DESC, name";
        } elseif ($table === 'lookup_catheter_indications') {
            $orderBy = "sort_order ASC, name";
        }
        
        $stmt = $this->db->query("SELECT * FROM {$table} WHERE active = 1 ORDER BY {$orderBy}");
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
    
    /**
     * Send notifications to assigned physicians about catheter insertion (v1.1)
     * Note: Admins assigned as attending physicians also receive notifications
     */
    private function notifyPhysiciansAboutCatheterInsertion($patientId, $catheterId, $catheterData) {
        require_once __DIR__ . '/../Models/Notification.php';
        require_once __DIR__ . '/../Models/PatientPhysician.php';
        
        $notificationModel = new \Models\Notification();
        $patientPhysicianModel = new \Models\PatientPhysician();
        
        // Get patient details
        $patient = $this->patientModel->find($patientId);
        if (!$patient) return;
        
        // Get assigned physicians
        $physicians = $patientPhysicianModel->getPhysiciansByPatient($patientId);
        if (empty($physicians)) return;
        
        $physicianIds = array_column($physicians, 'user_id');
        $currentUser = $this->user();
        
        $catheterType = $catheterData['catheter_type'] ?? 'catheter';
        $message = "A {$catheterType} has been inserted for patient '{$patient['patient_name']}' by {$currentUser['first_name']} {$currentUser['last_name']}.";
        
        $notificationModel->notifyMultiple(
            $physicianIds,
            'catheter_inserted',
            'Catheter Inserted',
            $message,
            [
                'priority' => 'high',
                'color' => 'info',
                'icon' => 'bi-clipboard-pulse',
                'related_type' => 'catheters',
                'related_id' => $catheterId,
                'action_url' => '/catheters/viewCatheter/' . $catheterId,
                'action_text' => 'View Catheter',
                'send_email' => true,
                'created_by' => $currentUser['id']
            ]
        );
    }
    
    /**
     * Send notifications to assigned physicians about catheter removal (v1.1)
     * Note: Admins assigned as attending physicians also receive notifications
     */
    private function notifyPhysiciansAboutCatheterRemoval($patientId, $catheterId, $removalId, $removalData) {
        require_once __DIR__ . '/../Models/Notification.php';
        require_once __DIR__ . '/../Models/PatientPhysician.php';
        
        $notificationModel = new \Models\Notification();
        $patientPhysicianModel = new \Models\PatientPhysician();
        
        // Get patient details
        $patient = $this->patientModel->find($patientId);
        if (!$patient) return;
        
        // Get assigned physicians
        $physicians = $patientPhysicianModel->getPhysiciansByPatient($patientId);
        if (empty($physicians)) return;
        
        $physicianIds = array_column($physicians, 'user_id');
        $currentUser = $this->user();
        
        $indication = $removalData['indication'] ?? 'unknown reason';
        $hasComplications = !empty($removalData['removal_complications']);
        
        $message = "Catheter for patient '{$patient['patient_name']}' has been removed ({$indication}) by {$currentUser['first_name']} {$currentUser['last_name']}.";
        
        if ($hasComplications) {
            $message .= " NOTE: Complications reported.";
        }
        
        $notificationModel->notifyMultiple(
            $physicianIds,
            'catheter_removed',
            $hasComplications ? 'Catheter Removed - Complications' : 'Catheter Removed',
            $message,
            [
                'priority' => $hasComplications ? 'high' : 'medium',
                'color' => $hasComplications ? 'warning' : 'success',
                'icon' => $hasComplications ? 'bi-exclamation-triangle' : 'bi-check-circle',
                'related_type' => 'catheters',
                'related_id' => $catheterId,
                'action_url' => '/catheters/viewCatheter/' . $catheterId,
                'action_text' => 'View Details',
                'send_email' => $hasComplications,
                'created_by' => $currentUser['id']
            ]
        );
    }
}
