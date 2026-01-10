/**
 * ACUTE PAIN SERVICE - MOBILE-RESPONSIVE ENHANCEMENTS
 */

(function() {
    'use strict';
    
    /**
     * Mobile Menu Management
     */
    function initMobileMenu() {
        const mobileSidebar = document.getElementById('mobileSidebar');
        
        if (!mobileSidebar) return;
        
        const navLinks = mobileSidebar.querySelectorAll('.nav-link');
        
        // Auto-close menu when clicking navigation links
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                const bsOffcanvas = bootstrap.Offcanvas.getInstance(mobileSidebar);
                if (bsOffcanvas) {
                    bsOffcanvas.hide();
                }
            });
        });
        
        console.log('Mobile menu initialized with', navLinks.length, 'navigation links');
    }
    
    /**
     * Responsive Table Enhancements
     */
    function initResponsiveTables() {
        const tables = document.querySelectorAll('.table');
        
        tables.forEach(table => {
            // Wrap tables in responsive container if not already wrapped
            if (!table.parentElement.classList.contains('table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
            
            // Add scroll indicator for mobile
            if (window.innerWidth < 768) {
                const wrapper = table.closest('.table-responsive');
                if (wrapper && wrapper.scrollWidth > wrapper.clientWidth) {
                    wrapper.classList.add('has-scroll');
                    
                    // Add scroll hint
                    if (!wrapper.querySelector('.scroll-hint')) {
                        const hint = document.createElement('div');
                        hint.className = 'scroll-hint text-muted small text-center mt-1';
                        hint.innerHTML = '<i class="bi bi-arrow-left-right"></i> Scroll for more';
                        wrapper.appendChild(hint);
                    }
                }
            }
        });
    }
    
    /**
     * Touch-Friendly Enhancements
     */
    function initTouchEnhancements() {
        // Add touch feedback to buttons
        const buttons = document.querySelectorAll('.btn');
        
        buttons.forEach(button => {
            button.addEventListener('touchstart', function() {
                this.classList.add('active');
            });
            
            button.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.classList.remove('active');
                }, 150);
            });
        });
    }
    
    /**
     * Dropdown Auto-positioning
     */
    function initDropdownPositioning() {
        const dropdowns = document.querySelectorAll('.dropdown-toggle');
        
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('show.bs.dropdown', function() {
                const menu = this.nextElementSibling;
                if (menu && window.innerWidth < 768) {
                    // Ensure dropdown doesn't go off-screen on mobile
                    setTimeout(() => {
                        const rect = menu.getBoundingClientRect();
                        if (rect.right > window.innerWidth) {
                            menu.classList.add('dropdown-menu-end');
                        }
                    }, 10);
                }
            });
        });
    }
    
    /**
     * Form Enhancements for Mobile
     */
    function initFormEnhancements() {
        // Auto-scroll to first error on form validation
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                
                // Find first invalid field
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                    
                    // Focus after scroll (with delay)
                    setTimeout(() => {
                        firstInvalid.focus();
                    }, 500);
                }
            }
            
            form.classList.add('was-validated');
        });
        
        // Auto-dismiss alerts after 5 seconds on mobile
        if (window.innerWidth < 768) {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        }
    }
    
    /**
     * Viewport Height Fix for iOS
     */
    function initViewportFix() {
        // Fix for iOS viewport height issue
        function setViewportHeight() {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }
        
        setViewportHeight();
        window.addEventListener('resize', setViewportHeight);
        window.addEventListener('orientationchange', setViewportHeight);
    }
    
    /**
     * Loading State Management
     */
    function initLoadingStates() {
        // Add loading state to forms on submit
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (this.checkValidity()) {
                    const submitButton = this.querySelector('button[type="submit"]');
                    if (submitButton) {
                        const originalText = submitButton.innerHTML;
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
                        
                        // Re-enable after 3 seconds as fallback
                        setTimeout(() => {
                            submitButton.disabled = false;
                            submitButton.innerHTML = originalText;
                        }, 3000);
                    }
                }
            });
        });
    }
    
    /**
     * Initialize Tooltips and Popovers
     */
    function initBootstrapComponents() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }
    
    /**
     * Smooth Scrolling
     */
    function initSmoothScrolling() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href !== '') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    }
    
    /**
     * Back to Top Button
     */
    function initBackToTop() {
        // Create back to top button for mobile
        if (window.innerWidth < 768) {
            const backToTop = document.createElement('button');
            backToTop.className = 'btn btn-primary btn-back-to-top';
            backToTop.innerHTML = '<i class="bi bi-arrow-up"></i>';
            backToTop.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 1000;
                display: none;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            `;
            
            document.body.appendChild(backToTop);
            
            // Show/hide based on scroll
            window.addEventListener('scroll', () => {
                if (window.scrollY > 300) {
                    backToTop.style.display = 'block';
                } else {
                    backToTop.style.display = 'none';
                }
            });
            
            backToTop.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    }
    
    /**
     * Initialize All Features
     */
    function init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }
        
        // Initialize all features
        initMobileMenu();
        initResponsiveTables();
        initTouchEnhancements();
        initDropdownPositioning();
        initFormEnhancements();
        initViewportFix();
        initLoadingStates();
        initBootstrapComponents();
        initSmoothScrolling();
        initBackToTop();
        
        console.log('APS Mobile-Responsive Features Initialized');
    }
    
    // Start initialization
    init();
    
})();
