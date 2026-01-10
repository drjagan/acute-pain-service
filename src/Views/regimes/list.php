<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2">Drug Regimes</h1>
        <p class="text-muted">Pain management & drug regime monitoring</p>
    </div>
    <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
    <div>
        <a href="<?= BASE_URL ?>/regimes/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Record New Drug Regime
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Search Bar -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/regimes" class="row g-3">
            <div class="col-md-10">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search by patient name, hospital number, drug, or adjuvant..." 
                       value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Drug Regimes Table -->
<div class="card">
    <div class="card-header">
        <strong>Total Drug Regimes:</strong> <?= $total ?>
    </div>
    <div class="card-body">
        <?php if (empty($regimes)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No drug regimes found. 
                <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
                    <a href="<?= BASE_URL ?>/regimes/create">Record the first drug regime</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Hospital #</th>
                            <th>POD</th>
                            <th>Entry Date</th>
                            <th>Drug</th>
                            <th>Adjuvant</th>
                            <th>VNRS Improvement</th>
                            <th>Effective</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($regimes as $regime): ?>
                        <tr>
                            <td><strong><?= e($regime['patient_name']) ?></strong></td>
                            <td><?= e($regime['hospital_number']) ?></td>
                            <td>
                                <span class="badge bg-info">Day <?= $regime['pod'] ?></span>
                            </td>
                            <td><?= formatDate($regime['entry_date']) ?></td>
                            <td>
                                <strong><?= e($regime['drug']) ?></strong><br>
                                <small class="text-muted"><?= $regime['concentration'] ?>% @ <?= $regime['volume'] ?> ml/hr</small>
                            </td>
                            <td>
                                <?php if ($regime['adjuvant']): ?>
                                    <?= e($regime['adjuvant']) ?>
                                <?php else: ?>
                                    <em class="text-muted">None</em>
                                <?php endif; ?>
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
                            <td>
                                <?php if ($regime['effective_analgesia']): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Yes
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning">
                                        <i class="bi bi-exclamation-circle"></i> No
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= BASE_URL ?>/regimes/viewRegime/<?= $regime['id'] ?>" 
                                       class="btn btn-outline-primary" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/patients/viewPatient/<?= $regime['patient_id'] ?>" 
                                       class="btn btn-outline-secondary" 
                                       title="View Patient">
                                        <i class="bi bi-person"></i>
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
