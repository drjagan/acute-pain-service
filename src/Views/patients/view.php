<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2"><?= e($patient['patient_name']) ?></h1>
        <p class="text-muted">Hospital #: <strong><?= e($patient['hospital_number']) ?></strong></p>
    </div>
    <div>
        <?php if (hasAnyRole(['attending', 'resident', 'admin'])): ?>
        <a href="<?= BASE_URL ?>/patients/edit/<?= $patient['id'] ?>" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Edit Patient
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/patients" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <!-- Patient Demographics -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Patient Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Patient Name:</th>
                        <td><?= e($patient['patient_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Hospital Number:</th>
                        <td><strong><?= e($patient['hospital_number']) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Age:</th>
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
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-clipboard2-pulse"></i> Clinical Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
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
                                <span class="badge bg-light text-dark me-1"><?= e($name) ?></span>
                            <?php
                                endforeach;
                            else:
                                echo '<em>None recorded</em>';
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
                                <span class="badge bg-light text-dark me-1"><?= e($name) ?></span>
                            <?php
                                endforeach;
                            else:
                                echo '<em>None recorded</em>';
                            endif;
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Status & Dates -->
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Status & Timeline</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Current Status:</strong><br>
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
                    <div class="col-md-3">
                        <strong>Admission Date:</strong><br>
                        <?= formatDate($patient['admission_date']) ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Discharge Date:</strong><br>
                        <?= $patient['discharge_date'] ? formatDate($patient['discharge_date']) : '<em>Not discharged</em>' ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Created:</strong><br>
                        <?= formatDateTime($patient['created_at']) ?>
        </div>
    </div>
</div>

<!-- Functional Outcomes Section -->
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-activity"></i> Functional Outcomes</h5>
                <div>
                    <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin']) && !empty($activeCatheters)): ?>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="addOutcomeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-plus-circle"></i> Record New Assessment
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="addOutcomeDropdown">
                            <?php foreach ($activeCatheters as $catheter): ?>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/outcomes/create?catheter_id=<?= $catheter['id'] ?>">
                                    For <?= e(ucwords(str_replace('_', ' ', $catheter['catheter_type']))) ?> 
                                    (POD: <?php
                                        $daysInserted = (new DateTime())->diff(new DateTime($catheter['date_of_insertion']))->days;
                                        echo $daysInserted;
                                    ?>)
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
                        <i class="bi bi-info-circle"></i> No functional outcomes recorded for this patient.
                        <?php if (!empty($activeCatheters) && hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
                            Use the dropdown above to record a functional outcome for an active catheter.
                        <?php elseif (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
                            <a href="<?= BASE_URL ?>/catheters/create?patient_id=<?= $patient['id'] ?>">Insert a catheter first</a> to record outcomes.
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Catheter Type</th>
                                    <th>POD</th>
                                    <th>Date</th>
                                    <th>Spirometry</th>
                                    <th>Ambulation</th>
                                    <th>Cough</th>
                                    <th>SpO2</th>
                                    <th>Infection</th>
                                    <th>Sentinel Events</th>
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
                                        <span class="badge bg-info">Day <?= $outcome['pod'] ?></span>
                                    </td>
                                    <td><?= formatDate($outcome['entry_date']) ?></td>
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
                                            <i class="bi bi-exclamation-triangle text-danger fs-5" title="<?= ucwords(str_replace('_', ' ', $outcome['catheter_site_infection'])) ?>"></i>
                                        <?php else: ?>
                                            <i class="bi bi-check-circle text-success fs-5" title="No infection"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($outcome['sentinel_events'] !== 'none'): ?>
                                            <i class="bi bi-exclamation-octagon text-danger fs-5" title="<?= ucwords(str_replace('_', ' ', $outcome['sentinel_events'])) ?>"></i>
                                        <?php else: ?>
                                            <i class="bi bi-check-circle text-success fs-5" title="No events"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= BASE_URL ?>/outcomes/viewOutcome/<?= $outcome['id'] ?>" 
                                               class="btn btn-outline-primary"
                                               title="View Outcome">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $outcome['catheter_id'] ?>" 
                                               class="btn btn-outline-info"
                                               title="View Catheter">
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

        </div>
    </div>
</div>

<!-- Catheters Section -->
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-file-medical"></i> Catheters</h5>
                <?php if (hasAnyRole(['attending', 'resident', 'admin'])): ?>
                <a href="<?= BASE_URL ?>/catheters/create?patient_id=<?= $patient['id'] ?>" class="btn btn-sm btn-light">
                    <i class="bi bi-plus-circle"></i> Insert New Catheter
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
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Insertion Date</th>
                                    <th>Days In Situ</th>
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
                                        <span class="<?= $colorClass ?>"><?= $daysInserted ?> days</span>
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

<!-- Drug Regimes Section -->
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-capsule"></i> Drug Regimes</h5>
                <div>
                    <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin']) && !empty($activeCatheters)): ?>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="addRegimeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-plus-circle"></i> Record New Drug Regime
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="addRegimeDropdown">
                            <?php foreach ($activeCatheters as $catheter): ?>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/regimes/create?catheter_id=<?= $catheter['id'] ?>">
                                    For <?= e(ucwords(str_replace('_', ' ', $catheter['catheter_type']))) ?> 
                                    (POD: <?php
                                        $daysInserted = (new DateTime())->diff(new DateTime($catheter['date_of_insertion']))->days;
                                        echo $daysInserted;
                                    ?>)
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
                        <i class="bi bi-info-circle"></i> No drug regimes recorded for this patient.
                        <?php if (!empty($activeCatheters) && hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
                            Use the dropdown above to record a drug regime for an active catheter.
                        <?php elseif (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
                            <a href="<?= BASE_URL ?>/catheters/create?patient_id=<?= $patient['id'] ?>">Insert a catheter first</a> to record drug regimes.
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Catheter Type</th>
                                    <th>POD</th>
                                    <th>Entry Date</th>
                                    <th>Drug Regime</th>
                                    <th>VNRS Baseline</th>
                                    <th>VNRS 15-Min</th>
                                    <th>Improvement</th>
                                    <th>Effective</th>
                                    <th>Side Effects</th>
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
                                        <small>
                                            Rest: <span class="badge bg-<?= $regime['baseline_vnrs_static'] > 6 ? 'danger' : ($regime['baseline_vnrs_static'] > 3 ? 'warning' : 'success') ?>">
                                                <?= $regime['baseline_vnrs_static'] ?>
                                            </span><br>
                                            Move: <span class="badge bg-<?= $regime['baseline_vnrs_dynamic'] > 6 ? 'danger' : ($regime['baseline_vnrs_dynamic'] > 3 ? 'warning' : 'success') ?>">
                                                <?= $regime['baseline_vnrs_dynamic'] ?>
                                            </span>
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            Rest: <span class="badge bg-<?= $regime['vnrs_15min_static'] > 6 ? 'danger' : ($regime['vnrs_15min_static'] > 3 ? 'warning' : 'success') ?>">
                                                <?= $regime['vnrs_15min_static'] ?>
                                            </span><br>
                                            Move: <span class="badge bg-<?= $regime['vnrs_15min_dynamic'] > 6 ? 'danger' : ($regime['vnrs_15min_dynamic'] > 3 ? 'warning' : 'success') ?>">
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
                                            <i class="bi bi-arrow-down-circle"></i> <?= number_format($avgImprovement, 1) ?> pts
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($regime['effective_analgesia']): ?>
                                            <i class="bi bi-check-circle text-success fs-5" title="Effective"></i>
                                        <?php else: ?>
                                            <i class="bi bi-exclamation-circle text-warning fs-5" title="Not fully effective"></i>
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
                                            <i class="bi bi-exclamation-triangle text-danger fs-5" title="Has side effects"></i>
                                        <?php else: ?>
                                            <i class="bi bi-check-circle text-success fs-5" title="No side effects"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= BASE_URL ?>/regimes/viewRegime/<?= $regime['id'] ?>" 
                                               class="btn btn-outline-primary"
                                               title="View Regime">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $regime['catheter_id'] ?>" 
                                               class="btn btn-outline-info"
                                               title="View Catheter">
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
