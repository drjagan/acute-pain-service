<?php
namespace Models;

/**
 * Patient Model (Screen 1 - Patient Registration & Demographics)
 */
class Patient extends BaseModel {
    
    protected $table = 'patients';
    
    /**
     * Find patient by hospital number
     */
    public function findByHospitalNumber($hospitalNumber) {
        return $this->findBy('hospital_number', $hospitalNumber);
    }
    
    /**
     * Check if hospital number is unique
     */
    public function isHospitalNumberUnique($hospitalNumber, $excludeId = null) {
        $stmt = $this->db->prepare("
            SELECT id FROM {$this->table} 
            WHERE hospital_number = ? 
            AND deleted_at IS NULL
            " . ($excludeId ? "AND id != ?" : "")
        );
        
        $params = [$hospitalNumber];
        if ($excludeId) {
            $params[] = $excludeId;
        }
        
        $stmt->execute($params);
        return !$stmt->fetch();
    }
    
    /**
     * Get patients by status
     */
    public function findByStatus($status) {
        return $this->findAllBy('status', $status);
    }
    
    /**
     * Get patients by speciality
     */
    public function findBySpeciality($speciality) {
        return $this->findAllBy('speciality', $speciality);
    }
    
    /**
     * Search patients
     */
    public function search($query) {
        $searchTerm = "%{$query}%";
        
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE deleted_at IS NULL
            AND (
                patient_name LIKE ? OR 
                hospital_number LIKE ? OR 
                diagnosis LIKE ?
            )
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all patients with pagination
     */
    public function paginate($page = 1, $perPage = 25) {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE deleted_at IS NULL 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$perPage, $offset]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get total count
     */
    public function getTotalCount() {
        $stmt = $this->db->query("
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE deleted_at IS NULL
        ");
        
        return $stmt->fetch()['count'];
    }
    
    /**
     * Get statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total patients
        $stmt = $this->db->query("
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE deleted_at IS NULL
        ");
        $stats['total'] = $stmt->fetch()['count'];
        
        // By status
        $stmt = $this->db->query("
            SELECT status, COUNT(*) as count 
            FROM {$this->table} 
            WHERE deleted_at IS NULL 
            GROUP BY status
        ");
        $stats['by_status'] = $stmt->fetchAll();
        
        // By speciality
        $stmt = $this->db->query("
            SELECT speciality, COUNT(*) as count 
            FROM {$this->table} 
            WHERE deleted_at IS NULL 
            GROUP BY speciality
        ");
        $stats['by_speciality'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    /**
     * Calculate BMI
     */
    public static function calculateBMI($height, $weight) {
        if ($height <= 0 || $weight <= 0) {
            return 0;
        }
        
        // Convert height to meters
        $heightM = $height / 100;
        
        // BMI = weight (kg) / height (m)^2
        $bmi = $weight / ($heightM * $heightM);
        
        return round($bmi, 2);
    }
    
    /**
     * Get patient with catheter info
     */
    public function getPatientWithCatheter($patientId) {
        $stmt = $this->db->prepare("
            SELECT 
                p.*,
                c.id as catheter_id,
                c.catheter_category,
                c.catheter_type,
                c.date_of_insertion,
                c.status as catheter_status
            FROM {$this->table} p
            LEFT JOIN catheters c ON p.id = c.patient_id AND c.deleted_at IS NULL
            WHERE p.id = ? AND p.deleted_at IS NULL
        ");
        
        $stmt->execute([$patientId]);
        return $stmt->fetch();
    }
    
    /**
     * Get patient with associated physicians
     */
    public function getPatientWithPhysicians($patientId) {
        // Get patient data
        $patient = $this->find($patientId);
        
        if (!$patient) {
            return false;
        }
        
        // Get associated physicians using PatientPhysician model
        require_once __DIR__ . '/PatientPhysician.php';
        $patientPhysicianModel = new PatientPhysician();
        $physicians = $patientPhysicianModel->getPhysiciansByPatient($patientId);
        
        // Organize physicians by type
        $patient['attending_physicians'] = [];
        $patient['residents'] = [];
        
        foreach ($physicians as $physician) {
            if ($physician['physician_type'] == 'attending') {
                $patient['attending_physicians'][] = $physician;
            } else {
                $patient['residents'][] = $physician;
            }
        }
        
        return $patient;
    }
    
    /**
     * Get patients assigned to a specific physician
     */
    public function getPatientsByPhysician($userId, $limit = null) {
        require_once __DIR__ . '/PatientPhysician.php';
        $patientPhysicianModel = new PatientPhysician();
        
        return $patientPhysicianModel->getPatientsByPhysician($userId, $limit);
    }
    
    /**
     * Sync physicians for a patient
     */
    public function syncPhysicians($patientId, $attendingIds = [], $residentIds = [], $userId = null) {
        require_once __DIR__ . '/PatientPhysician.php';
        $patientPhysicianModel = new PatientPhysician();
        
        return $patientPhysicianModel->syncPhysicians($patientId, $attendingIds, $residentIds, $userId);
    }
    
    /**
     * Get primary attending physician for a patient
     */
    public function getPrimaryAttending($patientId) {
        require_once __DIR__ . '/PatientPhysician.php';
        $patientPhysicianModel = new PatientPhysician();
        
        return $patientPhysicianModel->getPrimaryPhysician($patientId, 'attending');
    }
    
    /**
     * Get primary resident for a patient
     */
    public function getPrimaryResident($patientId) {
        require_once __DIR__ . '/PatientPhysician.php';
        $patientPhysicianModel = new PatientPhysician();
        
        return $patientPhysicianModel->getPrimaryPhysician($patientId, 'resident');
    }
}
