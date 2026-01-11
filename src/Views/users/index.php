<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-people"></i> User Management</h1>
    <a href="<?= BASE_URL ?>/users/create" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Add New User
    </a>
</div>

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/users" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Username, name, or email" 
                       value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="admin" <?= ($roleFilter ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="attending" <?= ($roleFilter ?? '') === 'attending' ? 'selected' : '' ?>>Attending</option>
                    <option value="resident" <?= ($roleFilter ?? '') === 'resident' ? 'selected' : '' ?>>Resident</option>
                    <option value="nurse" <?= ($roleFilter ?? '') === 'nurse' ? 'selected' : '' ?>>Nurse</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?= ($statusFilter ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($statusFilter ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="suspended" <?= ($statusFilter ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No users found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <strong><?= e($user['username']) ?></strong>
                                </td>
                                <td><?= e($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                <td><?= e($user['email']) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $user['role'] === 'admin' ? 'danger' : 
                                        ($user['role'] === 'attending' ? 'primary' : 
                                        ($user['role'] === 'resident' ? 'info' : 'secondary')) 
                                    ?>">
                                        <?= ucfirst(e($user['role'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $user['status'] === 'active' ? 'success' : 
                                        ($user['status'] === 'inactive' ? 'secondary' : 'warning') 
                                    ?>">
                                        <?= ucfirst(e($user['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['last_login_at']): ?>
                                        <small class="text-muted">
                                            <?= date('M d, Y H:i', strtotime($user['last_login_at'])) ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">Never</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= BASE_URL ?>/users/edit/<?= $user['id'] ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <?php if ($user['id'] != currentUser()['id']): ?>
                                            <!-- Toggle Status -->
                                            <form method="POST" action="<?= BASE_URL ?>/users/toggleStatus/<?= $user['id'] ?>" 
                                                  class="d-inline" onsubmit="return confirm('Toggle user status?');">
                                                <?= \Helpers\CSRF::field() ?>
                                                <button type="submit" class="btn btn-outline-<?= $user['status'] === 'active' ? 'warning' : 'success' ?>" 
                                                        title="<?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                                    <i class="bi bi-<?= $user['status'] === 'active' ? 'pause-circle' : 'play-circle' ?>"></i>
                                                </button>
                                            </form>
                                            
                                            <!-- Delete -->
                                            <form method="POST" action="<?= BASE_URL ?>/users/delete/<?= $user['id'] ?>" 
                                                  class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <?= \Helpers\CSRF::field() ?>
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-secondary" disabled title="Cannot modify own account">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Users pagination" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>">
                                Previous
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>">
                                Next
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="text-center text-muted">
                <small>Showing <?= count($users) ?> of <?= $total ?> users</small>
            </div>
        <?php endif; ?>
    </div>
</div>
