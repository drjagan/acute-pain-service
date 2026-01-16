<?php
namespace Models;

/**
 * Lookup Comorbidity Model
 * Manages patient comorbidities
 * 
 * @version 1.2.0
 */
class LookupComorbidity extends BaseLookupModel {
    
    protected $table = 'lookup_comorbidities';
    
    /**
     * Get comorbidities by IDs
     * 
     * @param array $ids
     * @return array
     */
    public function getByIds($ids) {
        if (empty($ids)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM {$this->table} 
                WHERE id IN ({$placeholders}) AND deleted_at IS NULL
                ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }
    
    /**
     * Get comorbidity names by IDs
     * 
     * @param array $ids
     * @return array
     */
    public function getNamesByIds($ids) {
        $comorbidities = $this->getByIds($ids);
        return array_column($comorbidities, 'name');
    }
}
