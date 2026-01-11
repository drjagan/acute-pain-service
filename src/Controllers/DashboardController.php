<?php
namespace Controllers;

use Models\Patient;
use Models\Catheter;
use Models\DrugRegime;
use Models\FunctionalOutcome;
use Models\CatheterRemoval;

/**
 * Dashboard Controller (Phase 6)
 * Enhanced with real-time statistics and analytics
 */
class DashboardController extends BaseController {
    
    /**
     * Show dashboard
     */
    public function index() {
        // Require authentication
        $this->requireAuth();
        
        $user = $this->user();
        
        // Get comprehensive dashboard data
        $data = [
            'user' => $user,
            'stats' => $this->getDashboardStats(),
            'activity' => $this->getRecentActivity(10),
            'alerts' => $this->getAlerts()
        ];
        
        $this->view('dashboard.index', $data);
    }
    
    /**
     * Get comprehensive dashboard statistics
     */
    private function getDashboardStats() {
        $stats = [];
        
        // Patient Statistics
        $patientModel = new Patient();
        $patientStats = $patientModel->getStatistics();
        $stats['patient_total'] = $patientStats['total'];
        
        // Active patients (with active catheters)
        $stmt = $this->db->query("
            SELECT COUNT(DISTINCT patient_id) as count 
            FROM catheters 
            WHERE status = 'active' AND deleted_at IS NULL
        ");
        $stats['patient_active'] = $stmt->fetch()['count'];
        
        // Gender distribution
        $stmt = $this->db->query("
            SELECT 
                SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female
            FROM patients 
            WHERE deleted_at IS NULL
        ");
        $genderData = $stmt->fetch();
        $stats['patient_male'] = $genderData['male'];
        $stats['patient_female'] = $genderData['female'];
        
        // Today's admissions
        $stmt = $this->db->query("
            SELECT COUNT(*) as count 
            FROM patients 
            WHERE DATE(admission_date) = CURDATE() AND deleted_at IS NULL
        ");
        $stats['patient_today'] = $stmt->fetch()['count'];
        
        // Catheter Statistics
        // Total catheters (all statuses)
        $stmt = $this->db->query("
            SELECT COUNT(*) as count 
            FROM catheters 
            WHERE deleted_at IS NULL
        ");
        $stats['catheter_total'] = $stmt->fetch()['count'];
        
        // Active catheters
        $stmt = $this->db->query("
            SELECT COUNT(*) as count 
            FROM catheters 
            WHERE status = 'active' AND deleted_at IS NULL
        ");
        $stats['catheter_active'] = $stmt->fetch()['count'];
        
        // Catheters removed today
        $stmt = $this->db->query("
            SELECT COUNT(*) as count 
            FROM catheter_removals 
            WHERE DATE(date_of_removal) = CURDATE() AND deleted_at IS NULL
        ");
        $stats['catheter_removed_today'] = $stmt->fetch()['count'];
        
        // Average catheter duration
        $removalModel = new CatheterRemoval();
        $removalStats = $removalModel->getStatistics();
        $stats['catheter_avg_days'] = $removalStats['catheter_days']['avg_days'] ?? 0;
        
        // Quality Indicators
        $regimeModel = new DrugRegime();
        $regimeStats = $regimeModel->getStatistics();
        $stats['effective_analgesia_rate'] = $regimeStats['effective_rate'];
        
        $stats['complication_rate'] = $removalStats['complication_rate'];
        
        // Patient satisfaction
        $satisfactionData = $removalStats['satisfaction'];
        $totalSatisfaction = 0;
        $satisfactionCount = 0;
        $satisfactionWeights = ['excellent' => 4, 'good' => 3, 'fair' => 2, 'poor' => 1];
        
        foreach ($satisfactionData as $sat) {
            if (isset($satisfactionWeights[$sat['patient_satisfaction']])) {
                $totalSatisfaction += $satisfactionWeights[$sat['patient_satisfaction']] * $sat['count'];
                $satisfactionCount += $sat['count'];
            }
        }
        
        $stats['satisfaction_avg'] = $satisfactionCount > 0 ? round(($totalSatisfaction / $satisfactionCount) * 25, 1) : 0;
        $stats['satisfaction_distribution'] = $satisfactionData;
        
        // Sentinel events this month
        $outcomeModel = new FunctionalOutcome();
        $stmt = $this->db->query("
            SELECT COUNT(*) as count 
            FROM functional_outcomes 
            WHERE sentinel_events != 'none' 
            AND MONTH(entry_date) = MONTH(CURDATE())
            AND YEAR(entry_date) = YEAR(CURDATE())
            AND deleted_at IS NULL
        ");
        $stats['sentinel_events_month'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    /**
     * Get recent activity feed
     */
    private function getRecentActivity($limit = 10) {
        $activities = [];
        
        // Get recent patients, catheters, regimes, outcomes, removals
        // Use UNION to combine all activities
        $sql = "
            (SELECT 'patient' as type, id, patient_name as title, created_at, created_by 
             FROM patients WHERE deleted_at IS NULL)
            UNION ALL
            (SELECT 'catheter' as type, c.id, CONCAT(p.patient_name, ' - ', c.catheter_type) as title, 
                    c.created_at, c.created_by
             FROM catheters c
             LEFT JOIN patients p ON c.patient_id = p.id
             WHERE c.deleted_at IS NULL)
            UNION ALL
            (SELECT 'regime' as type, dr.id, CONCAT(p.patient_name, ' - POD ', dr.pod) as title, 
                    dr.created_at, dr.created_by
             FROM drug_regimes dr
             LEFT JOIN patients p ON dr.patient_id = p.id
             WHERE dr.deleted_at IS NULL)
            UNION ALL
            (SELECT 'outcome' as type, fo.id, CONCAT(p.patient_name, ' - POD ', fo.pod) as title, 
                    fo.created_at, fo.created_by
             FROM functional_outcomes fo
             LEFT JOIN patients p ON fo.patient_id = p.id
             WHERE fo.deleted_at IS NULL)
            UNION ALL
            (SELECT 'removal' as type, cr.id, CONCAT(p.patient_name, ' - ', cr.indication) as title, 
                    cr.created_at, cr.created_by
             FROM catheter_removals cr
             LEFT JOIN patients p ON cr.patient_id = p.id
             WHERE cr.deleted_at IS NULL)
            ORDER BY created_at DESC
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        $activities = $stmt->fetchAll();
        
        // Get user names
        foreach ($activities as &$activity) {
            $userStmt = $this->db->prepare("
                SELECT CONCAT(first_name, ' ', last_name) as name, role 
                FROM users WHERE id = ?
            ");
            $userStmt->execute([$activity['created_by']]);
            $userData = $userStmt->fetch();
            $activity['user_name'] = $userData['name'] ?? 'Unknown';
            $activity['user_role'] = $userData['role'] ?? 'unknown';
        }
        
        return $activities;
    }
    
    /**
     * Get alerts and notifications
     */
    private function getAlerts() {
        $alerts = [];
        
        // Alert 1: Catheters >7 days (infection risk)
        $stmt = $this->db->query("
            SELECT c.id, c.catheter_type, p.patient_name, c.date_of_insertion,
                   DATEDIFF(CURDATE(), c.date_of_insertion) as days
            FROM catheters c
            LEFT JOIN patients p ON c.patient_id = p.id
            WHERE c.status = 'active' 
            AND DATEDIFF(CURDATE(), c.date_of_insertion) > 7
            AND c.deleted_at IS NULL
            ORDER BY days DESC
        ");
        $longCatheters = $stmt->fetchAll();
        
        if (count($longCatheters) > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'exclamation-triangle',
                'title' => 'Long-duration Catheters',
                'message' => count($longCatheters) . ' catheter(s) in situ for >7 days (infection risk)',
                'data' => $longCatheters,
                'link' => '/catheters'
            ];
        }
        
        // Alert 2: Poor functional scores (<50)
        $stmt = $this->db->query("
            SELECT fo.id, p.patient_name, fo.pod, fo.entry_date,
                   fo.ambulation, fo.room_air_spo2
            FROM functional_outcomes fo
            LEFT JOIN patients p ON fo.patient_id = p.id
            WHERE (fo.ambulation = 'bedbound' OR fo.room_air_spo2 = 'requires_o2')
            AND DATE(fo.entry_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            AND fo.deleted_at IS NULL
            ORDER BY fo.entry_date DESC
        ");
        $poorOutcomes = $stmt->fetchAll();
        
        if (count($poorOutcomes) > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'activity',
                'title' => 'Poor Functional Outcomes',
                'message' => count($poorOutcomes) . ' patient(s) with poor functional status in last 7 days',
                'data' => $poorOutcomes,
                'link' => '/outcomes'
            ];
        }
        
        // Alert 3: Recent complications
        $stmt = $this->db->query("
            SELECT cr.id, p.patient_name, cr.date_of_removal, cr.removal_complications
            FROM catheter_removals cr
            LEFT JOIN patients p ON cr.patient_id = p.id
            WHERE cr.removal_complications IS NOT NULL 
            AND cr.removal_complications != ''
            AND DATE(cr.date_of_removal) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            AND cr.deleted_at IS NULL
            ORDER BY cr.date_of_removal DESC
        ");
        $complications = $stmt->fetchAll();
        
        if (count($complications) > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'exclamation-octagon',
                'title' => 'Recent Complications',
                'message' => count($complications) . ' removal(s) with complications in last 7 days',
                'data' => $complications,
                'link' => '/catheters'
            ];
        }
        
        // Alert 4: Catheters without recent drug regime
        $stmt = $this->db->query("
            SELECT c.id, c.catheter_type, p.patient_name, 
                   MAX(dr.entry_date) as last_regime_date,
                   DATEDIFF(CURDATE(), MAX(dr.entry_date)) as days_since
            FROM catheters c
            LEFT JOIN patients p ON c.patient_id = p.id
            LEFT JOIN drug_regimes dr ON c.id = dr.catheter_id AND dr.deleted_at IS NULL
            WHERE c.status = 'active' AND c.deleted_at IS NULL
            GROUP BY c.id, c.catheter_type, p.patient_name
            HAVING days_since > 1 OR last_regime_date IS NULL
            ORDER BY days_since DESC
        ");
        $missingSRegimes = $stmt->fetchAll();
        
        if (count($missingSRegimes) > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'info-circle',
                'title' => 'Missing Drug Regimes',
                'message' => count($missingSRegimes) . ' active catheter(s) without drug regime in last 24 hours',
                'data' => $missingSRegimes,
                'link' => '/regimes/create'
            ];
        }
        
        return $alerts;
    }
}
