<?php
$patient = $report['patient'];
$catheters = $report['catheters'];
?>

<div class="report-header no-print mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h2">Individual Patient Report</h1>
            <p class="text-muted">Generated: <?= $generated_at ?></p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print Report
            </button>
            <a href="<?= BASE_URL ?>/reports" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<!-- Print Header -->
<div class="print-only text-center mb-4">
    <h2>ACUTE PAIN SERVICE</h2>
    <h3>Individual Patient Report</h3>
    <p>Generated: <?= $generated_at ?></p>
</div>

<!-- Section 1: Patient Demographics -->
<div class="card mb-3">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">1. Patient Demographics</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-bordered">
                    <tr>
                        <th width="40%">Patient Name:</th>
                        <td><strong><?= e($patient['patient_name']) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Hospital Number:</th>
                        <td><?= e($patient['hospital_number']) ?></td>
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
                        <th>BMI:</th>
                        <td><?= $patient['bmi'] ?> kg/m²</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-bordered">
                    <tr>
                        <th width="40%">Speciality:</th>
                        <td><?= e(ucwords(str_replace('_', ' ', $patient['speciality']))) ?></td>
                    </tr>
                    <tr>
                        <th>Diagnosis:</th>
                        <td><?= e($patient['diagnosis']) ?></td>
                    </tr>
                    <tr>
                        <th>Surgery:</th>
                        <td><?= e($patient['surgeries_list'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Comorbidities:</th>
                        <td><?= e($patient['comorbidities_list'] ?? 'None') ?></td>
                    </tr>
                    <tr>
                        <th>ASA Status:</th>
                        <td>ASA <?= $patient['asa_status'] ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Loop through each catheter -->
<?php foreach ($catheters as $index => $catheter): ?>
<div class="catheter-section mb-4" style="page-break-inside: avoid;">
    <h4 class="text-primary mt-4">Catheter <?= $index + 1 ?>: <?= e(ucwords(str_replace('_', ' ', $catheter['catheter_type']))) ?></h4>
    
    <!-- Section 2: Catheter Information -->
    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">2. Catheter Insertion Data</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th width="40%">Catheter Type:</th>
                            <td><?= e(ucwords(str_replace('_', ' ', $catheter['catheter_type']))) ?></td>
                        </tr>
                        <tr>
                            <th>Category:</th>
                            <td><?= e(ucwords(str_replace('_', ' ', $catheter['catheter_category']))) ?></td>
                        </tr>
                        <tr>
                            <th>Indication:</th>
                            <td><?= e($catheter['indication']) ?></td>
                        </tr>
                        <tr>
                            <th>Date of Insertion:</th>
                            <td><?= formatDate($catheter['date_of_insertion']) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th width="40%">Settings:</th>
                            <td><?= ucfirst($catheter['settings']) ?></td>
                        </tr>
                        <tr>
                            <th>Performed By:</th>
                            <td><?= ucfirst($catheter['performer']) ?></td>
                        </tr>
                        <tr>
                            <th>Confirmations:</th>
                            <td>
                                <?= $catheter['functional_confirmation'] ? 'Functional ✓' : '' ?>
                                <?= $catheter['anatomical_confirmation'] ? 'Anatomical ✓' : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Days in Situ:</th>
                            <td><strong><?= $catheter['days_in_situ'] ?> days</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Drug Regime Summary -->
    <div class="card mb-3">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">3. Drug Regime Summary</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6>Commonest Drug Regime</h6>
                    <?php if ($catheter['commonest_drug']): ?>
                    <p class="mb-0">
                        <strong><?= e($catheter['commonest_drug']['drug']) ?></strong> 
                        at <strong><?= $catheter['commonest_drug']['concentration'] ?>%</strong><br>
                        <small class="text-muted">Used <?= $catheter['commonest_drug']['count'] ?> time(s)</small>
                    </p>
                    <?php else: ?>
                    <p class="text-muted">No drug regimes recorded</p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h6>Number of Doses Administered</h6>
                    <p class="mb-0">
                        <strong><?= $catheter['dose_count'] ?></strong> regime(s) administered
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Pain Score Analysis -->
    <?php if (!empty($catheter['pain_analysis'])): ?>
    <div class="card mb-3">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">4. Mean VNRS Scores by POD</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>POD</th>
                            <th>Baseline Static</th>
                            <th>Baseline Dynamic</th>
                            <th>15-min Static</th>
                            <th>15-min Dynamic</th>
                            <th>Avg Improvement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($catheter['pain_analysis'] as $pa): ?>
                        <tr>
                            <td><strong>Day <?= $pa['pod'] ?></strong></td>
                            <td><?= number_format($pa['avg_baseline_static'] ?? 0, 1) ?></td>
                            <td><?= number_format($pa['avg_baseline_dynamic'] ?? 0, 1) ?></td>
                            <td><?= number_format($pa['avg_15min_static'] ?? 0, 1) ?></td>
                            <td><?= number_format($pa['avg_15min_dynamic'] ?? 0, 1) ?></td>
                            <td class="<?= ($pa['avg_improvement'] ?? 0) >= 2 ? 'table-success' : 'table-warning' ?>">
                                <strong><?= number_format($pa['avg_improvement'] ?? 0, 1) ?> points</strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section 5: Adverse Effects -->
    <?php if ($catheter['adverse_effects']): ?>
    <div class="card mb-3">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">5. Adverse Effects & Notable Events</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <h6>Hypotension</h6>
                    <p class="mb-0">
                        <strong><?= $catheter['adverse_effects']['hypotension_count'] ?></strong> incident(s)
                    </p>
                </div>
                <div class="col-md-3">
                    <h6>Bradycardia</h6>
                    <p class="mb-0">
                        <strong><?= $catheter['adverse_effects']['bradycardia_count'] ?></strong> incident(s)
                    </p>
                </div>
                <div class="col-md-3">
                    <h6>Sensory/Motor Deficit</h6>
                    <p class="mb-0">
                        <strong><?= $catheter['adverse_effects']['deficit_count'] ?></strong> incident(s)
                    </p>
                </div>
                <div class="col-md-3">
                    <h6>Nausea/Vomiting</h6>
                    <p class="mb-0">
                        <strong><?= $catheter['adverse_effects']['nausea_count'] ?></strong> incident(s)
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section 6: Functional Outcomes -->
    <?php if (!empty($catheter['outcomes'])): ?>
    <div class="card mb-3">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">6. Functional Outcomes Summary</h5>
        </div>
        <div class="card-body">
            <p><strong><?= count($catheter['outcomes']) ?></strong> functional assessment(s) recorded</p>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>POD</th>
                            <th>Date</th>
                            <th>Spirometry</th>
                            <th>Ambulation</th>
                            <th>Cough</th>
                            <th>SpO2</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($catheter['outcomes'] as $outcome): ?>
                        <tr>
                            <td>Day <?= $outcome['pod'] ?></td>
                            <td><?= formatDate($outcome['entry_date']) ?></td>
                            <td><?= ucwords($outcome['incentive_spirometry']) ?></td>
                            <td><?= ucwords($outcome['ambulation']) ?></td>
                            <td><?= ucwords($outcome['cough_ability']) ?></td>
                            <td><?= $outcome['spo2_value'] ? $outcome['spo2_value'] . '%' : ucwords(str_replace('_', ' ', $outcome['room_air_spo2'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section 7: Catheter Removal -->
    <?php if ($catheter['removal']): ?>
    <div class="card mb-3">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">7. Catheter Removal Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h6>Date of Removal</h6>
                    <p><?= formatDate($catheter['removal']['date_of_removal']) ?></p>
                </div>
                <div class="col-md-4">
                    <h6>Number of Catheter Days</h6>
                    <p><strong><?= $catheter['removal']['number_of_catheter_days'] ?> days</strong></p>
                </div>
                <div class="col-md-4">
                    <h6>Catheter Tip Status</h6>
                    <p><?= $catheter['removal']['catheter_tip_intact'] ? 'Intact ✓' : 'Not Intact ✗' ?></p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h6>Indication for Removal</h6>
                    <p><?= e(ucwords(str_replace('_', ' ', $catheter['removal']['indication']))) ?></p>
                </div>
                <div class="col-md-6">
                    <h6>Patient Satisfaction</h6>
                    <p><?= $catheter['removal']['patient_satisfaction'] ? '<strong>' . ucfirst($catheter['removal']['patient_satisfaction']) . '</strong>' : 'Not recorded' ?></p>
                </div>
            </div>
            <?php if ($catheter['removal']['removal_complications']): ?>
            <div class="alert alert-danger mt-2">
                <h6>Removal Complications:</h6>
                <p class="mb-0"><?= nl2br(e($catheter['removal']['removal_complications'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<!-- Summary Section -->
<div class="card mb-3">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">8. Summary</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <h6>Total Catheters</h6>
                <p><strong><?= count($catheters) ?></strong> catheter(s)</p>
            </div>
            <div class="col-md-4">
                <h6>Total Drug Regimes</h6>
                <p><strong><?= array_sum(array_column($catheters, 'dose_count')) ?></strong> regime(s)</p>
            </div>
            <div class="col-md-4">
                <h6>Total Functional Assessments</h6>
                <p><strong><?= array_sum(array_map(function($c) { return count($c['outcomes']); }, $catheters)) ?></strong> assessment(s)</p>
            </div>
        </div>
    </div>
</div>

<!-- Print Footer -->
<div class="print-only text-center mt-4 pt-4 border-top">
    <p class="text-muted mb-0">
        <small>
            Report generated from Acute Pain Service Management System<br>
            Generated on: <?= $generated_at ?><br>
            <em>This is a computer-generated report</em>
        </small>
    </p>
</div>
