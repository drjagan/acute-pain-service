<?php
namespace Models;

use PDO;

/**
 * DrugRegime Model
 * Manages drug regimes for catheters (Screen 3)
 */
class DrugRegime extends BaseModel {
    
    protected $table = 'drug_regimes';
    
    /**
     * Get all drug regimes for a catheter
     */
    public function getCatheterRegimes($catheterId) {
        $sql = "
            SELECT dr.*,
                   p.patient_name,
                   p.hospital_number,
                   c.catheter_type,
                   CONCAT(u.first_name, ' ', u.last_name) as created_by_name
            FROM {$this->table} dr
            LEFT JOIN catheters c ON dr.catheter_id = c.id
            LEFT JOIN patients p ON dr.patient_id = p.id
            LEFT JOIN users u ON dr.created_by = u.id
            WHERE dr.catheter_id = ? AND dr.deleted_at IS NULL
            ORDER BY dr.pod ASC, dr.entry_date ASC, dr.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catheterId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get all drug regimes for a patient
     */
    public function getPatientRegimes($patientId) {
        $sql = "
            SELECT dr.*,
                   c.catheter_type,
                   c.catheter_category,
                   c.status as catheter_status,
                   CONCAT(u.first_name, ' ', u.last_name) as created_by_name
            FROM {$this->table} dr
            LEFT JOIN catheters c ON dr.catheter_id = c.id
            LEFT JOIN users u ON dr.created_by = u.id
            WHERE dr.patient_id = ? AND dr.deleted_at IS NULL
            ORDER BY dr.entry_date DESC, dr.pod DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$patientId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get regime with full details
     */
    public function getRegimeWithDetails($regimeId) {
        $sql = "
            SELECT dr.*,
                   p.patient_name,
                   p.hospital_number,
                   p.age,
                   p.gender,
                   c.catheter_type,
                   c.catheter_category,
                   c.date_of_insertion,
                   CONCAT(creator.first_name, ' ', creator.last_name) as created_by_name,
                   CONCAT(updater.first_name, ' ', updater.last_name) as updated_by_name
            FROM {$this->table} dr
            LEFT JOIN catheters c ON dr.catheter_id = c.id
            LEFT JOIN patients p ON dr.patient_id = p.id
            LEFT JOIN users creator ON dr.created_by = creator.id
            LEFT JOIN users updater ON dr.updated_by = updater.id
            WHERE dr.id = ? AND dr.deleted_at IS NULL
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$regimeId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get latest regime for a catheter
     */
    public function getLatestRegime($catheterId) {
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
     * Get regime for specific POD
     */
    public function getRegimeByPOD($catheterId, $pod, $entryDate = null) {
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
     * Check if effective analgesia
     */
    public function hasEffectiveAnalgesia($regimeId) {
        $stmt = $this->db->prepare("
            SELECT effective_analgesia 
            FROM {$this->table} 
            WHERE id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$regimeId]);
        
        $result = $stmt->fetch();
        return $result ? (bool)$result['effective_analgesia'] : false;
    }
    
    /**
     * Get regimes with side effects
     */
    public function getRegimesWithSideEffects($catheterId = null) {
        $sql = "
            SELECT dr.*,
                   p.patient_name,
                   p.hospital_number,
                   c.catheter_type
            FROM {$this->table} dr
            LEFT JOIN catheters c ON dr.catheter_id = c.id
            LEFT JOIN patients p ON dr.patient_id = p.id
            WHERE dr.deleted_at IS NULL
            AND (
                dr.hypotension != 'none' OR
                dr.bradycardia != 'none' OR
                dr.sensory_motor_deficit != 'none' OR
                dr.nausea_vomiting != 'none'
            )
        ";
        
        $params = [];
        
        if ($catheterId) {
            $sql .= " AND dr.catheter_id = ?";
            $params[] = $catheterId;
        }
        
        $sql .= " ORDER BY dr.entry_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get statistics for dashboard
     */
    public function getStatistics() {
        $stats = [];
        
        // Total regimes recorded
        $stmt = $this->db->query("
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE deleted_at IS NULL
        ");
        $stats['total_regimes'] = $stmt->fetch()['count'];
        
        // Effective analgesia rate
        $stmt = $this->db->query("
            SELECT 
                SUM(effective_analgesia = 1) as effective,
                COUNT(*) as total
            FROM {$this->table}
            WHERE deleted_at IS NULL
        ");
        $result = $stmt->fetch();
        $stats['effective_rate'] = $result['total'] > 0 
            ? round(($result['effective'] / $result['total']) * 100, 1) 
            : 0;
        
        // Troubleshooting activation rate
        $stmt = $this->db->query("
            SELECT 
                SUM(troubleshooting_activated = 1) as troubleshooting,
                COUNT(*) as total
            FROM {$this->table}
            WHERE deleted_at IS NULL
        ");
        $result = $stmt->fetch();
        $stats['troubleshooting_rate'] = $result['total'] > 0 
            ? round(($result['troubleshooting'] / $result['total']) * 100, 1) 
            : 0;
        
        // Side effects summary
        $stmt = $this->db->query("
            SELECT 
                SUM(CASE WHEN hypotension != 'none' THEN 1 ELSE 0 END) as hypotension_count,
                SUM(CASE WHEN bradycardia != 'none' THEN 1 ELSE 0 END) as bradycardia_count,
                SUM(CASE WHEN sensory_motor_deficit != 'none' THEN 1 ELSE 0 END) as deficit_count,
                SUM(CASE WHEN nausea_vomiting != 'none' THEN 1 ELSE 0 END) as nausea_count
            FROM {$this->table}
            WHERE deleted_at IS NULL
        ");
        $stats['side_effects'] = $stmt->fetch();
        
        return $stats;
    }
    
    /**
     * Search drug regimes
     */
    public function search($query, $limit = 50) {
        $searchTerm = "%{$query}%";
        
        $sql = "
            SELECT dr.*,
                   p.patient_name,
                   p.hospital_number,
                   c.catheter_type
            FROM {$this->table} dr
            LEFT JOIN catheters c ON dr.catheter_id = c.id
            LEFT JOIN patients p ON dr.patient_id = p.id
            WHERE dr.deleted_at IS NULL
            AND (
                p.patient_name LIKE ? OR
                p.hospital_number LIKE ? OR
                dr.drug LIKE ? OR
                dr.adjuvant LIKE ?
            )
            ORDER BY dr.entry_date DESC
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Calculate pain score improvement
     */
    public function calculatePainImprovement($regimeId) {
        $regime = $this->find($regimeId);
        
        if (!$regime) {
            return null;
        }
        
        $staticImprovement = $regime['baseline_vnrs_static'] - $regime['vnrs_15min_static'];
        $dynamicImprovement = $regime['baseline_vnrs_dynamic'] - $regime['vnrs_15min_dynamic'];
        
        return [
            'static' => $staticImprovement,
            'dynamic' => $dynamicImprovement,
            'average' => ($staticImprovement + $dynamicImprovement) / 2
        ];
    }
}
