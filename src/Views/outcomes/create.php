<div class="mb-4">
    <h1 class="h2">Record Functional Outcome</h1>
    <p class="text-muted">Screen 4: Functional Assessment & Outcomes</p>
</div>

<form id="functional-outcome-form" method="POST" action="<?= BASE_URL ?>/outcomes/store">
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
                    <label for="entry_date" class="form-label">Assessment Date <span class="text-danger">*</span></label>
                    <input type="date" 
                           class="form-control" 
                           id="entry_date" 
                           name="entry_date" 
                           value="<?= date('Y-m-d') ?>"
                           max="<?= date('Y-m-d') ?>"
                           required>
                    <div class="invalid-feedback">Please provide assessment date</div>
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
    
    <!-- Section 3: Functional Metrics -->
    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-heart-pulse"></i> Functional Assessment</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="incentive_spirometry" class="form-label">Incentive Spirometry <span class="text-danger">*</span></label>
                    <select class="form-select" id="incentive_spirometry" name="incentive_spirometry" required>
                        <option value="">Select status...</option>
                        <option value="yes">Yes - Achieving target</option>
                        <option value="partial">Partial - Below target</option>
                        <option value="unable">Unable to perform</option>
                    </select>
                    <div class="form-text">Patient's ability to use incentive spirometer</div>
                    <div class="invalid-feedback">Please select spirometry status</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="ambulation" class="form-label">Ambulation <span class="text-danger">*</span></label>
                    <select class="form-select" id="ambulation" name="ambulation" required>
                        <option value="">Select status...</option>
                        <option value="independent">Independent - Walking without assistance</option>
                        <option value="assisted">Assisted - Requires help</option>
                        <option value="bedbound">Bedbound - Unable to ambulate</option>
                    </select>
                    <div class="form-text">Patient's mobility status</div>
                    <div class="invalid-feedback">Please select ambulation status</div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="cough_ability" class="form-label">Cough Ability <span class="text-danger">*</span></label>
                    <select class="form-select" id="cough_ability" name="cough_ability" required>
                        <option value="">Select status...</option>
                        <option value="effective">Effective - Strong, productive cough</option>
                        <option value="weak">Weak - Inadequate cough effort</option>
                        <option value="unable">Unable - Cannot cough</option>
                    </select>
                    <div class="form-text">Patient's ability to cough effectively</div>
                    <div class="invalid-feedback">Please select cough ability</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 4: Oxygen Saturation -->
    <div class="card mb-3">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-activity"></i> Oxygen Saturation</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="room_air_spo2" class="form-label">Room Air SpO2 Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="room_air_spo2" name="room_air_spo2" required>
                        <option value="">Select status...</option>
                        <option value="yes">Yes - Maintaining SpO2 on room air</option>
                        <option value="no">No - Requires supplemental O2</option>
                        <option value="requires_o2">Requires O2 - Unable to maintain on room air</option>
                    </select>
                    <div class="form-text">Whether patient maintains adequate SpO2 without oxygen</div>
                    <div class="invalid-feedback">Please select SpO2 status</div>
                </div>
                
                <div class="col-md-6 mb-3" id="spo2-value-container" style="display: none;">
                    <label for="spo2_value" class="form-label">SpO2 Value (%)</label>
                    <input type="number" 
                           class="form-control" 
                           id="spo2_value" 
                           name="spo2_value" 
                           min="0" 
                           max="100"
                           placeholder="Enter SpO2 percentage">
                    <div class="form-text">Record actual SpO2 reading</div>
                    <div class="invalid-feedback">SpO2 must be between 0 and 100</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 5: Complications -->
    <div class="card mb-3">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Complications & Events</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="catheter_site_infection" class="form-label">Catheter Site Infection <span class="text-danger">*</span></label>
                    <select class="form-select" id="catheter_site_infection" name="catheter_site_infection" required>
                        <option value="">Select status...</option>
                        <option value="none">None - No signs of infection</option>
                        <option value="redness">Redness - Mild erythema</option>
                        <option value="discharge">Discharge - Purulent drainage</option>
                        <option value="swelling">Swelling - Local inflammation</option>
                    </select>
                    <div class="form-text">Signs of infection at catheter insertion site</div>
                    <div class="invalid-feedback">Please select infection status</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="sentinel_events" class="form-label">Sentinel Events <span class="text-danger">*</span></label>
                    <select class="form-select" id="sentinel_events" name="sentinel_events" required>
                        <option value="">Select event...</option>
                        <option value="none">None - No sentinel events</option>
                        <option value="fall">Fall - Patient fell</option>
                        <option value="aspiration">Aspiration - Aspiration event</option>
                        <option value="other">Other - Describe in details</option>
                    </select>
                    <div class="form-text">Serious adverse events</div>
                    <div class="invalid-feedback">Please select sentinel event status</div>
                </div>
            </div>
            
            <div class="row" id="sentinel-event-details-container" style="display: none;">
                <div class="col-12 mb-3">
                    <label for="sentinel_event_details" class="form-label">Sentinel Event Details <span class="text-danger">*</span></label>
                    <textarea class="form-control" 
                              id="sentinel_event_details" 
                              name="sentinel_event_details" 
                              rows="3"
                              placeholder="Describe the sentinel event in detail..."></textarea>
                    <div class="form-text">Provide detailed description of the event, timing, and response</div>
                    <div class="invalid-feedback">Please provide event details</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 6: Clinical Notes -->
    <div class="card mb-3">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="bi bi-chat-left-text"></i> Clinical Notes</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="clinical_notes" class="form-label">Additional Clinical Notes</label>
                <textarea class="form-control" 
                          id="clinical_notes" 
                          name="clinical_notes" 
                          rows="4"
                          placeholder="Enter any additional observations, concerns, or notes..."></textarea>
                <div class="form-text">Optional: Document any other relevant clinical information</div>
            </div>
        </div>
    </div>
    
    <!-- Submit Button -->
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-check-circle"></i> Save Functional Outcome
        </button>
        <a href="<?= BASE_URL ?>/outcomes" class="btn btn-secondary btn-lg">
            <i class="bi bi-x-circle"></i> Cancel
        </a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Catheter selection handler
    const catheterSelect = document.getElementById('catheter_id');
    const patientIdInput = document.getElementById('patient_id');
    const catheterInfo = document.getElementById('catheter-info');
    const catheterDetails = document.getElementById('catheter-details');
    
    catheterSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const patientId = selectedOption.dataset.patientId;
            const patientName = selectedOption.dataset.patientName;
            const hospitalNumber = selectedOption.dataset.hospital;
            const catheterType = selectedOption.dataset.catheterType;
            
            patientIdInput.value = patientId;
            catheterDetails.innerHTML = `
                <strong>Patient:</strong> ${patientName}<br>
                <strong>Hospital #:</strong> ${hospitalNumber}<br>
                <strong>Type:</strong> ${catheterType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
            `;
            catheterInfo.style.display = 'block';
        } else {
            patientIdInput.value = '';
            catheterInfo.style.display = 'none';
        }
    });
    
    // SpO2 status handler - show value field conditionally
    const roomAirSpo2Select = document.getElementById('room_air_spo2');
    const spo2ValueContainer = document.getElementById('spo2-value-container');
    const spo2ValueInput = document.getElementById('spo2_value');
    
    roomAirSpo2Select.addEventListener('change', function() {
        if (this.value && this.value !== '') {
            spo2ValueContainer.style.display = 'block';
            // Make SpO2 value recommended but not required
        } else {
            spo2ValueContainer.style.display = 'none';
            spo2ValueInput.value = '';
        }
    });
    
    // Sentinel events handler - show details field when event selected
    const sentinelEventsSelect = document.getElementById('sentinel_events');
    const sentinelEventDetailsContainer = document.getElementById('sentinel-event-details-container');
    const sentinelEventDetailsTextarea = document.getElementById('sentinel_event_details');
    
    sentinelEventsSelect.addEventListener('change', function() {
        if (this.value && this.value !== 'none') {
            sentinelEventDetailsContainer.style.display = 'block';
            sentinelEventDetailsTextarea.required = true;
        } else {
            sentinelEventDetailsContainer.style.display = 'none';
            sentinelEventDetailsTextarea.required = false;
            sentinelEventDetailsTextarea.value = '';
        }
    });
    
    // Form validation
    const form = document.getElementById('functional-outcome-form');
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>
