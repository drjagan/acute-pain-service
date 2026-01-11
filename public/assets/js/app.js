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

/**
 * PATIENT SELECT2 SEARCHABLE DROPDOWN
 * Reusable component for patient selection with search functionality
 */

// Ensure window.APS namespace exists
window.APS = window.APS || {};

/**
 * Initialize Patient Select2 Dropdown
 * @param {string|jQuery|HTMLElement} selector - The select element(s) to initialize
 * @param {object} options - Optional Select2 configuration overrides
 */
window.APS.initPatientSelect2 = function(selector, options = {}) {
    // Check dependencies
    if (typeof jQuery === 'undefined') {
        console.error('APS Patient Select2: jQuery is not loaded');
        return false;
    }
    
    if (typeof jQuery.fn.select2 === 'undefined') {
        console.error('APS Patient Select2: Select2 plugin is not loaded');
        return false;
    }
    
    if (!window.BASE_URL) {
        console.error('APS Patient Select2: BASE_URL is not defined');
        return false;
    }
    
    const $elements = $(selector);
    
    if ($elements.length === 0) {
        console.warn('APS Patient Select2: No elements found for selector', selector);
        return false;
    }
    
    console.log('APS Patient Select2: Initializing', $elements.length, 'element(s)');
    
    const defaults = {
        theme: 'bootstrap-5',
        placeholder: 'Search by name or hospital number',
        allowClear: true,
        minimumInputLength: 0,
        width: '100%',
        dropdownAutoWidth: true,
        ajax: {
            url: window.BASE_URL + '/patients/searchAjax',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term || '',
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                
                console.log('APS Patient Select2: AJAX response', data);
                
                if (!data.results || !Array.isArray(data.results)) {
                    console.error('APS Patient Select2: Invalid response format', data);
                    return { results: [] };
                }
                
                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination ? data.pagination.more : false
                    }
                };
            },
            error: function(xhr, status, error) {
                console.error('APS Patient Select2: AJAX error', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
            },
            cache: true
        },
        templateResult: function(patient) {
            if (patient.loading) {
                return patient.text;
            }
            
            // For placeholder or empty
            if (!patient.id || patient.id === '') {
                return patient.text;
            }
            
            // If hospital_number doesn't exist, just show text
            if (!patient.hospital_number) {
                return $('<span>' + patient.text + '</span>');
            }
            
            // Format with patient details
            var patientName = patient.text.split(' (HN:')[0];
            return $('<div class="select2-patient-result">' + 
                '<div class="fw-bold">' + patientName + '</div>' +
                '<div class="small text-muted">HN: ' + patient.hospital_number + ' | ' + 
                patient.age + 'y/' + patient.gender + '</div>' +
                '</div>');
        },
        templateSelection: function(patient) {
            return patient.text || patient.id || 'Select a patient';
        },
        language: {
            searching: function() {
                return 'Searching patients...';
            },
            noResults: function() {
                return 'No patients found';
            },
            errorLoading: function() {
                return 'Error loading patients';
            },
            inputTooShort: function() {
                return 'Type to search...';
            }
        }
    };
    
    const settings = $.extend(true, {}, defaults, options);
    
    // Initialize each element
    $elements.each(function() {
        const $elem = $(this);
        
        // Skip if already initialized
        if ($elem.hasClass('select2-hidden-accessible')) {
            console.log('APS Patient Select2: Element already initialized, skipping');
            return;
        }
        
        // Skip if disabled
        if ($elem.is(':disabled')) {
            console.log('APS Patient Select2: Element is disabled, skipping');
            return;
        }
        
        try {
            $elem.select2(settings);
            console.log('APS Patient Select2: Successfully initialized element', $elem.attr('id') || $elem.attr('name'));
        } catch (error) {
            console.error('APS Patient Select2: Initialization error', error);
        }
    });
    
    return true;
};

/**
 * Auto-initialize all .patient-select2 elements on page load
 */
jQuery(document).ready(function($) {
    console.log('APS Patient Select2: DOM ready, checking for elements...');
    
    const $patientSelects = $('.patient-select2');
    
    if ($patientSelects.length > 0) {
        console.log('APS Patient Select2: Found', $patientSelects.length, 'element(s) to initialize');
        
        // Wait a bit to ensure all scripts are loaded
        setTimeout(function() {
            window.APS.initPatientSelect2('.patient-select2');
        }, 100);
    } else {
        console.log('APS Patient Select2: No .patient-select2 elements found on this page');
    }
});

/**
 * NOTIFICATIONS SYSTEM (v1.1)
 * Real-time notification bell with auto-refresh and mark-as-read
 */

window.APS = window.APS || {};

window.APS.Notifications = (function() {
    'use strict';
    
    let unreadCount = 0;
    let autoReadTimers = {};
    let refreshInterval = null;
    
    /**
     * Initialize notification system
     */
    function init() {
        console.log('APS Notifications: Initializing...');
        
        // Load initial notifications
        loadNotifications();
        
        // Auto-refresh every 30 seconds
        refreshInterval = setInterval(loadNotifications, 30000);
        
        // Mark all as read button
        const markAllBtn = document.getElementById('markAllReadBtn');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', markAllAsRead);
        }
        
        // Dropdown shown event
        const dropdown = document.getElementById('notificationDropdown');
        if (dropdown) {
            dropdown.addEventListener('shown.bs.dropdown', function() {
                loadNotifications(); // Refresh when opened
            });
        }
        
        console.log('APS Notifications: Initialized');
    }
    
    /**
     * Load notifications from server
     */
    function loadNotifications() {
        fetch(window.BASE_URL + '/notifications/getUnread')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateBadge(data.unread_count);
                    renderNotifications(data.notifications);
                } else {
                    console.error('APS Notifications: Failed to load', data.message);
                }
            })
            .catch(error => {
                console.error('APS Notifications: Network error', error);
            });
    }
    
    /**
     * Update notification badge
     */
    function updateBadge(count) {
        unreadCount = count;
        const badge = document.getElementById('notificationBadge');
        
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    
    /**
     * Render notifications in dropdown
     */
    function renderNotifications(notifications) {
        const listEl = document.getElementById('notificationList');
        
        if (!listEl) return;
        
        // Clear existing
        listEl.innerHTML = '';
        
        if (notifications.length === 0) {
            listEl.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                    <small>No notifications</small>
                </div>
            `;
            return;
        }
        
        notifications.forEach(notification => {
            const item = createNotificationItem(notification);
            listEl.appendChild(item);
            
            // Auto-mark as read after 10 seconds if auto_dismiss is true
            if (notification.auto_dismiss && notification.is_read == 0) {
                autoReadTimers[notification.id] = setTimeout(() => {
                    markAsRead(notification.id, item);
                }, 10000);
            }
        });
    }
    
    /**
     * Create notification HTML element
     */
    function createNotificationItem(notification) {
        const div = document.createElement('div');
        div.className = 'notification-item d-flex gap-2 align-items-start';
        div.dataset.notificationId = notification.id;
        
        if (notification.is_read == 0) {
            div.classList.add('unread');
        }
        
        // Icon
        const iconClass = notification.icon || 'bi-bell';
        const colorClass = 'bg-' + notification.color;
        
        // Time ago
        const timeAgo = formatTimeAgo(notification.created_at);
        
        div.innerHTML = `
            <div class="notification-icon ${colorClass}">
                <i class="${iconClass}"></i>
            </div>
            <div class="notification-content flex-grow-1">
                <div class="notification-title">${escapeHtml(notification.title)}</div>
                <div class="notification-message">${escapeHtml(notification.message)}</div>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="notification-time">${timeAgo}</span>
                    ${notification.action_url ? 
                        `<a href="${window.BASE_URL}${notification.action_url}" class="btn btn-sm btn-outline-primary">
                            ${escapeHtml(notification.action_text || 'View')}
                        </a>` : 
                        ''}
                </div>
            </div>
        `;
        
        // Click to mark as read
        if (notification.is_read == 0) {
            div.addEventListener('click', function(e) {
                // Don't mark as read if clicking action button
                if (!e.target.classList.contains('btn')) {
                    markAsRead(notification.id, div);
                }
            });
        }
        
        return div;
    }
    
    /**
     * Mark notification as read
     */
    function markAsRead(notificationId, itemElement) {
        // Clear auto-read timer
        if (autoReadTimers[notificationId]) {
            clearTimeout(autoReadTimers[notificationId]);
            delete autoReadTimers[notificationId];
        }
        
        fetch(window.BASE_URL + '/notifications/markAsRead/' + notificationId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (itemElement) {
                    itemElement.classList.remove('unread');
                }
                updateBadge(data.unread_count);
            }
        })
        .catch(error => {
            console.error('APS Notifications: Mark as read error', error);
        });
    }
    
    /**
     * Mark all as read
     */
    function markAllAsRead() {
        fetch(window.BASE_URL + '/notifications/markAllAsRead', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateBadge(0);
                loadNotifications(); // Refresh list
            }
        })
        .catch(error => {
            console.error('APS Notifications: Mark all as read error', error);
        });
    }
    
    /**
     * Format time ago
     */
    function formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);
        
        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
        if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
        if (seconds < 604800) return Math.floor(seconds / 86400) + 'd ago';
        
        return date.toLocaleDateString();
    }
    
    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Cleanup on page unload
     */
    function cleanup() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
        
        Object.values(autoReadTimers).forEach(timer => clearTimeout(timer));
    }
    
    // Public API
    return {
        init: init,
        loadNotifications: loadNotifications,
        markAsRead: markAsRead,
        markAllAsRead: markAllAsRead,
        cleanup: cleanup
    };
})();

// Auto-initialize notifications
document.addEventListener('DOMContentLoaded', function() {
    window.APS.Notifications.init();
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    window.APS.Notifications.cleanup();
});
