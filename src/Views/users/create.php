<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person-plus"></i> Add New User</h1>
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
                <form method="POST" action="<?= BASE_URL ?>/users/store">
                    <?= \Helpers\CSRF::field() ?>
                    
                    <div class="row g-3">
                        <!-- Username -->
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <small class="text-muted">Min 3 characters, unique</small>
                        </div>
                        
                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <!-- Password -->
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="text-muted">Min 6 characters</small>
                        </div>
                        
                        <!-- Role -->
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="attending">Attending Physician</option>
                                <option value="resident">Resident</option>
                                <option value="nurse">Nurse</option>
                            </select>
                        </div>
                        
                        <!-- First Name -->
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        
                        <!-- Last Name -->
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        
                        <!-- Phone -->
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        
                        <!-- Status -->
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create User
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
                <h6 class="card-title"><i class="bi bi-info-circle"></i> Role Descriptions</h6>
                <dl class="mb-0">
                    <dt class="text-danger">Admin</dt>
                    <dd class="small">Full system access, user management</dd>
                    
                    <dt class="text-primary">Attending</dt>
                    <dd class="small">Senior physician, all clinical features</dd>
                    
                    <dt class="text-info">Resident</dt>
                    <dd class="small">Training physician, all clinical features</dd>
                    
                    <dt class="text-secondary">Nurse</dt>
                    <dd class="small mb-0">Nursing staff, all clinical features</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
