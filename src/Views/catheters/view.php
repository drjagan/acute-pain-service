<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2">Catheter Details</h1>
        <p class="text-muted">
            Patient: <strong><?= e($catheter['patient_name']) ?></strong> 
            (HN: <?= e($catheter['hospital_number']) ?>)
        </p>
    </div>
    <div>
        <?php if (hasAnyRole(['attending', 'resident', 'admin'])): ?>
        <a href="<?= BASE_URL ?>/catheters/edit/<?= $catheter['id'] ?>" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/patients/viewPatient/<?= $catheter['patient_id'] ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Patient
        </a>
    </div>
</div>

<div class="row">
    <!-- Catheter Information -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-diagram-3"></i> Catheter Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Category:</th>
                        <td>
                            <span class="badge bg-primary">
                                <?= e($categories[$catheter['catheter_category']] ?? $catheter['catheter_category']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Specific Type:</th>
                        <td>
                            <strong>
                                <?php
                                $types = $catheterTypes[$catheter['catheter_category']] ?? [];
                                echo e($types[$catheter['catheter_type']] ?? $catheter['catheter_type']);
                                ?>
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <th>Date of Insertion:</th>
                        <td><?= formatDate($catheter['date_of_insertion']) ?></td>
                    </tr>
                    <tr>
                        <th>Days In Situ:</th>
                        <td>
                            <?php
                            $daysInserted = (new DateTime())->diff(new DateTime($catheter['date_of_insertion']))->days;
                            $colorClass = $daysInserted > 5 ? 'text-warning' : 'text-success';
                            ?>
                            <span class="<?= $colorClass ?>">
                                <strong><?= $daysInserted ?> days</strong>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <?php
                            $statusClass = [
                                'active' => 'success',
                                'removed' => 'secondary',
                                'displaced' => 'warning',
                                'infected' => 'danger'
                            ];
                            $class = $statusClass[$catheter['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $class ?>">
                                <?= ucfirst($catheter['status']) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Insertion Details -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-clipboard2-pulse"></i> Insertion Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Settings:</th>
                        <td>
                            <span class="badge bg-<?= $catheter['settings'] === 'emergency' ? 'danger' : 'info' ?>">
                                <?= ucfirst($catheter['settings']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Performed By:</th>
                        <td><?= ucfirst($catheter['performer']) ?></td>
                    </tr>
                    <tr>
                        <th>Indication:</th>
                        <td><?= nl2br(e($catheter['indication'])) ?></td>
                    </tr>
                    <tr>
                        <th>Confirmations:</th>
                        <td>
                            <?php if ($catheter['functional_confirmation']): ?>
                                <span class="badge bg-success me-1">
                                    <i class="bi bi-check-circle"></i> Functional
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($catheter['anatomical_confirmation']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Anatomical
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!$catheter['functional_confirmation'] && !$catheter['anatomical_confirmation']): ?>
                                <em class="text-muted">No confirmations recorded</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Patient Demographics -->
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Patient Demographics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Name:</strong> <?= e($catheter['patient_name']) ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Hospital #:</strong> <?= e($catheter['hospital_number']) ?>
                    </div>
                    <div class="col-md-2">
                        <strong>Age:</strong> <?= $catheter['age'] ?> years
                    </div>
                    <div class="col-md-2">
                        <strong>Gender:</strong> <?= ucfirst($catheter['gender']) ?>
                    </div>
                    <div class="col-md-2">
                        <a href="<?= BASE_URL ?>/patients/viewPatient/<?= $catheter['patient_id'] ?>" class="btn btn-sm btn-outline-primary">
                            View Full Profile
                        </a>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <strong>Diagnosis:</strong> <?= nl2br(e($catheter['diagnosis'])) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Red Flags -->
<?php if (!empty($catheter['red_flag_names'])): ?>
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3 border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-triangle"></i> Red Flags / Complications
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($catheter['red_flag_names'] as $flag): ?>
                    <div class="col-md-6 mb-2">
                        <div class="alert alert-<?= $flag['severity'] === 'severe' ? 'danger' : ($flag['severity'] === 'moderate' ? 'warning' : 'info') ?> mb-0">
                            <strong><?= e($flag['name']) ?></strong>
                            <?php if ($flag['requires_immediate_action']): ?>
                                <span class="badge bg-danger float-end">URGENT ACTION REQUIRED</span>
                            <?php endif; ?>
                            <br>
                            <small>Severity: <?= ucfirst($flag['severity']) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> No red flags or complications reported during insertion
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Audit Trail -->
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Audit Trail</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <small>
                            <strong>Created:</strong> <?= formatDate($catheter['created_at']) ?><br>
                            <strong>Created By:</strong> <?= e($catheter['created_by_name'] ?? 'Unknown') ?>
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small>
                            <strong>Last Updated:</strong> <?= formatDate($catheter['updated_at']) ?><br>
                            <strong>Updated By:</strong> <?= e($catheter['updated_by_name'] ?? 'N/A') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Drug Regimes Section -->
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-capsule"></i> Drug Regimes</h5>
                <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
                <a href="<?= BASE_URL ?>/regimes/create?catheter_id=<?= $catheter['id'] ?>" class="btn btn-sm btn-light">
                    <i class="bi bi-plus-circle"></i> Record New Drug Regime
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($regimes)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No drug regimes recorded for this catheter.
                        <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
                            <a href="<?= BASE_URL ?>/regimes/create?catheter_id=<?= $catheter['id'] ?>">Record first drug regime</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>POD</th>
                                    <th>Entry Date</th>
                                    <th>Drug Regime</th>
                                    <th>VNRS Improvement</th>
                                    <th>Effective</th>
                                    <th>Side Effects</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($regimes as $regime): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-info">Day <?= $regime['pod'] ?></span>
                                    </td>
                                    <td><?= formatDate($regime['entry_date']) ?></td>
                                    <td>
                                        <strong><?= e($regime['drug']) ?></strong> <?= $regime['concentration'] ?>%<br>
                                        <small class="text-muted">
                                            <?= $regime['volume'] ?> ml/hr
                                            <?php if ($regime['adjuvant']): ?>
                                                + <?= e($regime['adjuvant']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $staticImprovement = $regime['baseline_vnrs_static'] - $regime['vnrs_15min_static'];
                                        $dynamicImprovement = $regime['baseline_vnrs_dynamic'] - $regime['vnrs_15min_dynamic'];
                                        $avgImprovement = ($staticImprovement + $dynamicImprovement) / 2;
                                        $improvementClass = $avgImprovement >= 2 ? 'success' : ($avgImprovement > 0 ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge bg-<?= $improvementClass ?>">
                                            <?= number_format($avgImprovement, 1) ?> pts
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($regime['effective_analgesia']): ?>
                                            <i class="bi bi-check-circle text-success fs-5"></i>
                                        <?php else: ?>
                                            <i class="bi bi-exclamation-circle text-warning fs-5"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $hasSideEffects = ($regime['hypotension'] !== 'none' || 
                                                          $regime['bradycardia'] !== 'none' || 
                                                          $regime['sensory_motor_deficit'] !== 'none' || 
                                                          $regime['nausea_vomiting'] !== 'none');
                                        ?>
                                        <?php if ($hasSideEffects): ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-exclamation-triangle"></i> Yes
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> None
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/regimes/viewRegime/<?= $regime['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Form (for active catheters) -->
<?php if ($catheter['status'] === 'active' && hasAnyRole(['attending', 'resident', 'admin'])): ?>
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3 border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> Update Catheter Status</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/catheters/updateStatus/<?= $catheter['id'] ?>" class="row g-3">
                    <?= \Helpers\CSRF::field() ?>
                    
                    <div class="col-md-6">
                        <label for="status" class="form-label">New Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Select status...</option>
                            <option value="removed">Removed (Planned)</option>
                            <option value="displaced">Displaced (Requires Removal)</option>
                            <option value="infected">Infected (Requires Removal)</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-arrow-repeat"></i> Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
