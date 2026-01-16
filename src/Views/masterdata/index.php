<?php
/**
 * Master Data Management Dashboard
 * Shows all available master data types
 * 
 * @version 1.2.0
 */
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2"><i class="bi bi-database-gear"></i> Master Data Management</h1>
        <p class="text-muted mb-0">Configure and manage system lookup tables and reference data</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/settings" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Settings
        </a>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    <strong>Master Data:</strong> These are system-wide lookup tables used throughout the application. 
    Changes here will affect all forms and reports.
</div>

<div class="row">
    <?php foreach ($masterDataTypes as $key => $config): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 shadow-sm border-<?= $config['color'] ?>">
            <div class="card-header bg-<?= $config['color'] ?> text-white">
                <h5 class="mb-0">
                    <i class="<?= $config['icon'] ?>"></i>
                    <?= e($config['label']) ?>
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text text-muted">
                    <?= e($config['description']) ?>
                </p>
                
                <?php if (isset($config['parent'])): ?>
                <div class="alert alert-info py-2 mb-3">
                    <small>
                        <i class="bi bi-link-45deg"></i>
                        <strong>Related to:</strong> <?= e($config['parent']['label']) ?>
                    </small>
                </div>
                <?php endif; ?>
                
                <?php if (isset($config['has_children'])): ?>
                <div class="alert alert-warning py-2 mb-3">
                    <small>
                        <i class="bi bi-diagram-3"></i>
                        <strong>Has:</strong> <?= e($config['has_children']['label']) ?>
                    </small>
                </div>
                <?php endif; ?>
                
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/masterdata/list/<?= $key ?>" 
                       class="btn btn-<?= $config['color'] ?>">
                        <i class="bi bi-list-ul"></i> Manage Items
                    </a>
                    <a href="<?= BASE_URL ?>/masterdata/create/<?= $key ?>" 
                       class="btn btn-outline-<?= $config['color'] ?> btn-sm">
                        <i class="bi bi-plus-circle"></i> Add New
                    </a>
                </div>
            </div>
            <div class="card-footer bg-light text-muted">
                <small>
                    <i class="bi bi-table"></i> Table: <code><?= e($config['table']) ?></code>
                </small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card border-secondary">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> About Master Data Types</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="bi bi-clipboard-pulse"></i> Catheter Insertion Indications</h6>
                        <p class="small">Reasons for catheter placement. Used in catheter insertion forms to standardize indication selection.</p>
                        
                        <h6 class="text-warning"><i class="bi bi-x-circle"></i> Catheter Removal Indications</h6>
                        <p class="small">Reasons for catheter removal. Replaces hardcoded values with manageable database entries.</p>
                        
                        <h6 class="text-danger"><i class="bi bi-exclamation-triangle"></i> Sentinel Events</h6>
                        <p class="small">Adverse events and complications. Used in functional outcomes to track patient complications.</p>
                        
                        <h6 class="text-info"><i class="bi bi-hospital"></i> Medical Specialties</h6>
                        <p class="small">Surgical and medical specialties. Organizes surgical procedures into specialty groups.</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-success"><i class="bi bi-bandaid"></i> Surgical Procedures</h6>
                        <p class="small">Types of surgeries. Linked to specialties for organized selection in patient registration.</p>
                        
                        <h6 class="text-secondary"><i class="bi bi-heart-pulse"></i> Comorbidities</h6>
                        <p class="small">Patient medical conditions. Used in patient registration to track pre-existing conditions.</p>
                        
                        <h6 class="text-primary"><i class="bi bi-capsule"></i> Drugs</h6>
                        <p class="small">Medications used in drug regimes. Includes concentration and dosage information.</p>
                        
                        <h6 class="text-info"><i class="bi bi-plus-circle"></i> Adjuvants</h6>
                        <p class="small">Drug additives and adjuvants. Used alongside primary drugs in regime management.</p>
                        
                        <h6 class="text-danger"><i class="bi bi-flag"></i> Red Flags</h6>
                        <p class="small">Insertion complications. Used to track adverse events during catheter placement.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
