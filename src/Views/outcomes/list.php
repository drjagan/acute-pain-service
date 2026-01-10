<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2">Functional Outcomes</h1>
        <p class="text-muted">Screen 4: Functional Assessment Records</p>
    </div>
    <div>
        <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
        <a href="<?= BASE_URL ?>/outcomes/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Outcome Assessment
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Search Form -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/outcomes" class="row g-3">
            <div class="col-md-10">
                <input type="text" 
                       class="form-control" 
                       name="search" 
                       placeholder="Search by patient name, hospital number, or clinical notes..."
                       value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card border-primary">
            <div class="card-body text-center">
                <h3 class="text-primary mb-0"><?= $total ?></h3>
                <small class="text-muted">Total Assessments</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body text-center">
                <?php
                $goodOutcomes = array_filter($outcomes, function($o) {
                    return $o['ambulation'] === 'independent' && $o['room_air_spo2'] === 'yes';
                });
                $successRate = $total > 0 ? round((count($goodOutcomes) / $total) * 100, 1) : 0;
                ?>
                <h3 class="text-success mb-0"><?= $successRate ?>%</h3>
                <small class="text-muted">Good Functional Status</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-danger">
            <div class="card-body text-center">
                <?php
                $withComplications = array_filter($outcomes, function($o) {
                    return $o['catheter_site_infection'] !== 'none' || $o['sentinel_events'] !== 'none';
                });
                $complicationRate = $total > 0 ? round((count($withComplications) / $total) * 100, 1) : 0;
                ?>
                <h3 class="text-danger mb-0"><?= $complicationRate ?>%</h3>
                <small class="text-muted">With Complications</small>
            </div>
        </div>
    </div>
</div>

<!-- Outcomes Table -->
<?php if (empty($outcomes)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> 
        <?= $search ? "No functional outcomes found matching your search." : "No functional outcomes recorded yet." ?>
        <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
            <a href="<?= BASE_URL ?>/outcomes/create" class="alert-link">Record your first assessment</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>POD</th>
                            <th>Patient</th>
                            <th>Hospital #</th>
                            <th>Catheter Type</th>
                            <th>Ambulation</th>
                            <th>SpO2</th>
                            <th>Complications</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($outcomes as $outcome): ?>
                        <tr>
                            <td>
                                <small><?= formatDate($outcome['entry_date']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-info">Day <?= $outcome['pod'] ?></span>
                            </td>
                            <td>
                                <strong><?= e($outcome['patient_name']) ?></strong>
                            </td>
                            <td>
                                <small class="text-muted"><?= e($outcome['hospital_number']) ?></small>
                            </td>
                            <td>
                                <small><?= e(ucwords(str_replace('_', ' ', $outcome['catheter_type']))) ?></small>
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
                                $spo2Class = match($outcome['room_air_spo2']) {
                                    'yes' => 'success',
                                    'no' => 'warning',
                                    'requires_o2' => 'danger',
                                    default => 'secondary'
                                };
                                $spo2Icon = match($outcome['room_air_spo2']) {
                                    'yes' => 'check-circle',
                                    'no' => 'exclamation-circle',
                                    'requires_o2' => 'x-circle',
                                    default => 'question-circle'
                                };
                                ?>
                                <span class="badge bg-<?= $spo2Class ?>">
                                    <i class="bi bi-<?= $spo2Icon ?>"></i>
                                    <?php if ($outcome['spo2_value']): ?>
                                        <?= $outcome['spo2_value'] ?>%
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $hasComplications = ($outcome['catheter_site_infection'] !== 'none' || 
                                                    $outcome['sentinel_events'] !== 'none');
                                ?>
                                <?php if ($hasComplications): ?>
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
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/outcomes/viewOutcome/<?= $outcome['id'] ?>" 
                                       class="btn btn-outline-primary"
                                       title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
                                    <a href="<?= BASE_URL ?>/outcomes/edit/<?= $outcome['id'] ?>" 
                                       class="btn btn-outline-secondary"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Pagination Info -->
    <div class="mt-3 text-muted text-center">
        <small>Showing <?= count($outcomes) ?> of <?= $total ?> assessments</small>
    </div>
<?php endif; ?>
