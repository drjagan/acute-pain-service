<?php
/**
 * Settings Main Page (Admin Only)
 * Central hub for all system settings and master data management
 */
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2"><i class="bi bi-gear-fill"></i> System Settings</h1>
        <p class="text-muted mb-0">Manage system configuration, master data, and preferences</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="row">
    <!-- Email & Notifications -->
    <div class="col-md-6 mb-3">
        <div class="card h-100 border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-envelope-gear"></i> Email & Notifications</h5>
            </div>
            <div class="card-body">
                <p class="card-text">
                    Configure SMTP server settings for email notifications, test email delivery, 
                    and manage notification preferences.
                </p>
                
                <div class="list-group list-group-flush mb-3">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-envelope"></i> SMTP Configuration</span>
                        <a href="<?= BASE_URL ?>/settings/smtp" class="btn btn-sm btn-primary">
                            <i class="bi bi-gear"></i> Configure
                        </a>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 text-muted">
                        <span><i class="bi bi-bell"></i> Notification Preferences</span>
                        <span class="badge bg-secondary">Coming Soon</span>
                    </div>
                </div>
                
                <div class="alert alert-info mb-0">
                    <small>
                        <i class="bi bi-info-circle"></i>
                        <strong>Status:</strong> SMTP is 
                        <?php 
                        try {
                            $stmt = $db->query("SELECT is_active FROM smtp_settings LIMIT 1");
                            $smtp = $stmt->fetch();
                            echo $smtp && $smtp['is_active'] ? '<span class="text-success">Enabled</span>' : '<span class="text-danger">Disabled</span>';
                        } catch (Exception $e) {
                            echo '<span class="text-muted">Unknown</span>';
                        }
                        ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- User Management -->
    <div class="col-md-6 mb-3">
        <div class="card h-100 border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-people-fill"></i> User Management</h5>
            </div>
            <div class="card-body">
                <p class="card-text">
                    Manage system users, roles, permissions, and access control settings.
                </p>
                
                <div class="list-group list-group-flush mb-3">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-person-plus"></i> Manage Users</span>
                        <a href="<?= BASE_URL ?>/users" class="btn btn-sm btn-success">
                            <i class="bi bi-people"></i> View All
                        </a>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 text-muted">
                        <span><i class="bi bi-shield-check"></i> Role Permissions</span>
                        <span class="badge bg-secondary">Coming Soon</span>
                    </div>
                </div>
                
                <div class="alert alert-success mb-0">
                    <small>
                        <i class="bi bi-info-circle"></i>
                        <strong>Active Users:</strong> 
                        <?php 
                        try {
                            $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'active' AND deleted_at IS NULL");
                            echo $stmt->fetch()['count'];
                        } catch (Exception $e) {
                            echo 'N/A';
                        }
                        ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Master Data -->
    <div class="col-md-6 mb-3">
        <div class="card h-100 border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-database-gear"></i> Master Data</h5>
            </div>
            <div class="card-body">
                <p class="card-text">
                    Manage lookup tables, reference data, and system master lists.
                </p>
                
                <div class="list-group list-group-flush mb-3">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-database-gear"></i> All Master Data</span>
                        <a href="<?= BASE_URL ?>/masterdata/index" class="btn btn-sm btn-warning">
                            <i class="bi bi-grid"></i> Manage All
                        </a>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-clipboard-pulse"></i> Catheter Indications</span>
                        <a href="<?= BASE_URL ?>/masterdata/list/catheter_indications" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-gear"></i> Configure
                        </a>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-x-circle"></i> Removal Indications</span>
                        <a href="<?= BASE_URL ?>/masterdata/list/removal_indications" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-gear"></i> Configure
                        </a>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-hospital"></i> Specialties & Surgeries</span>
                        <a href="<?= BASE_URL ?>/masterdata/list/specialties" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-gear"></i> Configure
                        </a>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-heart-pulse"></i> Comorbidities</span>
                        <a href="<?= BASE_URL ?>/masterdata/list/comorbidities" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-gear"></i> Configure
                        </a>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-capsule"></i> Drugs & Adjuvants</span>
                        <a href="<?= BASE_URL ?>/masterdata/list/drugs" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-gear"></i> Configure
                        </a>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-exclamation-triangle"></i> Sentinel Events & Red Flags</span>
                        <a href="<?= BASE_URL ?>/masterdata/list/sentinel_events" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-gear"></i> Configure
                        </a>
                    </div>
                </div>
                
                <div class="alert alert-success mb-0">
                    <small>
                        <i class="bi bi-check-circle"></i>
                        <strong>v1.2.0:</strong> Master data management is now available!
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Configuration -->
    <div class="col-md-6 mb-3">
        <div class="card h-100 border-info">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-sliders"></i> System Configuration</h5>
            </div>
            <div class="card-body">
                <p class="card-text">
                    General system settings, application preferences, and configuration options.
                </p>
                
                <div class="list-group list-group-flush mb-3">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 text-muted">
                        <span><i class="bi bi-building"></i> Organization Details</span>
                        <span class="badge bg-secondary">Coming Soon</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 text-muted">
                        <span><i class="bi bi-palette"></i> Theme Settings</span>
                        <span class="badge bg-secondary">Coming Soon</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 text-muted">
                        <span><i class="bi bi-file-earmark-text"></i> Backup & Restore</span>
                        <span class="badge bg-secondary">Coming Soon</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 text-muted">
                        <span><i class="bi bi-graph-up"></i> Audit Logs</span>
                        <span class="badge bg-secondary">Coming Soon</span>
                    </div>
                </div>
                
                <div class="alert alert-info mb-0">
                    <small>
                        <i class="bi bi-info-circle"></i>
                        Additional settings coming in future updates
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reports Configuration -->
    <div class="col-md-6 mb-3">
        <div class="card h-100 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-file-earmark-bar-graph"></i> Reports & Analytics</h5>
            </div>
            <div class="card-body">
                <p class="card-text">
                    Configure report templates, analytics settings, and data export options.
                </p>
                
                <div class="list-group list-group-flush mb-3">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 text-muted">
                        <span><i class="bi bi-file-pdf"></i> Report Templates</span>
                        <span class="badge bg-secondary">Coming Soon</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 text-muted">
                        <span><i class="bi bi-calendar-range"></i> Scheduled Reports</span>
                        <span class="badge bg-secondary">Coming Soon</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 text-muted">
                        <span><i class="bi bi-download"></i> Export Settings</span>
                        <span class="badge bg-secondary">Coming Soon</span>
                    </div>
                </div>
                
                <div class="alert alert-danger mb-0">
                    <small>
                        <i class="bi bi-info-circle"></i>
                        Advanced reporting features planned for v1.2.0
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Information -->
    <div class="col-md-6 mb-3">
        <div class="card h-100 border-secondary">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle-fill"></i> System Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-3">
                    <tr>
                        <th width="40%">Application Version:</th>
                        <td><span class="badge bg-primary">v1.1.0</span></td>
                    </tr>
                    <tr>
                        <th>PHP Version:</th>
                        <td><?= phpversion() ?></td>
                    </tr>
                    <tr>
                        <th>Database:</th>
                        <td>MySQL <?php 
                            try {
                                $stmt = $db->query("SELECT VERSION() as version");
                                echo $stmt->fetch()['version'];
                            } catch (Exception $e) {
                                echo 'N/A';
                            }
                        ?></td>
                    </tr>
                    <tr>
                        <th>Server Time:</th>
                        <td><?= date('Y-m-d H:i:s') ?></td>
                    </tr>
                    <tr>
                        <th>Timezone:</th>
                        <td><?= date_default_timezone_get() ?></td>
                    </tr>
                </table>
                
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/CHANGELOG.md" class="btn btn-sm btn-outline-secondary" target="_blank">
                        <i class="bi bi-file-text"></i> View Changelog
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Access Shortcuts -->
<div class="card mt-3">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="bi bi-lightning-fill"></i> Quick Access</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-2">
                <a href="<?= BASE_URL ?>/settings/smtp" class="btn btn-outline-primary w-100">
                    <i class="bi bi-envelope"></i> SMTP Settings
                </a>
            </div>
            <div class="col-md-3 mb-2">
                <a href="<?= BASE_URL ?>/users" class="btn btn-outline-success w-100">
                    <i class="bi bi-people"></i> Manage Users
                </a>
            </div>
            <div class="col-md-3 mb-2">
                <a href="<?= BASE_URL ?>/reports" class="btn btn-outline-info w-100">
                    <i class="bi bi-graph-up"></i> View Reports
                </a>
            </div>
            <div class="col-md-3 mb-2">
                <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.card-header h5 {
    font-size: 1.1rem;
}
.list-group-item {
    border: none;
    padding: 0.5rem 0;
}
</style>
