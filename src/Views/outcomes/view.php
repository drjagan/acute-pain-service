<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2">Functional Outcome Details</h1>
        <p class="text-muted">
            Patient: <strong><?= e($outcome['patient_name']) ?></strong> 
            (HN: <?= e($outcome['hospital_number']) ?>) | POD: <strong><?= $outcome['pod'] ?></strong>
        </p>
    </div>
    <div>
        <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
        <a href="<?= BASE_URL ?>/outcomes/edit/<?= $outcome['id'] ?>" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $outcome['catheter_id'] ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Catheter
        </a>
    </div>
</div>

<!-- Functional Score Summary -->
<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-trophy"></i> Functional Score</h5>
            </div>
            <div class="card-body text-center">
                <h1 class="display-3 mb-0">
                    <span class="badge bg-<?= $functionalScore >= 75 ? 'success' : ($functionalScore >= 50 ? 'warning' : 'danger') ?> fs-1">
                        <?= $functionalScore ?>/100
                    </span>
                </h1>
                <p class="text-muted mt-2">
                    <?php if ($functionalScore >= 75): ?>
                        <strong>Excellent</strong> - Patient showing good functional recovery
                    <?php elseif ($functionalScore >= 50): ?>
                        <strong>Moderate</strong> - Patient showing partial functional recovery
                    <?php else: ?>
                        <strong>Poor</strong> - Patient requires additional support and monitoring
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Functional Assessment -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-heart-pulse"></i> Functional Assessment</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="50%">Incentive Spirometry:</th>
                        <td>
                            <?php
                            $spirometryClass = match($outcome['incentive_spirometry']) {
                                'yes' => 'success',
                                'partial' => 'warning',
                                'unable' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?= $spirometryClass ?>">
                                <?= ucwords(str_replace('_', ' ', $outcome['incentive_spirometry'])) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Ambulation:</th>
                        <td>
                            <?php
                            $ambulationClass = match($outcome['ambulation']) {
                                'independent' => 'success',
                                'assisted' => 'warning',
                                'bedbound' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?= $ambulationClass ?>">
                                <?= ucwords($outcome['ambulation']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Cough Ability:</th>
                        <td>
                            <?php
                            $coughClass = match($outcome['cough_ability']) {
                                'effective' => 'success',
                                'weak' => 'warning',
                                'unable' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?= $coughClass ?>">
                                <?= ucwords($outcome['cough_ability']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Assessment Date:</th>
                        <td><?= formatDate($outcome['entry_date']) ?></td>
                    </tr>
                    <tr>
                        <th>POD:</th>
                        <td>
                            <span class="badge bg-info">Day <?= $outcome['pod'] ?></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Oxygen Saturation -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-activity"></i> Oxygen Saturation</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <h6>Room Air SpO2 Status</h6>
                        <?php
                        $spo2Class = match($outcome['room_air_spo2']) {
                            'yes' => 'success',
                            'no' => 'warning',
                            'requires_o2' => 'danger',
                            default => 'secondary'
                        };
                        $spo2Text = match($outcome['room_air_spo2']) {
                            'yes' => 'Maintaining on room air',
                            'no' => 'Requires supplemental O2',
                            'requires_o2' => 'Unable to maintain - Requires O2',
                            default => 'Unknown'
                        };
                        ?>
                        <div class="alert alert-<?= $spo2Class ?> mb-0">
                            <strong><?= $spo2Text ?></strong>
                        </div>
                    </div>
                    
                    <?php if ($outcome['spo2_value']): ?>
                    <div class="col-12">
                        <h6>SpO2 Reading</h6>
                        <div class="text-center">
                            <span class="badge bg-<?= $outcome['spo2_value'] >= 95 ? 'success' : ($outcome['spo2_value'] >= 90 ? 'warning' : 'danger') ?> fs-3">
                                <?= $outcome['spo2_value'] ?>%
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complications & Events -->
<div class="row">
    <div class="col-md-12">
        <?php
        $hasComplications = ($outcome['catheter_site_infection'] !== 'none' || 
                            $outcome['sentinel_events'] !== 'none');
        ?>
        
        <div class="card mb-3 border-<?= $hasComplications ? 'danger' : 'success' ?>">
            <div class="card-header bg-<?= $hasComplications ? 'danger' : 'success' ?> text-white">
                <h5 class="mb-0">
                    <i class="bi bi-<?= $hasComplications ? 'exclamation-triangle' : 'check-circle' ?>"></i>
                    Complications & Events
                </h5>
            </div>
            <div class="card-body">
                <?php if (!$hasComplications): ?>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle"></i> No complications or sentinel events reported
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php if ($outcome['catheter_site_infection'] !== 'none'): ?>
                        <div class="col-md-6 mb-2">
                            <div class="alert alert-danger mb-0">
                                <strong><i class="bi bi-bug"></i> Catheter Site Infection:</strong><br>
                                <?= ucwords(str_replace('_', ' ', $outcome['catheter_site_infection'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($outcome['sentinel_events'] !== 'none'): ?>
                        <div class="col-md-6 mb-2">
                            <div class="alert alert-danger mb-0">
                                <strong><i class="bi bi-exclamation-octagon"></i> Sentinel Event:</strong><br>
                                <?= ucwords(str_replace('_', ' ', $outcome['sentinel_events'])) ?>
                                <?php if ($outcome['sentinel_event_details']): ?>
                                    <hr>
                                    <small><?= nl2br(e($outcome['sentinel_event_details'])) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Clinical Notes -->
<?php if ($outcome['clinical_notes']): ?>
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-chat-left-text"></i> Clinical Notes</h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?= nl2br(e($outcome['clinical_notes'])) ?></p>
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
                        <td><?= e($outcome['patient_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Hospital #:</th>
                        <td><?= e($outcome['hospital_number']) ?></td>
                    </tr>
                    <tr>
                        <th>Age:</th>
                        <td><?= $outcome['age'] ?> years</td>
                    </tr>
                    <tr>
                        <th>Gender:</th>
                        <td><?= ucfirst($outcome['gender']) ?></td>
                    </tr>
                </table>
                <div class="mt-2">
                    <a href="<?= BASE_URL ?>/patients/viewPatient/<?= $outcome['patient_id'] ?>" class="btn btn-sm btn-outline-primary w-100">
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
                        <td><?= e(ucwords(str_replace('_', ' ', $outcome['catheter_type']))) ?></td>
                    </tr>
                    <tr>
                        <th>Category:</th>
                        <td><?= e(ucwords(str_replace('_', ' ', $outcome['catheter_category']))) ?></td>
                    </tr>
                    <tr>
                        <th>Inserted:</th>
                        <td><?= formatDate($outcome['date_of_insertion']) ?></td>
                    </tr>
                    <tr>
                        <th>Days In Situ:</th>
                        <td>
                            <?php
                            $daysInserted = (new DateTime())->diff(new DateTime($outcome['date_of_insertion']))->days;
                            ?>
                            <span class="badge bg-info"><?= $daysInserted ?> days</span>
                        </td>
                    </tr>
                </table>
                <div class="mt-2">
                    <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $outcome['catheter_id'] ?>" class="btn btn-sm btn-outline-info w-100">
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
                            <strong>Created:</strong> <?= formatDate($outcome['created_at']) ?><br>
                            <strong>Created By:</strong> <?= e($outcome['created_by_name'] ?? 'Unknown') ?>
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small>
                            <strong>Last Updated:</strong> <?= formatDate($outcome['updated_at']) ?><br>
                            <strong>Updated By:</strong> <?= e($outcome['updated_by_name'] ?? 'N/A') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
