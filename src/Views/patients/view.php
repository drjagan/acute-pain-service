<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2"><?= e($patient['patient_name']) ?></h1>
        <p class="text-muted">Hospital #: <strong><?= e($patient['hospital_number']) ?></strong></p>
    </div>
    <div>
        <?php if (hasAnyRole(['attending', 'resident'])): ?>
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
                            $comorbidities = json_decode($patient['comorbid_illness'], true);
                            if (!empty($comorbidities)):
                                $stmt = $this->db->prepare("SELECT name FROM lookup_comorbidities WHERE id IN (" . implode(',', array_fill(0, count($comorbidities), '?')) . ")");
                                $stmt->execute($comorbidities);
                                $comorbidityNames = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                foreach ($comorbidityNames as $name):
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
                            $surgeries = json_decode($patient['surgery'], true);
                            if (!empty($surgeries)):
                                $stmt = $this->db->prepare("SELECT name FROM lookup_surgeries WHERE id IN (" . implode(',', array_fill(0, count($surgeries), '?')) . ")");
                                $stmt->execute($surgeries);
                                $surgeryNames = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                foreach ($surgeryNames as $name):
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

<!-- Catheter Information (if exists) -->
<?php if (isset($patient['catheter_id'])): ?>
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3 border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-file-medical"></i> Active Catheter</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Catheter Type:</strong><br>
                        <?= e(ucwords(str_replace('_', ' ', $patient['catheter_type']))) ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Category:</strong><br>
                        <?= e(ucwords(str_replace('_', ' ', $patient['catheter_category']))) ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Insertion Date:</strong><br>
                        <?= formatDate($patient['date_of_insertion']) ?>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $patient['catheter_id'] ?>" class="btn btn-sm btn-success">
                            View Catheter Details →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
