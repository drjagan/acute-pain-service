<?php
/**
 * Notifications Page (v1.1)
 * Display all notifications for the current user
 */
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2"><i class="bi bi-bell"></i> Notifications</h1>
        <p class="text-muted mb-0">View and manage all your notifications</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if ($unread_count > 0): ?>
        <button type="button" class="btn btn-sm btn-primary" id="markAllReadBtn">
            <i class="bi bi-check-all"></i> Mark All as Read (<?= $unread_count ?>)
        </button>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($notifications)): ?>
    <!-- Empty State -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3">No Notifications</h4>
            <p class="text-muted">
                You don't have any notifications yet.<br>
                You'll be notified here when important events occur.
            </p>
            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-primary mt-2">
                <i class="bi bi-house-door"></i> Back to Dashboard
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Notifications List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item list-group-item-action notification-item-page <?= $notification['is_read'] ? '' : 'bg-light border-start border-primary border-4' ?>" 
                             data-notification-id="<?= $notification['id'] ?>">
                            <div class="d-flex w-100 align-items-start">
                                <!-- Icon -->
                                <div class="notification-icon-page bg-<?= $notification['color'] ?> me-3 flex-shrink-0">
                                    <i class="<?= $notification['icon'] ?>"></i>
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-grow-1">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 fw-bold">
                                            <?= e($notification['title']) ?>
                                            <?php if (!$notification['is_read']): ?>
                                                <span class="badge bg-primary ms-2">New</span>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?= timeAgo($notification['created_at']) ?>
                                        </small>
                                    </div>
                                    <p class="mb-1"><?= e($notification['message']) ?></p>
                                    
                                    <!-- Metadata -->
                                    <div class="d-flex gap-3 align-items-center mt-2">
                                        <small class="text-muted">
                                            <i class="bi bi-tag"></i> <?= ucfirst(str_replace('_', ' ', $notification['type'])) ?>
                                        </small>
                                        <?php if ($notification['priority']): ?>
                                        <span class="badge bg-<?= 
                                            $notification['priority'] == 'high' ? 'danger' : 
                                            ($notification['priority'] == 'medium' ? 'warning' : 'secondary') 
                                        ?>">
                                            <?= ucfirst($notification['priority']) ?> Priority
                                        </span>
                                        <?php endif; ?>
                                        
                                        <!-- Action Buttons -->
                                        <div class="ms-auto">
                                            <?php if ($notification['action_url']): ?>
                                            <a href="<?= BASE_URL . $notification['action_url'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-arrow-right-circle"></i> <?= $notification['action_text'] ?? 'View' ?>
                                            </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!$notification['is_read']): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-success mark-read-btn"
                                                    data-notification-id="<?= $notification['id'] ?>">
                                                <i class="bi bi-check"></i> Mark as Read
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pagination Info -->
    <div class="mt-3 text-muted text-center">
        <small>Showing <?= count($notifications) ?> notification(s)</small>
    </div>
<?php endif; ?>

<style>
/* Notification item on page */
.notification-item-page {
    padding: 1rem;
    cursor: default;
}

.notification-icon-page {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
}

.notification-item-page:not(.bg-light):hover {
    background-color: #f8f9fa;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark all as read
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            if (!confirm('Mark all notifications as read?')) return;
            
            fetch('<?= BASE_URL ?>/notifications/markAllAsRead', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to mark notifications as read');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
    }
    
    // Mark individual notification as read
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const notificationId = this.dataset.notificationId;
            
            fetch(`<?= BASE_URL ?>/notifications/markAsRead/${notificationId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to mark notification as read');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
    });
});
</script>
