<?php
namespace Models;

use PDO;

/**
 * PatientPhysician Model
 * Manages many-to-many relationships between patients and physicians
 * Note: Admins are also attending physicians with extra privileges
 */
class PatientPhysician extends BaseModel {
    
    protected $table = 'patient_physicians';
    
    /**
     * Get all physicians for a patient
     * @param int $patientId
     * @return array
     */
    public function getPhysiciansByPatient($patientId) {
        $sql = "
            SELECT 
                pp.*,
                u.id as physician_id,
                u.username,
                u.first_name,
                u.last_name,
                u.email,
                u.role,
                CONCAT(u.first_name, ' ', u.last_name) as physician_name
            FROM {$this->table} pp
            INNER JOIN users u ON pp.user_id = u.id
            WHERE pp.patient_id = ? 
            AND pp.status = 'active'
            AND u.deleted_at IS NULL
            ORDER BY pp.is_primary DESC, pp.physician_type ASC, u.last_name ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all patients for a physician
     * @param int $userId
     * @param int $limit (optional)
     * @return array
     */
    public function getPatientsByPhysician($userId, $limit = null) {
        $limitClause = $limit ? "LIMIT " . (int)$limit : "";
        
        $sql = "
            SELECT 
                pp.*,
                p.id as patient_id,
                p.hospital_number,
                p.patient_name,
                p.age,
                p.gender,
                p.speciality,
                p.created_at as patient_registered_at
            FROM {$this->table} pp
            INNER JOIN patients p ON pp.patient_id = p.id
            WHERE pp.user_id = ? 
            AND pp.status = 'active'
            AND p.deleted_at IS NULL
            ORDER BY pp.assigned_at DESC
            {$limitClause}
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Assign a physician to a patient
     * @param int $patientId
     * @param int $userId
     * @param string $physicianType ('attending' or 'resident')
     * @param bool $isPrimary
     * @param int $createdBy
     * @return int|false
     */
    public function assignPhysician($patientId, $userId, $physicianType, $isPrimary = false, $createdBy = null) {
        // Check if association already exists (this table doesn't have deleted_at)
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE patient_id = ? 
            AND user_id = ? 
            AND physician_type = ?
        ");
        $stmt->execute([$patientId, $userId, $physicianType]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Reactivate if inactive
            if ($existing['status'] == 'inactive') {
                return $this->update($existing['id'], [
                    'status' => 'active',
                    'assigned_at' => date('Y-m-d H:i:s'),
                    'unassigned_at' => null,
                    'is_primary' => $isPrimary ? 1 : 0,
                    'updated_by' => $createdBy
                ]);
            }
            return $existing['id'];
        }
        
        // If setting as primary, unset other primaries of same type
        if ($isPrimary) {
            $this->unsetPrimaryPhysicians($patientId, $physicianType);
        }
        
        $data = [
            'patient_id' => $patientId,
            'user_id' => $userId,
            'physician_type' => $physicianType,
            'is_primary' => $isPrimary ? 1 : 0,
            'status' => 'active',
            'assigned_at' => date('Y-m-d H:i:s'),
            'created_by' => $createdBy
        ];
        
        return $this->create($data);
    }
    
    /**
     * Unassign a physician from a patient
     * @param int $patientId
     * @param int $userId
     * @param string $physicianType
     * @param int $updatedBy
     * @return bool
     */
    public function unassignPhysician($patientId, $userId, $physicianType, $updatedBy = null) {
        $sql = "
            UPDATE {$this->table} 
            SET status = 'inactive', 
                unassigned_at = NOW(),
                updated_by = ?
            WHERE patient_id = ? 
            AND user_id = ? 
            AND physician_type = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$updatedBy, $patientId, $userId, $physicianType]);
    }
    
    /**
     * Sync physicians for a patient (bulk assignment)
     * @param int $patientId
     * @param array $attendingIds
     * @param array $residentIds
     * @param int $userId (who is making the change)
     * @return bool
     */
    public function syncPhysicians($patientId, $attendingIds = [], $residentIds = [], $userId = null) {
        // Get current physicians
        $currentPhysicians = $this->getPhysiciansByPatient($patientId);
        
        $currentAttendingIds = [];
        $currentResidentIds = [];
        
        foreach ($currentPhysicians as $physician) {
            if ($physician['physician_type'] == 'attending') {
                $currentAttendingIds[] = $physician['user_id'];
            } else {
                $currentResidentIds[] = $physician['user_id'];
            }
        }
        
        // Process attendings
        // Remove attendings no longer assigned
        foreach ($currentAttendingIds as $attendingId) {
            if (!in_array($attendingId, $attendingIds)) {
                $this->unassignPhysician($patientId, $attendingId, 'attending', $userId);
            }
        }
        
        // Add new attendings
        foreach ($attendingIds as $index => $attendingId) {
            if (!in_array($attendingId, $currentAttendingIds)) {
                $isPrimary = ($index === 0); // First attending is primary
                $this->assignPhysician($patientId, $attendingId, 'attending', $isPrimary, $userId);
            }
        }
        
        // Process residents
        // Remove residents no longer assigned
        foreach ($currentResidentIds as $residentId) {
            if (!in_array($residentId, $residentIds)) {
                $this->unassignPhysician($patientId, $residentId, 'resident', $userId);
            }
        }
        
        // Add new residents
        foreach ($residentIds as $index => $residentId) {
            if (!in_array($residentId, $currentResidentIds)) {
                $isPrimary = ($index === 0); // First resident is primary
                $this->assignPhysician($patientId, $residentId, 'resident', $isPrimary, $userId);
            }
        }
        
        return true;
    }
    
    /**
     * Unset primary status for other physicians of same type
     * @param int $patientId
     * @param string $physicianType
     */
    private function unsetPrimaryPhysicians($patientId, $physicianType) {
        $sql = "
            UPDATE {$this->table} 
            SET is_primary = 0 
            WHERE patient_id = ? 
            AND physician_type = ?
            AND status = 'active'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$patientId, $physicianType]);
    }
    
    /**
     * Check if a physician is assigned to a patient
     * @param int $patientId
     * @param int $userId
     * @return bool
     */
    public function isPhysicianAssigned($patientId, $userId) {
        $sql = "
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE patient_id = ? 
            AND user_id = ? 
            AND status = 'active'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$patientId, $userId]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Get primary physician for a patient by type
     * @param int $patientId
     * @param string $physicianType
     * @return array|false
     */
    public function getPrimaryPhysician($patientId, $physicianType) {
        $sql = "
            SELECT 
                pp.*,
                u.id as physician_id,
                u.username,
                u.first_name,
                u.last_name,
                u.email,
                CONCAT(u.first_name, ' ', u.last_name) as physician_name
            FROM {$this->table} pp
            INNER JOIN users u ON pp.user_id = u.id
            WHERE pp.patient_id = ? 
            AND pp.physician_type = ?
            AND pp.is_primary = 1
            AND pp.status = 'active'
            AND u.deleted_at IS NULL
            LIMIT 1
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$patientId, $physicianType]);
        return $stmt->fetch();
    }
    
    /**
     * Get count of patients for a physician
     * @param int $userId
     * @return int
     */
    public function getPatientCountByPhysician($userId) {
        $sql = "
            SELECT COUNT(DISTINCT patient_id) as count 
            FROM {$this->table} 
            WHERE user_id = ? 
            AND status = 'active'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        return $result['count'];
    }
}
