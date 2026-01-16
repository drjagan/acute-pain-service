<?php
namespace Models;

/**
 * Lookup Adjuvant Model
 * Manages drug adjuvants
 * 
 * @version 1.2.0
 */
class LookupAdjuvant extends BaseLookupModel {
    
    protected $table = 'lookup_adjuvants';
    
    /**
     * Get adjuvant with dosage information
     * 
     * @param int $id
     * @return array|false
     */
    public function getAdjuvantWithDosage($id) {
        $adjuvant = $this->find($id);
        
        if ($adjuvant) {
            $adjuvant['has_typical_dose'] = !empty($adjuvant['typical_dose']);
            $adjuvant['dosage_info'] = $this->formatDosageInfo($adjuvant);
        }
        
        return $adjuvant;
    }
    
    /**
     * Format dosage information for display
     * 
     * @param array $adjuvant
     * @return string
     */
    protected function formatDosageInfo($adjuvant) {
        if (!empty($adjuvant['typical_dose'])) {
            return "Typical: {$adjuvant['typical_dose']}{$adjuvant['unit']}";
        }
        
        return '';
    }
}
