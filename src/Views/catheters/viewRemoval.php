<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2">Catheter Removal Record</h1>
        <p class="text-muted">
            Patient: <strong><?= e($removal['patient_name']) ?></strong> 
            (HN: <?= e($removal['hospital_number']) ?>)
        </p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $removal['catheter_id'] ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Catheter
        </a>
    </div>
</div>

<div class="row">
    <!-- Removal Details -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-calendar-x"></i> Removal Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="50%">Date of Removal:</th>
                        <td><strong><?= formatDate($removal['date_of_removal']) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Catheter Days:</th>
                        <td>
                            <span class="badge bg-<?= $removal['number_of_catheter_days'] > 7 ? 'warning' : 'success' ?>">
                                <?= $removal['number_of_catheter_days'] ?> days
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Catheter Tip Status:</th>
                        <td>
                            <?php if ($removal['catheter_tip_intact']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Intact
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-exclamation-triangle"></i> Not Intact
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Indication:</th>
                        <td>
                            <strong><?= e($indications[$removal['indication']] ?? ucwords(str_replace('_', ' ', $removal['indication']))) ?></strong>
                        </td>
                    </tr>
                    <?php if ($removal['indication_notes']): ?>
                    <tr>
                        <th>Indication Notes:</th>
                        <td><?= nl2br(e($removal['indication_notes'])) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Patient Satisfaction & Assessment -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-emoji-smile"></i> Patient Satisfaction</h5>
            </div>
            <div class="card-body text-center">
                <?php if ($removal['patient_satisfaction']): ?>
                    <?php
                    $satisfactionClass = match($removal['patient_satisfaction']) {
                        'excellent' => 'success',
                        'good' => 'primary',
                        'fair' => 'warning',
                        'poor' => 'danger',
                        default => 'secondary'
                    };
                    $satisfactionIcon = match($removal['patient_satisfaction']) {
                        'excellent' => 'emoji-laughing',
                        'good' => 'emoji-smile',
                        'fair' => 'emoji-neutral',
                        'poor' => 'emoji-frown',
                        default => 'emoji-expressionless'
                    };
                    ?>
                    <div class="mb-3">
                        <i class="bi bi-<?= $satisfactionIcon ?> text-<?= $satisfactionClass ?>" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="text-<?= $satisfactionClass ?>">
                        <?= ucfirst($removal['patient_satisfaction']) ?>
                    </h3>
                    <p class="text-muted">Patient's overall satisfaction with pain management</p>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> Patient satisfaction not recorded
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Complications -->
<?php if ($removal['removal_complications']): ?>
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Removal Complications</h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?= nl2br(e($removal['removal_complications'])) ?></p>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> No removal complications reported
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Final Clinical Notes -->
<?php if ($removal['final_notes']): ?>
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-chat-left-text"></i> Final Clinical Notes</h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?= nl2br(e($removal['final_notes'])) ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Catheter Information -->
<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-file-medical"></i> Catheter Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <th width="40%">Type:</th>
                        <td><?= e(ucwords(str_replace('_', ' ', $removal['catheter_type']))) ?></td>
                    </tr>
                    <tr>
                        <th>Category:</th>
                        <td><?= e(ucwords(str_replace('_', ' ', $removal['catheter_category']))) ?></td>
                    </tr>
                    <tr>
                        <th>Inserted:</th>
                        <td><?= formatDate($removal['date_of_insertion']) ?></td>
                    </tr>
                    <tr>
                        <th>Removed:</th>
                        <td><?= formatDate($removal['date_of_removal']) ?></td>
                    </tr>
                    <tr>
                        <th>Duration:</th>
                        <td>
                            <span class="badge bg-info"><?= $removal['number_of_catheter_days'] ?> days</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Final Status:</th>
                        <td>
                            <span class="badge bg-<?= $removal['catheter_status'] === 'removed' ? 'secondary' : 'warning' ?>">
                                <?= ucfirst($removal['catheter_status']) ?>
                            </span>
                        </td>
                    </tr>
                </table>
                <div class="mt-2">
                    <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $removal['catheter_id'] ?>" class="btn btn-sm btn-outline-info w-100">
                        <i class="bi bi-file-medical"></i> View Full Catheter Record
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Patient Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <th width="40%">Name:</th>
                        <td><?= e($removal['patient_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Hospital #:</th>
                        <td><?= e($removal['hospital_number']) ?></td>
                    </tr>
                    <tr>
                        <th>Age:</th>
                        <td><?= $removal['age'] ?> years</td>
                    </tr>
                    <tr>
                        <th>Gender:</th>
                        <td><?= ucfirst($removal['gender']) ?></td>
                    </tr>
                </table>
                <div class="mt-2">
                    <a href="<?= BASE_URL ?>/patients/viewPatient/<?= $removal['patient_id'] ?>" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-person"></i> View Patient Profile
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
                            <strong>Documented:</strong> <?= formatDate($removal['created_at']) ?><br>
                            <strong>Documented By:</strong> <?= e($removal['created_by_name'] ?? 'Unknown') ?>
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small>
                            <strong>Last Updated:</strong> <?= formatDate($removal['updated_at']) ?><br>
                            <strong>Updated By:</strong> <?= e($removal['updated_by_name'] ?? 'N/A') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
