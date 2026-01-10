<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= asset('css/main.css') ?>" rel="stylesheet">
    
    <?= \Helpers\CSRF::meta() ?>
</head>
<body>
    <?php include VIEWS_PATH . '/components/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <nav class="col-md-2 d-md-block bg-light sidebar">
                <?php include VIEWS_PATH . '/components/navigation.php'; ?>
            </nav>
            
            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <!-- Flash Messages -->
                <?php if (\Helpers\Flash::has()): ?>
                    <div class="flash-messages mt-3">
                        <?= \Helpers\Flash::display() ?>
                    </div>
                <?php endif; ?>
                
                <!-- Content -->
                <div class="content-area mt-4">
                    <?= $content ?>
                </div>
            </main>
        </div>
    </div>
    
    <?php include VIEWS_PATH . '/components/footer.php'; ?>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
