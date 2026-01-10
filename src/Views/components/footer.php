<footer class="footer mt-auto py-3 bg-light border-top">
    <div class="container-fluid">
        <div class="row align-items-center">
            <!-- Left: Copyright & Version -->
            <div class="col-12 col-md-4 text-center text-md-start mb-2 mb-md-0">
                <span class="text-muted small">
                    <i class="bi bi-heart-pulse text-primary"></i>
                    &copy; <?= date('Y') ?> <?= APP_NAME ?>
                </span>
                <br class="d-md-none">
                <span class="text-muted small d-none d-md-inline">
                    | v<?= APP_VERSION ?>
                </span>
            </div>
            
            <!-- Center: Current User & Session Info (Desktop Only) -->
            <div class="col-md-4 text-center d-none d-md-block">
                <span class="text-muted small">
                    <i class="bi bi-person-badge"></i>
                    Logged in as <strong><?= e(currentUser()['username']) ?></strong>
                    <span class="d-none d-lg-inline">
                        | Session: <?= date('H:i') ?>
                    </span>
                </span>
            </div>
            
            <!-- Right: Status & Links -->
            <div class="col-12 col-md-4 text-center text-md-end">
                <span class="text-muted small">
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle"></i> System Active
                    </span>
                </span>
                <br class="d-md-none">
                <span class="text-muted small d-none d-lg-inline">
                    | <a href="#" class="text-decoration-none text-muted" data-bs-toggle="modal" data-bs-target="#aboutModal">
                        <i class="bi bi-info-circle"></i> About
                    </a>
                    | <a href="#" class="text-decoration-none text-muted" data-bs-toggle="modal" data-bs-target="#helpModal">
                        <i class="bi bi-question-circle"></i> Help
                    </a>
                </span>
            </div>
        </div>
        
        <!-- Mobile-Only: Additional Info -->
        <div class="row d-md-none mt-2">
            <div class="col-12 text-center">
                <span class="text-muted" style="font-size: 0.75rem;">
                    v<?= APP_VERSION ?> | User: <?= e(currentUser()['username']) ?> (<?= ucfirst(e(currentUser()['role'])) ?>)
                </span>
            </div>
        </div>
    </div>
</footer>

<!-- About Modal -->
<div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="aboutModalLabel">
                    <i class="bi bi-heart-pulse"></i> About <?= APP_NAME ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="fw-bold">Acute Pain Service Management System</h6>
                <p class="mb-3">
                    A comprehensive digital solution for managing epidural and peripheral nerve catheters 
                    in postoperative acute pain management.
                </p>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Version:</strong><br>
                        <?= APP_VERSION ?>
                    </div>
                    <div class="col-6">
                        <strong>Released:</strong><br>
                        <?= date('F Y') ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Current Features:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Patient Registration & Management</li>
                        <li>Catheter Insertion Documentation</li>
                        <li>Drug Regime Tracking</li>
                        <li>Pain Assessment (VNRS)</li>
                        <li>Side Effects Monitoring</li>
                        <li>Mobile-Responsive Design</li>
                    </ul>
                </div>
                
                <div class="alert alert-info mb-0">
                    <small>
                        <i class="bi bi-info-circle"></i>
                        <strong>Note:</strong> This system is designed for clinical use by authorized medical personnel only.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="helpModalLabel">
                    <i class="bi bi-question-circle"></i> Quick Help
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="fw-bold mb-3">Getting Started</h6>
                
                <div class="accordion" id="helpAccordion">
                    <!-- Patient Management -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#helpPatients">
                                <i class="bi bi-person-plus me-2"></i> Patient Management
                            </button>
                        </h2>
                        <div id="helpPatients" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <ol class="mb-0">
                                    <li>Go to <strong>Patients</strong> from the navigation menu</li>
                                    <li>Click <strong>"Register New Patient"</strong> button</li>
                                    <li>Fill in all required fields (marked with *)</li>
                                    <li>BMI auto-calculates from height and weight</li>
                                    <li>Hospital number must be unique</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Catheter Management -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#helpCatheters">
                                <i class="bi bi-file-medical me-2"></i> Catheter Management
                            </button>
                        </h2>
                        <div id="helpCatheters" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <ol class="mb-0">
                                    <li>From patient view, click <strong>"Insert New Catheter"</strong></li>
                                    <li>Select catheter category (Epidural/Peripheral Nerve/Fascial Plane)</li>
                                    <li>Choose specific type based on category</li>
                                    <li>Document insertion details and confirmations</li>
                                    <li>Record any red flags or complications</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Drug Regime -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#helpRegimes">
                                <i class="bi bi-capsule me-2"></i> Drug Regime Recording
                            </button>
                        </h2>
                        <div id="helpRegimes" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <ol class="mb-0">
                                    <li>From patient view, use <strong>"Record New Drug Regime"</strong> dropdown</li>
                                    <li>Select which active catheter to record for</li>
                                    <li>Enter POD (Post-Operative Day): 0 = surgery day</li>
                                    <li>Document drug, concentration, volume, and adjuvant</li>
                                    <li>Record VNRS scores before and 15 minutes after</li>
                                    <li>Note any side effects and their severity</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mobile Usage -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#helpMobile">
                                <i class="bi bi-phone me-2"></i> Mobile Usage
                            </button>
                        </h2>
                        <div id="helpMobile" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <ul class="mb-0">
                                    <li>Tap <strong>hamburger menu</strong> (☰) to access navigation</li>
                                    <li>Tables scroll horizontally - swipe to see more columns</li>
                                    <li>Forms are optimized for touch input</li>
                                    <li>All features work identically on mobile and desktop</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-3 mb-0">
                    <small>
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Important:</strong> Always ensure data accuracy when documenting clinical information.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
