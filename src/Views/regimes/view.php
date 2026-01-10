<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2">Drug Regime Details</h1>
        <p class="text-muted">
            Patient: <strong><?= e($regime['patient_name']) ?></strong> 
            (HN: <?= e($regime['hospital_number']) ?>) | POD: <strong><?= $regime['pod'] ?></strong>
        </p>
    </div>
    <div>
        <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
        <a href="<?= BASE_URL ?>/regimes/edit/<?= $regime['id'] ?>" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $regime['catheter_id'] ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Catheter
        </a>
    </div>
</div>

<div class="row">
    <!-- Drug Regime Details -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-capsule"></i> Drug Regime</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Drug:</th>
                        <td><strong><?= e($regime['drug']) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Concentration:</th>
                        <td><?= $regime['concentration'] ?>%</td>
                    </tr>
                    <tr>
                        <th>Volume:</th>
                        <td><?= $regime['volume'] ?> ml/hr</td>
                    </tr>
                    <tr>
                        <th>Adjuvant:</th>
                        <td>
                            <?php if ($regime['adjuvant']): ?>
                                <strong><?= e($regime['adjuvant']) ?></strong>
                                <?php if ($regime['dose']): ?>
                                    (<?= $regime['dose'] ?> dose)
                                <?php endif; ?>
                            <?php else: ?>
                                <em class="text-muted">No adjuvant</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Entry Date:</th>
                        <td><?= formatDate($regime['entry_date']) ?></td>
                    </tr>
                    <tr>
                        <th>POD:</th>
                        <td>
                            <span class="badge bg-info">Day <?= $regime['pod'] ?></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Pain Scores -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-clipboard2-data"></i> Pain Assessment (VNRS)</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <h6 class="text-primary">Baseline</h6>
                        <div class="mb-2">
                            <small class="text-muted">At Rest:</small><br>
                            <span class="badge bg-<?= $regime['baseline_vnrs_static'] > 6 ? 'danger' : ($regime['baseline_vnrs_static'] > 3 ? 'warning' : 'success') ?> fs-5">
                                <?= $regime['baseline_vnrs_static'] ?>/10
                            </span>
                        </div>
                        <div>
                            <small class="text-muted">On Movement:</small><br>
                            <span class="badge bg-<?= $regime['baseline_vnrs_dynamic'] > 6 ? 'danger' : ($regime['baseline_vnrs_dynamic'] > 3 ? 'warning' : 'success') ?> fs-5">
                                <?= $regime['baseline_vnrs_dynamic'] ?>/10
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <h6 class="text-success">15 Minutes Post</h6>
                        <div class="mb-2">
                            <small class="text-muted">At Rest:</small><br>
                            <span class="badge bg-<?= $regime['vnrs_15min_static'] > 6 ? 'danger' : ($regime['vnrs_15min_static'] > 3 ? 'warning' : 'success') ?> fs-5">
                                <?= $regime['vnrs_15min_static'] ?>/10
                            </span>
                        </div>
                        <div>
                            <small class="text-muted">On Movement:</small><br>
                            <span class="badge bg-<?= $regime['vnrs_15min_dynamic'] > 6 ? 'danger' : ($regime['vnrs_15min_dynamic'] > 3 ? 'warning' : 'success') ?> fs-5">
                                <?= $regime['vnrs_15min_dynamic'] ?>/10
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Pain Improvement -->
                <div class="alert alert-<?= $painImprovement['average'] >= 2 ? 'success' : ($painImprovement['average'] > 0 ? 'warning' : 'danger') ?>">
                    <strong>Pain Improvement:</strong><br>
                    <i class="bi bi-arrow-down-circle"></i> At Rest: <strong><?= abs($painImprovement['static']) ?> points</strong><br>
                    <i class="bi bi-arrow-down-circle"></i> On Movement: <strong><?= abs($painImprovement['dynamic']) ?> points</strong><br>
                    <i class="bi bi-graph-down"></i> Average: <strong><?= number_format($painImprovement['average'], 1) ?> points</strong>
                </div>
                
                <!-- Effective Analgesia -->
                <div class="text-center">
                    <?php if ($regime['effective_analgesia']): ?>
                        <span class="badge bg-success fs-6">
                            <i class="bi bi-check-circle"></i> Effective Analgesia Achieved
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning fs-6">
                            <i class="bi bi-exclamation-circle"></i> Analgesia Not Fully Effective
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Side Effects -->
<div class="row">
    <div class="col-md-12">
        <?php
        $hasSideEffects = ($regime['hypotension'] !== 'none' || 
                          $regime['bradycardia'] !== 'none' || 
                          $regime['sensory_motor_deficit'] !== 'none' || 
                          $regime['nausea_vomiting'] !== 'none');
        ?>
        
        <div class="card mb-3 border-<?= $hasSideEffects ? 'danger' : 'success' ?>">
            <div class="card-header bg-<?= $hasSideEffects ? 'danger' : 'success' ?> text-white">
                <h5 class="mb-0">
                    <i class="bi bi-<?= $hasSideEffects ? 'exclamation-triangle' : 'check-circle' ?>"></i>
                    Side Effects
                </h5>
            </div>
            <div class="card-body">
                <?php if (!$hasSideEffects): ?>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle"></i> No side effects reported
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php if ($regime['hypotension'] !== 'none'): ?>
                        <div class="col-md-6 mb-2">
                            <div class="alert alert-<?= $regime['hypotension'] === 'severe' ? 'danger' : 'warning' ?> mb-0">
                                <strong>Hypotension:</strong> <?= ucfirst($regime['hypotension']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($regime['bradycardia'] !== 'none'): ?>
                        <div class="col-md-6 mb-2">
                            <div class="alert alert-<?= $regime['bradycardia'] === 'severe' ? 'danger' : 'warning' ?> mb-0">
                                <strong>Bradycardia:</strong> <?= ucfirst($regime['bradycardia']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($regime['sensory_motor_deficit'] !== 'none'): ?>
                        <div class="col-md-6 mb-2">
                            <div class="alert alert-<?= $regime['sensory_motor_deficit'] === 'severe' ? 'danger' : 'warning' ?> mb-0">
                                <strong>Sensory/Motor Deficit:</strong> <?= ucfirst($regime['sensory_motor_deficit']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($regime['nausea_vomiting'] !== 'none'): ?>
                        <div class="col-md-6 mb-2">
                            <div class="alert alert-<?= $regime['nausea_vomiting'] === 'severe' ? 'danger' : 'warning' ?> mb-0">
                                <strong>Nausea/Vomiting:</strong> <?= ucfirst($regime['nausea_vomiting']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Troubleshooting & Clinical Notes -->
<?php if ($regime['troubleshooting_activated'] || $regime['clinical_notes']): ?>
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-tools"></i> Troubleshooting & Clinical Notes</h5>
            </div>
            <div class="card-body">
                <?php if ($regime['troubleshooting_activated']): ?>
                <div class="alert alert-warning">
                    <strong><i class="bi bi-exclamation-triangle"></i> Troubleshooting Activated</strong>
                    <?php if ($regime['troubleshooting_notes']): ?>
                        <p class="mb-0 mt-2"><?= nl2br(e($regime['troubleshooting_notes'])) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($regime['clinical_notes']): ?>
                <div>
                    <strong>Clinical Notes:</strong>
                    <p class="mb-0"><?= nl2br(e($regime['clinical_notes'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Patient & Catheter Information -->
<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Patient Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <th width="40%">Name:</th>
                        <td><?= e($regime['patient_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Hospital #:</th>
                        <td><?= e($regime['hospital_number']) ?></td>
                    </tr>
                    <tr>
                        <th>Age:</th>
                        <td><?= $regime['age'] ?> years</td>
                    </tr>
                    <tr>
                        <th>Gender:</th>
                        <td><?= ucfirst($regime['gender']) ?></td>
                    </tr>
                </table>
                <div class="mt-2">
                    <a href="<?= BASE_URL ?>/patients/viewPatient/<?= $regime['patient_id'] ?>" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-person"></i> View Full Patient Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-file-medical"></i> Catheter Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <th width="40%">Type:</th>
                        <td><?= e(ucwords(str_replace('_', ' ', $regime['catheter_type']))) ?></td>
                    </tr>
                    <tr>
                        <th>Category:</th>
                        <td><?= e(ucwords(str_replace('_', ' ', $regime['catheter_category']))) ?></td>
                    </tr>
                    <tr>
                        <th>Inserted:</th>
                        <td><?= formatDate($regime['date_of_insertion']) ?></td>
                    </tr>
                    <tr>
                        <th>Days In Situ:</th>
                        <td>
                            <?php
                            $daysInserted = (new DateTime())->diff(new DateTime($regime['date_of_insertion']))->days;
                            ?>
                            <span class="badge bg-info"><?= $daysInserted ?> days</span>
                        </td>
                    </tr>
                </table>
                <div class="mt-2">
                    <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $regime['catheter_id'] ?>" class="btn btn-sm btn-outline-info w-100">
                        <i class="bi bi-file-medical"></i> View Catheter Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

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
                            <strong>Created:</strong> <?= formatDate($regime['created_at']) ?><br>
                            <strong>Created By:</strong> <?= e($regime['created_by_name'] ?? 'Unknown') ?>
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small>
                            <strong>Last Updated:</strong> <?= formatDate($regime['updated_at']) ?><br>
                            <strong>Updated By:</strong> <?= e($regime['updated_by_name'] ?? 'N/A') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
