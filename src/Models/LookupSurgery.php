<?php
namespace Models;

/**
 * Lookup Surgery Model
 * Manages surgical procedures
 * 
 * @version 1.2.0
 */
class LookupSurgery extends BaseLookupModel {
    
    protected $table = 'lookup_surgeries';
    
    /**
     * Get all surgeries with specialty name
     * 
     * @return array
     */
    public function getAllWithSpecialty() {
        $sql = "SELECT ls.*, lsp.name as specialty_name, lsp.code as specialty_code
                FROM {$this->table} ls
                LEFT JOIN lookup_specialties lsp ON ls.specialty_id = lsp.id
                WHERE ls.deleted_at IS NULL
                ORDER BY lsp.name ASC, ls.sort_order ASC, ls.name ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get active surgeries with specialty name
     * 
     * @return array
     */
    public function getActiveWithSpecialty() {
        $sql = "SELECT ls.*, lsp.name as specialty_name, lsp.code as specialty_code
                FROM {$this->table} ls
                LEFT JOIN lookup_specialties lsp ON ls.specialty_id = lsp.id
                WHERE ls.deleted_at IS NULL AND ls.active = 1 AND lsp.active = 1
                ORDER BY lsp.name ASC, ls.sort_order ASC, ls.name ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get surgeries by specialty
     * 
     * @param int $specialtyId
     * @return array
     */
    public function getBySpecialty($specialtyId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE specialty_id = ? AND deleted_at IS NULL AND active = 1
                ORDER BY sort_order ASC, name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$specialtyId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get surgeries grouped by specialty
     * 
     * @return array
     */
    public function getGroupedBySpecialty() {
        $surgeries = $this->getActiveWithSpecialty();
        
        $grouped = [];
        foreach ($surgeries as $surgery) {
            $specialtyName = $surgery['specialty_name'] ?? 'Uncategorized';
            if (!isset($grouped[$specialtyName])) {
                $grouped[$specialtyName] = [];
            }
            $grouped[$specialtyName][] = $surgery;
        }
        
        return $grouped;
    }
    
    /**
     * Get dropdown options grouped by specialty (for select2)
     * 
     * @return array
     */
    public function getDropdownOptionsGrouped() {
        $grouped = $this->getGroupedBySpecialty();
        
        $options = [];
        foreach ($grouped as $specialty => $surgeries) {
            $options[$specialty] = [];
            foreach ($surgeries as $surgery) {
                $options[$specialty][$surgery['id']] = $surgery['name'];
            }
        }
        
        return $options;
    }
}
