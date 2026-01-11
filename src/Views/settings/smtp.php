<?php
/**
 * SMTP Settings Page (Admin Only)
 * Configure email notification settings
 */
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-envelope-gear"></i> SMTP Settings</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php if (isset($settings['last_tested_at']) && $settings['last_tested_at']): ?>
<div class="alert alert-<?= $settings['last_test_result'] == 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
    <i class="bi bi-<?= $settings['last_test_result'] == 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
    <strong>Last Test:</strong> 
    <?= $settings['last_test_result'] == 'success' ? 'Connection successful' : 'Connection failed' ?>
    (<?= date('Y-m-d H:i:s', strtotime($settings['last_tested_at'])) ?>)
    <?php if ($settings['last_test_error']): ?>
        <br><small><?= e($settings['last_test_error']) ?></small>
    <?php endif; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="bi bi-gear"></i> Email Configuration</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/settings/saveSMTP" id="smtpForm">
                    <input type="hidden" name="csrf_token" value="<?= \Helpers\CSRF::token() ?>">
                    
                    <!-- SMTP Server Settings -->
                    <h6 class="border-bottom pb-2 mb-3">SMTP Server</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="smtp_host" class="form-label">SMTP Host *</label>
                            <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                   value="<?= e($settings['smtp_host'] ?? '') ?>" required
                                   placeholder="smtp.gmail.com">
                            <small class="text-muted">e.g., smtp.gmail.com, smtp-mail.outlook.com</small>
                        </div>
                        <div class="col-md-4">
                            <label for="smtp_port" class="form-label">Port *</label>
                            <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                   value="<?= e($settings['smtp_port'] ?? 587) ?>" required min="1" max="65535">
                            <small class="text-muted">587 (TLS) or 465 (SSL)</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_encryption" class="form-label">Encryption</label>
                        <select class="form-select" id="smtp_encryption" name="smtp_encryption" required>
                            <option value="tls" <?= ($settings['smtp_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' ?>>TLS (Recommended)</option>
                            <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' ?>>SSL</option>
                            <option value="none" <?= ($settings['smtp_encryption'] ?? '') == 'none' ? 'selected' : '' ?>>None</option>
                        </select>
                    </div>
                    
                    <!-- Authentication -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Authentication</h6>
                    
                    <div class="mb-3">
                        <label for="smtp_username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                               value="<?= e($settings['smtp_username'] ?? '') ?>" required
                               placeholder="your-email@gmail.com">
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                               placeholder="<?= !empty($settings['smtp_password']) ? '••••••••' : 'Enter password' ?>">
                        <small class="text-muted">Leave blank to keep current password</small>
                    </div>
                    
                    <!-- Sender Details -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Sender Details</h6>
                    
                    <div class="mb-3">
                        <label for="from_email" class="form-label">From Email *</label>
                        <input type="email" class="form-control" id="from_email" name="from_email" 
                               value="<?= e($settings['from_email'] ?? '') ?>" required
                               placeholder="noreply@aps-system.com">
                    </div>
                    
                    <div class="mb-3">
                        <label for="from_name" class="form-label">From Name *</label>
                        <input type="text" class="form-control" id="from_name" name="from_name" 
                               value="<?= e($settings['from_name'] ?? 'APS System') ?>" required
                               placeholder="APS Notification System">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="reply_to_email" class="form-label">Reply-To Email</label>
                            <input type="email" class="form-control" id="reply_to_email" name="reply_to_email" 
                                   value="<?= e($settings['reply_to_email'] ?? '') ?>"
                                   placeholder="support@hospital.com">
                        </div>
                        <div class="col-md-6">
                            <label for="reply_to_name" class="form-label">Reply-To Name</label>
                            <input type="text" class="form-control" id="reply_to_name" name="reply_to_name" 
                                   value="<?= e($settings['reply_to_name'] ?? '') ?>"
                                   placeholder="APS Support Team">
                        </div>
                    </div>
                    
                    <!-- Additional Settings -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Additional Settings</h6>
                    
                    <div class="mb-3">
                        <label for="test_email" class="form-label">Test Email Address</label>
                        <input type="email" class="form-control" id="test_email" name="test_email" 
                               value="<?= e($settings['test_email'] ?? '') ?>"
                               placeholder="admin@hospital.com">
                        <small class="text-muted">Email address for sending test emails</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email_footer" class="form-label">Email Footer</label>
                        <textarea class="form-control" id="email_footer" name="email_footer" rows="3"
                                  placeholder="--&#10;Acute Pain Service&#10;Your Hospital Name"><?= e($settings['email_footer'] ?? '') ?></textarea>
                        <small class="text-muted">Footer text appended to all emails</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="max_emails_per_hour" class="form-label">Max Emails Per Hour</label>
                        <input type="number" class="form-control" id="max_emails_per_hour" name="max_emails_per_hour" 
                               value="<?= e($settings['max_emails_per_hour'] ?? 100) ?>" min="1" max="1000">
                        <small class="text-muted">Rate limit for email sending</small>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="use_html" name="use_html" 
                               value="1" <?= ($settings['use_html'] ?? true) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="use_html">
                            Send HTML Emails
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="smtp_debug" name="smtp_debug" 
                               value="1" <?= ($settings['smtp_debug'] ?? false) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="smtp_debug">
                            Enable Debug Mode
                        </label>
                        <small class="text-muted d-block">Shows detailed SMTP logs (for troubleshooting)</small>
                    </div>
                    
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                               value="1" <?= ($settings['is_active'] ?? false) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">
                            <strong>Enable Email Notifications</strong>
                        </label>
                        <small class="text-muted d-block">Master switch for all email notifications</small>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Settings
                        </button>
                        <button type="button" class="btn btn-success" id="testEmailBtn">
                            <i class="bi bi-envelope-check"></i> Send Test Email
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar with Info -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0"><i class="bi bi-info-circle"></i> Information</h6>
            </div>
            <div class="card-body">
                <h6>Email Status</h6>
                <p class="mb-2">
                    <span class="badge bg-<?= ($settings['is_active'] ?? false) ? 'success' : 'secondary' ?>">
                        <?= ($settings['is_active'] ?? false) ? 'Enabled' : 'Disabled' ?>
                    </span>
                </p>
                
                <h6 class="mt-3">PHPMailer Status</h6>
                <p class="mb-2">
                    <span class="badge bg-<?= class_exists('PHPMailer\PHPMailer\PHPMailer') ? 'success' : 'warning' ?>">
                        <?= class_exists('PHPMailer\PHPMailer\PHPMailer') ? 'Installed' : 'Not Installed' ?>
                    </span>
                </p>
                
                <?php if (!class_exists('PHPMailer\PHPMailer\PHPMailer')): ?>
                <div class="alert alert-warning mt-3">
                    <small>
                        <strong>Note:</strong> PHPMailer not detected. 
                        Email will use PHP mail() function as fallback.
                        <br><br>
                        To install PHPMailer:
                        <code class="d-block mt-1">composer require phpmailer/phpmailer</code>
                    </small>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h6 class="card-title mb-0"><i class="bi bi-book"></i> Quick Guide</h6>
            </div>
            <div class="card-body">
                <h6>Gmail Setup</h6>
                <ol class="small">
                    <li>Enable 2-Step Verification</li>
                    <li>Generate App Password</li>
                    <li>Use App Password here</li>
                </ol>
                
                <h6 class="mt-3">Common Ports</h6>
                <ul class="small">
                    <li><strong>587:</strong> TLS (Recommended)</li>
                    <li><strong>465:</strong> SSL</li>
                    <li><strong>25:</strong> No encryption</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Test email functionality
document.getElementById('testEmailBtn').addEventListener('click', function() {
    const testEmail = document.getElementById('test_email').value;
    
    if (!testEmail) {
        alert('Please enter a test email address');
        return;
    }
    
    const btn = this;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';
    
    fetch('<?= BASE_URL ?>/settings/testSMTP', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ test_email: testEmail })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ Test email sent successfully to ' + testEmail);
        } else {
            alert('✗ Test email failed: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});
</script>
