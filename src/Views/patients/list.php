<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2">Patients</h1>
        <p class="text-muted">Manage patient registrations and demographics</p>
    </div>
    <?php if (hasAnyRole(['attending', 'resident', 'admin'])): ?>
    <div>
        <a href="<?= BASE_URL ?>/patients/create" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Register New Patient
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Search Bar -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/patients" class="row g-3">
            <div class="col-md-10">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search by name, hospital number, or diagnosis..." 
                       value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Patients Table -->
<div class="card">
    <div class="card-header">
        <strong>Total Patients:</strong> <?= $total ?>
    </div>
    <div class="card-body">
        <?php if (empty($patients)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No patients found. 
                <?php if (hasAnyRole(['attending', 'resident', 'admin'])): ?>
                    <a href="<?= BASE_URL ?>/patients/create">Register the first patient</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Hospital #</th>
                            <th>Patient Name</th>
                            <th>Age/Gender</th>
                            <th>Speciality</th>
                            <th>Status</th>
                            <th>Admission Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><strong><?= e($patient['hospital_number']) ?></strong></td>
                            <td><?= e($patient['patient_name']) ?></td>
                            <td><?= $patient['age'] ?> / <?= ucfirst($patient['gender']) ?></td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= e(ucwords(str_replace('_', ' ', $patient['speciality']))) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusClass = [
                                    'admitted' => 'info',
                                    'active_catheter' => 'success',
                                    'discharged' => 'secondary',
                                    'transferred' => 'warning'
                                ];
                                $class = $statusClass[$patient['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $class ?>">
                                    <?= ucwords(str_replace('_', ' ', $patient['status'])) ?>
                                </span>
                            </td>
                            <td><?= formatDate($patient['admission_date']) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= BASE_URL ?>/patients/viewPatient/<?= $patient['id'] ?>" 
                                       class="btn btn-outline-primary" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (hasAnyRole(['attending', 'resident', 'admin'])): ?>
                                    <a href="<?= BASE_URL ?>/patients/edit/<?= $patient['id'] ?>" 
                                       class="btn btn-outline-secondary" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total > $perPage): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php
                    $totalPages = ceil($total / $perPage);
                    for ($i = 1; $i <= $totalPages; $i++):
                    ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/patients?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
