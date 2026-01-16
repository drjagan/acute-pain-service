<div class="mb-4">
    <h1 class="h2">Edit Catheter Details</h1>
    <p class="text-muted">Modify catheter insertion information</p>
</div>

<form id="catheter-edit-form" method="POST" action="<?= BASE_URL ?>/catheters/update/<?= $catheter['id'] ?>">
    <?= \Helpers\CSRF::field() ?>
    
    <!-- Section 1: Patient Selection (Read-only in edit mode) -->
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-person-badge"></i> Patient Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                    <select class="form-select" 
                            id="patient_id" 
                            name="patient_id" 
                            required
                            disabled>
                        <?php 
                        $selectedPatient = null;
                        foreach ($patients as $patient) {
                            if ($patient['id'] == $catheter['patient_id']) {
                                $selectedPatient = $patient;
                                break;
                            }
                        }
                        ?>
                        <?php if ($selectedPatient): ?>
                        <option value="<?= $selectedPatient['id'] ?>" selected>
                            <?= e($selectedPatient['patient_name']) ?> (HN: <?= e($selectedPatient['hospital_number']) ?>) - 
                            <?= $selectedPatient['age'] ?>y/<?= ucfirst($selectedPatient['gender']) ?>
                        </option>
                        <?php endif; ?>
                    </select>
                    <input type="hidden" name="patient_id" value="<?= $catheter['patient_id'] ?>">
                    <small class="form-text text-muted">
                        <i class="bi bi-info-circle"></i> Patient cannot be changed after catheter insertion
                    </small>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Patient Info</label>
                    <div class="alert alert-info mb-0">
                        <small>
                            <strong>HN:</strong> <?= e($selectedPatient['hospital_number']) ?><br>
                            <strong>Age:</strong> <?= $selectedPatient['age'] ?> years<br>
                            <strong>Gender:</strong> <?= ucfirst($selectedPatient['gender']) ?>
                        </small>
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
                           value="<?= e($catheter['date_of_insertion']) ?>"
                           max="<?= date('Y-m-d') ?>"
                           required>
                    <div class="invalid-feedback">Please provide insertion date</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="settings" class="form-label">Settings <span class="text-danger">*</span></label>
                    <select class="form-select" id="settings" name="settings" required>
                        <option value="">Select...</option>
                        <option value="elective" <?= $catheter['settings'] === 'elective' ? 'selected' : '' ?>>Elective</option>
                        <option value="emergency" <?= $catheter['settings'] === 'emergency' ? 'selected' : '' ?>>Emergency</option>
                    </select>
                    <div class="invalid-feedback">Please select settings</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="performer" class="form-label">Performed By <span class="text-danger">*</span></label>
                    <select class="form-select" id="performer" name="performer" required>
                        <option value="">Select...</option>
                        <option value="consultant" <?= $catheter['performer'] === 'consultant' ? 'selected' : '' ?>>Consultant</option>
                        <option value="resident" <?= $catheter['performer'] === 'resident' ? 'selected' : '' ?>>Resident</option>
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
                        <option value="<?= $key ?>" <?= $catheter['catheter_category'] === $key ? 'selected' : '' ?>>
                            <?= $name ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Please select catheter category</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="catheter_type" class="form-label">Specific Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="catheter_type" name="catheter_type" required>
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
                <div class="col-md-6 mb-3">
                    <label for="indication_id" class="form-label">Primary Indication <span class="text-danger">*</span></label>
                    <select class="form-select" id="indication_id" name="indication_id" required>
                        <option value="">Select indication...</option>
                        <?php 
                        // Group by common/other
                        $common = array_filter($catheterIndications, fn($i) => $i['is_common']);
                        $other = array_filter($catheterIndications, fn($i) => !$i['is_common']);
                        $currentId = $catheter['indication_id'] ?? '';
                        ?>
                        <?php if (!empty($common)): ?>
                        <optgroup label="Common Indications">
                            <?php foreach ($common as $indication): ?>
                            <option value="<?= $indication['id'] ?>" <?= $currentId == $indication['id'] ? 'selected' : '' ?>>
                                <?= e($indication['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endif; ?>
                        <?php if (!empty($other)): ?>
                        <optgroup label="Other Indications">
                            <?php foreach ($other as $indication): ?>
                            <option value="<?= $indication['id'] ?>" <?= $currentId == $indication['id'] ? 'selected' : '' ?>>
                                <?= e($indication['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endif; ?>
                    </select>
                    <div class="invalid-feedback">Please select an indication</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="indication_notes" class="form-label">Indication Notes</label>
                    <textarea class="form-control" 
                              id="indication_notes" 
                              name="indication_notes" 
                              rows="3" 
                              placeholder="Additional details about the indication (optional)"><?= e($catheter['indication_notes'] ?? '') ?></textarea>
                    <div class="form-text">Provide specific details if needed</div>
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
                               value="1"
                               <?= $catheter['functional_confirmation'] ? 'checked' : '' ?>>
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
                               value="1"
                               <?= $catheter['anatomical_confirmation'] ? 'checked' : '' ?>>
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
                        <?php 
                        $selectedRedFlags = is_array($catheter['red_flags']) ? $catheter['red_flags'] : [];
                        ?>
                        <?php foreach ($redFlags as $flag): ?>
                        <option value="<?= $flag['id'] ?>"
                                data-severity="<?= $flag['severity'] ?>"
                                class="severity-<?= $flag['severity'] ?>"
                                <?= in_array($flag['id'], $selectedRedFlags) ? 'selected' : '' ?>>
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
        <a href="<?= BASE_URL ?>/catheters/viewCatheter/<?= $catheter['id'] ?>" class="btn btn-secondary">
            <i class="bi bi-x-circle"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-save"></i> Update Catheter Details
        </button>
    </div>
</form>

<!-- Store catheter types and current values as JSON for JavaScript -->
<script>
const catheterTypes = <?= json_encode($catheterTypes) ?>;
const currentCategory = <?= json_encode($catheter['catheter_category']) ?>;
const currentType = <?= json_encode($catheter['catheter_type']) ?>;
</script>

<script>
// Hierarchical catheter type selection
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('catheter_category');
    const typeSelect = document.getElementById('catheter_type');
    
    function populateTypeSelect(category, selectedType = null) {
        // Clear and disable type select
        typeSelect.innerHTML = '<option value="">-- Select Type --</option>';
        typeSelect.disabled = !category;
        
        if (category && catheterTypes[category]) {
            // Populate type select based on category
            Object.entries(catheterTypes[category]).forEach(([key, name]) => {
                const option = document.createElement('option');
                option.value = key;
                option.textContent = name;
                if (selectedType && key === selectedType) {
                    option.selected = true;
                }
                typeSelect.appendChild(option);
            });
            
            typeSelect.disabled = false;
        }
    }
    
    categorySelect.addEventListener('change', function() {
        populateTypeSelect(this.value);
    });
    
    // Initialize with current values on page load
    if (currentCategory) {
        populateTypeSelect(currentCategory, currentType);
    }
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
    const form = document.getElementById('catheter-edit-form');
    
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
/* Make disabled select look readonly but keep value */
select[disabled] {
    pointer-events: none;
    background-color: #e9ecef;
}
</style>
