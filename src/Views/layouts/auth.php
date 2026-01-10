<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Login</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Auth CSS -->
    <link href="<?= asset('css/auth.css') ?>" rel="stylesheet">
    
    <?= \Helpers\CSRF::meta() ?>
</head>
<body class="bg-light">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header text-center mb-4">
                <h2 class="mb-1"><?= APP_NAME ?></h2>
                <p class="text-muted">Acute Postoperative Pain Management</p>
            </div>
            
            <!-- Flash Messages -->
            <?php if (\Helpers\Flash::has()): ?>
                <div class="flash-messages mb-3">
                    <?= \Helpers\Flash::display() ?>
                </div>
            <?php endif; ?>
            
            <!-- Content -->
            <?= $content ?>
            
            <div class="auth-footer text-center mt-4">
                <small class="text-muted">Version <?= APP_VERSION ?></small>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
