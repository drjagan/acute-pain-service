<?php
namespace Models;

use PDO;

/**
 * Catheter Model
 * Manages catheter insertion records
 */
class Catheter extends BaseModel {
    
    protected $table = 'catheters';
    
    /**
     * Catheter type hierarchy
     * Category -> Specific Types
     */
    public static function getCatheterTypes() {
        return [
            'epidural' => [
                'cervical' => 'Cervical Epidural',
                'upper_thoracic' => 'Upper Thoracic (T1-T6)',
                'mid_thoracic' => 'Mid Thoracic (T7-T9)',
                'lower_thoracic' => 'Lower Thoracic (T10-T12)',
                'lumbar' => 'Lumbar Epidural',
                'caudal' => 'Caudal Epidural'
            ],
            'peripheral_nerve' => [
                'interscalene' => 'Interscalene Block',
                'supraclavicular' => 'Supraclavicular Block',
                'costoclavicular' => 'Costoclavicular Block',
                'axillary' => 'Axillary Block',
                'femoral' => 'Femoral Nerve Block',
                'adductor' => 'Adductor Canal Block',
                'lumbar_plexus' => 'Lumbar Plexus Block',
                'sacral_plexus' => 'Sacral Plexus Block',
                'popliteal_sciatic' => 'Popliteal Sciatic Block'
            ],
            'fascial_plane' => [
                'pec2' => 'PEC2 Block',
                'sap' => 'Serratus Anterior Plane (SAP)',
                'esp' => 'Erector Spinae Plane (ESP)',
                'ql' => 'Quadratus Lumborum (QL)',
                'tap' => 'Transversus Abdominis Plane (TAP)',
                'rectus_sheath' => 'Rectus Sheath Block'
            ]
        ];
    }
    
    /**
     * Get category display names
     */
    public static function getCategoryNames() {
        return [
            'epidural' => 'Epidural',
            'peripheral_nerve' => 'Peripheral Nerve Block',
            'fascial_plane' => 'Fascial Plane Block'
        ];
    }
    
    /**
     * Get catheters for a specific patient
     */
    public function getPatientCatheters($patientId, $activeOnly = false) {
        $sql = "
            SELECT c.*, 
                   p.patient_name, 
                   p.hospital_number,
                   u.full_name as created_by_name
            FROM {$this->table} c
            LEFT JOIN patients p ON c.patient_id = p.id
            LEFT JOIN users u ON c.created_by = u.id
            WHERE c.patient_id = ? AND c.deleted_at IS NULL
        ";
        
        if ($activeOnly) {
            $sql .= " AND c.status = 'active'";
        }
        
        $sql .= " ORDER BY c.date_of_insertion DESC, c.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$patientId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get catheter with patient details
     */
    public function getCatheterWithDetails($catheterId) {
        $sql = "
            SELECT c.*, 
                   p.patient_name, 
                   p.hospital_number,
                   p.age,
                   p.gender,
                   p.diagnosis,
                   creator.full_name as created_by_name,
                   updater.full_name as updated_by_name
            FROM {$this->table} c
            LEFT JOIN patients p ON c.patient_id = p.id
            LEFT JOIN users creator ON c.created_by = creator.id
            LEFT JOIN users updater ON c.updated_by = updater.id
            WHERE c.id = ? AND c.deleted_at IS NULL
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catheterId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get all active catheters (dashboard view)
     */
    public function getActiveCatheters($limit = 50) {
        $sql = "
            SELECT c.*, 
                   p.patient_name, 
                   p.hospital_number,
                   p.age,
                   p.gender,
                   DATEDIFF(CURDATE(), c.date_of_insertion) as days_inserted
            FROM {$this->table} c
            LEFT JOIN patients p ON c.patient_id = p.id
            WHERE c.status = 'active' AND c.deleted_at IS NULL
            ORDER BY c.date_of_insertion ASC
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get catheters by category
     */
    public function getCathetersByCategory($category, $limit = 100) {
        $sql = "
            SELECT c.*, 
                   p.patient_name, 
                   p.hospital_number
            FROM {$this->table} c
            LEFT JOIN patients p ON c.patient_id = p.id
            WHERE c.catheter_category = ? AND c.deleted_at IS NULL
            ORDER BY c.date_of_insertion DESC
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category, $limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get statistics for dashboard
     */
    public function getStatistics() {
        $stats = [];
        
        // Total active catheters
        $stmt = $this->db->query("
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE status = 'active' AND deleted_at IS NULL
        ");
        $stats['active_count'] = $stmt->fetch()['count'];
        
        // Catheters by category
        $stmt = $this->db->query("
            SELECT catheter_category, COUNT(*) as count 
            FROM {$this->table} 
            WHERE status = 'active' AND deleted_at IS NULL
            GROUP BY catheter_category
        ");
        $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Catheters inserted today
        $stmt = $this->db->query("
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE DATE(date_of_insertion) = CURDATE() AND deleted_at IS NULL
        ");
        $stats['inserted_today'] = $stmt->fetch()['count'];
        
        // Average catheter duration
        $stmt = $this->db->query("
            SELECT AVG(DATEDIFF(CURDATE(), date_of_insertion)) as avg_days
            FROM {$this->table} 
            WHERE status = 'active' AND deleted_at IS NULL
        ");
        $stats['avg_duration'] = round($stmt->fetch()['avg_days'] ?? 0, 1);
        
        return $stats;
    }
    
    /**
     * Update catheter status
     */
    public function updateStatus($catheterId, $status, $userId) {
        $sql = "
            UPDATE {$this->table} 
            SET status = ?, updated_by = ?, updated_at = NOW()
            WHERE id = ? AND deleted_at IS NULL
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $userId, $catheterId]);
    }
    
    /**
     * Check if patient has active catheter
     */
    public function hasActiveCatheter($patientId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE patient_id = ? AND status = 'active' AND deleted_at IS NULL
        ");
        $stmt->execute([$patientId]);
        
        return $stmt->fetch()['count'] > 0;
    }
    
    /**
     * Search catheters
     */
    public function search($query, $limit = 50) {
        $searchTerm = "%{$query}%";
        
        $sql = "
            SELECT c.*, 
                   p.patient_name, 
                   p.hospital_number
            FROM {$this->table} c
            LEFT JOIN patients p ON c.patient_id = p.id
            WHERE c.deleted_at IS NULL
            AND (
                p.patient_name LIKE ? OR
                p.hospital_number LIKE ? OR
                c.catheter_type LIKE ? OR
                c.indication LIKE ?
            )
            ORDER BY c.date_of_insertion DESC
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
        
        return $stmt->fetchAll();
    }
}
