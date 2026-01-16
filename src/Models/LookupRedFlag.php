<?php
namespace Models;

/**
 * Lookup Red Flag Model
 * Manages insertion complications/red flags
 * 
 * @version 1.2.0
 */
class LookupRedFlag extends BaseLookupModel {
    
    protected $table = 'lookup_red_flags';
    
    /**
     * Get red flags by severity
     * 
     * @param string $severity - 'mild', 'moderate', or 'severe'
     * @return array
     */
    public function getBySeverity($severity) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE deleted_at IS NULL AND active = 1 AND severity = ?
                ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$severity]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get critical red flags (requiring immediate action)
     * 
     * @return array
     */
    public function getCriticalFlags() {
        $sql = "SELECT * FROM {$this->table} 
                WHERE deleted_at IS NULL AND active = 1 AND requires_immediate_action = 1
                ORDER BY severity DESC, name ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get red flags grouped by severity
     * 
     * @return array
     */
    public function getGroupedBySeverity() {
        return $this->getGrouped('severity');
    }
    
    /**
     * Get red flags by IDs
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
                ORDER BY severity DESC, name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }
    
    /**
     * Get red flag names by IDs
     * 
     * @param array $ids
     * @return array
     */
    public function getNamesByIds($ids) {
        $flags = $this->getByIds($ids);
        return array_column($flags, 'name');
    }
    
    /**
     * Get severity badge color for display
     * 
     * @param string $severity
     * @return string
     */
    public static function getSeverityBadgeColor($severity) {
        $colors = [
            'mild' => 'warning',
            'moderate' => 'danger',
            'severe' => 'dark'
        ];
        
        return $colors[$severity] ?? 'secondary';
    }
}
