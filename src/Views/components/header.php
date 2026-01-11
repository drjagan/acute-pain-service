<header class="navbar navbar-dark sticky-top bg-primary flex-md-nowrap p-0 shadow">
    <!-- Mobile Menu Toggle (Left Side) -->
    <button class="btn btn-primary d-md-none mobile-menu-toggle-header" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
        <i class="bi bi-list fs-4"></i>
    </button>
    
    <!-- Logo/Brand (Center on Mobile, Left on Desktop) -->
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 flex-grow-1 flex-md-grow-0" href="<?= BASE_URL ?>/dashboard">
        <i class="bi bi-heart-pulse"></i> 
        <span class="d-none d-sm-inline"><?= APP_NAME ?></span>
        <span class="d-inline d-sm-none">APS</span>
    </a>
    
    <!-- User Info & Actions (Right Side) -->
    <div class="navbar-nav flex-row ms-auto">
        <!-- Notification Bell (v1.1) -->
        <div class="nav-item dropdown">
            <a class="nav-link px-3 position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                <i class="bi bi-bell fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="display: none; font-size: 0.65rem;">
                    0
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown" style="width: 380px; max-width: 95vw;">
                <!-- Notification Header -->
                <div class="dropdown-header d-flex justify-content-between align-items-center py-2 px-3 bg-light border-bottom">
                    <strong>Notifications</strong>
                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" id="markAllReadBtn" style="font-size: 0.8rem;">
                        Mark all as read
                    </button>
                </div>
                
                <!-- Notification List -->
                <div id="notificationList" class="notification-list" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                        <small>No notifications</small>
                    </div>
                </div>
                
                <!-- View All Link -->
                <div class="dropdown-divider m-0"></div>
                <a class="dropdown-item text-center small py-2 text-primary" href="<?= BASE_URL ?>/notifications">
                    <i class="bi bi-arrow-right-circle me-1"></i> View all notifications
                </a>
            </div>
        </div>
        
        <!-- User Info (Desktop Only) -->
        <div class="nav-item text-nowrap d-none d-lg-flex align-items-center px-3 border-end border-light border-opacity-25">
            <i class="bi bi-person-circle me-2"></i>
            <span class="small">
                <?php 
                $user = currentUser();
                echo e($user['first_name'] . ' ' . $user['last_name']);
                ?>
                <br>
                <span class="text-light opacity-75" style="font-size: 0.75rem;">
                    <?= ucfirst(e($user['role'])) ?>
                </span>
            </span>
        </div>
        
        <!-- User Dropdown (Tablet/Mobile) -->
        <div class="nav-item dropdown d-lg-none">
            <a class="nav-link dropdown-toggle px-3" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li class="dropdown-header">
                    <strong>
                        <?php 
                        $user = currentUser();
                        echo e($user['first_name'] . ' ' . $user['last_name']);
                        ?>
                    </strong>
                    <br>
                    <small class="text-muted"><?= ucfirst(e($user['role'])) ?></small>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="<?= BASE_URL ?>/auth/logout">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Logout Button (Desktop Only) -->
        <div class="nav-item text-nowrap d-none d-lg-block">
            <a class="nav-link px-3" href="<?= BASE_URL ?>/auth/logout">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</header>
