<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2">Active Catheters</h1>
        <p class="text-muted">Manage catheter insertions and monitoring</p>
    </div>
    <?php if (hasAnyRole(['attending', 'resident', 'admin'])): ?>
    <div>
        <a href="<?= BASE_URL ?>/catheters/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Record New Catheter
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Filter Options -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/catheters" class="row g-3">
            <div class="col-md-5">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search by patient name, hospital number, or catheter type..." 
                       value="<?= e($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $key => $name): ?>
                    <option value="<?= $key ?>" <?= $category === $key ? 'selected' : '' ?>>
                        <?= $name ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/catheters" class="btn btn-secondary w-100">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Catheters Table -->
<div class="card">
    <div class="card-header">
        <strong>Total Catheters:</strong> <?= $total ?>
    </div>
    <div class="card-body">
        <?php if (empty($catheters)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No catheters found. 
                <?php if (hasAnyRole(['attending', 'resident', 'admin'])): ?>
                    <a href="<?= BASE_URL ?>/catheters/create">Record the first catheter insertion</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Hospital #</th>
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
                                <strong><?= e($catheter['patient_name']) ?></strong>
                            </td>
                            <td><?= e($catheter['hospital_number']) ?></td>
                            <td>
                                <span class="badge bg-primary">
                                    <?= e(ucwords(str_replace('_', ' ', $catheter['catheter_category']))) ?>
                                </span>
                            </td>
                            <td><?= e(ucwords(str_replace('_', ' ', $catheter['catheter_type']))) ?></td>
                            <td><?= formatDate($catheter['date_of_insertion']) ?></td>
                            <td>
                                <?php
                                if (isset($catheter['days_inserted'])) {
                                    $daysInserted = $catheter['days_inserted'];
                                } else {
                                    $daysInserted = (new DateTime())->diff(new DateTime($catheter['date_of_insertion']))->days;
                                }
                                $colorClass = $daysInserted > 5 ? 'text-warning' : 'text-success';
                                ?>
                                <span class="<?= $colorClass ?>">
                                    <strong><?= $daysInserted ?> days</strong>
                                </span>
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
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $catheter['id'] ?>" 
                                       class="btn btn-outline-primary" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/patients/viewPatient/<?= $catheter['patient_id'] ?>" 
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
