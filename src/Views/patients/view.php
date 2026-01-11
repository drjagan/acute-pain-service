<?php
// NEW ORGANIZED PATIENT VIEW
// Section Order: Clinical Details → Status & Timeline → Catheters → Removals → Drug Regimes → Functional Outcomes
?>

<!-- Patient Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
    <div class="mb-2 mb-md-0">
        <h1 class="h3 mb-1"><?= e($patient['patient_name']) ?></h1>
        <p class="text-muted mb-0">Hospital #: <strong><?= e($patient['hospital_number']) ?></strong></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if (hasAnyRole(['attending', 'resident', 'admin'])): ?>
        <a href="<?= BASE_URL ?>/patients/edit/<?= $patient['id'] ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-pencil"></i> <span class="d-none d-md-inline">Edit</span>
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/patients" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> <span class="d-none d-md-inline">Back</span>
        </a>
    </div>
</div>

<!-- SECTION 1: Clinical Details (2 columns on desktop, stacked on mobile) -->
<div class="row mb-3">
    <!-- Demographics -->
    <div class="col-12 col-md-6 mb-3 mb-md-0">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-person"></i> Demographics</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <th width="40%">Age:</th>
                        <td><?= $patient['age'] ?> years</td>
                    </tr>
                    <tr>
                        <th>Gender:</th>
                        <td><?= ucfirst($patient['gender']) ?></td>
                    </tr>
                    <tr>
                        <th>Height:</th>
                        <td><?= $patient['height'] ?> <?= $patient['height_unit'] ?></td>
                    </tr>
                    <tr>
                        <th>Weight:</th>
                        <td><?= $patient['weight'] ?> kg</td>
                    </tr>
                    <tr>
                        <th>BMI:</th>
                        <td>
                            <strong><?= $patient['bmi'] ?></strong>
                            <?php
                            $bmiClass = '';
                            $bmiText = '';
                            if ($patient['bmi'] < 18.5) {
                                $bmiClass = 'text-warning';
                                $bmiText = '(Underweight)';
                            } elseif ($patient['bmi'] >= 18.5 && $patient['bmi'] <= 24.9) {
                                $bmiClass = 'text-success';
                                $bmiText = '(Normal)';
                            } elseif ($patient['bmi'] >= 25 && $patient['bmi'] <= 29.9) {
                                $bmiClass = 'text-warning';
                                $bmiText = '(Overweight)';
                            } else {
                                $bmiClass = 'text-danger';
                                $bmiText = '(Obese)';
                            }
                            ?>
                            <span class="<?= $bmiClass ?>"><?= $bmiText ?></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Clinical Information -->
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-clipboard2-pulse"></i> Clinical Details</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <th width="40%">Speciality:</th>
                        <td>
                            <span class="badge bg-secondary">
                                <?= e(ucwords(str_replace('_', ' ', $patient['speciality']))) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Diagnosis:</th>
                        <td><?= nl2br(e($patient['diagnosis'])) ?></td>
                    </tr>
                    <tr>
                        <th>ASA Status:</th>
                        <td>
                            <span class="badge bg-info">ASA <?= $patient['asa_status'] ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th>Comorbidities:</th>
                        <td>
                            <?php
                            if (!empty($patient['comorbidity_names'])):
                                foreach ($patient['comorbidity_names'] as $name):
                            ?>
                                <span class="badge bg-light text-dark me-1 mb-1"><?= e($name) ?></span>
                            <?php
                                endforeach;
                            else:
                                echo '<em class="text-muted">None</em>';
                            endif;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Surgeries:</th>
                        <td>
                            <?php
                            if (!empty($patient['surgery_names'])):
                                foreach ($patient['surgery_names'] as $name):
                            ?>
                                <span class="badge bg-light text-dark me-1 mb-1"><?= e($name) ?></span>
                            <?php
                                endforeach;
                            else:
                                echo '<em class="text-muted">None</em>';
                            endif;
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 1.5: Assigned Physicians (v1.1) -->
<?php
$patientWithPhysicians = (new Models\Patient())->getPatientWithPhysicians($patient['id']);
$attendingPhysicians = $patientWithPhysicians['attending_physicians'] ?? [];
$residents = $patientWithPhysicians['residents'] ?? [];
?>
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-people"></i> Assigned Physicians</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Attending Physicians -->
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-person-badge"></i> Attending Physicians
                        </h6>
                        <?php if (empty($attendingPhysicians)): ?>
                            <p class="text-muted"><em>No attending physicians assigned</em></p>
                        <?php else: ?>
                            <?php foreach ($attendingPhysicians as $physician): ?>
                                <div class="d-flex align-items-center gap-2 mb-2 p-2 border rounded">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">
                                            <?= e($physician['physician_name']) ?>
                                            <?php if ($physician['is_primary']): ?>
                                                <span class="badge bg-primary ms-1" title="Primary Attending">
                                                    <i class="bi bi-star-fill"></i> Primary
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-envelope"></i> <?= e($physician['email']) ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?= $physician['status'] == 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($physician['status']) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Residents -->
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-person"></i> Residents
                        </h6>
                        <?php if (empty($residents)): ?>
                            <p class="text-muted"><em>No residents assigned</em></p>
                        <?php else: ?>
                            <?php foreach ($residents as $physician): ?>
                                <div class="d-flex align-items-center gap-2 mb-2 p-2 border rounded">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">
                                            <?= e($physician['physician_name']) ?>
                                            <?php if ($physician['is_primary']): ?>
                                                <span class="badge bg-primary ms-1" title="Primary Resident">
                                                    <i class="bi bi-star-fill"></i> Primary
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-envelope"></i> <?= e($physician['email']) ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?= $physician['status'] == 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($physician['status']) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (hasAnyRole(['attending', 'admin'])): ?>
                    <div class="text-end mt-3">
                        <a href="<?= BASE_URL ?>/patients/edit/<?= $patient['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i> Edit Physicians
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 2: Status & Timeline -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-calendar-event"></i> Status & Timeline</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <strong class="d-block mb-1">Status:</strong>
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
                    </div>
                    <div class="col-6 col-md-3">
                        <strong class="d-block mb-1">Admitted:</strong>
                        <span><?= formatDate($patient['admission_date']) ?></span>
                    </div>
                    <div class="col-6 col-md-3">
                        <strong class="d-block mb-1">Discharged:</strong>
                        <span><?= $patient['discharge_date'] ? formatDate($patient['discharge_date']) : '<em class="text-muted">Active</em>' ?></span>
                    </div>
                    <div class="col-6 col-md-3">
                        <strong class="d-block mb-1">Created:</strong>
                        <span class="small"><?= formatDateTime($patient['created_at']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 3: Catheters -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-file-medical"></i> Catheters</h6>
                <?php if (hasAnyRole(['attending', 'resident', 'admin'])): ?>
                <a href="<?= BASE_URL ?>/catheters/create?patient_id=<?= $patient['id'] ?>" class="btn btn-sm btn-light">
                    <i class="bi bi-plus-circle"></i> <span class="d-none d-md-inline">Insert New</span>
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($catheters)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No catheters recorded for this patient.
                        <?php if (hasAnyRole(['attending', 'resident', 'admin'])): ?>
                            <a href="<?= BASE_URL ?>/catheters/create?patient_id=<?= $patient['id'] ?>">Insert first catheter</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Inserted</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($catheters as $catheter): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?= e(ucwords(str_replace('_', ' ', $catheter['catheter_category']))) ?>
                                        </span>
                                    </td>
                                    <td><?= e(ucwords(str_replace('_', ' ', $catheter['catheter_type']))) ?></td>
                                    <td><?= formatDate($catheter['date_of_insertion']) ?></td>
                                    <td>
                                        <?php
                                        $daysInserted = (new DateTime())->diff(new DateTime($catheter['date_of_insertion']))->days;
                                        $colorClass = $daysInserted > 5 ? 'text-warning' : 'text-success';
                                        ?>
                                        <span class="<?= $colorClass ?>"><?= $daysInserted ?>d</span>
                                    </td>
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
                                    <td>
                                        <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $catheter['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
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

<!-- SECTION 4: Catheter Removals -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="bi bi-archive"></i> Catheter Removals</h6>
            </div>
            <div class="card-body">
                <?php if (empty($removals)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No catheter removals documented yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Inserted</th>
                                    <th>Removed</th>
                                    <th>Days</th>
                                    <th>Indication</th>
                                    <th>Tip</th>
                                    <th>Satisfaction</th>
                                    <th>Complications</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($removals as $removal): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= e(ucwords(str_replace('_', ' ', $removal['catheter_type']))) ?>
                                        </span>
                                    </td>
                                    <td><?= formatDate($removal['date_of_insertion']) ?></td>
                                    <td><?= formatDate($removal['date_of_removal']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $removal['number_of_catheter_days'] > 7 ? 'warning' : 'success' ?>">
                                            <?= $removal['number_of_catheter_days'] ?>d
                                        </span>
                                    </td>
                                    <td>
                                        <small><?= e(ucwords(str_replace('_', ' ', $removal['indication']))) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($removal['catheter_tip_intact']): ?>
                                            <i class="bi bi-check-circle text-success" title="Intact"></i>
                                        <?php else: ?>
                                            <i class="bi bi-exclamation-triangle text-danger" title="Not intact"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($removal['patient_satisfaction']): ?>
                                            <?php
                                            $satisfactionClass = match($removal['patient_satisfaction']) {
                                                'excellent' => 'success',
                                                'good' => 'primary',
                                                'fair' => 'warning',
                                                'poor' => 'danger',
                                                default => 'secondary'
                                            };
                                            ?>
                                            <span class="badge bg-<?= $satisfactionClass ?>">
                                                <?= ucfirst($removal['patient_satisfaction']) ?>
                                            </span>
                                        <?php else: ?>
                                            <em class="text-muted">N/A</em>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($removal['removal_complications']): ?>
                                            <i class="bi bi-exclamation-triangle text-danger" title="Has complications"></i>
                                        <?php else: ?>
                                            <i class="bi bi-check-circle text-success" title="None"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/catheters/viewRemoval/<?= $removal['id'] ?>" 
                                               class="btn btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $removal['catheter_id'] ?>" 
                                               class="btn btn-outline-info" title="Catheter">
                                                <i class="bi bi-file-medical"></i>
                                            </a>
                                        </div>
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

<!-- SECTION 5: Drug Regimes -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-capsule"></i> Drug Regimes</h6>
                <div>
                    <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin']) && !empty($activeCatheters)): ?>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-plus-circle"></i> <span class="d-none d-md-inline">Record New</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php foreach ($activeCatheters as $catheter): ?>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/regimes/create?catheter_id=<?= $catheter['id'] ?>">
                                    <?= e(ucwords(str_replace('_', ' ', $catheter['catheter_type']))) ?> 
                                    <small class="text-muted">(POD <?php
                                        $daysInserted = (new DateTime())->diff(new DateTime($catheter['date_of_insertion']))->days;
                                        echo $daysInserted;
                                    ?>)</small>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php elseif (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
                    <a href="<?= BASE_URL ?>/catheters/create?patient_id=<?= $patient['id'] ?>" class="btn btn-sm btn-warning">
                        <i class="bi bi-exclamation-circle"></i> Insert Catheter First
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($regimes)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No drug regimes recorded.
                        <?php if (!empty($activeCatheters) && hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
                            Use the dropdown above to record one.
                        <?php elseif (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
                            <a href="<?= BASE_URL ?>/catheters/create?patient_id=<?= $patient['id'] ?>">Insert a catheter first</a>.
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Catheter</th>
                                    <th>POD</th>
                                    <th>Date</th>
                                    <th>Drug Regime</th>
                                    <th>VNRS Baseline</th>
                                    <th>VNRS 15-Min</th>
                                    <th>Δ</th>
                                    <th class="text-center">Effective</th>
                                    <th class="text-center">Side Effects</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($regimes as $regime): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-<?= $regime['catheter_status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= e(ucwords(str_replace('_', ' ', $regime['catheter_type']))) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">D<?= $regime['pod'] ?></span>
                                    </td>
                                    <td><small><?= formatDate($regime['entry_date']) ?></small></td>
                                    <td>
                                        <strong><?= e($regime['drug']) ?></strong> <?= $regime['concentration'] ?>%<br>
                                        <small class="text-muted">
                                            <?= $regime['volume'] ?>ml/hr
                                            <?php if ($regime['adjuvant']): ?>
                                                + <?= e($regime['adjuvant']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            R: <span class="badge bg-<?= $regime['baseline_vnrs_static'] > 6 ? 'danger' : ($regime['baseline_vnrs_static'] > 3 ? 'warning' : 'success') ?>">
                                                <?= $regime['baseline_vnrs_static'] ?>
                                            </span>
                                            M: <span class="badge bg-<?= $regime['baseline_vnrs_dynamic'] > 6 ? 'danger' : ($regime['baseline_vnrs_dynamic'] > 3 ? 'warning' : 'success') ?>">
                                                <?= $regime['baseline_vnrs_dynamic'] ?>
                                            </span>
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            R: <span class="badge bg-<?= $regime['vnrs_15min_static'] > 6 ? 'danger' : ($regime['vnrs_15min_static'] > 3 ? 'warning' : 'success') ?>">
                                                <?= $regime['vnrs_15min_static'] ?>
                                            </span>
                                            M: <span class="badge bg-<?= $regime['vnrs_15min_dynamic'] > 6 ? 'danger' : ($regime['vnrs_15min_dynamic'] > 3 ? 'warning' : 'success') ?>">
                                                <?= $regime['vnrs_15min_dynamic'] ?>
                                            </span>
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
                                            <?= number_format($avgImprovement, 1) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($regime['effective_analgesia']): ?>
                                            <i class="bi bi-check-circle text-success"></i>
                                        <?php else: ?>
                                            <i class="bi bi-exclamation-circle text-warning"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $hasSideEffects = ($regime['hypotension'] !== 'none' || 
                                                          $regime['bradycardia'] !== 'none' || 
                                                          $regime['sensory_motor_deficit'] !== 'none' || 
                                                          $regime['nausea_vomiting'] !== 'none');
                                        ?>
                                        <?php if ($hasSideEffects): ?>
                                            <i class="bi bi-exclamation-triangle text-danger"></i>
                                        <?php else: ?>
                                            <i class="bi bi-check-circle text-success"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/regimes/viewRegime/<?= $regime['id'] ?>" 
                                               class="btn btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $regime['catheter_id'] ?>" 
                                               class="btn btn-outline-info">
                                                <i class="bi bi-file-medical"></i>
                                            </a>
                                        </div>
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

<!-- SECTION 6: Functional Outcomes -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-activity"></i> Functional Outcomes</h6>
                <div>
                    <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin']) && !empty($activeCatheters)): ?>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-plus-circle"></i> <span class="d-none d-md-inline">Record New</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php foreach ($activeCatheters as $catheter): ?>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/outcomes/create?catheter_id=<?= $catheter['id'] ?>">
                                    <?= e(ucwords(str_replace('_', ' ', $catheter['catheter_type']))) ?>
                                    <small class="text-muted">(POD <?php
                                        $daysInserted = (new DateTime())->diff(new DateTime($catheter['date_of_insertion']))->days;
                                        echo $daysInserted;
                                    ?>)</small>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php elseif (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
                    <a href="<?= BASE_URL ?>/catheters/create?patient_id=<?= $patient['id'] ?>" class="btn btn-sm btn-warning">
                        <i class="bi bi-exclamation-circle"></i> Insert Catheter First
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($outcomes)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No functional outcomes recorded.
                        <?php if (!empty($activeCatheters) && hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
                            Use the dropdown above to record one.
                        <?php elseif (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
                            <a href="<?= BASE_URL ?>/catheters/create?patient_id=<?= $patient['id'] ?>">Insert a catheter first</a>.
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Catheter</th>
                                    <th>POD</th>
                                    <th>Date</th>
                                    <th>Spirometry</th>
                                    <th>Ambulation</th>
                                    <th>Cough</th>
                                    <th>SpO2</th>
                                    <th class="text-center">Infection</th>
                                    <th class="text-center">Sentinel Events</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($outcomes as $outcome): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-<?= $outcome['catheter_status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= e(ucwords(str_replace('_', ' ', $outcome['catheter_type']))) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">D<?= $outcome['pod'] ?></span>
                                    </td>
                                    <td><small><?= formatDate($outcome['entry_date']) ?></small></td>
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
                                            <?= ucwords($outcome['incentive_spirometry']) ?>
                                        </span>
                                    </td>
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
                                    <td>
                                        <?php
                                        $spo2Class = match($outcome['room_air_spo2']) {
                                            'yes' => 'success',
                                            'no' => 'warning',
                                            'requires_o2' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $spo2Class ?>">
                                            <?php if ($outcome['spo2_value']): ?>
                                                <?= $outcome['spo2_value'] ?>%
                                            <?php else: ?>
                                                <?= $outcome['room_air_spo2'] === 'yes' ? 'OK' : 'O2' ?>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($outcome['catheter_site_infection'] !== 'none'): ?>
                                            <i class="bi bi-exclamation-triangle text-danger"></i>
                                        <?php else: ?>
                                            <i class="bi bi-check-circle text-success"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($outcome['sentinel_events'] !== 'none'): ?>
                                            <i class="bi bi-exclamation-octagon text-danger"></i>
                                        <?php else: ?>
                                            <i class="bi bi-check-circle text-success"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/outcomes/viewOutcome/<?= $outcome['id'] ?>" 
                                               class="btn btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $outcome['catheter_id'] ?>" 
                                               class="btn btn-outline-info">
                                                <i class="bi bi-file-medical"></i>
                                            </a>
                                        </div>
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
