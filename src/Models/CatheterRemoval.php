<?php
namespace Models;

use PDO;

/**
 * CatheterRemoval Model
 * Manages catheter removal documentation (Screen 5)
 */
class CatheterRemoval extends BaseModel {
    
    protected $table = 'catheter_removals';
    
    /**
     * Get removal record for a specific catheter
     */
    public function getRemovalByCatheter($catheterId) {
        $sql = "
            SELECT cr.*,
                   p.patient_name,
                   p.hospital_number,
                   c.catheter_type,
                   c.catheter_category,
                   c.date_of_insertion,
                   CONCAT(u.first_name, ' ', u.last_name) as created_by_name
            FROM {$this->table} cr
            LEFT JOIN catheters c ON cr.catheter_id = c.id
            LEFT JOIN patients p ON cr.patient_id = p.id
            LEFT JOIN users u ON cr.created_by = u.id
            WHERE cr.catheter_id = ? AND cr.deleted_at IS NULL
            LIMIT 1
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catheterId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get all removals for a patient
     */
    public function getPatientRemovals($patientId) {
        $sql = "
            SELECT cr.*,
                   c.catheter_type,
                   c.catheter_category,
                   c.date_of_insertion,
                   CONCAT(u.first_name, ' ', u.last_name) as created_by_name
            FROM {$this->table} cr
            LEFT JOIN catheters c ON cr.catheter_id = c.id
            LEFT JOIN users u ON cr.created_by = u.id
            WHERE cr.patient_id = ? AND cr.deleted_at IS NULL
            ORDER BY cr.date_of_removal DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$patientId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get removal with full details
     */
    public function getRemovalWithDetails($removalId) {
        $sql = "
            SELECT cr.*,
                   p.patient_name,
                   p.hospital_number,
                   p.age,
                   p.gender,
                   c.catheter_type,
                   c.catheter_category,
                   c.date_of_insertion,
                   c.status as catheter_status,
                   CONCAT(creator.first_name, ' ', creator.last_name) as created_by_name,
                   CONCAT(updater.first_name, ' ', updater.last_name) as updated_by_name
            FROM {$this->table} cr
            LEFT JOIN catheters c ON cr.catheter_id = c.id
            LEFT JOIN patients p ON cr.patient_id = p.id
            LEFT JOIN users creator ON cr.created_by = creator.id
            LEFT JOIN users updater ON cr.updated_by = updater.id
            WHERE cr.id = ? AND cr.deleted_at IS NULL
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$removalId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Check if catheter has removal record
     */
    public function catheterHasRemoval($catheterId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE catheter_id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$catheterId]);
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Get removals with complications
     */
    public function getRemovalsWithComplications() {
        $sql = "
            SELECT cr.*,
                   p.patient_name,
                   p.hospital_number,
                   c.catheter_type
            FROM {$this->table} cr
            LEFT JOIN catheters c ON cr.catheter_id = c.id
            LEFT JOIN patients p ON cr.patient_id = p.id
            WHERE cr.deleted_at IS NULL
            AND cr.removal_complications IS NOT NULL
            AND cr.removal_complications != ''
            ORDER BY cr.date_of_removal DESC
        ";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get removals by indication
     */
    public function getRemovalsByIndication($indication) {
        $sql = "
            SELECT cr.*,
                   p.patient_name,
                   p.hospital_number,
                   c.catheter_type
            FROM {$this->table} cr
            LEFT JOIN catheters c ON cr.catheter_id = c.id
            LEFT JOIN patients p ON cr.patient_id = p.id
            WHERE cr.indication = ? AND cr.deleted_at IS NULL
            ORDER BY cr.date_of_removal DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$indication]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get statistics for dashboard
     */
    public function getStatistics() {
        $stats = [];
        
        // Total removals
        $stmt = $this->db->query("
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE deleted_at IS NULL
        ");
        $stats['total_removals'] = $stmt->fetch()['count'];
        
        // Average catheter days
        $stmt = $this->db->query("
            SELECT AVG(number_of_catheter_days) as avg_days,
                   MIN(number_of_catheter_days) as min_days,
                   MAX(number_of_catheter_days) as max_days
            FROM {$this->table}
            WHERE deleted_at IS NULL
        ");
        $stats['catheter_days'] = $stmt->fetch();
        
        // Removal indications breakdown
        $stmt = $this->db->query("
            SELECT indication, COUNT(*) as count
            FROM {$this->table}
            WHERE deleted_at IS NULL
            GROUP BY indication
            ORDER BY count DESC
        ");
        $stats['indications'] = $stmt->fetchAll();
        
        // Complications rate
        $stmt = $this->db->query("
            SELECT 
                SUM(CASE WHEN removal_complications IS NOT NULL AND removal_complications != '' THEN 1 ELSE 0 END) as with_complications,
                COUNT(*) as total
            FROM {$this->table}
            WHERE deleted_at IS NULL
        ");
        $result = $stmt->fetch();
        $stats['complication_rate'] = $result['total'] > 0 
            ? round(($result['with_complications'] / $result['total']) * 100, 1) 
            : 0;
        
        // Catheter tip intact rate
        $stmt = $this->db->query("
            SELECT 
                SUM(catheter_tip_intact = 1) as intact,
                COUNT(*) as total
            FROM {$this->table}
            WHERE deleted_at IS NULL
        ");
        $result = $stmt->fetch();
        $stats['tip_intact_rate'] = $result['total'] > 0 
            ? round(($result['intact'] / $result['total']) * 100, 1) 
            : 0;
        
        // Patient satisfaction breakdown
        $stmt = $this->db->query("
            SELECT patient_satisfaction, COUNT(*) as count
            FROM {$this->table}
            WHERE deleted_at IS NULL AND patient_satisfaction IS NOT NULL
            GROUP BY patient_satisfaction
            ORDER BY FIELD(patient_satisfaction, 'excellent', 'good', 'fair', 'poor')
        ");
        $stats['satisfaction'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    /**
     * Search removals
     */
    public function search($query, $limit = 50) {
        $searchTerm = "%{$query}%";
        
        $sql = "
            SELECT cr.*,
                   p.patient_name,
                   p.hospital_number,
                   c.catheter_type
            FROM {$this->table} cr
            LEFT JOIN catheters c ON cr.catheter_id = c.id
            LEFT JOIN patients p ON cr.patient_id = p.id
            WHERE cr.deleted_at IS NULL
            AND (
                p.patient_name LIKE ? OR
                p.hospital_number LIKE ? OR
                cr.indication_notes LIKE ? OR
                cr.removal_complications LIKE ? OR
                cr.final_notes LIKE ?
            )
            ORDER BY cr.date_of_removal DESC
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get indication display names
     */
    public static function getIndicationNames() {
        return [
            'adequate_analgesia' => 'Adequate Analgesia Achieved',
            'adverse_effects' => 'Adverse Effects',
            'patient_request' => 'Patient Request',
            'infection' => 'Infection',
            'catheter_displacement' => 'Catheter Displacement',
            'surgical_completion' => 'Surgical Completion',
            'other' => 'Other'
        ];
    }
    
    /**
     * Calculate average catheter duration by type
     */
    public function getAverageDurationByType() {
        $sql = "
            SELECT c.catheter_category,
                   c.catheter_type,
                   AVG(cr.number_of_catheter_days) as avg_days,
                   COUNT(*) as count
            FROM {$this->table} cr
            LEFT JOIN catheters c ON cr.catheter_id = c.id
            WHERE cr.deleted_at IS NULL
            GROUP BY c.catheter_category, c.catheter_type
            ORDER BY c.catheter_category, avg_days DESC
        ";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
