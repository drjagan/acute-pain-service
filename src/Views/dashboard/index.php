<div class="dashboard-header mb-4">
    <h1 class="h2">Dashboard</h1>
    <p class="text-muted">Welcome back, <?= e($user['first_name'] . ' ' . $user['last_name']) ?> (<?= ucfirst(e($user['role'])) ?>)</p>
</div>

<!-- Quick Stats Row 1: Patient Metrics -->
<div class="row mb-3">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Total Patients</h6>
                        <h2 class="mb-0"><?= $stats['patient_total'] ?></h2>
                    </div>
                    <div class="text-primary">
                        <i class="bi bi-people-fill" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/patients" class="btn btn-sm btn-outline-primary mt-2 w-100">View All</a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Active Patients</h6>
                        <h2 class="mb-0"><?= $stats['patient_active'] ?></h2>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-person-check-fill" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <small class="text-muted">With active catheters</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Gender Ratio</h6>
                        <h2 class="mb-0"><?= $stats['patient_male'] ?>:<?= $stats['patient_female'] ?></h2>
                    </div>
                    <div class="text-info">
                        <i class="bi bi-gender-ambiguous" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <small class="text-muted">Male : Female</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Today's Admissions</h6>
                        <h2 class="mb-0"><?= $stats['patient_today'] ?></h2>
                    </div>
                    <div class="text-warning">
                        <i class="bi bi-calendar-plus-fill" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <small class="text-muted"><?= date('F j, Y') ?></small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Row 2: Catheter Metrics -->
<div class="row mb-3">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Total Catheters</h6>
                        <h2 class="mb-0"><?= $stats['catheter_total'] ?></h2>
                    </div>
                    <div class="text-primary">
                        <i class="bi bi-file-medical-fill" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/catheters" class="btn btn-sm btn-outline-primary mt-2 w-100">View All</a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Active Catheters</h6>
                        <h2 class="mb-0"><?= $stats['catheter_active'] ?></h2>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-activity" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <small class="text-muted">Currently in situ</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-secondary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Removed Today</h6>
                        <h2 class="mb-0"><?= $stats['catheter_removed_today'] ?></h2>
                    </div>
                    <div class="text-secondary">
                        <i class="bi bi-calendar-x-fill" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <small class="text-muted"><?= date('F j, Y') ?></small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Avg Duration</h6>
                        <h2 class="mb-0"><?= number_format($stats['catheter_avg_days'], 1) ?></h2>
                    </div>
                    <div class="text-info">
                        <i class="bi bi-hourglass-split" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <small class="text-muted">Days in situ (mean)</small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Row 3: Quality Indicators -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-<?= $stats['effective_analgesia_rate'] >= 85 ? 'success' : ($stats['effective_analgesia_rate'] >= 70 ? 'warning' : 'danger') ?> h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Effective Analgesia</h6>
                        <h2 class="mb-0"><?= number_format($stats['effective_analgesia_rate'], 1) ?>%</h2>
                    </div>
                    <div class="text-<?= $stats['effective_analgesia_rate'] >= 85 ? 'success' : ($stats['effective_analgesia_rate'] >= 70 ? 'warning' : 'danger') ?>">
                        <i class="bi bi-capsule-pill" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <small class="text-muted">Target: >85%</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-<?= $stats['complication_rate'] <= 10 ? 'success' : 'danger' ?> h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Complication Rate</h6>
                        <h2 class="mb-0"><?= number_format($stats['complication_rate'], 1) ?>%</h2>
                    </div>
                    <div class="text-<?= $stats['complication_rate'] <= 10 ? 'success' : 'danger' ?>">
                        <i class="bi bi-shield-fill-exclamation" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <small class="text-muted">Target: <10%</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Patient Satisfaction</h6>
                        <h2 class="mb-0"><?= number_format($stats['satisfaction_avg'], 1) ?>%</h2>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-emoji-smile-fill" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <small class="text-muted">From removal feedback</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-danger h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Sentinel Events</h6>
                        <h2 class="mb-0"><?= $stats['sentinel_events_month'] ?></h2>
                    </div>
                    <div class="text-danger">
                        <i class="bi bi-exclamation-octagon-fill" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <small class="text-muted">This month</small>
            </div>
        </div>
    </div>
</div>

<!-- Alerts Section -->
<?php if (!empty($alerts)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-bell-fill"></i> Alerts & Notifications</h5>
            </div>
            <div class="card-body">
                <?php foreach ($alerts as $alert): ?>
                <div class="alert alert-<?= $alert['type'] ?> d-flex align-items-center mb-2">
                    <i class="bi bi-<?= $alert['icon'] ?> me-3" style="font-size: 1.5rem;"></i>
                    <div class="flex-grow-1">
                        <strong><?= $alert['title'] ?>:</strong> <?= $alert['message'] ?>
                    </div>
                    <a href="<?= BASE_URL ?><?= $alert['link'] ?>" class="btn btn-sm btn-outline-<?= $alert['type'] ?>">View</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <!-- Recent Activity Feed -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Activity</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($activity)): ?>
                    <div class="p-3 text-center text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-2">No recent activity</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($activity as $act): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div class="d-flex align-items-start">
                                    <?php
                                    $icon = match($act['type']) {
                                        'patient' => 'person-plus-fill',
                                        'catheter' => 'file-medical-fill',
                                        'regime' => 'capsule-pill',
                                        'outcome' => 'activity',
                                        'removal' => 'calendar-x-fill',
                                        default => 'circle-fill'
                                    };
                                    $color = match($act['type']) {
                                        'patient' => 'primary',
                                        'catheter' => 'success',
                                        'regime' => 'info',
                                        'outcome' => 'warning',
                                        'removal' => 'secondary',
                                        default => 'secondary'
                                    };
                                    $label = match($act['type']) {
                                        'patient' => 'Patient Registered',
                                        'catheter' => 'Catheter Inserted',
                                        'regime' => 'Drug Regime',
                                        'outcome' => 'Functional Outcome',
                                        'removal' => 'Catheter Removed',
                                        default => 'Activity'
                                    };
                                    ?>
                                    <i class="bi bi-<?= $icon ?> text-<?= $color ?> me-3 mt-1" style="font-size: 1.2rem;"></i>
                                    <div>
                                        <h6 class="mb-1"><?= e($act['title']) ?></h6>
                                        <small class="text-muted">
                                            <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                                            by <?= e($act['user_name']) ?> (<?= ucfirst($act['user_role']) ?>)
                                        </small>
                                    </div>
                                </div>
                                <small class="text-muted"><?= timeAgo($act['created_at']) ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-lightning-fill"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <?php if (hasAnyRole(['attending', 'resident', 'admin'])): ?>
                <a href="<?= BASE_URL ?>/patients/create" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-person-plus"></i> Register Patient
                </a>
                <a href="<?= BASE_URL ?>/catheters/create" class="btn btn-success w-100 mb-2">
                    <i class="bi bi-file-medical"></i> Insert Catheter
                </a>
                <?php endif; ?>
                
                <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
                <a href="<?= BASE_URL ?>/regimes/create" class="btn btn-info w-100 mb-2">
                    <i class="bi bi-capsule"></i> Record Drug Regime
                </a>
                <a href="<?= BASE_URL ?>/outcomes/create" class="btn btn-warning w-100 mb-2">
                    <i class="bi bi-activity"></i> Record Outcome
                </a>
                <?php endif; ?>
                
                <hr>
                
                <a href="<?= BASE_URL ?>/reports" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-file-earmark-text"></i> Generate Reports
                </a>
                
                <?php if (hasRole('admin')): ?>
                <a href="<?= BASE_URL ?>/users" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-people"></i> Manage Users
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
