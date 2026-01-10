<?php
namespace Models;

use PDO;

/**
 * FunctionalOutcome Model
 * Manages functional assessments for catheters (Screen 4)
 */
class FunctionalOutcome extends BaseModel {
    
    protected $table = 'functional_outcomes';
    
    /**
     * Get all functional outcomes for a catheter
     */
    public function getCatheterOutcomes($catheterId) {
        $sql = "
            SELECT fo.*,
                   p.patient_name,
                   p.hospital_number,
                   c.catheter_type,
                   CONCAT(u.first_name, ' ', u.last_name) as created_by_name
            FROM {$this->table} fo
            LEFT JOIN catheters c ON fo.catheter_id = c.id
            LEFT JOIN patients p ON fo.patient_id = p.id
            LEFT JOIN users u ON fo.created_by = u.id
            WHERE fo.catheter_id = ? AND fo.deleted_at IS NULL
            ORDER BY fo.pod ASC, fo.entry_date ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catheterId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get all functional outcomes for a patient
     */
    public function getPatientOutcomes($patientId) {
        $sql = "
            SELECT fo.*,
                   c.catheter_type,
                   c.catheter_category,
                   c.status as catheter_status,
                   CONCAT(u.first_name, ' ', u.last_name) as created_by_name
            FROM {$this->table} fo
            LEFT JOIN catheters c ON fo.catheter_id = c.id
            LEFT JOIN users u ON fo.created_by = u.id
            WHERE fo.patient_id = ? AND fo.deleted_at IS NULL
            ORDER BY fo.entry_date DESC, fo.pod DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$patientId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get outcome with full details
     */
    public function getOutcomeWithDetails($outcomeId) {
        $sql = "
            SELECT fo.*,
                   p.patient_name,
                   p.hospital_number,
                   p.age,
                   p.gender,
                   c.catheter_type,
                   c.catheter_category,
                   c.date_of_insertion,
                   CONCAT(creator.first_name, ' ', creator.last_name) as created_by_name,
                   CONCAT(updater.first_name, ' ', updater.last_name) as updated_by_name
            FROM {$this->table} fo
            LEFT JOIN catheters c ON fo.catheter_id = c.id
            LEFT JOIN patients p ON fo.patient_id = p.id
            LEFT JOIN users creator ON fo.created_by = creator.id
            LEFT JOIN users updater ON fo.updated_by = updater.id
            WHERE fo.id = ? AND fo.deleted_at IS NULL
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$outcomeId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get latest outcome for a catheter
     */
    public function getLatestOutcome($catheterId) {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE catheter_id = ? AND deleted_at IS NULL
            ORDER BY entry_date DESC, pod DESC, created_at DESC
            LIMIT 1
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catheterId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get outcome for specific POD
     */
    public function getOutcomeByPOD($catheterId, $pod, $entryDate = null) {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE catheter_id = ? AND pod = ?
        ";
        
        $params = [$catheterId, $pod];
        
        if ($entryDate) {
            $sql .= " AND entry_date = ?";
            $params[] = $entryDate;
        }
        
        $sql .= " AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch();
    }
    
    /**
     * Check for sentinel events
     */
    public function hasSentinelEvents($outcomeId) {
        $stmt = $this->db->prepare("
            SELECT sentinel_events 
            FROM {$this->table} 
            WHERE id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$outcomeId]);
        
        $result = $stmt->fetch();
        return $result && $result['sentinel_events'] !== 'none';
    }
    
    /**
     * Get outcomes with complications (infections or sentinel events)
     */
    public function getOutcomesWithComplications($catheterId = null) {
        $sql = "
            SELECT fo.*,
                   p.patient_name,
                   p.hospital_number,
                   c.catheter_type
            FROM {$this->table} fo
            LEFT JOIN catheters c ON fo.catheter_id = c.id
            LEFT JOIN patients p ON fo.patient_id = p.id
            WHERE fo.deleted_at IS NULL
            AND (
                fo.catheter_site_infection != 'none' OR
                fo.sentinel_events != 'none'
            )
        ";
        
        $params = [];
        
        if ($catheterId) {
            $sql .= " AND fo.catheter_id = ?";
            $params[] = $catheterId;
        }
        
        $sql .= " ORDER BY fo.entry_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get outcomes with poor functional status
     */
    public function getOutcomesWithPoorFunctionalStatus($catheterId = null) {
        $sql = "
            SELECT fo.*,
                   p.patient_name,
                   p.hospital_number,
                   c.catheter_type
            FROM {$this->table} fo
            LEFT JOIN catheters c ON fo.catheter_id = c.id
            LEFT JOIN patients p ON fo.patient_id = p.id
            WHERE fo.deleted_at IS NULL
            AND (
                fo.incentive_spirometry IN ('unable') OR
                fo.ambulation = 'bedbound' OR
                fo.cough_ability = 'unable' OR
                fo.room_air_spo2 = 'requires_o2'
            )
        ";
        
        $params = [];
        
        if ($catheterId) {
            $sql .= " AND fo.catheter_id = ?";
            $params[] = $catheterId;
        }
        
        $sql .= " ORDER BY fo.entry_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get statistics for dashboard
     */
    public function getStatistics() {
        $stats = [];
        
        // Total outcomes recorded
        $stmt = $this->db->query("
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE deleted_at IS NULL
        ");
        $stats['total_outcomes'] = $stmt->fetch()['count'];
        
        // Infection rate
        $stmt = $this->db->query("
            SELECT 
                SUM(CASE WHEN catheter_site_infection != 'none' THEN 1 ELSE 0 END) as infections,
                COUNT(*) as total
            FROM {$this->table}
            WHERE deleted_at IS NULL
        ");
        $result = $stmt->fetch();
        $stats['infection_rate'] = $result['total'] > 0 
            ? round(($result['infections'] / $result['total']) * 100, 1) 
            : 0;
        
        // Sentinel events rate
        $stmt = $this->db->query("
            SELECT 
                SUM(CASE WHEN sentinel_events != 'none' THEN 1 ELSE 0 END) as events,
                COUNT(*) as total
            FROM {$this->table}
            WHERE deleted_at IS NULL
        ");
        $result = $stmt->fetch();
        $stats['sentinel_event_rate'] = $result['total'] > 0 
            ? round(($result['events'] / $result['total']) * 100, 1) 
            : 0;
        
        // Functional status summary
        $stmt = $this->db->query("
            SELECT 
                SUM(CASE WHEN ambulation = 'independent' THEN 1 ELSE 0 END) as independent_ambulation,
                SUM(CASE WHEN ambulation = 'bedbound' THEN 1 ELSE 0 END) as bedbound,
                SUM(CASE WHEN room_air_spo2 = 'yes' THEN 1 ELSE 0 END) as room_air_ok,
                SUM(CASE WHEN room_air_spo2 = 'requires_o2' THEN 1 ELSE 0 END) as requires_oxygen,
                COUNT(*) as total
            FROM {$this->table}
            WHERE deleted_at IS NULL
        ");
        $result = $stmt->fetch();
        $stats['functional_status'] = $result;
        
        return $stats;
    }
    
    /**
     * Search functional outcomes
     */
    public function search($query, $limit = 50) {
        $searchTerm = "%{$query}%";
        
        $sql = "
            SELECT fo.*,
                   p.patient_name,
                   p.hospital_number,
                   c.catheter_type
            FROM {$this->table} fo
            LEFT JOIN catheters c ON fo.catheter_id = c.id
            LEFT JOIN patients p ON fo.patient_id = p.id
            WHERE fo.deleted_at IS NULL
            AND (
                p.patient_name LIKE ? OR
                p.hospital_number LIKE ? OR
                fo.clinical_notes LIKE ? OR
                fo.sentinel_event_details LIKE ?
            )
            ORDER BY fo.entry_date DESC
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Calculate functional score (0-100)
     * Higher score = better functional status
     */
    public function calculateFunctionalScore($outcomeId) {
        $outcome = $this->find($outcomeId);
        
        if (!$outcome) {
            return null;
        }
        
        $score = 0;
        
        // Incentive spirometry (0-30 points)
        switch ($outcome['incentive_spirometry']) {
            case 'yes':
                $score += 30;
                break;
            case 'partial':
                $score += 15;
                break;
        }
        
        // Ambulation (0-30 points)
        switch ($outcome['ambulation']) {
            case 'independent':
                $score += 30;
                break;
            case 'assisted':
                $score += 15;
                break;
        }
        
        // Cough ability (0-20 points)
        switch ($outcome['cough_ability']) {
            case 'effective':
                $score += 20;
                break;
            case 'weak':
                $score += 10;
                break;
        }
        
        // Room air SpO2 (0-20 points)
        if ($outcome['room_air_spo2'] === 'yes') {
            $score += 20;
        } elseif ($outcome['room_air_spo2'] === 'no' && $outcome['spo2_value'] >= 95) {
            $score += 10;
        }
        
        // Deduct for complications
        if ($outcome['catheter_site_infection'] !== 'none') {
            $score -= 10;
        }
        
        if ($outcome['sentinel_events'] !== 'none') {
            $score -= 15;
        }
        
        return max(0, min(100, $score));
    }
}
