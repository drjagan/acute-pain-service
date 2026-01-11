<?php
namespace Controllers;

use Models\Notification;
use Helpers\Flash;

/**
 * Notification Controller (v1.1)
 * Handles in-app notifications - view, mark as read, etc.
 */
class NotificationController extends BaseController {
    
    private $notificationModel;
    
    public function __construct() {
        parent::__construct();
        $this->notificationModel = new Notification();
    }
    
    /**
     * Get unread notifications for current user (AJAX)
     */
    public function getUnread() {
        $this->requireAuth();
        header('Content-Type: application/json');
        
        try {
            $userId = $this->user()['id'];
            $notifications = $this->notificationModel->getUnreadByUser($userId, 10);
            $unreadCount = $this->notificationModel->getUnreadCount($userId);
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch notifications: ' . $e->getMessage()
            ]);
        }
        
        exit;
    }
    
    /**
     * Get all notifications for current user
     */
    public function index() {
        $this->requireAuth();
        
        $userId = $this->user()['id'];
        $notifications = $this->notificationModel->getByUser($userId, 100);
        $unreadCount = $this->notificationModel->getUnreadCount($userId);
        
        $this->view('notifications.index', [
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }
    
    /**
     * Mark notification as read (AJAX)
     */
    public function markAsRead($id) {
        $this->requireAuth();
        header('Content-Type: application/json');
        
        try {
            // Verify notification belongs to current user
            $notification = $this->notificationModel->find($id);
            
            if (!$notification) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Notification not found'
                ]);
                exit;
            }
            
            if ($notification['user_id'] != $this->user()['id']) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
                exit;
            }
            
            $this->notificationModel->markAsRead($id);
            
            // Get updated unread count
            $unreadCount = $this->notificationModel->getUnreadCount($this->user()['id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Notification marked as read',
                'unread_count' => $unreadCount
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to mark notification as read: ' . $e->getMessage()
            ]);
        }
        
        exit;
    }
    
    /**
     * Mark all notifications as read (AJAX)
     */
    public function markAllAsRead() {
        $this->requireAuth();
        header('Content-Type: application/json');
        
        try {
            $userId = $this->user()['id'];
            $this->notificationModel->markAllAsRead($userId);
            
            echo json_encode([
                'success' => true,
                'message' => 'All notifications marked as read',
                'unread_count' => 0
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to mark all notifications as read: ' . $e->getMessage()
            ]);
        }
        
        exit;
    }
    
    /**
     * Delete notification
     */
    public function delete($id) {
        $this->requireAuth();
        $this->validateCSRF();
        
        try {
            // Verify notification belongs to current user
            $notification = $this->notificationModel->find($id);
            
            if (!$notification) {
                Flash::error('Notification not found');
                return $this->redirect('/notifications');
            }
            
            if ($notification['user_id'] != $this->user()['id']) {
                Flash::error('Unauthorized');
                return $this->redirect('/notifications');
            }
            
            $this->notificationModel->forceDelete($id);
            Flash::success('Notification deleted successfully');
            
        } catch (\Exception $e) {
            Flash::error('Failed to delete notification: ' . $e->getMessage());
        }
        
        return $this->redirect('/notifications');
    }
    
    /**
     * Get unread count for current user (AJAX - lightweight)
     */
    public function getUnreadCount() {
        $this->requireAuth();
        header('Content-Type: application/json');
        
        try {
            $userId = $this->user()['id'];
            $unreadCount = $this->notificationModel->getUnreadCount($userId);
            
            echo json_encode([
                'success' => true,
                'unread_count' => $unreadCount
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        
        exit;
    }
}
