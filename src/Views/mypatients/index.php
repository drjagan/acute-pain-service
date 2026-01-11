<?php
/**
 * My Patients Page (v1.1)
 * Shows all patients assigned to the logged-in physician
 */
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2"><i class="bi bi-person-heart"></i> My Patients</h1>
        <p class="text-muted mb-0">Patients assigned to <?= e($user['first_name'] . ' ' . $user['last_name']) ?></p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <span class="badge bg-primary fs-6 px-3 py-2">
            <?= $total ?> Patient<?= $total != 1 ? 's' : '' ?> Assigned
        </span>
    </div>
</div>

<?php if (empty($patients)): ?>
    <!-- Empty State -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3">No Patients Assigned</h4>
            <p class="text-muted">
                You don't have any patients assigned to you yet.<br>
                Patients will appear here when they are assigned to you by administrators or attending physicians.
            </p>
            <a href="<?= BASE_URL ?>/patients" class="btn btn-primary mt-2">
                <i class="bi bi-list"></i> View All Patients
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Patients Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Patient Name</th>
                            <th>Hospital #</th>
                            <th>Demographics</th>
                            <th>Specialty</th>
                            <th>Catheter Status</th>
                            <th>Assigned Date</th>
                            <th>Assignment Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $index => $patient): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <strong><?= e($patient['patient_name']) ?></strong>
                                <?php if ($patient['is_primary']): ?>
                                    <span class="badge bg-warning text-dark ms-1" title="You are the primary <?= $patient['physician_type'] ?>">
                                        <i class="bi bi-star-fill"></i> Primary
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code><?= e($patient['hospital_number']) ?></code>
                            </td>
                            <td>
                                <div class="small">
                                    <i class="bi bi-person"></i> <?= $patient['age'] ?> years<br>
                                    <i class="bi bi-gender-<?= $patient['gender'] == 'male' ? 'male' : 'female' ?>"></i> 
                                    <?= ucfirst($patient['gender']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= e(ucwords(str_replace('_', ' ', $patient['speciality']))) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($patient['active_catheters'] > 0): ?>
                                    <div>
                                        <span class="badge bg-success mb-1">
                                            <i class="bi bi-check-circle"></i> 
                                            <?= $patient['active_catheters'] ?> Active
                                        </span>
                                        <?php if ($patient['latest_catheter']): ?>
                                            <div class="small text-muted">
                                                <?= e($patient['latest_catheter']['catheter_type']) ?><br>
                                                <small>Since: <?= date('M j', strtotime($patient['latest_catheter']['date_of_insertion'])) ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No Active Catheter</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="small">
                                    <?= date('M j, Y', strtotime($patient['assigned_at'])) ?><br>
                                    <span class="text-muted"><?= date('H:i', strtotime($patient['assigned_at'])) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $patient['physician_type'] == 'attending' ? 'primary' : 'info' ?>">
                                    <i class="bi bi-person-badge"></i>
                                    <?= ucfirst($patient['physician_type']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= BASE_URL ?>/patients/viewPatient/<?= $patient['patient_id'] ?>" 
                                       class="btn btn-outline-primary"
                                       title="View Patient">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (hasAnyRole(['attending', 'admin'])): ?>
                                        <a href="<?= BASE_URL ?>/patients/edit/<?= $patient['patient_id'] ?>" 
                                           class="btn btn-outline-secondary"
                                           title="Edit Patient">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($patient['active_catheters'] > 0): ?>
                                        <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $patient['latest_catheter']['id'] ?? '#' ?>" 
                                           class="btn btn-outline-success"
                                           title="View Active Catheter">
                                            <i class="bi bi-clipboard-pulse"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>/catheters/create?patient_id=<?= $patient['patient_id'] ?>" 
                                           class="btn btn-outline-success"
                                           title="Insert Catheter">
                                            <i class="bi bi-plus-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Summary Footer -->
        <div class="card-footer bg-light">
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Showing all <?= $total ?> assigned patient<?= $total != 1 ? 's' : '' ?>
                    </small>
                </div>
                <div class="col-md-8 text-md-end">
                    <small class="text-muted">
                        <i class="bi bi-star-fill text-warning"></i> = You are the primary physician |
                        <i class="bi bi-check-circle text-success"></i> = Active catheter
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="row mt-3">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= $total ?></h3>
                    <small class="text-muted">Total Assigned</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <?php 
                    $withCatheters = array_filter($patients, fn($p) => $p['active_catheters'] > 0);
                    ?>
                    <h3 class="mb-0 text-success"><?= count($withCatheters) ?></h3>
                    <small class="text-muted">With Active Catheters</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <?php 
                    $primaryCount = array_filter($patients, fn($p) => $p['is_primary']);
                    ?>
                    <h3 class="mb-0 text-warning"><?= count($primaryCount) ?></h3>
                    <small class="text-muted">As Primary</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <?php 
                    $asAttending = array_filter($patients, fn($p) => $p['physician_type'] == 'attending');
                    ?>
                    <h3 class="mb-0 text-primary"><?= count($asAttending) ?></h3>
                    <small class="text-muted">As Attending</small>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
