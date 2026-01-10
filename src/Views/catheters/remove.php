<div class="mb-4">
    <h1 class="h2">Document Catheter Removal</h1>
    <p class="text-muted">Screen 5: Catheter Removal Documentation</p>
</div>

<!-- Catheter Information Summary -->
<div class="card mb-3 border-info">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Catheter Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <strong>Patient:</strong><br>
                <?= e($catheter['patient_name']) ?><br>
                <small class="text-muted">HN: <?= e($catheter['hospital_number']) ?></small>
            </div>
            <div class="col-md-3">
                <strong>Catheter Type:</strong><br>
                <span class="badge bg-primary">
                    <?= e(ucwords(str_replace('_', ' ', $catheter['catheter_category']))) ?>
                </span><br>
                <?= e(ucwords(str_replace('_', ' ', $catheter['catheter_type']))) ?>
            </div>
            <div class="col-md-3">
                <strong>Insertion Date:</strong><br>
                <?= formatDate($catheter['date_of_insertion']) ?><br>
                <small class="text-muted">(<?= $catheterDays ?> days ago)</small>
            </div>
            <div class="col-md-3">
                <strong>Current Status:</strong><br>
                <span class="badge bg-<?= $catheter['status'] === 'active' ? 'success' : 'warning' ?>">
                    <?= ucfirst($catheter['status']) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<form id="removal-form" method="POST" action="<?= BASE_URL ?>/catheters/storeRemoval/<?= $catheter['id'] ?>">
    <?= \Helpers\CSRF::field() ?>
    
    <!-- Section 1: Removal Details -->
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-calendar-x"></i> Removal Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="date_of_removal" class="form-label">Date of Removal <span class="text-danger">*</span></label>
                    <input type="date" 
                           class="form-control" 
                           id="date_of_removal" 
                           name="date_of_removal" 
                           value="<?= date('Y-m-d') ?>"
                           max="<?= date('Y-m-d') ?>"
                           required>
                    <div class="invalid-feedback">Please provide removal date</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="number_of_catheter_days" class="form-label">Number of Catheter Days <span class="text-danger">*</span></label>
                    <input type="number" 
                           class="form-control" 
                           id="number_of_catheter_days" 
                           name="number_of_catheter_days" 
                           value="<?= $catheterDays ?>"
                           min="0" 
                           max="30" 
                           required
                           readonly>
                    <div class="form-text">Auto-calculated from insertion date</div>
                    <div class="invalid-feedback">Catheter days must be between 0 and 30</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Catheter Tip Status</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="catheter_tip_intact" 
                               name="catheter_tip_intact"
                               checked>
                        <label class="form-check-label" for="catheter_tip_intact">
                            Catheter tip intact on removal
                        </label>
                    </div>
                    <div class="form-text">Uncheck if catheter tip was not intact</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 2: Indication for Removal -->
    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Indication for Removal</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="indication" class="form-label">Primary Indication <span class="text-danger">*</span></label>
                    <select class="form-select" id="indication" name="indication" required>
                        <option value="">Select indication...</option>
                        <?php foreach ($indications as $value => $label): ?>
                        <option value="<?= $value ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Please select an indication</div>
                </div>
                
                <div class="col-md-6 mb-3" id="indication-notes-container" style="display: none;">
                    <label for="indication_notes" class="form-label">Indication Notes <span class="text-danger" id="notes-required">*</span></label>
                    <textarea class="form-control" 
                              id="indication_notes" 
                              name="indication_notes" 
                              rows="3"
                              placeholder="Describe the specific reason for removal..."></textarea>
                    <div class="form-text">Required when indication is "Other" or provide additional details</div>
                    <div class="invalid-feedback">Please provide indication details</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 3: Complications & Clinical Assessment -->
    <div class="card mb-3">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Complications & Assessment</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="removal_complications" class="form-label">Removal Complications</label>
                <textarea class="form-control" 
                          id="removal_complications" 
                          name="removal_complications" 
                          rows="3"
                          placeholder="Document any complications encountered during or after removal (leave blank if none)"></textarea>
                <div class="form-text">Document any bleeding, nerve injury, infection, or other complications</div>
            </div>
            
            <div class="mb-3">
                <label for="patient_satisfaction" class="form-label">Patient Satisfaction</label>
                <select class="form-select" id="patient_satisfaction" name="patient_satisfaction">
                    <option value="">Select satisfaction level...</option>
                    <option value="excellent">Excellent - Very satisfied with pain management</option>
                    <option value="good">Good - Satisfied with pain management</option>
                    <option value="fair">Fair - Somewhat satisfied</option>
                    <option value="poor">Poor - Not satisfied</option>
                </select>
                <div class="form-text">Patient's overall satisfaction with catheter-based pain management</div>
            </div>
        </div>
    </div>
    
    <!-- Section 4: Final Notes -->
    <div class="card mb-3">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="bi bi-chat-left-text"></i> Final Clinical Notes</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="final_notes" class="form-label">Final Assessment & Recommendations</label>
                <textarea class="form-control" 
                          id="final_notes" 
                          name="final_notes" 
                          rows="4"
                          placeholder="Document final clinical assessment, patient instructions, follow-up recommendations..."></textarea>
                <div class="form-text">Optional: Overall summary and any follow-up care instructions</div>
            </div>
        </div>
    </div>
    
    <!-- Warning Notice -->
    <div class="alert alert-warning mb-3">
        <i class="bi bi-exclamation-triangle"></i> <strong>Important:</strong> 
        Documenting removal will automatically update the catheter status to "Removed". 
        This action documents the official removal of the catheter from the patient.
    </div>
    
    <!-- Submit Button -->
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-danger btn-lg">
            <i class="bi bi-check-circle"></i> Document Removal
        </button>
        <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $catheter['id'] ?>" class="btn btn-secondary btn-lg">
            <i class="bi bi-x-circle"></i> Cancel
        </a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate catheter days when removal date changes
    const removalDateInput = document.getElementById('date_of_removal');
    const catheterDaysInput = document.getElementById('number_of_catheter_days');
    const insertionDate = new Date('<?= $catheter['date_of_insertion'] ?>');
    
    removalDateInput.addEventListener('change', function() {
        const removalDate = new Date(this.value);
        if (removalDate >= insertionDate) {
            const diffTime = Math.abs(removalDate - insertionDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            catheterDaysInput.value = diffDays;
        }
    });
    
    // Show/hide indication notes based on selection
    const indicationSelect = document.getElementById('indication');
    const indicationNotesContainer = document.getElementById('indication-notes-container');
    const indicationNotesTextarea = document.getElementById('indication_notes');
    const notesRequired = document.getElementById('notes-required');
    
    indicationSelect.addEventListener('change', function() {
        if (this.value === 'other') {
            indicationNotesContainer.style.display = 'block';
            indicationNotesTextarea.required = true;
            notesRequired.style.display = 'inline';
        } else if (this.value) {
            indicationNotesContainer.style.display = 'block';
            indicationNotesTextarea.required = false;
            notesRequired.style.display = 'none';
        } else {
            indicationNotesContainer.style.display = 'none';
            indicationNotesTextarea.required = false;
            indicationNotesTextarea.value = '';
        }
    });
    
    // Form validation
    const form = document.getElementById('removal-form');
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>
