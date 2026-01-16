<?php
namespace Models;

/**
 * Lookup Removal Indication Model
 * Manages catheter removal indications
 * Replaces the hardcoded getIndicationNames() in CatheterRemoval model
 * 
 * @version 1.2.0
 */
class LookupRemovalIndication extends BaseLookupModel {
    
    protected $table = 'lookup_removal_indications';
    
    /**
     * Get indications as code => name pairs (for backward compatibility)
     * 
     * @return array
     */
    public static function getIndicationNames() {
        $model = new self();
        $indications = $model->getActive();
        
        $names = [];
        foreach ($indications as $indication) {
            $names[$indication['code']] = $indication['name'];
        }
        
        return $names;
    }
    
    /**
     * Get planned removal indications
     * 
     * @return array
     */
    public function getPlannedIndications() {
        $sql = "SELECT * FROM {$this->table} 
                WHERE deleted_at IS NULL AND active = 1 AND is_planned = 1
                ORDER BY sort_order ASC, name ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get unplanned removal indications
     * 
     * @return array
     */
    public function getUnplannedIndications() {
        $sql = "SELECT * FROM {$this->table} 
                WHERE deleted_at IS NULL AND active = 1 AND is_planned = 0
                ORDER BY sort_order ASC, name ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get indications grouped by planned/unplanned
     * 
     * @return array - ['planned' => [], 'unplanned' => []]
     */
    public function getGroupedByType() {
        $all = $this->getActive();
        
        $grouped = [
            'planned' => [],
            'unplanned' => []
        ];
        
        foreach ($all as $indication) {
            if ($indication['is_planned']) {
                $grouped['planned'][] = $indication;
            } else {
                $grouped['unplanned'][] = $indication;
            }
        }
        
        return $grouped;
    }
    
    /**
     * Check if indication requires additional notes
     * 
     * @param string $code - Indication code
     * @return bool
     */
    public function requiresNotes($code) {
        $indication = $this->findByCode($code);
        return $indication ? (bool)$indication['requires_notes'] : false;
    }
}
