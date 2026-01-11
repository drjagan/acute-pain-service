<?php
/**
 * Step 1: System Requirements Check
 */

$phpVersion = checkPhpVersion();
$extensions = checkRequiredExtensions();
$directories = checkWritableDirectories();

$canProceed = $phpVersion && $extensions['all_loaded'];
foreach ($directories as $dir) {
    if (!$dir['writable']) {
        $canProceed = false;
    }
}
?>

<h3 class="mb-4">
    <i class="bi bi-shield-check"></i> System Requirements Check
</h3>

<!-- PHP Version -->
<div class="card mb-3">
    <div class="card-header bg-light">
        <strong>PHP Version</strong>
    </div>
    <div class="card-body p-0">
        <div class="requirement-item">
            <div>
                <strong>PHP 8.1.0 or higher</strong>
                <div class="small text-muted">Current: <?= PHP_VERSION ?></div>
            </div>
            <span class="status-badge <?= $phpVersion ? 'status-pass' : 'status-fail' ?>">
                <?= $phpVersion ? '✓ Pass' : '✗ Fail' ?>
            </span>
        </div>
    </div>
</div>

<!-- PHP Extensions -->
<div class="card mb-3">
    <div class="card-header bg-light">
        <strong>PHP Extensions</strong>
    </div>
    <div class="card-body p-0">
        <?php
        $allExtensions = array_merge($extensions['loaded'], $extensions['missing']);
        foreach ($allExtensions as $ext):
            $isLoaded = in_array($ext, $extensions['loaded']);
        ?>
        <div class="requirement-item">
            <div>
                <strong><?= $ext ?></strong>
            </div>
            <span class="status-badge <?= $isLoaded ? 'status-pass' : 'status-fail' ?>">
                <?= $isLoaded ? '✓ Loaded' : '✗ Missing' ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Directory Permissions -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <strong>Directory Permissions</strong>
    </div>
    <div class="card-body p-0">
        <?php foreach ($directories as $name => $info): ?>
        <div class="requirement-item">
            <div>
                <strong><?= ucfirst($name) ?> Directory</strong>
                <div class="small text-muted"><?= $info['path'] ?></div>
            </div>
            <span class="status-badge <?= $info['writable'] ? 'status-pass' : 'status-fail' ?>">
                <?= $info['writable'] ? '✓ Writable' : '✗ Not Writable' ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if (!$canProceed): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle"></i>
    <strong>Requirements Not Met!</strong>
    <p class="mb-0 mt-2">Please fix the issues above before continuing with the installation.</p>
</div>
<?php else: ?>
<div class="alert alert-success">
    <i class="bi bi-check-circle"></i>
    <strong>All Requirements Met!</strong>
    <p class="mb-0 mt-2">Your server meets all the requirements. You can proceed with the installation.</p>
</div>
<?php endif; ?>

<!-- Navigation -->
<div class="d-flex justify-content-end gap-2">
    <?php if ($canProceed): ?>
    <a href="?step=2" class="btn btn-install btn-primary">
        Next Step <i class="bi bi-arrow-right"></i>
    </a>
    <?php else: ?>
    <button type="button" class="btn btn-secondary" disabled>
        Fix Issues First
    </button>
    <?php endif; ?>
</div>
