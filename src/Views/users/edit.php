<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil"></i> Edit User</h1>
    <a href="<?= BASE_URL ?>/users" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/users/update/<?= $user['id'] ?>">
                    <?= \Helpers\CSRF::field() ?>
                    
                    <div class="row g-3">
                        <!-- Username (Read-only) -->
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?= e($user['username']) ?>" disabled>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>
                        
                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= e($user['email']) ?>" required>
                        </div>
                        
                        <!-- Password -->
                        <div class="col-md-6">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current">
                            <small class="text-muted">Min 6 characters (leave blank to keep current)</small>
                        </div>
                        
                        <!-- Role -->
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="attending" <?= $user['role'] === 'attending' ? 'selected' : '' ?>>Attending Physician</option>
                                <option value="resident" <?= $user['role'] === 'resident' ? 'selected' : '' ?>>Resident</option>
                                <option value="nurse" <?= $user['role'] === 'nurse' ? 'selected' : '' ?>>Nurse</option>
                            </select>
                        </div>
                        
                        <!-- First Name -->
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= e($user['first_name']) ?>" required>
                        </div>
                        
                        <!-- Last Name -->
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= e($user['last_name']) ?>" required>
                        </div>
                        
                        <!-- Phone -->
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?= e($user['phone'] ?? '') ?>">
                        </div>
                        
                        <!-- Status -->
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="suspended" <?= $user['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update User
                        </button>
                        <a href="<?= BASE_URL ?>/users" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-info-circle"></i> User Details</h6>
                <dl class="mb-0 small">
                    <dt>Created</dt>
                    <dd><?= date('M d, Y H:i', strtotime($user['created_at'])) ?></dd>
                    
                    <dt>Last Updated</dt>
                    <dd><?= date('M d, Y H:i', strtotime($user['updated_at'])) ?></dd>
                    
                    <dt>Last Login</dt>
                    <dd><?= $user['last_login_at'] ? date('M d, Y H:i', strtotime($user['last_login_at'])) : 'Never' ?></dd>
                    
                    <dt>Last Login IP</dt>
                    <dd class="mb-0"><?= $user['last_login_ip'] ?? 'N/A' ?></dd>
                </dl>
            </div>
        </div>
        
        <?php if ($user['id'] != currentUser()['id']): ?>
        <div class="card border-danger mt-3">
            <div class="card-body">
                <h6 class="card-title text-danger"><i class="bi bi-exclamation-triangle"></i> Danger Zone</h6>
                <p class="small mb-2">Permanently delete this user account</p>
                <form method="POST" action="<?= BASE_URL ?>/users/delete/<?= $user['id'] ?>" onsubmit="return confirm('Are you absolutely sure? This cannot be undone!');">
                    <?= \Helpers\CSRF::field() ?>
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="bi bi-trash"></i> Delete User
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
