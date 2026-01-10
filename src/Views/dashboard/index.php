<div class="dashboard-header mb-4">
    <h1 class="h2">Dashboard</h1>
    <p class="text-muted">Welcome back, <?= e($user['first_name'] . ' ' . $user['last_name']) ?> (<?= ucfirst(e($user['role'])) ?>)</p>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Patients</h5>
                <h2><?= $stats['total_patients'] ?></h2>
                <small>Phase 2+</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Active Catheters</h5>
                <h2><?= $stats['active_catheters'] ?></h2>
                <small>Phase 2+</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Pending Alerts</h5>
                <h2><?= $stats['pending_alerts'] ?></h2>
                <small>Phase 4+</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">My Role</h5>
                <h4><?= ucfirst(e($user['role'])) ?></h4>
                <small>Access Level</small>
            </div>
        </div>
    </div>
</div>

<!-- Phase 1 Information -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-check-circle"></i> Phase 1: Foundation - Complete!</h5>
            </div>
            <div class="card-body">
                <h6>✅ What's Working:</h6>
                <ul>
                    <li>✓ User Authentication (Login, Logout, Remember Me)</li>
                    <li>✓ Password Reset Workflow (Email stub)</li>
                    <li>✓ Session Management with Timeout</li>
                    <li>✓ CSRF Protection on All Forms</li>
                    <li>✓ Role-Based Access Control (4 roles)</li>
                    <li>✓ Database Schema (13 tables created)</li>
                    <li>✓ MVC Architecture with Clean Code Structure</li>
                </ul>
                
                <h6 class="mt-3">🚀 Next Steps (Phase 2):</h6>
                <ul>
                    <li>Screen 1: Patient Registration & Demographics</li>
                    <li>Screen 2: Catheter Insertion & Procedural Details</li>
                    <li>Enhanced RBAC with Permission Matrix</li>
                </ul>
                
                <h6 class="mt-3">📚 Test Accounts:</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Permissions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-danger">Admin</span></td>
                                <td>admin</td>
                                <td>admin123</td>
                                <td>Full system access, user management</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">Attending</span></td>
                                <td>dr.sharma</td>
                                <td>admin123</td>
                                <td>All clinical screens, approvals</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-info">Resident</span></td>
                                <td>dr.patel</td>
                                <td>admin123</td>
                                <td>Data entry, limited approvals</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">Nurse</span></td>
                                <td>nurse.kumar</td>
                                <td>admin123</td>
                                <td>Daily monitoring, data entry</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
