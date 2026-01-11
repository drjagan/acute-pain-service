<?php
namespace Controllers;

use Models\Report;
use Models\Patient;
use Helpers\Flash;

/**
 * Report Controller (Phase 6)
 * Handles individual and consolidated reports
 */
class ReportController extends BaseController {
    
    private $reportModel;
    private $patientModel;
    
    public function __construct() {
        parent::__construct();
        $this->reportModel = new Report();
        $this->patientModel = new Patient();
    }
    
    /**
     * Reports index - Selection page
     */
    public function index() {
        $this->requireAuth();
        
        // Get all patients for individual report selection
        $patients = $this->patientModel->all();
        
        $this->view('reports.index', [
            'patients' => $patients
        ]);
    }
    
    /**
     * Generate Individual Patient Report
     */
    public function individual($patientId = null) {
        $this->requireAuth();
        
        if (!$patientId) {
            Flash::error('Patient ID is required');
            return $this->redirect('/reports');
        }
        
        // Verify patient exists
        $patient = $this->patientModel->find($patientId);
        if (!$patient) {
            Flash::error('Patient not found');
            return $this->redirect('/reports');
        }
        
        // Generate comprehensive report
        $reportData = $this->reportModel->generateIndividualReport($patientId);
        
        $this->view('reports.individual', [
            'report' => $reportData,
            'generated_at' => date('F j, Y g:i A')
        ]);
    }
    
    /**
     * Show consolidated report form
     */
    public function consolidated() {
        $this->requireAuth();
        
        // Default to current month
        $defaultStart = date('Y-m-01'); // First day of current month
        $defaultEnd = date('Y-m-t'); // Last day of current month
        
        $this->view('reports.consolidated_form', [
            'default_start' => $defaultStart,
            'default_end' => $defaultEnd
        ]);
    }
    
    /**
     * Generate Consolidated Report
     */
    public function generateConsolidated() {
        $this->requireAuth();
        
        $startDate = $_POST['start_date'] ?? $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_POST['end_date'] ?? $_GET['end_date'] ?? date('Y-m-t');
        
        // Validate dates
        if (strtotime($startDate) > strtotime($endDate)) {
            Flash::error('Start date must be before end date');
            return $this->redirect('/reports/consolidated');
        }
        
        // Generate report
        $reportData = $this->reportModel->generateConsolidatedReport($startDate, $endDate);
        
        $this->view('reports.consolidated', [
            'report' => $reportData,
            'generated_at' => date('F j, Y g:i A')
        ]);
    }
}
