<div class="mb-4">
    <h1 class="h2">Record Drug Regime</h1>
    <p class="text-muted">Screen 3: Drug Regime & Pain Assessment</p>
</div>

<form id="drug-regime-form" method="POST" action="<?= BASE_URL ?>/regimes/store">
    <?= \Helpers\CSRF::field() ?>
    
    <!-- Section 1: Catheter & Patient Selection -->
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-file-medical"></i> Catheter & Patient Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="catheter_id" class="form-label">Select Catheter <span class="text-danger">*</span></label>
                    <select class="form-select" 
                            id="catheter_id" 
                            name="catheter_id" 
                            required
                            <?= $selectedCatheter ? 'readonly' : '' ?>>
                        <option value="">-- Select Active Catheter --</option>
                        <?php foreach ($catheters as $catheter): ?>
                        <option value="<?= $catheter['id'] ?>"
                                <?= $selectedCatheter && $selectedCatheter['id'] == $catheter['id'] ? 'selected' : '' ?>
                                data-patient-id="<?= $catheter['patient_id'] ?>"
                                data-patient-name="<?= e($catheter['patient_name']) ?>"
                                data-hospital="<?= e($catheter['hospital_number']) ?>"
                                data-catheter-type="<?= e($catheter['catheter_type']) ?>">
                            <?= e($catheter['patient_name']) ?> (HN: <?= e($catheter['hospital_number']) ?>) - 
                            <?= e(ucwords(str_replace('_', ' ', $catheter['catheter_type']))) ?>
                            (Inserted: <?= formatDate($catheter['date_of_insertion']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Please select a catheter</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Catheter Info</label>
                    <div id="catheter-info" class="alert alert-info mb-0" style="display: <?= $selectedCatheter ? 'block' : 'none' ?>;">
                        <small id="catheter-details">
                            <?php if ($selectedCatheter): ?>
                                <strong>Patient:</strong> <?= e($selectedPatient['patient_name']) ?><br>
                                <strong>Hospital #:</strong> <?= e($selectedPatient['hospital_number']) ?><br>
                                <strong>Type:</strong> <?= e(ucwords(str_replace('_', ' ', $selectedCatheter['catheter_type']))) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Hidden patient_id field -->
            <input type="hidden" id="patient_id" name="patient_id" value="<?= $selectedPatient['id'] ?? '' ?>">
        </div>
    </div>
    
    <!-- Section 2: Timeline -->
    <div class="card mb-3">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Timeline</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="entry_date" class="form-label">Entry Date <span class="text-danger">*</span></label>
                    <input type="date" 
                           class="form-control" 
                           id="entry_date" 
                           name="entry_date" 
                           value="<?= date('Y-m-d') ?>"
                           max="<?= date('Y-m-d') ?>"
                           required>
                    <div class="invalid-feedback">Please provide entry date</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="pod" class="form-label">POD (Post-Operative Day) <span class="text-danger">*</span></label>
                    <input type="number" 
                           class="form-control" 
                           id="pod" 
                           name="pod" 
                           value="<?= $suggestedPOD ?>"
                           min="0" 
                           max="30" 
                           required>
                    <div class="form-text">Day 0 = Surgery day, Day 1 = First post-op day</div>
                    <div class="invalid-feedback">POD must be between 0 and 30</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 3: Drug Details -->
    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-capsule"></i> Drug Regime</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="drug" class="form-label">Drug <span class="text-danger">*</span></label>
                    <select class="form-select" id="drug" name="drug" required>
                        <option value="">Select drug...</option>
                        <?php foreach ($drugs as $drug): ?>
                        <option value="<?= e($drug['name']) ?>"
                                data-concentration="<?= $drug['typical_concentration'] ?>"
                                data-max-dose="<?= $drug['max_dose'] ?>">
                            <?= e($drug['name']) ?> (typical: <?= $drug['typical_concentration'] ?>%)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Please select a drug</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="volume" class="form-label">Volume (ml/hr) <span class="text-danger">*</span></label>
                    <input type="number" 
                           class="form-control" 
                           id="volume" 
                           name="volume" 
                           step="0.01" 
                           min="0.1"
                           max="20"
                           required>
                    <div class="invalid-feedback">Volume must be between 0.1 and 20 ml/hr</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="concentration" class="form-label">Concentration (%) <span class="text-danger">*</span></label>
                    <input type="number" 
                           class="form-control" 
                           id="concentration" 
                           name="concentration" 
                           step="0.01" 
                           min="0.01"
                           max="1"
                           required>
                    <div class="invalid-feedback">Concentration must be between 0.01 and 1%</div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="adjuvant" class="form-label">Adjuvant (optional)</label>
                    <select class="form-select" id="adjuvant" name="adjuvant">
                        <option value="">No adjuvant</option>
                        <?php foreach ($adjuvants as $adjuvant): ?>
                        <option value="<?= e($adjuvant['name']) ?>"
                                data-typical-dose="<?= $adjuvant['typical_dose'] ?>"
                                data-unit="<?= e($adjuvant['unit']) ?>">
                            <?= e($adjuvant['name']) ?> (typical: <?= $adjuvant['typical_dose'] ?> <?= e($adjuvant['unit']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="dose" class="form-label">Adjuvant Dose</label>
                    <input type="number" 
                           class="form-control" 
                           id="dose" 
                           name="dose" 
                           step="0.01" 
                           min="0"
                           placeholder="Enter dose (auto-filled with typical dose)">
                    <div class="form-text" id="dose-unit"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 4: Pain Scores (VNRS) -->
    <div class="card mb-3">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-clipboard2-data"></i> Pain Assessment (VNRS 0-10)</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <strong>VNRS Scale:</strong> 0 = No pain, 1-3 = Mild, 4-6 = Moderate, 7-9 = Severe, 10 = Worst possible
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="text-primary">Baseline (Before Drug Regime)</h6>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label for="baseline_vnrs_static" class="form-label">VNRS at Rest <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control" 
                                   id="baseline_vnrs_static" 
                                   name="baseline_vnrs_static" 
                                   min="0" 
                                   max="10" 
                                   required>
                        </div>
                        <div class="col-6 mb-2">
                            <label for="baseline_vnrs_dynamic" class="form-label">VNRS on Movement <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control" 
                                   id="baseline_vnrs_dynamic" 
                                   name="baseline_vnrs_dynamic" 
                                   min="0" 
                                   max="10" 
                                   required>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <h6 class="text-success">15 Minutes Post-Regime</h6>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label for="vnrs_15min_static" class="form-label">VNRS at Rest <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control" 
                                   id="vnrs_15min_static" 
                                   name="vnrs_15min_static" 
                                   min="0" 
                                   max="10" 
                                   required>
                        </div>
                        <div class="col-6 mb-2">
                            <label for="vnrs_15min_dynamic" class="form-label">VNRS on Movement <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control" 
                                   id="vnrs_15min_dynamic" 
                                   name="vnrs_15min_dynamic" 
                                   min="0" 
                                   max="10" 
                                   required>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="effective_analgesia" 
                               name="effective_analgesia"
                               value="1">
                        <label class="form-check-label" for="effective_analgesia">
                            <strong class="text-success">Effective Analgesia Achieved</strong>
                            <small class="d-block text-muted">Check if pain relief is satisfactory</small>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 5: Side Effects -->
    <div class="card mb-3">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Side Effects</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="hypotension" class="form-label">Hypotension</label>
                    <select class="form-select" id="hypotension" name="hypotension">
                        <option value="none">None</option>
                        <option value="mild">Mild (SBP 90-100 mmHg)</option>
                        <option value="moderate">Moderate (SBP 80-89 mmHg)</option>
                        <option value="severe">Severe (SBP <80 mmHg)</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="bradycardia" class="form-label">Bradycardia</label>
                    <select class="form-select" id="bradycardia" name="bradycardia">
                        <option value="none">None</option>
                        <option value="mild">Mild (HR 50-60 bpm)</option>
                        <option value="moderate">Moderate (HR 40-49 bpm)</option>
                        <option value="severe">Severe (HR <40 bpm)</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="sensory_motor_deficit" class="form-label">Sensory/Motor Deficit</label>
                    <select class="form-select" id="sensory_motor_deficit" name="sensory_motor_deficit">
                        <option value="none">None</option>
                        <option value="mild">Mild (Sensory only)</option>
                        <option value="moderate">Moderate (Mild motor weakness)</option>
                        <option value="severe">Severe (Significant motor block)</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nausea_vomiting" class="form-label">Nausea/Vomiting</label>
                    <select class="form-select" id="nausea_vomiting" name="nausea_vomiting">
                        <option value="none">None</option>
                        <option value="mild">Mild (Nausea only)</option>
                        <option value="moderate">Moderate (Occasional vomiting)</option>
                        <option value="severe">Severe (Persistent vomiting)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 6: Troubleshooting & Notes -->
    <div class="card mb-3">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="bi bi-tools"></i> Troubleshooting & Clinical Notes</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="troubleshooting_activated" 
                               name="troubleshooting_activated"
                               value="1">
                        <label class="form-check-label" for="troubleshooting_activated">
                            <strong class="text-warning">Troubleshooting Activated</strong>
                            <small class="d-block text-muted">Check if interventions were required</small>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3" id="troubleshooting-notes-section" style="display: none;">
                    <label for="troubleshooting_notes" class="form-label">Troubleshooting Notes</label>
                    <textarea class="form-control" 
                              id="troubleshooting_notes" 
                              name="troubleshooting_notes" 
                              rows="2"
                              placeholder="Describe interventions taken (e.g., bolus given, catheter repositioned, etc.)"></textarea>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="clinical_notes" class="form-label">Clinical Notes</label>
                    <textarea class="form-control" 
                              id="clinical_notes" 
                              name="clinical_notes" 
                              rows="3"
                              placeholder="Any additional clinical observations or comments"></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="d-flex justify-content-between">
        <a href="<?= BASE_URL ?>/catheters<?= $selectedCatheter ? '/viewCatheter/' . $selectedCatheter['id'] : '' ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-save"></i> Record Drug Regime
        </button>
    </div>
</form>

<script>
// Catheter selection display
document.addEventListener('DOMContentLoaded', function() {
    const catheterSelect = document.getElementById('catheter_id');
    const catheterInfo = document.getElementById('catheter-info');
    const catheterDetails = document.getElementById('catheter-details');
    const patientIdField = document.getElementById('patient_id');
    
    catheterSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        
        if (this.value) {
            const patientId = option.dataset.patientId;
            const patientName = option.dataset.patientName;
            const hospital = option.dataset.hospital;
            const catheterType = option.dataset.catheterType;
            
            patientIdField.value = patientId;
            
            catheterDetails.innerHTML = `
                <strong>Patient:</strong> ${patientName}<br>
                <strong>Hospital #:</strong> ${hospital}<br>
                <strong>Type:</strong> ${catheterType}
            `;
            catheterInfo.style.display = 'block';
        } else {
            catheterInfo.style.display = 'none';
            patientIdField.value = '';
        }
    });
});

// Drug selection - auto-fill typical concentration
document.addEventListener('DOMContentLoaded', function() {
    const drugSelect = document.getElementById('drug');
    const concentrationInput = document.getElementById('concentration');
    
    drugSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.dataset.concentration) {
            concentrationInput.value = option.dataset.concentration;
        }
    });
});

// Adjuvant selection - auto-fill typical dose
document.addEventListener('DOMContentLoaded', function() {
    const adjuvantSelect = document.getElementById('adjuvant');
    const doseInput = document.getElementById('dose');
    const doseUnit = document.getElementById('dose-unit');
    
    adjuvantSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.dataset.typicalDose) {
            doseInput.value = option.dataset.typicalDose;
            doseUnit.textContent = 'Unit: ' + option.dataset.unit;
            doseInput.disabled = false;
        } else {
            doseInput.value = '';
            doseUnit.textContent = '';
            doseInput.disabled = true;
        }
    });
});

// Troubleshooting checkbox toggle
document.addEventListener('DOMContentLoaded', function() {
    const troubleshootingCheckbox = document.getElementById('troubleshooting_activated');
    const notesSection = document.getElementById('troubleshooting-notes-section');
    
    troubleshootingCheckbox.addEventListener('change', function() {
        notesSection.style.display = this.checked ? 'block' : 'none';
    });
});

// Form validation
(function() {
    'use strict';
    const form = document.getElementById('drug-regime-form');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
})();
</script>

<style>
select[readonly] {
    pointer-events: none;
    background-color: #e9ecef;
}
</style>
