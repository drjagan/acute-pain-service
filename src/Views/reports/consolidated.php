<?php
$period = $report['period'];
$startDate = $period['start'];
$endDate = $period['end'];
?>

<div class="report-header no-print mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h2">Consolidated Report</h1>
            <p class="text-muted">
                Period: <?= formatDate($startDate) ?> to <?= formatDate($endDate) ?><br>
                Generated: <?= $generated_at ?>
            </p>
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
    <h3>Consolidated Report</h3>
    <p>Period: <?= formatDate($startDate) ?> to <?= formatDate($endDate) ?></p>
    <p>Generated: <?= $generated_at ?></p>
</div>

<!-- Section 1: Patient Statistics -->
<div class="card mb-3">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">1. Patient Statistics</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <h4 class="text-primary"><?= $report['patient_stats']['total'] ?></h4>
                <p class="text-muted mb-0">Total Patients</p>
            </div>
            <div class="col-md-9">
                <h6>Gender Distribution</h6>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Gender</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = $report['patient_stats']['total'];
                        foreach ($report['patient_stats']['gender'] as $g): 
                        ?>
                        <tr>
                            <td><?= ucfirst($g['gender']) ?></td>
                            <td><strong><?= $g['count'] ?></strong></td>
                            <td><?= $total > 0 ? number_format(($g['count'] / $total) * 100, 1) : 0 ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php if (!empty($report['patient_stats']['speciality'])): ?>
        <h6 class="mt-3">Distribution by Speciality</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Speciality</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['patient_stats']['speciality'] as $spec): ?>
                    <tr>
                        <td><?= e(ucwords(str_replace('_', ' ', $spec['speciality']))) ?></td>
                        <td><strong><?= $spec['count'] ?></strong></td>
                        <td><?= $total > 0 ? number_format(($spec['count'] / $total) * 100, 1) : 0 ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Section 2: Catheter Statistics -->
<div class="card mb-3">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">2. Catheter Statistics</h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-12">
                <h4 class="text-success"><?= $report['catheter_stats']['total'] ?></h4>
                <p class="text-muted">Total Catheters Inserted</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <h6>By Category</h6>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th>Count</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['catheter_stats']['by_category'] as $cat): ?>
                        <tr>
                            <td><?= e(ucwords(str_replace('_', ' ', $cat['catheter_category']))) ?></td>
                            <td><strong><?= $cat['count'] ?></strong></td>
                            <td><?= $report['catheter_stats']['total'] > 0 ? number_format(($cat['count'] / $report['catheter_stats']['total']) * 100, 1) : 0 ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="col-md-6">
                <h6>Top 5 Catheter Types</h6>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $types = array_slice($report['catheter_stats']['by_type'], 0, 5);
                        foreach ($types as $type): 
                        ?>
                        <tr>
                            <td><?= e(ucwords(str_replace('_', ' ', $type['catheter_type']))) ?></td>
                            <td><strong><?= $type['count'] ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Section 3: Procedural Details -->
<div class="card mb-3">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">3. Procedural Details</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Elective vs Emergency</h6>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Setting</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['catheter_stats']['by_settings'] as $setting): ?>
                        <tr>
                            <td><?= ucfirst($setting['settings']) ?></td>
                            <td><strong><?= $setting['count'] ?></strong></td>
                            <td><?= $report['catheter_stats']['total'] > 0 ? number_format(($setting['count'] / $report['catheter_stats']['total']) * 100, 1) : 0 ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="col-md-6">
                <h6>By Performer</h6>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Performer</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['catheter_stats']['by_performer'] as $perf): ?>
                        <tr>
                            <td><?= ucfirst($perf['performer']) ?></td>
                            <td><strong><?= $perf['count'] ?></strong></td>
                            <td><?= $report['catheter_stats']['total'] > 0 ? number_format(($perf['count'] / $report['catheter_stats']['total']) * 100, 1) : 0 ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Section 4: Pain Management Efficacy -->
<div class="card mb-3">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">4. Pain Management Efficacy</h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <h6>Effective Analgesia Rate</h6>
                <h3 class="text-<?= ($report['pain_stats']['effective_rate'] ?? 0) >= 85 ? 'success' : 'warning' ?>">
                    <?= number_format($report['pain_stats']['effective_rate'] ?? 0, 1) ?>%
                </h3>
                <p class="text-muted">Target: >85%</p>
            </div>
            <div class="col-md-6">
                <h6>Doses per Catheter</h6>
                <?php if ($report['pain_stats']['doses_per_catheter'] && $report['pain_stats']['doses_per_catheter']['mean_doses'] !== null): ?>
                <p>
                    Mean: <strong><?= number_format($report['pain_stats']['doses_per_catheter']['mean_doses'] ?? 0, 1) ?></strong><br>
                    Range: <?= $report['pain_stats']['doses_per_catheter']['min_doses'] ?? 0 ?> - <?= $report['pain_stats']['doses_per_catheter']['max_doses'] ?? 0 ?>
                </p>
                <?php else: ?>
                <p class="text-muted">No data available</p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($report['pain_stats']['vnrs_by_pod'])): ?>
        <h6>Mean VNRS Post-15min by POD</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>POD</th>
                        <th>Mean Static</th>
                        <th>Mean Dynamic</th>
                        <th>Mean Improvement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['pain_stats']['vnrs_by_pod'] as $pod): ?>
                    <tr>
                        <td><strong>Day <?= $pod['pod'] ?></strong></td>
                        <td><?= number_format($pod['mean_static'] ?? 0, 1) ?>/10</td>
                        <td><?= number_format($pod['mean_dynamic'] ?? 0, 1) ?>/10</td>
                        <td class="<?= ($pod['mean_improvement'] ?? 0) >= 2 ? 'table-success' : 'table-warning' ?>">
                            <strong><?= number_format($pod['mean_improvement'] ?? 0, 1) ?> points</strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Section 5: Adverse Effects Analysis -->
<div class="card mb-3">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0">5. Incidence of Adverse Effects</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Effect</th>
                        <th>Total Count</th>
                        <th>Mild</th>
                        <th>Moderate</th>
                        <th>Severe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['adverse_effects'] as $effect): ?>
                    <tr>
                        <td><strong><?= $effect['effect'] ?></strong></td>
                        <td><?= $effect['count'] ?></td>
                        <td><?= $effect['mild'] ?></td>
                        <td><?= $effect['moderate'] ?></td>
                        <td class="<?= $effect['severe'] > 0 ? 'table-danger' : '' ?>"><?= $effect['severe'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Section 6: Sentinel Events -->
<?php if (!empty($report['sentinel_events'])): ?>
<div class="card mb-3">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0">6. Sentinel Events</h5>
    </div>
    <div class="card-body">
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Event Type</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report['sentinel_events'] as $event): ?>
                <tr>
                    <td><?= e(ucwords(str_replace('_', ' ', $event['sentinel_events']))) ?></td>
                    <td><strong><?= $event['count'] ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="alert alert-success">
    <i class="bi bi-check-circle"></i> <strong>No sentinel events reported during this period</strong>
</div>
<?php endif; ?>

<!-- Section 7: Catheter Removal Analysis -->
<div class="card mb-3">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">7. Catheter Removal Analysis</h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <h6>Number of Catheter Days</h6>
                <?php if ($report['removal_stats']['catheter_days'] && $report['removal_stats']['catheter_days']['mean_days'] !== null): ?>
                <p>
                    Mean: <strong><?= number_format($report['removal_stats']['catheter_days']['mean_days'] ?? 0, 1) ?></strong> days<br>
                    Range: <?= $report['removal_stats']['catheter_days']['min_days'] ?? 0 ?> - <?= $report['removal_stats']['catheter_days']['max_days'] ?? 0 ?> days
                </p>
                <?php else: ?>
                <p class="text-muted">No data available</p>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <h6>Catheter Tip Integrity</h6>
                <h3 class="text-<?= ($report['removal_stats']['tip_integrity_rate'] ?? 0) >= 95 ? 'success' : 'warning' ?>">
                    <?= number_format($report['removal_stats']['tip_integrity_rate'] ?? 0, 1) ?>%
                </h3>
                <p class="text-muted">Intact on removal</p>
            </div>
        </div>
        
        <?php if (!empty($report['removal_stats']['indications'])): ?>
        <h6>Indications for Removal</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Indication</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalRemovals = array_sum(array_column($report['removal_stats']['indications'], 'count'));
                    foreach ($report['removal_stats']['indications'] as $ind): 
                    ?>
                    <tr>
                        <td><?= e(ucwords(str_replace('_', ' ', $ind['indication']))) ?></td>
                        <td><strong><?= $ind['count'] ?></strong></td>
                        <td><?= $totalRemovals > 0 ? number_format(($ind['count'] / $totalRemovals) * 100, 1) : 0 ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Section 8: Patient Satisfaction -->
<?php if (!empty($report['removal_stats']['satisfaction'])): ?>
<div class="card mb-3">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">8. Patient Satisfaction</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Rating</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalSat = array_sum(array_column($report['removal_stats']['satisfaction'], 'count'));
                    foreach ($report['removal_stats']['satisfaction'] as $sat): 
                    ?>
                    <tr>
                        <td><?= ucfirst($sat['patient_satisfaction']) ?></td>
                        <td><strong><?= $sat['count'] ?></strong></td>
                        <td><?= $totalSat > 0 ? number_format(($sat['count'] / $totalSat) * 100, 1) : 0 ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php
        // Calculate percentage of Good/Excellent
        $goodExcellent = 0;
        foreach ($report['removal_stats']['satisfaction'] as $sat) {
            if (in_array($sat['patient_satisfaction'], ['good', 'excellent'])) {
                $goodExcellent += $sat['count'];
            }
        }
        $goodExcellentPct = $totalSat > 0 ? ($goodExcellent / $totalSat) * 100 : 0;
        ?>
        
        <div class="alert alert-<?= $goodExcellentPct >= 80 ? 'success' : 'warning' ?> mt-3">
            <strong><?= number_format($goodExcellentPct, 1) ?>%</strong> of patients rated their experience as Good or Excellent
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Summary Section -->
<div class="card mb-3">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">9. Summary & Key Performance Indicators</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="border p-3 rounded">
                    <h6 class="text-muted mb-1">Total Patients</h6>
                    <h3 class="mb-0"><?= $report['patient_stats']['total'] ?></h3>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="border p-3 rounded">
                    <h6 class="text-muted mb-1">Total Catheters</h6>
                    <h3 class="mb-0"><?= $report['catheter_stats']['total'] ?></h3>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="border p-3 rounded <?= ($report['pain_stats']['effective_rate'] ?? 0) >= 85 ? 'bg-success text-white' : 'bg-warning' ?>">
                    <h6 class="mb-1">Effective Analgesia</h6>
                    <h3 class="mb-0"><?= number_format($report['pain_stats']['effective_rate'] ?? 0, 1) ?>%</h3>
                    <small>Target: >85%</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="border p-3 rounded">
                    <h6 class="text-muted mb-1">Avg Catheter Days</h6>
                    <h3 class="mb-0"><?= $report['removal_stats']['catheter_days'] ? number_format($report['removal_stats']['catheter_days']['mean_days'] ?? 0, 1) : 'N/A' ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Footer -->
<div class="print-only text-center mt-4 pt-4 border-top">
    <p class="text-muted mb-0">
        <small>
            Consolidated Report - Acute Pain Service Management System<br>
            Period: <?= formatDate($startDate) ?> to <?= formatDate($endDate) ?><br>
            Generated on: <?= $generated_at ?><br>
            <em>This is a computer-generated report</em>
        </small>
    </p>
</div>
