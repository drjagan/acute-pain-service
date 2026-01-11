<?php
namespace Models;

use PDO;

/**
 * Notification Model
 * Manages in-app and email notifications
 */
class Notification extends BaseModel {
    
    protected $table = 'notifications';
    
    /**
     * Get unread notifications for a user
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getUnreadByUser($userId, $limit = 10) {
        $sql = "
            SELECT * 
            FROM {$this->table} 
            WHERE user_id = ? 
            AND is_read = 0 
            AND (expires_at IS NULL OR expires_at > NOW())
            ORDER BY priority DESC, created_at DESC 
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get all notifications for a user (read and unread)
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getByUser($userId, $limit = 50) {
        $sql = "
            SELECT * 
            FROM {$this->table} 
            WHERE user_id = ? 
            AND (expires_at IS NULL OR expires_at > NOW())
            ORDER BY is_read ASC, created_at DESC 
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get unread count for a user
     * @param int $userId
     * @return int
     */
    public function getUnreadCount($userId) {
        $sql = "
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE user_id = ? 
            AND is_read = 0
            AND (expires_at IS NULL OR expires_at > NOW())
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        return (int) $result['count'];
    }
    
    /**
     * Mark notification as read
     * @param int $notificationId
     * @return bool
     */
    public function markAsRead($notificationId) {
        $sql = "
            UPDATE {$this->table} 
            SET is_read = 1, 
                read_at = NOW() 
            WHERE id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$notificationId]);
    }
    
    /**
     * Mark all notifications as read for a user
     * @param int $userId
     * @return bool
     */
    public function markAllAsRead($userId) {
        $sql = "
            UPDATE {$this->table} 
            SET is_read = 1, 
                read_at = NOW() 
            WHERE user_id = ? 
            AND is_read = 0
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }
    
    /**
     * Create a notification
     * @param array $data
     * @return int|false
     */
    public function createNotification($data) {
        // Ensure required fields
        $defaults = [
            'priority' => 'medium',
            'color' => 'info',
            'is_read' => 0,
            'send_email' => 0,
            'email_sent' => 0,
            'auto_dismiss' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $data = array_merge($defaults, $data);
        
        // Convert booleans to integers for MySQL
        if (isset($data['send_email'])) {
            $data['send_email'] = $data['send_email'] ? 1 : 0;
        }
        if (isset($data['email_sent'])) {
            $data['email_sent'] = $data['email_sent'] ? 1 : 0;
        }
        if (isset($data['auto_dismiss'])) {
            $data['auto_dismiss'] = $data['auto_dismiss'] ? 1 : 0;
        }
        
        return $this->create($data);
    }
    
    /**
     * Send notification to user
     * @param int $userId
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $options (priority, color, icon, action_url, etc.)
     * @return int|false
     */
    public function notify($userId, $type, $title, $message, $options = []) {
        $data = [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'priority' => $options['priority'] ?? 'medium',
            'color' => $options['color'] ?? 'info',
            'icon' => $options['icon'] ?? null,
            'related_type' => $options['related_type'] ?? null,
            'related_id' => $options['related_id'] ?? null,
            'action_url' => $options['action_url'] ?? null,
            'action_text' => $options['action_text'] ?? 'View Details',
            'send_email' => $options['send_email'] ?? false,
            'auto_dismiss' => $options['auto_dismiss'] ?? true,
            'created_by' => $options['created_by'] ?? null
        ];
        
        return $this->createNotification($data);
    }
    
    /**
     * Send notification to multiple users
     * @param array $userIds
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $options
     * @return array (notification IDs)
     */
    public function notifyMultiple($userIds, $type, $title, $message, $options = []) {
        $notificationIds = [];
        
        foreach ($userIds as $userId) {
            $id = $this->notify($userId, $type, $title, $message, $options);
            if ($id) {
                $notificationIds[] = $id;
            }
        }
        
        return $notificationIds;
    }
    
    /**
     * Delete expired notifications
     * @return int (number of deleted notifications)
     */
    public function deleteExpired() {
        $sql = "
            DELETE FROM {$this->table} 
            WHERE expires_at IS NOT NULL 
            AND expires_at < NOW()
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }
    
    /**
     * Get notifications pending email delivery
     * @param int $limit
     * @return array
     */
    public function getPendingEmails($limit = 50) {
        $sql = "
            SELECT n.*, u.email as user_email, u.first_name, u.last_name
            FROM {$this->table} n
            INNER JOIN users u ON n.user_id = u.id
            WHERE n.send_email = 1 
            AND n.email_sent = 0 
            AND u.status = 'active'
            AND u.deleted_at IS NULL
            ORDER BY n.priority DESC, n.created_at ASC 
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Mark notification email as sent
     * @param int $notificationId
     * @return bool
     */
    public function markEmailSent($notificationId) {
        $sql = "
            UPDATE {$this->table} 
            SET email_sent = 1, 
                email_sent_at = NOW() 
            WHERE id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$notificationId]);
    }
    
    /**
     * Mark notification email as failed
     * @param int $notificationId
     * @param string $error
     * @return bool
     */
    public function markEmailFailed($notificationId, $error) {
        $sql = "
            UPDATE {$this->table} 
            SET email_error = ? 
            WHERE id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$error, $notificationId]);
    }
    
    /**
     * Get notifications by related record
     * @param string $relatedType
     * @param int $relatedId
     * @return array
     */
    public function getByRelatedRecord($relatedType, $relatedId) {
        $sql = "
            SELECT * 
            FROM {$this->table} 
            WHERE related_type = ? 
            AND related_id = ?
            ORDER BY created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$relatedType, $relatedId]);
        return $stmt->fetchAll();
    }
}
