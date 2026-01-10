<?php
namespace Controllers;

use Models\FunctionalOutcome;
use Models\Catheter;
use Models\Patient;
use Helpers\Flash;
use Helpers\CSRF;
use Helpers\Sanitizer;

/**
 * FunctionalOutcome Controller (Screen 4 - Functional Outcomes Assessment)
 * Handles functional outcome documentation and monitoring
 */
class FunctionalOutcomeController extends BaseController {
    
    private $outcomeModel;
    private $catheterModel;
    private $patientModel;
    
    public function __construct() {
        parent::__construct();
        $this->outcomeModel = new FunctionalOutcome();
        $this->catheterModel = new Catheter();
        $this->patientModel = new Patient();
    }
    
    /**
     * List all functional outcomes
     */
    public function index() {
        $this->requireAuth();
        
        $search = $_GET['search'] ?? '';
        
        if ($search) {
            $outcomes = $this->outcomeModel->search($search);
        } else {
            // Get recent outcomes (last 100)
            $outcomes = $this->outcomeModel->search('', 100);
        }
        
        $total = count($outcomes);
        
        $this->view('outcomes.list', [
            'outcomes' => $outcomes,
            'total' => $total,
            'search' => $search
        ]);
    }
    
    /**
     * Show create functional outcome form (Screen 4)
     * Can be called with catheter_id parameter
     */
    public function create() {
        $this->requireRole(['attending', 'resident', 'nurse', 'admin']);
        
        $catheterId = $_GET['catheter_id'] ?? null;
        $selectedCatheter = null;
        $selectedPatient = null;
        
        // If catheter_id provided, load catheter and patient details
        if ($catheterId) {
            $selectedCatheter = $this->catheterModel->getCatheterWithDetails($catheterId);
            if (!$selectedCatheter) {
                Flash::error('Catheter not found');
                return $this->redirect('/catheters');
            }
            
            $selectedPatient = $this->patientModel->find($selectedCatheter['patient_id']);
            
            // Get latest outcome to suggest next POD
            $latestOutcome = $this->outcomeModel->getLatestOutcome($catheterId);
            $suggestedPOD = $latestOutcome ? $latestOutcome['pod'] + 1 : 0;
        } else {
            $suggestedPOD = 0;
        }
        
        // Load all active catheters for dropdown
        $catheters = $this->catheterModel->getActiveCatheters(200);
        
        $this->view('outcomes.create', [
            'catheters' => $catheters,
            'selectedCatheter' => $selectedCatheter,
            'selectedPatient' => $selectedPatient,
            'suggestedPOD' => $suggestedPOD
        ]);
    }
    
    /**
     * Store new functional outcome
     */
    public function store() {
        $this->requireRole(['attending', 'resident', 'nurse', 'admin']);
        $this->validateCSRF();
        
        // Validate input
        $validation = $this->validateOutcomeData($_POST);
        if (!$validation['valid']) {
            Flash::error($validation['message']);
            return $this->redirect('/outcomes/create?catheter_id=' . ($_POST['catheter_id'] ?? ''));
        }
        
        // Prepare data
        $data = $this->prepareOutcomeData($_POST);
        $data['created_by'] = $this->user()['id'];
        
        try {
            $outcomeId = $this->outcomeModel->create($data);
            
            Flash::success('Functional outcome recorded successfully');
            return $this->redirect('/outcomes/viewOutcome/' . $outcomeId);
            
        } catch (\Exception $e) {
            Flash::error('Failed to record functional outcome: ' . $e->getMessage());
            return $this->redirect('/outcomes/create?catheter_id=' . ($_POST['catheter_id'] ?? ''));
        }
    }
    
    /**
     * View functional outcome details
     */
    public function viewOutcome($id) {
        $this->requireAuth();
        
        $outcome = $this->outcomeModel->getOutcomeWithDetails($id);
        
        if (!$outcome) {
            Flash::error('Functional outcome not found');
            return $this->redirect('/outcomes');
        }
        
        // Calculate functional score
        $functionalScore = $this->outcomeModel->calculateFunctionalScore($id);
        
        $this->view('outcomes.view', [
            'outcome' => $outcome,
            'functionalScore' => $functionalScore
        ]);
    }
    
    /**
     * Show edit functional outcome form
     */
    public function edit($id) {
        $this->requireRole(['attending', 'resident', 'nurse', 'admin']);
        
        $outcome = $this->outcomeModel->find($id);
        
        if (!$outcome) {
            Flash::error('Functional outcome not found');
            return $this->redirect('/outcomes');
        }
        
        // Load catheter and patient
        $catheter = $this->catheterModel->getCatheterWithDetails($outcome['catheter_id']);
        $patient = $this->patientModel->find($outcome['patient_id']);
        
        $this->view('outcomes.edit', [
            'outcome' => $outcome,
            'catheter' => $catheter,
            'patient' => $patient
        ]);
    }
    
    /**
     * Update functional outcome
     */
    public function update($id) {
        $this->requireRole(['attending', 'resident', 'nurse', 'admin']);
        $this->validateCSRF();
        
        $outcome = $this->outcomeModel->find($id);
        if (!$outcome) {
            Flash::error('Functional outcome not found');
            return $this->redirect('/outcomes');
        }
        
        // Validate input
        $validation = $this->validateOutcomeData($_POST);
        if (!$validation['valid']) {
            Flash::error($validation['message']);
            return $this->redirect('/outcomes/edit/' . $id);
        }
        
        // Prepare data
        $data = $this->prepareOutcomeData($_POST);
        $data['updated_by'] = $this->user()['id'];
        
        try {
            $this->outcomeModel->update($id, $data);
            
            Flash::success('Functional outcome updated successfully');
            return $this->redirect('/outcomes/viewOutcome/' . $id);
            
        } catch (\Exception $e) {
            Flash::error('Failed to update functional outcome: ' . $e->getMessage());
            return $this->redirect('/outcomes/edit/' . $id);
        }
    }
    
    /**
     * Delete functional outcome (soft delete)
     */
    public function delete($id) {
        $this->requireRole('attending');
        $this->validateCSRF();
        
        $outcome = $this->outcomeModel->find($id);
        if (!$outcome) {
            Flash::error('Functional outcome not found');
            return $this->redirect('/outcomes');
        }
        
        try {
            $this->outcomeModel->delete($id);
            Flash::success('Functional outcome deleted successfully');
        } catch (\Exception $e) {
            Flash::error('Failed to delete functional outcome: ' . $e->getMessage());
        }
        
        return $this->redirect('/catheters/viewCatheter/' . $outcome['catheter_id']);
    }
    
    /**
     * Validate functional outcome data
     */
    private function validateOutcomeData($data) {
        $errors = [];
        
        // Required fields
        $required = [
            'catheter_id', 'patient_id', 'pod', 'entry_date',
            'incentive_spirometry', 'ambulation', 'cough_ability', 
            'room_air_spo2', 'catheter_site_infection', 'sentinel_events'
        ];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        // Validate catheter exists
        if (!empty($data['catheter_id'])) {
            $catheter = $this->catheterModel->find($data['catheter_id']);
            if (!$catheter) {
                $errors[] = 'Invalid catheter selected';
            }
        }
        
        // Validate patient exists
        if (!empty($data['patient_id'])) {
            $patient = $this->patientModel->find($data['patient_id']);
            if (!$patient) {
                $errors[] = 'Invalid patient selected';
            }
        }
        
        // Validate POD
        if (isset($data['pod']) && ($data['pod'] < 0 || $data['pod'] > 30)) {
            $errors[] = 'POD must be between 0 and 30';
        }
        
        // Validate SpO2 value if provided
        if (!empty($data['spo2_value'])) {
            if ($data['spo2_value'] < 0 || $data['spo2_value'] > 100) {
                $errors[] = 'SpO2 value must be between 0 and 100';
            }
        }
        
        // Validate sentinel event details if event is not 'none'
        if (isset($data['sentinel_events']) && $data['sentinel_events'] !== 'none') {
            if (empty($data['sentinel_event_details'])) {
                $errors[] = 'Sentinel event details are required when an event is reported';
            }
        }
        
        if (!empty($errors)) {
            return ['valid' => false, 'message' => implode(', ', $errors)];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Prepare functional outcome data for storage
     */
    private function prepareOutcomeData($data) {
        return [
            'catheter_id' => (int)$data['catheter_id'],
            'patient_id' => (int)$data['patient_id'],
            'pod' => (int)$data['pod'],
            'entry_date' => $data['entry_date'],
            'incentive_spirometry' => Sanitizer::string($data['incentive_spirometry']),
            'ambulation' => Sanitizer::string($data['ambulation']),
            'cough_ability' => Sanitizer::string($data['cough_ability']),
            'room_air_spo2' => Sanitizer::string($data['room_air_spo2']),
            'spo2_value' => !empty($data['spo2_value']) ? (int)$data['spo2_value'] : null,
            'catheter_site_infection' => Sanitizer::string($data['catheter_site_infection']),
            'sentinel_events' => Sanitizer::string($data['sentinel_events']),
            'sentinel_event_details' => !empty($data['sentinel_event_details']) ? Sanitizer::string($data['sentinel_event_details']) : null,
            'clinical_notes' => !empty($data['clinical_notes']) ? Sanitizer::string($data['clinical_notes']) : null
        ];
    }
}
