<?php
namespace Models;

/**
 * Lookup Sentinel Event Model
 * Manages adverse events and complications
 * 
 * @version 1.2.0
 */
class LookupSentinelEvent extends BaseLookupModel {
    
    protected $table = 'lookup_sentinel_events';
    
    /**
     * Get events by category
     * 
     * @param string $category - Event category
     * @return array
     */
    public function getByCategory($category) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE deleted_at IS NULL AND active = 1 AND category = ?
                ORDER BY severity DESC, sort_order ASC, name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get events by severity
     * 
     * @param string $severity - Event severity
     * @return array
     */
    public function getBySeverity($severity) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE deleted_at IS NULL AND active = 1 AND severity = ?
                ORDER BY category ASC, sort_order ASC, name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$severity]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get critical events (requiring immediate action)
     * 
     * @return array
     */
    public function getCriticalEvents() {
        $sql = "SELECT * FROM {$this->table} 
                WHERE deleted_at IS NULL AND active = 1 AND requires_immediate_action = 1
                ORDER BY severity DESC, sort_order ASC, name ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get events grouped by category
     * 
     * @return array
     */
    public function getGroupedByCategory() {
        return $this->getGrouped('category');
    }
    
    /**
     * Get events grouped by severity
     * 
     * @return array
     */
    public function getGroupedBySeverity() {
        return $this->getGrouped('severity');
    }
    
    /**
     * Get severity badge color
     * 
     * @param string $severity
     * @return string
     */
    public static function getSeverityBadgeColor($severity) {
        $colors = [
            'mild' => 'success',
            'moderate' => 'warning',
            'severe' => 'danger',
            'critical' => 'dark'
        ];
        
        return $colors[$severity] ?? 'secondary';
    }
    
    /**
     * Get category icon
     * 
     * @param string $category
     * @return string
     */
    public static function getCategoryIcon($category) {
        $icons = [
            'infection' => 'bi-shield-exclamation',
            'neurological' => 'bi-activity',
            'cardiovascular' => 'bi-heart-pulse',
            'respiratory' => 'bi-lungs',
            'mechanical' => 'bi-wrench',
            'other' => 'bi-exclamation-circle'
        ];
        
        return $icons[$category] ?? 'bi-exclamation-triangle';
    }
}
