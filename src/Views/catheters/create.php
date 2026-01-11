<div class="mb-4">
    <h1 class="h2">Record Catheter Insertion</h1>
    <p class="text-muted">Screen 2: Catheter Insertion Documentation</p>
</div>

<form id="catheter-insertion-form" method="POST" action="<?= BASE_URL ?>/catheters/store">
    <?= \Helpers\CSRF::field() ?>
    
    <!-- Section 1: Patient Selection -->
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-person-badge"></i> Patient Selection</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="patient_id" class="form-label">Select Patient <span class="text-danger">*</span></label>
                    <select class="form-select patient-select2" 
                            id="patient_id" 
                            name="patient_id" 
                            required
                            <?= $selectedPatient ? 'disabled' : '' ?>>
                        <?php if ($selectedPatient): ?>
                        <option value="<?= $selectedPatient['id'] ?>" selected>
                            <?= e($selectedPatient['patient_name']) ?> (HN: <?= e($selectedPatient['hospital_number']) ?>) - 
                            <?= $selectedPatient['age'] ?>y/<?= ucfirst($selectedPatient['gender']) ?>
                        </option>
                        <?php else: ?>
                        <option value="">-- Search or select a patient --</option>
                        <?php endif; ?>
                    </select>
                    <?php if ($selectedPatient): ?>
                    <input type="hidden" name="patient_id" value="<?= $selectedPatient['id'] ?>">
                    <?php endif; ?>
                    <div class="invalid-feedback">Please select a patient</div>
                    <small class="form-text text-muted">
                        <i class="bi bi-search"></i> Type to search by name or hospital number
                    </small>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Patient Info</label>
                    <div id="patient-info" class="alert alert-info mb-0" style="display: none;">
                        <small id="patient-details"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 2: Insertion Details -->
    <div class="card mb-3">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Insertion Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="date_of_insertion" class="form-label">Date of Insertion <span class="text-danger">*</span></label>
                    <input type="date" 
                           class="form-control" 
                           id="date_of_insertion" 
                           name="date_of_insertion" 
                           value="<?= date('Y-m-d') ?>"
                           max="<?= date('Y-m-d') ?>"
                           required>
                    <div class="invalid-feedback">Please provide insertion date</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="settings" class="form-label">Settings <span class="text-danger">*</span></label>
                    <select class="form-select" id="settings" name="settings" required>
                        <option value="">Select...</option>
                        <option value="elective">Elective</option>
                        <option value="emergency">Emergency</option>
                    </select>
                    <div class="invalid-feedback">Please select settings</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="performer" class="form-label">Performed By <span class="text-danger">*</span></label>
                    <select class="form-select" id="performer" name="performer" required>
                        <option value="">Select...</option>
                        <option value="consultant">Consultant</option>
                        <option value="resident">Resident</option>
                    </select>
                    <div class="invalid-feedback">Please select performer</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 3: Catheter Type (Hierarchical Selection) -->
    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-diagram-3"></i> Catheter Type Selection</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="catheter_category" class="form-label">Catheter Category <span class="text-danger">*</span></label>
                    <select class="form-select" id="catheter_category" name="catheter_category" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $key => $name): ?>
                        <option value="<?= $key ?>"><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Please select catheter category</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="catheter_type" class="form-label">Specific Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="catheter_type" name="catheter_type" required disabled>
                        <option value="">-- Select Category First --</option>
                    </select>
                    <div class="invalid-feedback">Please select specific catheter type</div>
                </div>
            </div>
            
            <div class="alert alert-warning">
                <i class="bi bi-info-circle"></i> 
                <strong>Step 1:</strong> Select catheter category (Epidural, Peripheral Nerve, or Fascial Plane)<br>
                <strong>Step 2:</strong> Select specific type based on chosen category
            </div>
        </div>
    </div>
    
    <!-- Section 4: Clinical Information -->
    <div class="card mb-3">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-clipboard2-pulse"></i> Clinical Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="indication" class="form-label">Indication <span class="text-danger">*</span></label>
                    <textarea class="form-control" 
                              id="indication" 
                              name="indication" 
                              rows="3" 
                              required
                              placeholder="Reason for catheter insertion (e.g., post-operative pain management for thoracotomy)"></textarea>
                    <div class="invalid-feedback">Please provide indication</div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirmations</label>
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="functional_confirmation" 
                               name="functional_confirmation"
                               value="1">
                        <label class="form-check-label" for="functional_confirmation">
                            <strong>Functional Confirmation</strong>
                            <small class="d-block text-muted">Sensory/motor block confirmed</small>
                        </label>
                    </div>
                    
                    <div class="form-check mt-2">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="anatomical_confirmation" 
                               name="anatomical_confirmation"
                               value="1">
                        <label class="form-check-label" for="anatomical_confirmation">
                            <strong>Anatomical Confirmation</strong>
                            <small class="d-block text-muted">USG/fluoroscopy/landmark confirmation</small>
                        </label>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="red_flags" class="form-label">Red Flags (if any)</label>
                    <select class="form-select" 
                            id="red_flags" 
                            name="red_flags[]" 
                            multiple 
                            size="7">
                        <?php foreach ($redFlags as $flag): ?>
                        <option value="<?= $flag['id'] ?>"
                                data-severity="<?= $flag['severity'] ?>"
                                class="severity-<?= $flag['severity'] ?>">
                            <?= e($flag['name']) ?>
                            <?php if ($flag['requires_immediate_action']): ?>
                                <strong>[URGENT]</strong>
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Hold Ctrl/Cmd to select multiple. Red flags are complications noted during insertion.</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="d-flex justify-content-between">
        <a href="<?= BASE_URL ?>/patients<?= $selectedPatient ? '/viewPatient/' . $selectedPatient['id'] : '' ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-save"></i> Record Catheter Insertion
        </button>
    </div>
</form>

<!-- Store catheter types as JSON for JavaScript -->
<script>
const catheterTypes = <?= json_encode($catheterTypes) ?>;
</script>

<script>
// Patient selection display
document.addEventListener('DOMContentLoaded', function() {
    const patientSelect = document.getElementById('patient_id');
    const patientInfo = document.getElementById('patient-info');
    const patientDetails = document.getElementById('patient-details');
    
    patientSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        
        if (this.value) {
            const hospital = option.dataset.hospital;
            const age = option.dataset.age;
            const gender = option.dataset.gender;
            
            patientDetails.innerHTML = `
                <strong>Hospital #:</strong> ${hospital}<br>
                <strong>Age:</strong> ${age} years<br>
                <strong>Gender:</strong> ${gender}
            `;
            patientInfo.style.display = 'block';
        } else {
            patientInfo.style.display = 'none';
        }
    });
    
    // Trigger on page load if patient pre-selected
    if (patientSelect.value) {
        patientSelect.dispatchEvent(new Event('change'));
    }
});

// Hierarchical catheter type selection
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('catheter_category');
    const typeSelect = document.getElementById('catheter_type');
    
    categorySelect.addEventListener('change', function() {
        const category = this.value;
        
        // Clear and disable type select
        typeSelect.innerHTML = '<option value="">-- Select Type --</option>';
        typeSelect.disabled = !category;
        
        if (category && catheterTypes[category]) {
            // Populate type select based on category
            Object.entries(catheterTypes[category]).forEach(([key, name]) => {
                const option = document.createElement('option');
                option.value = key;
                option.textContent = name;
                typeSelect.appendChild(option);
            });
            
            typeSelect.disabled = false;
        }
    });
});

// Red flags severity highlighting
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .severity-severe {
            background-color: #fee;
            color: #c00;
            font-weight: bold;
        }
        .severity-moderate {
            background-color: #ffc;
            color: #660;
        }
        .severity-mild {
            background-color: #eff;
            color: #006;
        }
    `;
    document.head.appendChild(style);
});

// Form validation
(function() {
    'use strict';
    const form = document.getElementById('catheter-insertion-form');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
})();
</script>

<!-- Patient Select2 Initialization (Deferred until jQuery loads) -->
<script>
<?php if (!$selectedPatient): ?>
// Wait for window load to ensure all libraries are available
(function waitForLibraries() {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined' || !window.APS) {
        // Libraries not ready yet, try again in 50ms
        setTimeout(waitForLibraries, 50);
        return;
    }
    
    // Libraries are ready, initialize
    jQuery(document).ready(function($) {
        console.log('=== CATHETER CREATE: Select2 Debug ===');
        console.log('jQuery loaded:', typeof jQuery !== 'undefined');
        console.log('Select2 loaded:', typeof $.fn.select2 !== 'undefined');
        console.log('BASE_URL:', window.BASE_URL);
        console.log('APS namespace:', typeof window.APS);
        
        const $patientSelect = $('#patient_id');
        
        if (!$patientSelect.hasClass('select2-hidden-accessible')) {
            console.log('Manually initializing patient select...');
            window.APS.initPatientSelect2('#patient_id');
        }
        
        // Add custom event handler for patient info display
        $patientSelect.on('select2:select', function (e) {
            var data = e.params.data;
            console.log('Patient selected:', data);
            if (data.hospital_number) {
                document.getElementById('patient-details').innerHTML = 
                    'HN: ' + data.hospital_number + ' | ' + 
                    data.age + 'y/' + data.gender;
                document.getElementById('patient-info').style.display = 'block';
            }
        });
    });
})();
<?php endif; ?>
</script>

<style>
/* Make disabled select look readonly but keep value */
select[disabled].select2-container {
    pointer-events: none;
}
</style>
