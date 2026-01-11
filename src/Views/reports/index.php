<div class="mb-4">
    <h1 class="h2">Reports & Analytics</h1>
    <p class="text-muted">Generate comprehensive patient and system-wide reports</p>
</div>

<div class="row">
    <!-- Individual Patient Report -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100 border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> Individual Patient Report</h5>
            </div>
            <div class="card-body">
                <p class="card-text">
                    Generate a comprehensive lifecycle report for a single patient including:
                </p>
                <ul class="small mb-3">
                    <li>Patient demographics and clinical details</li>
                    <li>Complete catheter information</li>
                    <li>Drug regime summary and commonest drugs used</li>
                    <li>Pain score analysis by POD (1, 2, 3)</li>
                    <li>Adverse effects and side effects</li>
                    <li>Functional outcomes and trends</li>
                    <li>Catheter removal details and patient satisfaction</li>
                </ul>
                
                <form method="GET" action="<?= BASE_URL ?>/reports/individual">
                    <div class="mb-3">
                        <label for="patient_select" class="form-label">Select Patient <span class="text-danger">*</span></label>
                        <select class="form-select patient-select2" id="patient_select" name="id" required>
                            <option value="">-- Search or select a patient --</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="bi bi-search"></i> Type to search by name or hospital number
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-file-earmark-text"></i> Generate Individual Report
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Consolidated Monthly Report -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100 border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Consolidated Report</h5>
            </div>
            <div class="card-body">
                <p class="card-text">
                    Generate aggregate statistics for a date range including:
                </p>
                <ul class="small mb-3">
                    <li>Total patients and gender distribution</li>
                    <li>Catheter statistics by type and category</li>
                    <li>Elective vs Emergency breakdown</li>
                    <li>Mean pain scores by POD with effectiveness rates</li>
                    <li>Adverse effects incidence and severity</li>
                    <li>Sentinel events summary</li>
                    <li>Removal statistics and patient satisfaction</li>
                    <li>Quality indicators and KPIs</li>
                </ul>
                
                <form method="GET" action="<?= BASE_URL ?>/reports/generateConsolidated">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control" 
                                   id="start_date" 
                                   name="start_date" 
                                   value="<?= date('Y-m-01') ?>"
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control" 
                                   id="end_date" 
                                   name="end_date" 
                                   value="<?= date('Y-m-t') ?>"
                                   max="<?= date('Y-m-d') ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Quick presets:
                            <button type="button" class="btn btn-link btn-sm" onclick="setThisMonth()">This Month</button> |
                            <button type="button" class="btn btn-link btn-sm" onclick="setLastMonth()">Last Month</button> |
                            <button type="button" class="btn btn-link btn-sm" onclick="setThisYear()">This Year</button>
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Generate Consolidated Report
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Quick Statistics Summary -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Report Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>Available Patients</h6>
                        <p class="mb-0">
                            <strong><?= count($patients) ?></strong> patients registered in the system
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6>Report Types</h6>
                        <p class="mb-0">
                            <strong>2</strong> report types available<br>
                            <small class="text-muted">Individual & Consolidated</small>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6>Export Options</h6>
                        <p class="mb-0">
                            Print-to-PDF available<br>
                            <small class="text-muted">Use browser print function</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setThisMonth() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    document.getElementById('start_date').value = firstDay.toISOString().split('T')[0];
    document.getElementById('end_date').value = lastDay.toISOString().split('T')[0];
}

function setLastMonth() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth() - 1, 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth(), 0);
    
    document.getElementById('start_date').value = firstDay.toISOString().split('T')[0];
    document.getElementById('end_date').value = lastDay.toISOString().split('T')[0];
}

function setThisYear() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), 0, 1);
    const lastDay = today;
    
    document.getElementById('start_date').value = firstDay.toISOString().split('T')[0];
    document.getElementById('end_date').value = lastDay.toISOString().split('T')[0];
}
</script>

<!-- Patient Select2 Initialization (Deferred until jQuery loads) -->
<script>
// Wait for window load to ensure all libraries are available
(function waitForLibraries() {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined' || !window.APS) {
        // Libraries not ready yet, try again in 50ms
        setTimeout(waitForLibraries, 50);
        return;
    }
    
    // Libraries are ready, initialize
    jQuery(document).ready(function($) {
        console.log('=== REPORTS PAGE: Select2 Debug ===');
        console.log('jQuery loaded:', typeof jQuery !== 'undefined');
        console.log('Select2 loaded:', typeof $.fn.select2 !== 'undefined');
        console.log('BASE_URL:', window.BASE_URL);
        console.log('APS namespace:', typeof window.APS);
        console.log('Patient select elements:', $('.patient-select2').length);
        
        // If auto-init didn't work, try manual init after 500ms
        setTimeout(function() {
            const $patientSelect = $('#patient_select');
            if (!$patientSelect.hasClass('select2-hidden-accessible')) {
                console.log('Auto-init failed, manually initializing...');
                window.APS.initPatientSelect2('#patient_select');
            } else {
                console.log('Select2 already initialized successfully');
            }
        }, 500);
    });
})();
</script>
