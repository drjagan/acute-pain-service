<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-4">Login to Your Account</h4>
        
        <form method="POST" action="<?= BASE_URL ?>/auth/login">
            <?= \Helpers\CSRF::field() ?>
            
            <div class="mb-3">
                <label for="username" class="form-label">Username or Email</label>
                <input type="text" class="form-control" id="username" name="username" required autofocus>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember me for 30 days</label>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
            
            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>/auth/forgot-password" class="text-decoration-none">Forgot your password?</a>
            </div>
        </form>
    </div>
</div>

<div class="mt-3 p-3 bg-info bg-opacity-10 rounded">
    <h6 class="mb-2">Test Credentials (Phase 1):</h6>
    <small>
        <strong>Admin:</strong> admin / admin123<br>
        <strong>Attending:</strong> dr.sharma / admin123<br>
        <strong>Resident:</strong> dr.patel / admin123<br>
        <strong>Nurse:</strong> nurse.kumar / admin123
    </small>
</div>
