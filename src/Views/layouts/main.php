<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
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
    
    <!-- Mobile Sidebar (Offcanvas) -->
    <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
        <div class="offcanvas-header bg-primary text-white">
            <h5 class="offcanvas-title" id="mobileSidebarLabel">
                <i class="bi bi-hospital"></i> <?= APP_NAME ?>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <?php include VIEWS_PATH . '/components/navigation.php'; ?>
        </div>
    </div>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Desktop Sidebar -->
            <nav class="col-md-3 col-lg-2 d-none d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <?php include VIEWS_PATH . '/components/navigation.php'; ?>
                </div>
            </nav>
            
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 ms-sm-auto px-2 px-md-4">
                <!-- Flash Messages -->
                <?php if (\Helpers\Flash::has()): ?>
                    <div class="flash-messages mt-3">
                        <?= \Helpers\Flash::display() ?>
                    </div>
                <?php endif; ?>
                
                <!-- Content -->
                <div class="content-area mt-4 pb-5">
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
    
    <!-- Mobile Menu Auto-close on Link Click -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-close mobile menu when clicking a link
            const mobileSidebar = document.getElementById('mobileSidebar');
            
            if (mobileSidebar) {
                const sidebarLinks = mobileSidebar.querySelectorAll('.nav-link');
                
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        const bsOffcanvas = bootstrap.Offcanvas.getInstance(mobileSidebar);
                        if (bsOffcanvas) {
                            bsOffcanvas.hide();
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
