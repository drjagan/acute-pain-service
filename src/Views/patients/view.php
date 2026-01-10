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
