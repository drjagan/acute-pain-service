<?php
namespace Models;

use PDO;

/**
 * Report Model (Phase 6)
 * Generates individual and consolidated reports
 */
class Report extends BaseModel {
    
    protected $table = 'patients'; // Base table, but we query across all tables
    
    /**
     * Generate Individual Patient Report
     * Comprehensive lifecycle report for a single patient
     */
    public function generateIndividualReport($patientId) {
        $data = [];
        
        // 1. Patient Demographics
        $data['patient'] = $this->getPatientDemographics($patientId);
        
        // 2. All Catheters with details
        $data['catheters'] = $this->getPatientCatheters($patientId);
        
        // 3. For each catheter, get detailed information
        foreach ($data['catheters'] as &$catheter) {
            $catheterId = $catheter['id'];
            
            // Drug regimes
            $catheter['regimes'] = $this->getCatheterRegimes($catheterId);
            $catheter['commonest_drug'] = $this->getCommonestDrug($catheterId);
            $catheter['dose_count'] = count($catheter['regimes']);
            
            // Pain scores by POD
            $catheter['pain_analysis'] = $this->getPainAnalysisByPOD($catheterId);
            
            // Adverse effects
            $catheter['adverse_effects'] = $this->getAdverseEffects($catheterId);
            
            // Functional outcomes
            $catheter['outcomes'] = $this->getCatheterOutcomes($catheterId);
            
            // Removal record
            $catheter['removal'] = $this->getCatheterRemoval($catheterId);
        }
        
        return $data;
    }
    
    /**
     * Generate Consolidated Report
     * Aggregate statistics for a date range
     */
    public function generateConsolidatedReport($startDate, $endDate) {
        $data = [];
        
        $data['period'] = [
            'start' => $startDate,
            'end' => $endDate
        ];
        
        // 1. Patient Statistics
        $data['patient_stats'] = $this->getPatientStatistics($startDate, $endDate);
        
        // 2. Catheter Statistics
        $data['catheter_stats'] = $this->getCatheterStatistics($startDate, $endDate);
        
        // 3. Pain Management Efficacy
        $data['pain_stats'] = $this->getPainManagementStats($startDate, $endDate);
        
        // 4. Adverse Effects
        $data['adverse_effects'] = $this->getAdverseEffectsStats($startDate, $endDate);
        
        // 5. Sentinel Events
        $data['sentinel_events'] = $this->getSentinelEventsStats($startDate, $endDate);
        
        // 6. Removal Statistics
        $data['removal_stats'] = $this->getRemovalStatistics($startDate, $endDate);
        
        return $data;
    }
    
    // ==================== INDIVIDUAL REPORT METHODS ====================
    
    private function getPatientDemographics($patientId) {
        $sql = "
            SELECT p.*,
                   GROUP_CONCAT(DISTINCT lc.name SEPARATOR ', ') as comorbidities_list,
                   GROUP_CONCAT(DISTINCT ls.name SEPARATOR ', ') as surgeries_list
            FROM patients p
            LEFT JOIN patient_comorbidities pc ON p.id = pc.patient_id
            LEFT JOIN lookup_comorbidities lc ON pc.comorbidity_id = lc.id
            LEFT JOIN patient_surgeries ps ON p.id = ps.patient_id
            LEFT JOIN lookup_surgeries ls ON ps.surgery_id = ls.id
            WHERE p.id = ? AND p.deleted_at IS NULL
            GROUP BY p.id
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$patientId]);
        
        return $stmt->fetch();
    }
    
    private function getPatientCatheters($patientId) {
        $sql = "
            SELECT c.*, 
                   DATEDIFF(COALESCE(cr.date_of_removal, CURDATE()), c.date_of_insertion) as days_in_situ
            FROM catheters c
            LEFT JOIN catheter_removals cr ON c.id = cr.catheter_id AND cr.deleted_at IS NULL
            WHERE c.patient_id = ? AND c.deleted_at IS NULL
            ORDER BY c.date_of_insertion
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$patientId]);
        
        return $stmt->fetchAll();
    }
    
    private function getCatheterRegimes($catheterId) {
        $sql = "
            SELECT * FROM drug_regimes
            WHERE catheter_id = ? AND deleted_at IS NULL
            ORDER BY pod, entry_date
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catheterId]);
        
        return $stmt->fetchAll();
    }
    
    private function getCommonestDrug($catheterId) {
        $sql = "
            SELECT drug, concentration, COUNT(*) as count
            FROM drug_regimes
            WHERE catheter_id = ? AND deleted_at IS NULL
            GROUP BY drug, concentration
            ORDER BY count DESC
            LIMIT 1
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catheterId]);
        
        return $stmt->fetch();
    }
    
    private function getPainAnalysisByPOD($catheterId) {
        $sql = "
            SELECT pod,
                   AVG(baseline_vnrs_static) as avg_baseline_static,
                   AVG(baseline_vnrs_dynamic) as avg_baseline_dynamic,
                   AVG(vnrs_15min_static) as avg_15min_static,
                   AVG(vnrs_15min_dynamic) as avg_15min_dynamic,
                   AVG((baseline_vnrs_static - vnrs_15min_static + 
                        baseline_vnrs_dynamic - vnrs_15min_dynamic) / 2) as avg_improvement
            FROM drug_regimes
            WHERE catheter_id = ? AND deleted_at IS NULL
            GROUP BY pod
            ORDER BY pod
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catheterId]);
        
        return $stmt->fetchAll();
    }
    
    private function getAdverseEffects($catheterId) {
        $sql = "
            SELECT 
                SUM(CASE WHEN hypotension != 'none' THEN 1 ELSE 0 END) as hypotension_count,
                SUM(CASE WHEN bradycardia != 'none' THEN 1 ELSE 0 END) as bradycardia_count,
                SUM(CASE WHEN sensory_motor_deficit != 'none' THEN 1 ELSE 0 END) as deficit_count,
                SUM(CASE WHEN nausea_vomiting != 'none' THEN 1 ELSE 0 END) as nausea_count,
                GROUP_CONCAT(DISTINCT 
                    CASE WHEN hypotension != 'none' THEN CONCAT('Hypotension (', hypotension, ')') END 
                    SEPARATOR ', ') as hypotension_details,
                GROUP_CONCAT(DISTINCT 
                    CASE WHEN bradycardia != 'none' THEN CONCAT('Bradycardia (', bradycardia, ')') END 
                    SEPARATOR ', ') as bradycardia_details
            FROM drug_regimes
            WHERE catheter_id = ? AND deleted_at IS NULL
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catheterId]);
        
        return $stmt->fetch();
    }
    
    private function getCatheterOutcomes($catheterId) {
        $sql = "
            SELECT * FROM functional_outcomes
            WHERE catheter_id = ? AND deleted_at IS NULL
            ORDER BY pod, entry_date
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catheterId]);
        
        return $stmt->fetchAll();
    }
    
    private function getCatheterRemoval($catheterId) {
        $sql = "
            SELECT * FROM catheter_removals
            WHERE catheter_id = ? AND deleted_at IS NULL
            LIMIT 1
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$catheterId]);
        
        return $stmt->fetch();
    }
    
    // ==================== CONSOLIDATED REPORT METHODS ====================
    
    private function getPatientStatistics($startDate, $endDate) {
        $stats = [];
        
        // Total patients in period
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM patients
            WHERE admission_date BETWEEN ? AND ? AND deleted_at IS NULL
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats['total'] = $stmt->fetch()['total'];
        
        // Male/Female ratio
        $stmt = $this->db->prepare("
            SELECT gender, COUNT(*) as count
            FROM patients
            WHERE admission_date BETWEEN ? AND ? AND deleted_at IS NULL
            GROUP BY gender
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats['gender'] = $stmt->fetchAll();
        
        // By speciality
        $stmt = $this->db->prepare("
            SELECT speciality, COUNT(*) as count
            FROM patients
            WHERE admission_date BETWEEN ? AND ? AND deleted_at IS NULL
            GROUP BY speciality
            ORDER BY count DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats['speciality'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    private function getCatheterStatistics($startDate, $endDate) {
        $stats = [];
        
        // Total catheters
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM catheters
            WHERE date_of_insertion BETWEEN ? AND ? AND deleted_at IS NULL
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats['total'] = $stmt->fetch()['total'];
        
        // By category
        $stmt = $this->db->prepare("
            SELECT catheter_category, COUNT(*) as count
            FROM catheters
            WHERE date_of_insertion BETWEEN ? AND ? AND deleted_at IS NULL
            GROUP BY catheter_category
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats['by_category'] = $stmt->fetchAll();
        
        // By type
        $stmt = $this->db->prepare("
            SELECT catheter_type, COUNT(*) as count
            FROM catheters
            WHERE date_of_insertion BETWEEN ? AND ? AND deleted_at IS NULL
            GROUP BY catheter_type
            ORDER BY count DESC
            LIMIT 10
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats['by_type'] = $stmt->fetchAll();
        
        // Elective vs Emergency
        $stmt = $this->db->prepare("
            SELECT settings, COUNT(*) as count
            FROM catheters
            WHERE date_of_insertion BETWEEN ? AND ? AND deleted_at IS NULL
            GROUP BY settings
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats['by_settings'] = $stmt->fetchAll();
        
        // By performer
        $stmt = $this->db->prepare("
            SELECT performer, COUNT(*) as count
            FROM catheters
            WHERE date_of_insertion BETWEEN ? AND ? AND deleted_at IS NULL
            GROUP BY performer
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats['by_performer'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    private function getPainManagementStats($startDate, $endDate) {
        $stats = [];
        
        // Mean VNRS by POD (for POD 1, 2, 3)
        $stmt = $this->db->prepare("
            SELECT pod,
                   AVG(vnrs_15min_static) as mean_static,
                   AVG(vnrs_15min_dynamic) as mean_dynamic,
                   AVG((baseline_vnrs_static - vnrs_15min_static + 
                        baseline_vnrs_dynamic - vnrs_15min_dynamic) / 2) as mean_improvement
            FROM drug_regimes dr
            JOIN catheters c ON dr.catheter_id = c.id
            WHERE c.date_of_insertion BETWEEN ? AND ?
            AND dr.deleted_at IS NULL AND c.deleted_at IS NULL
            AND pod IN (1, 2, 3)
            GROUP BY pod
            ORDER BY pod
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats['vnrs_by_pod'] = $stmt->fetchAll();
        
        // Effective analgesia rate
        $stmt = $this->db->prepare("
            SELECT 
                SUM(effective_analgesia) as effective,
                COUNT(*) as total
            FROM drug_regimes dr
            JOIN catheters c ON dr.catheter_id = c.id
            WHERE c.date_of_insertion BETWEEN ? AND ?
            AND dr.deleted_at IS NULL AND c.deleted_at IS NULL
        ");
        $stmt->execute([$startDate, $endDate]);
        $result = $stmt->fetch();
        $stats['effective_rate'] = $result['total'] > 0 
            ? round(($result['effective'] / $result['total']) * 100, 1) 
            : 0;
        
        // Number of doses per catheter
        $stmt = $this->db->prepare("
            SELECT 
                AVG(dose_count) as mean_doses,
                MIN(dose_count) as min_doses,
                MAX(dose_count) as max_doses
            FROM (
                SELECT catheter_id, COUNT(*) as dose_count
                FROM drug_regimes dr
                JOIN catheters c ON dr.catheter_id = c.id
                WHERE c.date_of_insertion BETWEEN ? AND ?
                AND dr.deleted_at IS NULL AND c.deleted_at IS NULL
                GROUP BY catheter_id
            ) as dose_counts
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats['doses_per_catheter'] = $stmt->fetch();
        
        return $stats;
    }
    
    private function getAdverseEffectsStats($startDate, $endDate) {
        $stmt = $this->db->prepare("
            SELECT 
                'Hypotension' as effect,
                SUM(CASE WHEN hypotension != 'none' THEN 1 ELSE 0 END) as count,
                SUM(CASE WHEN hypotension = 'mild' THEN 1 ELSE 0 END) as mild,
                SUM(CASE WHEN hypotension = 'moderate' THEN 1 ELSE 0 END) as moderate,
                SUM(CASE WHEN hypotension = 'severe' THEN 1 ELSE 0 END) as severe
            FROM drug_regimes dr
            JOIN catheters c ON dr.catheter_id = c.id
            WHERE c.date_of_insertion BETWEEN ? AND ?
            AND dr.deleted_at IS NULL AND c.deleted_at IS NULL
            
            UNION ALL
            
            SELECT 
                'Bradycardia' as effect,
                SUM(CASE WHEN bradycardia != 'none' THEN 1 ELSE 0 END) as count,
                SUM(CASE WHEN bradycardia = 'mild' THEN 1 ELSE 0 END) as mild,
                SUM(CASE WHEN bradycardia = 'moderate' THEN 1 ELSE 0 END) as moderate,
                SUM(CASE WHEN bradycardia = 'severe' THEN 1 ELSE 0 END) as severe
            FROM drug_regimes dr
            JOIN catheters c ON dr.catheter_id = c.id
            WHERE c.date_of_insertion BETWEEN ? AND ?
            AND dr.deleted_at IS NULL AND c.deleted_at IS NULL
            
            UNION ALL
            
            SELECT 
                'Sensory/Motor Deficit' as effect,
                SUM(CASE WHEN sensory_motor_deficit != 'none' THEN 1 ELSE 0 END) as count,
                SUM(CASE WHEN sensory_motor_deficit = 'mild' THEN 1 ELSE 0 END) as mild,
                SUM(CASE WHEN sensory_motor_deficit = 'moderate' THEN 1 ELSE 0 END) as moderate,
                SUM(CASE WHEN sensory_motor_deficit = 'severe' THEN 1 ELSE 0 END) as severe
            FROM drug_regimes dr
            JOIN catheters c ON dr.catheter_id = c.id
            WHERE c.date_of_insertion BETWEEN ? AND ?
            AND dr.deleted_at IS NULL AND c.deleted_at IS NULL
            
            UNION ALL
            
            SELECT 
                'Nausea/Vomiting' as effect,
                SUM(CASE WHEN nausea_vomiting != 'none' THEN 1 ELSE 0 END) as count,
                SUM(CASE WHEN nausea_vomiting = 'mild' THEN 1 ELSE 0 END) as mild,
                SUM(CASE WHEN nausea_vomiting = 'moderate' THEN 1 ELSE 0 END) as moderate,
                SUM(CASE WHEN nausea_vomiting = 'severe' THEN 1 ELSE 0 END) as severe
            FROM drug_regimes dr
            JOIN catheters c ON dr.catheter_id = c.id
            WHERE c.date_of_insertion BETWEEN ? AND ?
            AND dr.deleted_at IS NULL AND c.deleted_at IS NULL
        ");
        $stmt->execute([$startDate, $endDate, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate]);
        
        return $stmt->fetchAll();
    }
    
    private function getSentinelEventsStats($startDate, $endDate) {
        $stmt = $this->db->prepare("
            SELECT sentinel_events, COUNT(*) as count
            FROM functional_outcomes fo
            JOIN catheters c ON fo.catheter_id = c.id
            WHERE c.date_of_insertion BETWEEN ? AND ?
            AND fo.sentinel_events != 'none'
            AND fo.deleted_at IS NULL AND c.deleted_at IS NULL
            GROUP BY sentinel_events
        ");
        $stmt->execute([$startDate, $endDate]);
        
        return $stmt->fetchAll();
    }
    
    private function getRemovalStatistics($startDate, $endDate) {
        $stats = [];
        
        // Indications for removal
        $stmt = $this->db->prepare("
            SELECT indication, COUNT(*) as count
            FROM catheter_removals
            WHERE date_of_removal BETWEEN ? AND ? AND deleted_at IS NULL
            GROUP BY indication
            ORDER BY count DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats['indications'] = $stmt->fetchAll();
        
        // Catheter days statistics
        $stmt = $this->db->prepare("
            SELECT 
                AVG(number_of_catheter_days) as mean_days,
                MIN(number_of_catheter_days) as min_days,
                MAX(number_of_catheter_days) as max_days
            FROM catheter_removals
            WHERE date_of_removal BETWEEN ? AND ? AND deleted_at IS NULL
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats['catheter_days'] = $stmt->fetch();
        
        // Tip integrity rate
        $stmt = $this->db->prepare("
            SELECT 
                SUM(catheter_tip_intact) as intact,
                COUNT(*) as total
            FROM catheter_removals
            WHERE date_of_removal BETWEEN ? AND ? AND deleted_at IS NULL
        ");
        $stmt->execute([$startDate, $endDate]);
        $result = $stmt->fetch();
        $stats['tip_integrity_rate'] = $result['total'] > 0 
            ? round(($result['intact'] / $result['total']) * 100, 1) 
            : 0;
        
        // Patient satisfaction
        $stmt = $this->db->prepare("
            SELECT patient_satisfaction, COUNT(*) as count
            FROM catheter_removals
            WHERE date_of_removal BETWEEN ? AND ? 
            AND patient_satisfaction IS NOT NULL
            AND deleted_at IS NULL
            GROUP BY patient_satisfaction
            ORDER BY FIELD(patient_satisfaction, 'excellent', 'good', 'fair', 'poor')
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats['satisfaction'] = $stmt->fetchAll();
        
        return $stats;
    }
}
