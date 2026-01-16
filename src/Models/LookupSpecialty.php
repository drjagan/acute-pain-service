<?php
namespace Models;

/**
 * Lookup Specialty Model
 * Manages medical and surgical specialties
 * 
 * @version 1.2.0
 */
class LookupSpecialty extends BaseLookupModel {
    
    protected $table = 'lookup_specialties';
    
    /**
     * Get specialties with surgery count
     * 
     * @return array
     */
    public function getAllWithSurgeryCount() {
        $sql = "SELECT ls.*, 
                       COUNT(lsurg.id) as surgery_count
                FROM {$this->table} ls
                LEFT JOIN lookup_surgeries lsurg ON ls.id = lsurg.specialty_id 
                    AND lsurg.deleted_at IS NULL
                WHERE ls.deleted_at IS NULL
                GROUP BY ls.id
                ORDER BY ls.sort_order ASC, ls.name ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get active specialties with surgery count
     * 
     * @return array
     */
    public function getActiveWithSurgeryCount() {
        $sql = "SELECT ls.*, 
                       COUNT(lsurg.id) as surgery_count
                FROM {$this->table} ls
                LEFT JOIN lookup_surgeries lsurg ON ls.id = lsurg.specialty_id 
                    AND lsurg.deleted_at IS NULL AND lsurg.active = 1
                WHERE ls.deleted_at IS NULL AND ls.active = 1
                GROUP BY ls.id
                ORDER BY ls.sort_order ASC, ls.name ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get surgeries for a specialty
     * 
     * @param int $specialtyId
     * @return array
     */
    public function getSurgeries($specialtyId) {
        $sql = "SELECT * FROM lookup_surgeries 
                WHERE specialty_id = ? AND deleted_at IS NULL AND active = 1
                ORDER BY sort_order ASC, name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$specialtyId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if specialty has surgeries
     * 
     * @param int $specialtyId
     * @return bool
     */
    public function hasSurgeries($specialtyId) {
        $sql = "SELECT COUNT(*) as count FROM lookup_surgeries 
                WHERE specialty_id = ? AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$specialtyId]);
        
        return $stmt->fetch()['count'] > 0;
    }
    
    /**
     * Override delete to prevent deletion if specialty has surgeries
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        if ($this->hasSurgeries($id)) {
            return false;
        }
        
        return parent::delete($id);
    }
}
