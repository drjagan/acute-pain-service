<?php
namespace Models;

/**
 * Lookup Catheter Indication Model
 * Manages catheter insertion indications
 * 
 * @version 1.2.0
 */
class LookupCatheterIndication extends BaseLookupModel {
    
    protected $table = 'lookup_catheter_indications';
    
    /**
     * Get common indications (frequently used)
     * 
     * @return array
     */
    public function getCommonIndications() {
        $sql = "SELECT * FROM {$this->table} 
                WHERE deleted_at IS NULL AND active = 1 AND is_common = 1
                ORDER BY sort_order ASC, name ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all indications grouped by frequency
     * 
     * @return array - ['common' => [], 'other' => []]
     */
    public function getGroupedByFrequency() {
        $all = $this->getActive();
        
        $grouped = [
            'common' => [],
            'other' => []
        ];
        
        foreach ($all as $indication) {
            if ($indication['is_common']) {
                $grouped['common'][] = $indication;
            } else {
                $grouped['other'][] = $indication;
            }
        }
        
        return $grouped;
    }
}
