<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-4">Create New Password</h4>
        
        <form method="POST" action="<?= BASE_URL ?>/auth/reset-password">
            <?= \Helpers\CSRF::field() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">
            
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password" required autofocus minlength="<?= PASSWORD_MIN_LENGTH ?>">
                <small class="text-muted">Minimum <?= PASSWORD_MIN_LENGTH ?> characters</small>
            </div>
            
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
            
            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>/auth/login" class="text-decoration-none">Back to Login</a>
            </div>
        </form>
    </div>
</div>
