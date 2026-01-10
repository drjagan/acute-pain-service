<?php
namespace Controllers;

use Models\DrugRegime;
use Models\Catheter;
use Models\Patient;
use Helpers\Flash;
use Helpers\CSRF;
use Helpers\Sanitizer;

/**
 * DrugRegime Controller (Screen 3 - Drug Regime Management)
 * Handles drug regime documentation and monitoring
 */
class DrugRegimeController extends BaseController {
    
    private $regimeModel;
    private $catheterModel;
    private $patientModel;
    
    public function __construct() {
        parent::__construct();
        $this->regimeModel = new DrugRegime();
        $this->catheterModel = new Catheter();
        $this->patientModel = new Patient();
    }
    
    /**
     * List all drug regimes
     */
    public function index() {
        $this->requireAuth();
        
        $search = $_GET['search'] ?? '';
        
        if ($search) {
            $regimes = $this->regimeModel->search($search);
        } else {
            // Get recent regimes (last 100)
            $regimes = $this->regimeModel->search('', 100);
        }
        
        $total = count($regimes);
        
        $this->view('regimes.list', [
            'regimes' => $regimes,
            'total' => $total,
            'search' => $search
        ]);
    }
    
    /**
     * Show create drug regime form (Screen 3)
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
            
            // Get latest regime to suggest next POD
            $latestRegime = $this->regimeModel->getLatestRegime($catheterId);
            $suggestedPOD = $latestRegime ? $latestRegime['pod'] + 1 : 0;
        } else {
            $suggestedPOD = 0;
        }
        
        // Load all active catheters for dropdown
        $catheters = $this->catheterModel->getActiveCatheters(200);
        
        // Load lookup data
        $drugs = $this->getLookupData('lookup_drugs');
        $adjuvants = $this->getLookupData('lookup_adjuvants');
        
        $this->view('regimes.create', [
            'catheters' => $catheters,
            'selectedCatheter' => $selectedCatheter,
            'selectedPatient' => $selectedPatient,
            'drugs' => $drugs,
            'adjuvants' => $adjuvants,
            'suggestedPOD' => $suggestedPOD
        ]);
    }
    
    /**
     * Store new drug regime
     */
    public function store() {
        $this->requireRole(['attending', 'resident', 'nurse', 'admin']);
        $this->validateCSRF();
        
        // Validate input
        $validation = $this->validateRegimeData($_POST);
        if (!$validation['valid']) {
            Flash::error($validation['message']);
            return $this->redirect('/regimes/create?catheter_id=' . ($_POST['catheter_id'] ?? ''));
        }
        
        // Prepare data
        $data = $this->prepareRegimeData($_POST);
        $data['created_by'] = $this->user()['id'];
        
        try {
            $regimeId = $this->regimeModel->create($data);
            
            Flash::success('Drug regime recorded successfully');
            return $this->redirect('/regimes/viewRegime/' . $regimeId);
            
        } catch (\Exception $e) {
            Flash::error('Failed to record drug regime: ' . $e->getMessage());
            return $this->redirect('/regimes/create?catheter_id=' . ($_POST['catheter_id'] ?? ''));
        }
    }
    
    /**
     * View drug regime details
     */
    public function viewRegime($id) {
        $this->requireAuth();
        
        $regime = $this->regimeModel->getRegimeWithDetails($id);
        
        if (!$regime) {
            Flash::error('Drug regime not found');
            return $this->redirect('/regimes');
        }
        
        // Calculate pain improvement
        $painImprovement = $this->regimeModel->calculatePainImprovement($id);
        
        $this->view('regimes.view', [
            'regime' => $regime,
            'painImprovement' => $painImprovement
        ]);
    }
    
    /**
     * Show edit drug regime form
     */
    public function edit($id) {
        $this->requireRole(['attending', 'resident', 'nurse', 'admin']);
        
        $regime = $this->regimeModel->find($id);
        
        if (!$regime) {
            Flash::error('Drug regime not found');
            return $this->redirect('/regimes');
        }
        
        // Load catheter and patient
        $catheter = $this->catheterModel->getCatheterWithDetails($regime['catheter_id']);
        $patient = $this->patientModel->find($regime['patient_id']);
        
        // Load lookup data
        $drugs = $this->getLookupData('lookup_drugs');
        $adjuvants = $this->getLookupData('lookup_adjuvants');
        
        $this->view('regimes.edit', [
            'regime' => $regime,
            'catheter' => $catheter,
            'patient' => $patient,
            'drugs' => $drugs,
            'adjuvants' => $adjuvants
        ]);
    }
    
    /**
     * Update drug regime
     */
    public function update($id) {
        $this->requireRole(['attending', 'resident', 'nurse', 'admin']);
        $this->validateCSRF();
        
        $regime = $this->regimeModel->find($id);
        if (!$regime) {
            Flash::error('Drug regime not found');
            return $this->redirect('/regimes');
        }
        
        // Validate input
        $validation = $this->validateRegimeData($_POST);
        if (!$validation['valid']) {
            Flash::error($validation['message']);
            return $this->redirect('/regimes/edit/' . $id);
        }
        
        // Prepare data
        $data = $this->prepareRegimeData($_POST);
        $data['updated_by'] = $this->user()['id'];
        
        try {
            $this->regimeModel->update($id, $data);
            
            Flash::success('Drug regime updated successfully');
            return $this->redirect('/regimes/viewRegime/' . $id);
            
        } catch (\Exception $e) {
            Flash::error('Failed to update drug regime: ' . $e->getMessage());
            return $this->redirect('/regimes/edit/' . $id);
        }
    }
    
    /**
     * Delete drug regime (soft delete)
     */
    public function delete($id) {
        $this->requireRole('attending');
        $this->validateCSRF();
        
        $regime = $this->regimeModel->find($id);
        if (!$regime) {
            Flash::error('Drug regime not found');
            return $this->redirect('/regimes');
        }
        
        try {
            $this->regimeModel->delete($id);
            Flash::success('Drug regime deleted successfully');
        } catch (\Exception $e) {
            Flash::error('Failed to delete drug regime: ' . $e->getMessage());
        }
        
        return $this->redirect('/catheters/viewCatheter/' . $regime['catheter_id']);
    }
    
    /**
     * Validate drug regime data
     */
    private function validateRegimeData($data) {
        $errors = [];
        
        // Required fields
        $required = [
            'catheter_id', 'patient_id', 'pod', 'entry_date', 'drug',
            'volume', 'concentration', 'baseline_vnrs_static', 'baseline_vnrs_dynamic',
            'vnrs_15min_static', 'vnrs_15min_dynamic'
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
        
        // Validate VNRS scores (0-10)
        $vnrsFields = ['baseline_vnrs_static', 'baseline_vnrs_dynamic', 'vnrs_15min_static', 'vnrs_15min_dynamic'];
        foreach ($vnrsFields as $field) {
            if (isset($data[$field]) && ($data[$field] < 0 || $data[$field] > 10)) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be between 0 and 10';
            }
        }
        
        // Validate numeric fields
        if (isset($data['volume']) && $data['volume'] <= 0) {
            $errors[] = 'Volume must be greater than 0';
        }
        
        if (isset($data['concentration']) && $data['concentration'] <= 0) {
            $errors[] = 'Concentration must be greater than 0';
        }
        
        if (!empty($errors)) {
            return ['valid' => false, 'message' => implode(', ', $errors)];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Prepare drug regime data for storage
     */
    private function prepareRegimeData($data) {
        return [
            'catheter_id' => (int)$data['catheter_id'],
            'patient_id' => (int)$data['patient_id'],
            'pod' => (int)$data['pod'],
            'entry_date' => $data['entry_date'],
            'drug' => Sanitizer::string($data['drug']),
            'volume' => (float)$data['volume'],
            'concentration' => (float)$data['concentration'],
            'adjuvant' => !empty($data['adjuvant']) ? Sanitizer::string($data['adjuvant']) : null,
            'dose' => !empty($data['dose']) ? (float)$data['dose'] : null,
            'baseline_vnrs_static' => (int)$data['baseline_vnrs_static'],
            'baseline_vnrs_dynamic' => (int)$data['baseline_vnrs_dynamic'],
            'vnrs_15min_static' => (int)$data['vnrs_15min_static'],
            'vnrs_15min_dynamic' => (int)$data['vnrs_15min_dynamic'],
            'effective_analgesia' => isset($data['effective_analgesia']) ? 1 : 0,
            'troubleshooting_activated' => isset($data['troubleshooting_activated']) ? 1 : 0,
            'troubleshooting_notes' => !empty($data['troubleshooting_notes']) ? Sanitizer::string($data['troubleshooting_notes']) : null,
            'hypotension' => $data['hypotension'] ?? 'none',
            'bradycardia' => $data['bradycardia'] ?? 'none',
            'sensory_motor_deficit' => $data['sensory_motor_deficit'] ?? 'none',
            'nausea_vomiting' => $data['nausea_vomiting'] ?? 'none',
            'clinical_notes' => !empty($data['clinical_notes']) ? Sanitizer::string($data['clinical_notes']) : null
        ];
    }
    
    /**
     * Get lookup data
     */
    private function getLookupData($table) {
        $stmt = $this->db->query("SELECT * FROM {$table} WHERE active = 1 ORDER BY name");
        return $stmt->fetchAll();
    }
}
