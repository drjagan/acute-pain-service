<?php
namespace Models;

/**
 * Lookup Drug Model
 * Manages drugs/medications
 * 
 * @version 1.2.0
 */
class LookupDrug extends BaseLookupModel {
    
    protected $table = 'lookup_drugs';
    
    /**
     * Get drug with dosage information
     * 
     * @param int $id
     * @return array|false
     */
    public function getDrugWithDosage($id) {
        $drug = $this->find($id);
        
        if ($drug) {
            $drug['has_concentration'] = !empty($drug['typical_concentration']);
            $drug['has_max_dose'] = !empty($drug['max_dose']);
            $drug['dosage_info'] = $this->formatDosageInfo($drug);
        }
        
        return $drug;
    }
    
    /**
     * Format dosage information for display
     * 
     * @param array $drug
     * @return string
     */
    protected function formatDosageInfo($drug) {
        $info = [];
        
        if (!empty($drug['typical_concentration'])) {
            $info[] = "Typical: {$drug['typical_concentration']}{$drug['unit']}";
        }
        
        if (!empty($drug['max_dose'])) {
            $info[] = "Max: {$drug['max_dose']}{$drug['unit']}";
        }
        
        return implode(', ', $info);
    }
    
    /**
     * Search drugs by name or generic name
     * 
     * @param string $search
     * @return array
     */
    public function search($search) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE deleted_at IS NULL AND active = 1
                AND (name LIKE ? OR generic_name LIKE ?)
                ORDER BY name ASC";
        
        $searchTerm = "%{$search}%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
}
