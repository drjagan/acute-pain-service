<div class="position-sticky pt-3">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?= isRoute('dashboard') ? 'active' : '' ?>" href="<?= BASE_URL ?>/dashboard">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
        </li>
        
        <!-- Phase 2: Active Items -->
        <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
        <li class="nav-item">
            <a class="nav-link <?= isRoute('patients') ? 'active' : '' ?>" href="<?= BASE_URL ?>/patients">
                <i class="bi bi-person-plus"></i> Patients
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
        <li class="nav-item">
            <a class="nav-link <?= isRoute('catheters') ? 'active' : '' ?>" href="<?= BASE_URL ?>/catheters">
                <i class="bi bi-file-medical"></i> Catheters
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (hasAnyRole(['attending', 'resident', 'nurse', 'admin'])): ?>
        <li class="nav-item">
            <a class="nav-link <?= isRoute('regimes') ? 'active' : '' ?>" href="<?= BASE_URL ?>/regimes">
                <i class="bi bi-capsule"></i> Drug Regimes
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (hasAnyRole(['attending', 'resident', 'nurse'])): ?>
        <li class="nav-item">
            <a class="nav-link disabled" href="#">
                <i class="bi bi-activity"></i> Outcomes <small>(Phase 3)</small>
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (hasRole('admin')): ?>
        <li class="nav-item mt-3">
            <h6 class="sidebar-heading px-3 text-muted">
                <span>Administration</span>
            </h6>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" href="#">
                <i class="bi bi-people"></i> Users <small>(Phase 2)</small>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" href="#">
                <i class="bi bi-file-earmark-spreadsheet"></i> Reports <small>(Phase 5)</small>
            </a>
        </li>
        <?php endif; ?>
    </ul>
    
    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
        <span>User Info</span>
    </h6>
    <ul class="nav flex-column mb-2">
        <li class="nav-item">
            <div class="px-3 py-2 text-muted small">
                <strong><?= e(currentUser()['first_name'] . ' ' . currentUser()['last_name']) ?></strong><br>
                Role: <?= ucfirst(e(currentUser()['role'])) ?><br>
                Email: <?= e(currentUser()['email']) ?>
            </div>
        </li>
    </ul>
</div>
