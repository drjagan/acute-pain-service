<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-4">Reset Your Password</h4>
        <p class="text-muted mb-4">Enter your email address and we'll send you a password reset link.</p>
        
        <form method="POST" action="<?= BASE_URL ?>/auth/forgot-password">
            <?= \Helpers\CSRF::field() ?>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required autofocus>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Send Reset Link</button>
            </div>
            
            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>/auth/login" class="text-decoration-none">Back to Login</a>
            </div>
        </form>
    </div>
</div>

<div class="mt-3 p-3 bg-warning bg-opacity-10 rounded">
    <small><i class="bi bi-info-circle"></i> <strong>Phase 1 Note:</strong> Reset link will be logged to <code>logs/email.log</code> instead of being emailed.</small>
</div>
